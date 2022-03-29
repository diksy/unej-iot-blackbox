import os
import requests
import json
import mariadb
API      = "http://192.168.10.63:8888/unej-blackbox/public/api"
email    = "diksy@unej.ac.id"
password = "secretxx"

# read token from file
if os.path.exists("token.txt"):
    file  = open("token.txt", "rt")
    token = file.read()

# set token if not exists
else:
    try:
        token = requests.post(API + "/signin", data={ "email": email, "password": password })
    except Exception as e:
        raise e
    token = token.json()["authorization"]
    file  = open("token.txt", "wt")
    file.write(token)
file.close()

# connect to local DB
try:
    db = mariadb.connect(
        user     = "unej",
        password = "unejblackbox",
        host     = "localhost",
        port     = 3306,
        database = "blackbox"
    )
except mariadb.Error as edb:
    raise edb

# get not yet uploaded data
sql = db.cursor()
try:
    sql.execute("SELECT id, temperature, humidity, created_at FROM sensor_dht WHERE cloud IS NULL ORDER BY created_at ASC LIMIT 10")
except mariadb.Error as edb:
    db.close()
    raise edb

# send not yet uploaded data to web service
dht = []
for (id, temperature, humidity, created_at) in sql:
    try:
        response = requests.post(API + "/dht", data={ "temperature": temperature, "humidity": humidity, "created_at": created_at }, headers={ "Authorization": token })
        if response.status_code == 401:
            os.remove("token.txt")
        else:
            dht.append(id)
    except Exception as e:
        print(f"Upload failed for data {created_at}.")

# update cloud status on local DB
db.autocommit = True
sql           = db.cursor()
for id in dht:
    try:
        sql.execute(f"UPDATE sensor_dht SET cloud = 1 WHERE id = {id}")
    except mariadb.Error as edb:
        print(edb)

# close opened instance
db.close()
