# Installation of Contao Visitors Bundle

There are two types of installation.

* with the Contao-Manager, for Contao Managed-Editon
* via the command line, for Contao Managed-Editon


## Installation with Contao-Manager

* search for package: `bugbuster/contao-visitors-bundle`
* install the package
* Click on "Install Tool"
* Login and update the database

__Attention__: Users of Contao 4.13, use `^1.8` as version number! 


## Installation via command line

Installation in a Composer-based Contao 5.2+ Managed-Edition:

* `composer require "bugbuster/contao-visitors-bundle"`
* Call http://yourdomain/contao/install
* Login and update the database

Installation in a Composer-based Contao 4.13 Managed-Edition:

* `composer require "bugbuster/contao-visitors-bundle=^1.8"`
* Call http://yourdomain/contao/install
* Login and update the database
