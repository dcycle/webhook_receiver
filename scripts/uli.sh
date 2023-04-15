#!/bin/bash
#
# Get a login link for the environment.
#
set -e

docker-compose exec -T drupal /bin/bash -c "drush -l $(docker-compose port webserver 80) uli"
