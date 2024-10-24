#!/bin/bash
#
# Lint PHP code.
#
set -e

echo 'Linting PHP files'
echo 'If you are getting a false negative, use:'
echo 'Linting PHP files with https://github.com/dcycle/docker-php-lint'
echo 'If you are getting a false positive, use:'
echo ''
echo '// @codingStandardsIgnoreStart'
echo '...'
echo '// @codingStandardsIgnoreEnd'
echo ''
echo 'To automatically fix errors, you can run:'
echo ''
echo './scripts/lint-php-fix.sh'
echo ''

docker run --rm \
  -v "$(pwd)":/code dcycle/php-lint:3 \
  --extensions=php,module,install,inc --standard=DrupalPractice /code
docker run --rm \
  -v "$(pwd)":/code dcycle/php-lint:3 \
  --extensions=php,module,install,inc --standard=Drupal /code
