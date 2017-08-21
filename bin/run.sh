#!/bin/bash

docker-compose up -d

php runtest.php

docker-compose down -v