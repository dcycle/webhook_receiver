#!/bin/bash
#
# Run tests, meant to be run on CirlceCI.
#
set -e

echo '=> Run fast tests.'
./scripts/test.sh

echo '=> Deploy a Drupal 9 environment.'
./scripts/deploy.sh 9

echo '=> Tests on Drupal 9 environment.'
./scripts/test-running-environment.sh

echo '=> Destroy the Drupal 9 environment.'
./scripts/destroy.sh

echo '=> Deploy a Drupal 10 environment.'
./scripts/deploy.sh 10

echo '=> Tests on Drupal 10 environment.'
./scripts/test-running-environment.sh

echo '=> Destroy the Drupal 10 environment.'
./scripts/destroy.sh

echo '=> Done with continuous integration tests!'
echo '=>'
