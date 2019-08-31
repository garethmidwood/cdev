#!/bin/bash

# credit to: https://moquet.net/blog/distributing-php-cli/ 

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

TAG=$1

#
# Tag & build master branch
#
echo 'checking ulimit value'
ulimit -n
echo 'setting ulimit to 10000'
ulimit -n 10000
git checkout master
git push
git tag ${TAG}
#box key:create
box build

#
# Copy executable file into GH pages
#
git checkout gh-pages
git pull

mv cdev.phar downloads/cdev.phar
cp cdev.phar.pubkey downloads/cdev.phar.pubkey
shasum downloads/cdev.phar > downloads/cdev.version

git add downloads/cdev.phar
git add downloads/cdev.phar.pubkey
git add downloads/cdev.version

#
# Commit and push
#
git commit -m "Add version ${TAG}"

git push origin gh-pages

#
# Go back to master
#
git checkout master
git push --tags
echo "New version created."

