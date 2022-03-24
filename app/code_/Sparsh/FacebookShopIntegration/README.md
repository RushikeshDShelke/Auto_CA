##Sparsh Facebook Shop Integration Extension
This extension allows the store admin to showcase their products on Facebook Shop by synchronizing their online Magento 2 store products with the Facebook store.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the files
3. Create a folder [Magento_Root]/app/code/Sparsh/FacebookShopIntegration
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/FacebookShopIntegration'

#Enable Extension:
- php bin/magento module:enable Sparsh_FacebookShopIntegration
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_FacebookShopIntegration
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
