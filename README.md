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



