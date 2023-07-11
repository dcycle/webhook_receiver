#!/bin/bash
#
# Fix PHP code.
#
set -e

echo 'Fixing PHP style errors with https://github.com/dcycle/docker-php-lint'
echo ''

docker run --rm \
  --entrypoint=/vendor/bin/phpcbf \
  -v "$(pwd)":/code dcycle/php-lint:3 \
  --extensions=php,module,install,inc --standard=DrupalPractice /code \
  || true
docker run --rm \
  --entrypoint=/vendor/bin/phpcbf \
  -v "$(pwd)":/code dcycle/php-lint:3 \
  --extensions=php,module,install,inc --standard=Drupal /code \
  || true
