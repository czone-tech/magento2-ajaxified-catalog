## Ajax Layered Navigation module for Magento2
This module applies AJAX on the products catalog page. The left layered navigation block links are also modified to load via AJAX.

The module has been tested with Magento 2.0.x and 2.1.x, however, it should work fine for other Magento 2.x versions as well.
Please note that while this module works oob for Magento_Blank and Magento_Luma themes, it may break if a custom theme modifies certain view files related to layered navigation or products list block. This seems an unavoidable issue at the moment.
If you have any issues using this module, you may contact us at support@czonetechnologies.com

###Why AJAX?
Catalog is the most visited section of any e-commerce site. A fast loading catalog section is intrinstic to a superior user experience.
For faster loading of the catalog section, it is necessary that we do not rebuild the entire page on each request, instead only the minimum required sections are rebuilt. This results in a faster UX.

#### Demo
You can see this extension in action here-

1. Products Catalog page-
http://work.czonetechnologies.com/mage2.0/women/tops-women/jackets-women.html
http://work.czonetechnologies.com/mage2.1/women/tops-women/jackets-women.html

2. Catalog Search page-
http://work.czonetechnologies.com/mage2.0/catalogsearch/result/?q=jacket
http://work.czonetechnologies.com/mage2.1/catalogsearch/result/?q=jacket

####1 - Installation
##### Manual Installation

 * Download the extension
 * Unzip the file
 * Create a folder {Magento root}/app/code/CzoneTech
 * Extract the contents of the zipped folder inside it.


#####Using Composer

```
composer require czonetech/ajaxified-catalog
```

####2 -  Enabling the module
Using command line access to your server, run the following commands -
```
 $ cd <magento-installation-dir>
 $ php bin/magento module:enable --clear-static-content CzoneTech_AjaxifiedCatalog
 $ php bin/magento setup:upgrade
 $ rm -r var/di
 $ php bin/magento setup:di:compile
 $ php bin/magento cache:clean
```


## Screenshot
![CzoneTech_AjaxifiedCatalog](https://cloud.githubusercontent.com/assets/1729518/18914661/a9f63caa-85ab-11e6-9598-85a2eaa387df.png)
