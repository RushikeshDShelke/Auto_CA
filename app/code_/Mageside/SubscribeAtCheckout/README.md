Magento 2 Subscribe At Checkout by Mageside
===========================================

####Support
    v1.1.6 - Magento 2.3.* - 2.4.*

####Change list
    v1.1.6 - Fixed sending emails for Magento 2.4
    v1.1.5 - Fixed send confirmation success email function
    v1.1.4 - Magento 2.4 support checking (updated composer.json)
    v1.1.3 - Added PHP 7.3 compatibility
    v1.1.2 - Added Magento 2.3 compatibility
    v1.1.1 - Added config for sending confirmation email
    v1.1.0 - Disable second force subscribe for the same email
    v1.0.7 - Fix for Magento 2.2.5
    v1.0.6 - Move config from layout to layoutProcessors
    v1.0.4 - Magento 2.2 support checking (updated composer.json)
    v1.0.3 - Updated composer.json
    v1.0.0 - Start project

####Installation
    1. Download the archive.
    2. Unzip the content of archive, use command 'unzip ArchiveName.zip'. Now you have folder with name 'SubscribeAtCheckout'.
    3. Make sure to create the directory structure in your Magento - 'Magento_Root/app/code/Mageside'.
    4. Drop/move the unzipped folder to directory 'Magento_Root/app/code/Mageside'.
    5. Run the command 'php bin/magento module:enable Mageside_SubscribeAtCheckout' in Magento root. If you need to clear static content use 'php bin/magento module:enable --clear-static-content Mageside_SubscribeAtCheckout'.
    6. Run the command 'php bin/magento setup:upgrade' in Magento root.
    7. Run the command 'php bin/magento setup:di:compile' if you have a single website and store, or 'php bin/magento setup:di:compile-multi-tenant' if you have multiple ones.
    8. Clear cache: 'php bin/magento cache:clean', 'php bin/magento cache:flush'
