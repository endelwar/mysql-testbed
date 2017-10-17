#!/bin/bash

__DIR__="$(cd "$(dirname "${0}")"; echo "$(pwd)")"

#docker-compose pull

docker-compose up -d && \
sleep 10 && \
php ${__DIR__}/../src/runtest.php && \
docker-compose down