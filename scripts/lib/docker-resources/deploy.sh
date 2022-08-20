#!/bin/bash
#
# This script is run when the Drupal docker container is ready. It prepares
# an environment for development or testing, which contains a full Drupal
# installation with a running website.
#
set -e

TRIES=20
echo "Server: Will try to connect to MySQL container until it is up. This can take up to $TRIES seconds if the container has just been spun up."
OUTPUT="ERROR"
for i in $(seq 1 "$TRIES");
do
  OUTPUT=$(echo 'show databases'|{ mysql -h mysql -u root --password=drupal 2>&1 || true; })
  if [[ "$OUTPUT" == *"ERROR"* ]]; then
    sleep 1
    echo "Try $i of $TRIES. MySQL container is not available yet. Should not be long..."
  else
    echo "MySQL is up! Moving on..."
    break;
  fi
done

if [[ "$OUTPUT" == *"ERROR"* ]]; then
  >&2 echo "Server could not connect to MySQL after $TRIES tries. Abandoning."
  >&2 echo "$OUTPUT"
  exit 1
fi

drush si -y --db-url "mysql://root:drupal@mysql/drupal" minimal
drush en -y webhook_receiver_example
drush ev "\Drupal::service('webhook_receiver')->instructions()"
