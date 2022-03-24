# Mage2 Module Kellton Ogmeta

    ``kellton/module-ogmeta``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities


## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Kellton`
 - Enable the module by running `php bin/magento module:enable Kellton_Ogmeta`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require kellton/module-ogmeta`
 - enable the module by running `php bin/magento module:enable Kellton_Ogmeta`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - enable (ogmeta/module/enable)

 - Default OG Image (ogmeta/module/def_og_img)

 - def_og_desc (ogmeta/module/def_og_desc)

 - Facebook App ID (ogmeta/module/def_og_fb_app_id)

 - Use Open Graph Meta Tag For	 (ogmeta/module/use_og_meta_tag_for)


## Specifications

 - Helper
	- Kellton\Ogmeta\Helper\Data


## Attributes

 - Category - OG Title (kell_og_title)

 - Category - OG Description (kell_og_description)

 - Category - OG Image (kell_og_img)

