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

### Creating a new collection
Some configuration options require the user to choose which class they want to use (e.g. environment, framework, storage).
If you want to create a new collection you must do a few things:

- Create collection class in `Creode\Collections` namespace
 - If your collection is bundled with options already then you need to add them with `addItem()` (see framework collection for an example)
- Create a register function (e.g. `registerStorage()`) in the plugin manager
- Add a folder in the `Creode` director, this folder will be the name of your namespace and must be capitalized.
- Add your option classes in their own folder within your new namespace.
- If your classes have their own configuration then create a `Command` directory within the option dir, add a setup command in here
- Update `Cdev\ConfigureCommand`, add a search and a replacement value in `saveServicesXml` for your new config option
 - Add the same search value into `templates/services.env.xml`. This template will be used when a configuration is saved


