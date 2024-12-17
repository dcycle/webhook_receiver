#!/bin/bash
#
# Run tests, meant to be run on CirlceCI.
#
set -e

# echo '=> Run fast tests.'
# ./scripts/test.sh

# echo '=> Deploy a Drupal 11 environment.'
# ./scripts/deploy.sh 11

# echo '=> Tests on Drupal 11 environment.'
# ./scripts/test-running-environment.sh

# echo '=> Destroy the Drupal 11 environment.'
# ./scripts/destroy.sh

# echo '=> Deploy a Drupal 10 environment.'
# ./scripts/deploy.sh 10

# echo '=> Tests on Drupal 10 environment.'
# ./scripts/test-running-environment.sh

# echo '=> Destroy the Drupal 10 environment.'
# ./scripts/destroy.sh

echo '=> Deploy a Drupal 11 environment without Sodium.'
echo '   (PHP Sodium is an encryption library which is not available on all'
echo '   systems. For example see'
echo '   https://www.reddit.com/r/PHP/comments/1afvx0c/redhat_and_libsodium/)'
./scripts/deploy.sh 11-no-sodium

echo '=> Tests on Drupal 11 environment without Sodium.'
./scripts/test-running-environment.sh

echo '=> Destroy the Drupal 11 environment without Sodium.'
./scripts/destroy.sh

echo '=> Done with continuous integration tests!'
echo '=>'
