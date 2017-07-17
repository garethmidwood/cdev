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
# this will build and copy the file to /usr/local/bin/cdev
./build.sh
# check it worked
which cdev
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

