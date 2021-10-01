#!/bin/sh

mkdir -m 777 -p bootstrap/cache
mkdir -m 777 -p storage/logs
mkdir -m 777 -p storage/app/public
mkdir -m 777 -p storage/framework/cache
mkdir -m 777 -p storage/framework/views
mkdir -m 777 -p storage/framework/sessions

chmod -R 777 storage
chmod -R 777 bootstrap/cache

if [ -d '/tmp/symfony-cache' ]; then
  chmod -R 777 /tmp/symfony-cache
fi

