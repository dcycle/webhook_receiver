#!/bin/bash
#
# Make sure we can POST to this.
#
set -e

WEBPATH=$(docker-compose exec -T drupal /bin/bash -c "drush ev 'webhook_receiver()->printTestPath();'")
DOMAIN=$(docker-compose port drupal 80)

FULL=http://"$DOMAIN""$WEBPATH"

echo "Running POST on $FULL"

curl -i -X POST \
"$FULL" \
-H 'Accept: application/json' \
-H 'Content-Type: application/json' \
-d '{
"This must be set!": "It works!!!"
}'
