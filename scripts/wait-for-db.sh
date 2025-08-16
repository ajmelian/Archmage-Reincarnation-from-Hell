#!/bin/sh
set -eu
echo "Waiting for DB..."
for i in $(seq 1 60); do
  if mysql -h "${DB_HOST:-db}" -u"${DB_USER:-archmage}" -p"${DB_PASS:-archmage}" -e "SELECT 1" >/dev/null 2>&1; then
    echo "DB ready"; exit 0
  fi
  sleep 2
done
echo "DB not ready in time"; exit 1
