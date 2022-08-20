#!/bin/bash
#
# Destroy the environment.
#
set -e

docker-compose down -v
docker network rm webhook_receiver_default
