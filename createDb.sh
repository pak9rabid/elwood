#!/bin/bash

rm /etc/elwood/elwood.db
sqlite3 /etc/elwood/elwood.db < /etc/elwood/createdb.sql
chown root:www-data /etc/elwood/elwood.db
chmod 660 /etc/elwood/elwood.db
