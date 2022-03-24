# Peakhour website acceleration for Magento 2

Congratulations on choosing [Peakhour](https://www.peakhour.io) to accelerate your Magento 2 store. 
The Peakhour_Cdn extension uses Magento's built in page caching functionality to list Peakhour.io 
as a full page cache option. This enables greatly simplified full page caching with Peakhour and 
automated flushing of Peakhour's global cache when editing content through the Magento admin.

## Prerequisites

Before installing make a backup of your Magento site as a precaution. If you have a development
environment it is strongly recommended that you install the plugin there first to test and confirm
functionality.

You will need a Peakhour account to be able to utilise this plugin. If you don't already have one
you can [sign up here](https://www.peakhour.io/app/signup/), pricing is based on page views and
all new signups get a free trial. Once you have your account the only other change you need to 
make to activate our acceleration and web application firewall is a DNS change. Do not enable the 
plugin until you have successfully configured your domain behind the Peakhour service.

## Install via Magento marketplace

This is only available for Magento 2.2.x and greater, see the [official magento doc page](https://docs.magento.com/m2/ce/user_guide/system/web-setup-extension-manager.html)

## Install via Composer

This will be done via command line on your Magento server. You will have to make sure [you have composer installed](https://getcomposer.org/download/). 

* `php composer.phar require peakhour/magento2`
* `php bin/magento module:enable Peakhour_Cdn`
* `php bin/magento setup:upgrade`
* `php bin/magento cache:clean`

Then you can log in to the Magento admin and finish configuration.

## Install via zip file

1. Visit our [github page](https://github.com/peakhour-io/peakhour-magento2)
2. Click on the 'clone or download' button and choose 'Download zip'
3. Upload the zip file to your Magento server.
4. Log onto the Magento server as the Magento user (if not possible to log in as the Magento User you will need to do some extra steps)
5. Change directory to the Magento home and create the directory `app/code/Peakhour/Cdn`
6. Change directory to the newly created directory from step 5.
7. unzip the zip file
8. change directory back to the Magento Home
9. verify with `php bin/magento module:status` and make sure Peakhour is listed
10. Enable module with `php bin/magento module:enable Peakhour_Cdn`
11. then run `php bin/magento setup:upgrade`
12. then clear caches with `php bin/magento cache:clean` 

## Configuration

1. Log in to your Magento admin
2. Navigate to Stores -> Configuration, then Advanced -> System, then Full Page Cache
3. Untick Use System Value
4. Select Peakhour.io from the dropdown for Caching Application
5. Expand the Peakhour.io Settings section
6. Enter your Peakhour API Key:
    1. Log in to your Peakhour account
    2. Click on API Keys in the left menu.  
    3. If you don't have a key for your domain then enter your domain name and click Create
    4. Copy the generated key and paste into the API Key field in the Peakhour.io settings section
7. Enter your domain name and save config
8. Click 'Test Connection', you will hopefully see 'Success!'

## Trouble shooting
* If you installed via zip file/command line you may run into file permission issues if you did not do everything as the Magento user. See [this stack overflow post](https://magento.stackexchange.com/questions/91870/magento-2-folder-file-permissions) to help resolve issues.
* If any of the bin/magento commands are failing make sure you have the same php version on the command line as the one that is running on your webserver. 
* If you cannot resolve then disable the module `php bin/magento module:disable Peakhour_Cdn` and clean caches `php bin/magento cache:clean`
