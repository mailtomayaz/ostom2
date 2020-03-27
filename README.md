
# OscommerceToMagento

Manually Installation

Magento2 module installation is very easy, please follow the steps for installation.


Download and unzip the respective extension zip and create Embraceitechnologies/OscommerceToMagento  folder inside your magento/app/code/ directory and then move all module's files into magento root directory /app/code/Embraceitechnologies/OscommerceToMagento/folder.



Install with Composer



Specify the version of the module you need, and go.

composer config repositories.oscommerce-to-magento vcs git@bitbucket.org:kmembrace/oscommerce-to-magento.git 

composer require  embraceitechnologies/oscommerce-to-magento


Run following command 

$ php bin/magento setup:upgrade

$ php bin/magento setup:di:compile

$ php bin/magento setup:static-content:deploy

Uninstalling module

composer remove  embraceitechnologies/oscommerce-to-magento

$ php bin/magento setup:upgrade

$ php bin/magento setup:di:compile

$ php bin/magento setup:static-content:deploy
