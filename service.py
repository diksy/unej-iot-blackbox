import time
import board
import adafruit_dht
import mariadb

# initialize raspberry gpio
dhtDevice = adafruit_dht.DHT22(board.D4, use_pulseio=False)

# create one minute max looping
for i in range(30):
    try:
        # get sensor result
        temperature = dhtDevice.temperature
        humidity    = dhtDevice.humidity
        # connect to local DB
        try:
            db = mariadb.connect(
                user     = "unej",
                password = "unejblackbox",
                host     = "localhost",
                port     = 3306,
                database = "blackbox"
            )
        # error handler of local DB
        except mariadb.Error as edb:
            dhtDevice.exit()
            raise edb
        # add sensor result to DB
        db.autocommit = True
        sql           = db.cursor()
        try:
            sql.execute(
                "INSERT INTO sensor_dht VALUES(NULL, ?, ?, NULL, NOW())",
                (temperature, humidity)
            )
        # error handler of localDB
        except mariadb.Error as edb:
            db.close()
            dhtDevice.exit()
            raise edb
        # close local DB connection
        db.close()

    # error handler of sensor
    except RuntimeError as e:
        time.sleep(2.0)
        continue
    except Exception as e:
        dhtDevice.exit()
        raise e

    # exit if success
    dhtDevice.exit()
    break
