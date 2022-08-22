#!/bin/bash
#
# Run some checks on a running environment
#
set -e

docker-compose exec -T drupal /bin/bash -c "drush ev '\Drupal::service("'"'"webhook_receiver.request_response_test"'"'")->run("'"'"webhook_receiver_example"'"'")'"
