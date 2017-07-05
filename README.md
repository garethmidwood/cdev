# CDEV
A tool to manage dev environments

# Installation instructions
```
git clone git@github.com:garethmidwood/cdev.git
cd path/to/repo
```
```
# install dependencies
cd src
composer install
```
```
# build phar file
# you will need to install the dependencies before you can run this section
cd ..
php build/build.php
# make it available system-wide
cp cdev.phar /usr/local/bin/cdev
```


# Dependencies

## Install libssh2
```
brew install libssh2
```

## Install PHP7 SSH
```
wget https://github.com/Sean-Der/pecl-networking-ssh2/archive/php7.zip
unzip php7.zip
cd pecl-networking-ssh2-php7/
phpize
./configure
make
sudo make install
```

## Activate module. In php.ini file add:
```
# file: php.ini
extension=ssh2.so
```

## Allow phar files to be created
```
# file: php.ini
[Phar]
; http://php.net/phar.readonly
phar.readonly = Off
```

## Test it has activated
```
php -i | grep ssh

# should see something like:
Registered PHP Streams => https, ftps, compress.zlib, compress.bzip2, php, file, glob, data, http, ftp, phar, zip, ssh2.shell, ssh2.exec, ssh2.tunnel, ssh2.scp, ssh2.sftp
ssh2
libssh2 version => 1.8.0
```


# Usage

## Docker
### Setup dev environment for a project
```
git clone git@your:repo.git
cd project/dir
cdev docker:setup
```

### Switch dev environment on
```
cd project/dir
cdev docker:start
```

### Switch dev environment off
```
cd project/dir
cdev docker:stop
```

### Destroy dev environment
```
cd project/dir
cdev docker:nuke
```


## Backups
### Configure to pull backups
```
cd project/dir
cdev cdev:configure
```

### Pull latest DB backup
```
cd project/dir
cdev db:pull
```


## Magento 2
### Clear caches
```
cd project/dir
cdev mage2:cache:clear
```

