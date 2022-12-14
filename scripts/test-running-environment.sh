#!/bin/bash
#
# Run some checks on a running environment
#
set -e

./scripts/request-response-tests.sh
./scripts/defer-test.sh
./scripts/post-test.sh

echo 'Make sure it is possible to uninstall the module'
docker-compose exec -T drupal /bin/bash -c 'drush pmu -y webhook_receiver'
