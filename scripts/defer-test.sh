#!/bin/bash
#
# Run tests on the defer module
#
set -e

docker-compose exec -T drupal /bin/bash -c 'drush pmu -y webhook_receiver_defer || true'
docker-compose exec -T drupal /bin/bash -c 'drush en -y dblog'
docker-compose exec -T drupal /bin/bash -c 'drush watchdog-delete all -y'
docker-compose exec -T drupal /bin/bash -c 'drush en -y webhook_receiver_defer'
docker-compose exec -T drupal /bin/bash -c "drush ev '\Drupal::service("'"'"webhook_receiver_defer.selftest"'"'")->run()'"
