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


Self Updating
-------------
cdev will update itself to the latest version when you run
```
cdev update
```


Requirements
------------
PHP 7+



Contributing to cdev
--------------------
All contributions are welcome, please submit a pull request!

The repository is packaged with a `local-build.sh` script that will generate a `cdev-local` app for you to test your changes.

### Installation instructions
```
git clone git@github.com:garethmidwood/cdev.git cdev && cd cdev

# install dependencies
cd src && composer install && cd -

# Allow phar files to be created
# file: php.ini
[Phar]
; http://php.net/phar.readonly
phar.readonly = Off
```

### Local build instructions

```
# build a local copy of cdev for testing
# this will build and copy the file to /usr/local/bin/cdev-local
./local-build.sh

# check it worked
which cdev-local
# should output /usr/local/bin/cdev-local
```



Usage
-----

### Configure global defaults
Global config is used as default values for project configuration.
```
cdev global:configure
```

### Configure environment for a project
In order to use cdev on a project you must first configure it:
```
git clone git@your:repo.git
cd project/dir
cdev configure
```

### Switch environment on
```
cd project/dir
cdev env:start
```

### Switch environment off
```
cd project/dir
cdev env:stop
```

### Destroy environment
```
cd project/dir
cdev env:nuke
```

### Clean up environment(s)
```
# from anywhere
cdev env:cleanup
```

### Open SSH connection to environment
```
cd project/dir
cdev env:ssh
```

### Open Database connection to environment
```
cd project/dir
cdev env:db:connect
```

