cdev - A tool to manage dev environments
========================================

cdev helps you configure your repository to run on virtual environments.
It provides a consistent approach to configure, start, stop and destroy environments
There are also a smattering of site commands for popular frameworks

[![Dependency Status](https://www.versioneye.com/user/projects/599720fc0fb24f0bf7c2082a/badge.svg?style=flat)](https://www.versioneye.com/user/projects/599720fc0fb24f0bf7c2082a)

Installation
------------
Download the latest release from [GitHub](https://github.com/garethmidwood/cdev/releases/latest).

### Installation on mac/linux
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

### Mac instructions
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

Self Updating
-------------
cdev will update itself to the latest version when you run
```
cdev update
```


Requirements
------------
PHP 5.6 or above



Contributing to cdev
--------------------
All contributions are welcome, please submit a pull request!

The repository is packaged with a `local-build.sh` script that will generate a `cdev-local` app for you to test your changes.

### Installation instructions
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

### Configure dev environment for a project
In order to use cdev on a project you must first configure it:
```
git clone git@your:repo.git
cd project/dir
cdev configure
```

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

