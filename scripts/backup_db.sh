#!/bin/sh
set -eu
: "${DB_NAME:=archmage}"
: "${DB_USER:=archmage}"
: "${DB_PASS:=archmage}"
: "${DB_HOST:=db}"

ts="$(date +%Y%m%d_%H%M%S)"
mkdir -p /var/www/html/backups
mysqldump -h "$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "/var/www/html/backups/${DB_NAME}_${ts}.sql"
echo "Backup done: backups/${DB_NAME}_${ts}.sql"
