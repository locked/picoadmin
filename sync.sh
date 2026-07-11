#!/bin/sh
while inotifywait -r -e create,delete,modify,move /home/locked/admin/picoadmin/; do
  echo "sync"
  rsync -av /home/locked/admin/picoadmin/ --exclude .env --exclude storage/framework yuki.lunasys.fr:/var/www/html/picoadmin/
done
