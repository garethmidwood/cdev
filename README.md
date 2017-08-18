cdev - A tool to manage dev environments
========================================

cdev helps you configure your repository to run on virtual environments.
It provides a consistent approach to configure, start, stop and destroy environments
There are also a smattering of site commands for popular frameworks

Installation / Usage
--------------------
Download the latest release from [GitHub](https://github.com/garethmidwood/cdev/releases/latest).

# Installation on mac/linux
```
cd /path/to/downloads

# filename will vary per version
chmod +x cdev.phar
# move phar file to a location in your PATH
mv cdev.phar /usr/local/bin/cdev

# confirm installation
which cdev
```

Dependencies
------------
In order to run SSH commands (e.g. to retrieve backups) you will need to install the PHP SSH module

# Mac instructions
```
# Install libssh2
brew install libssh2

# Install PHP7 SSH
wget https://github.com/Sean-Der/pecl-networking-ssh2/archive/php7.zip
unzip php7.zip
cd pecl-networking-ssh2-php7/
phpize
./configure
make
sudo make install

# Activate module. In php.ini file add:
php -i | grep php.ini
# file: php.ini
extension=ssh2.so
```


Requirements
------------
PHP 5.6 or above



Contributing to cdev
--------------------
All contributions are welcome, please submit a pull request!

The repository is packaged with a `local-build.sh` script that will generate a `cdev-local` app for you to test your changes.

# Installation instructions
```
git clone git@github.com:garethmidwood/cdev.git cdev && cd cdev

# install dependencies
cd src && composer install && cd -

# build a local copy of cdev for testing
# this will build and copy the file to /usr/local/bin/cdev-local
./local-build.sh

# check it worked
which cdev-local
# should output /usr/local/bin/cdev-local

# Allow phar files to be created
# file: php.ini
[Phar]
; http://php.net/phar.readonly
phar.readonly = Off

# Test it has activated
php -i | grep ssh

# You should see something like:
Registered PHP Streams => https, ftps, compress.zlib, compress.bzip2, php, file, glob, data, http, ftp, phar, zip, ssh2.shell, ssh2.exec, ssh2.tunnel, ssh2.scp, ssh2.sftp
ssh2
libssh2 version => 1.8.0
```



Usage
-----

### Setup dev environment for a project
```
git clone git@your:repo.git
cd project/dir
cdev configure
```

## Dev Environment Commands

### Switch dev environment on
```
cd project/dir
cdev env:start
```

### Switch dev environment off
```
cd project/dir
cdev env:stop
```

### Destroy dev environment
```
cd project/dir
cdev env:nuke
```

### Clean up dev environment(s)
```
# from anywhere
cdev env:cleanup
```


## Backups
### Configure to pull backups
```
cd project/dir
cdev configure
```

### Pull latest DB and/or Media backup
```
cd project/dir
cdev backup:pull
```

### Remove unnecessary inserts from the DB dump (framework specific)
```
cd project/dir
cdev backup:db:cleanse
```


## Site Commands
### Clear caches
```
cd project/dir
cdev site:cache:clear
```
