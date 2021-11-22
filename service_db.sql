create table sensor_dht(
    id bigint auto_increment primary key,
    temperature float not null,
    humidity float not null,
    cloud boolean,
    created_at timestamp
);
