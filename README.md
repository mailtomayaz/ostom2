<<<<<<< HEAD
# OscommerceToMagento

Manually Installation

Magento2 module installation is very easy, please follow the steps for installation.


Download and unzip the respective extension zip and create Embraceit/OscommerceToMagento  folder inside your magento/app/code/ directory and then move all module's files into magento root directory /app/code/mbraceit/OscommerceToMagento/folder.



Install with Composer



Specify the version of the module you need, and go.

composer config repositories.reponame vcs https://github.com/mailtomayaz/oscommerce-to-magento 

composer require  embraceit/oscommerce-to-magento:dev-master


Run following command 

$ php bin/magento setup:upgrade

$ php bin/magento setup:di:compile

$ php bin/magento setup:static-content:deploy

Uninstalling module

composer remove  embraceit/oscommerce-to-magento

$ php bin/magento setup:upgrade

$ php bin/magento setup:di:compile

$ php bin/magento setup:static-content:deploy




=======
# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Quick summary
* Version
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### How do I get set up? ###

* Summary of set up
* Configuration
* Dependencies
* Database configuration
* How to run tests
* Deployment instructions

### Contribution guidelines ###

* Writing tests
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact
>>>>>>> 3faf7a486b421be010abd6088314de703ed4ae0c
