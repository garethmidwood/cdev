#!/bin/bash

INSTALL_DIR=~/.cdev

ICON_COMPLETE_COLOUR=`tput setaf 2`
NO_COLOUR=`tput sgr0`

ICON_COMPLETE="${ICON_COMPLETE_COLOUR}\xcf\xbe${NO_COLOUR}"

TARGET_RELEASE_PATH="${INSTALL_DIR}/cdev-local.phar"
TARGET_RELEASE_KEY_PATH="${INSTALL_DIR}/cdev-local.phar.pubkey"

ALIAS='/usr/local/bin/cdev-local'

#box key:create
box build

mv cdev.phar $TARGET_RELEASE_PATH
cp cdev.phar.pubkey $TARGET_RELEASE_KEY_PATH

rm $ALIAS
ln -s $TARGET_RELEASE_PATH $ALIAS
