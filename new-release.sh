#!/bin/bash

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

TAG=$1

#
# Tag & build master branch
#
git checkout master
git tag ${TAG}
box build

#
# Copy executable file into GH pages
#
git checkout gh-pages

cp cdev.phar downloads/cdev-${TAG}.phar
git add downloads/cdev-${TAG}.phar

SHA1=$(openssl sha1 cdev.phar)

JSON='name:"cdev.phar"'
JSON="${JSON},sha1:\"${SHA1}\""
JSON="${JSON},url:\"https://garethmidwood.github.io/cdev/downloads/cdev-${TAG}.phar\""
JSON="${JSON},version:\"${TAG}\""

if [ -f cdev.phar.pubkey ]; then
    cp cdev.phar.pubkey pubkeys/cdev-${TAG}.phar.pubkeys
    git add pubkeys/cdev-${TAG}.phar.pubkeys
    JSON="${JSON},publicKey:\"https://garethmidwood.github.io/cdev/downloads/cdev-${TAG}.phar.pubkey\""
fi

#
# Update manifest
#
cat manifest.json | jsawk -a "this.push({${JSON}})" | python -mjson.tool > manifest.json.tmp
mv manifest.json.tmp manifest.json
git add manifest.json

git commit -m "Add version ${TAG}"

#
# Go back to master
#
git checkout master

echo "New version created. Now you should run:"
echo "git push origin gh-pages"
echo "git push ${TAG}"
