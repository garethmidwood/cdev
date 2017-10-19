cdev - A tool to manage dev environments
========================================

cdev helps you configure your repository to run on virtual environments.
It provides a consistent approach to configure, start, stop and destroy environments
There are also a smattering of site commands for popular frameworks

[![Dependency Status](https://www.versioneye.com/user/projects/599720fc0fb24f0bf7c2082a/badge.svg?style=flat)](https://www.versioneye.com/user/projects/599720fc0fb24f0bf7c2082a)

Installation
------------

### Installation on mac/linux
This will install the phar to the ~/.cdev directory, and create an alias at /usr/local/bin/cdev
```
curl -s https://garethmidwood.github.io/cdev/install | bash -s

# confirm installation
which cdev
```


Self Updating
-------------
cdev will update itself to the latest version when you run
```
cdev self-update
```


Requirements
------------
PHP 5.6 or higher



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
cdev env:db
```

Running Scripts on Startup
--------------------------
You can run a series of shell scripts on the server in alphanumerical order each time an environment has started up. Place your scripts inside the cdev root directory under "scripts/".

You can run the scripts manually via cdev env:scripts

```
# Example script to add a command to the crontab (scripts/0_cron.sh)
command="php /var/www/html/artisan schedule:run >> /dev/null 2>&1"
job="* * * * * $command"
cat <(fgrep -i -v "$command" <(crontab -l)) <(echo "$job") | crontab -
```

Contributing to cdev
--------------------
All contributions are welcome, please submit a pull request!

The repository is packaged with a `local-build.sh` script that will generate a `cdev-local` app for you to test your changes.

### Installation instructions
```
git clone git@github.com:garethmidwood/cdev.git cdev && cd cdev

# install dependencies
cd src && composer install && cd -

# You must have box installed. See https://github.com/box-project/box2

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

