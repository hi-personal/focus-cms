#!/bin/bash

# MySQL kapcsolódási adatok
MYSQL_USER="istvan"
MYSQL_PASSWORD="istvan"
MYSQL_DB="breeze"

# SQL parancsok futtatása a MySQL-ben
mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -D "$MYSQL_DB" -e "
SET FOREIGN_KEY_CHECKS = 0;
SET @tables = (SELECT GROUP_CONCAT(table_name) FROM information_schema.tables WHERE table_schema = '$MYSQL_DB');
SET @sql = CONCAT('DROP TABLE IF EXISTS ', @tables);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET FOREIGN_KEY_CHECKS = 1;
"

echo "Minden tábla törölve a $MYSQL_DB adatbázisból."
