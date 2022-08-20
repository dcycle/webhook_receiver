#!/bin/bash
#
# Run some checks on a running environment
#
set -e

echo '=> Running tests on a running environment.'
URL="$(docker-compose port drupal 80)"
TOKEN="$(docker-compose exec -T drupal /bin/bash -c 'drush ev "webhook_receiver_token()"')"

echo 'Make sure values returned make sense with base and submodules enabled'
curl "$URL/admin/reports/status/expose/not-the-right-token" | grep 'Access denied'
curl "$URL/admin/reports/status/expose/$TOKEN" | grep '"status":"issues found; please check"'

docker-compose exec -T drupal /bin/bash -c 'drush pmu -y webhook_receiver_details webhook_receiver_ignore webhook_receiver_severity'

echo 'Make sure values returned make sense with only the base module enabled'
curl "$URL/admin/reports/status/expose/not-the-right-token" | grep 'Access denied'
curl "$URL/admin/reports/status/expose/$TOKEN" | grep '"status":"issues found; please check"'

docker-compose exec -T drupal /bin/bash -c 'drush sset webhook_receiver_token some-token'
echo 'Make sure it is possible to uninstall the module'
docker-compose exec -T drupal /bin/bash -c 'drush pmu -y webhook_receiver'
echo 'Once module is uninstalled, state variable should be deleted'
docker-compose exec -T drupal /bin/bash -c 'drush sget webhook_receiver_token' | grep -v some-token
