#!/usr/bin/env bash

action=$1

case $action in
"check")
  composer update
  composer install
  ./vendor/bin/phpcs -s omnisend-for-ninja-forms
  ;;
"fix")
  composer update
  composer install
  ./vendor/bin/phpcbf omnisend-for-ninja-forms
  ;;
*)
  echo "pass one of these argument: check,fix"
  exit 1
  ;;
esac
