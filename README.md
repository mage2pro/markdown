The «[**Markdown Editor**](https://mage2.pro/c/extensions/markdown)» module for Magento 2 allows you to format the store's content (product descriptions, articles, CMS blocks and so on) with Markdown markup language.  
The standard Magento editor (TinyMCE) produces lot of HTML mess. Markdown allows you to make your content more accurate, clean, professionally looked.  
The module is **free** and **open source**.

## Features
- Full support for [GitHub Flavored Markdown](https://guides.github.com/features/mastering-markdown/)
    - [Markdown Basics](https://help.github.com/articles/basic-writing-and-formatting-syntax/)
    - [Markdown Syntax](https://daringfireball.net/projects/markdown/syntax)
- Full support for HTML.
- Full support for Magento features:
    - widgets
    - media storage (uploading and inserting images)
    - variables 
- Works for Magento products categories, CMS pages and CMS blocks.
- Autosaves the article as you type.
- Precise preview rendering.

**Demo video**: https://www.youtube.com/watch?v=gGL0IPJhzvY

## Screenshots

### 1. Full-screen mode
![](https://mage2.pro/uploads/default/original/1X/de441d096b7dd5e54c3d886fa29d8a405255fd0c.png)

### 2. Mini mode
![](https://mage2.pro/uploads/default/original/1X/1675cbee6eb9773ff030eed1a1dc208c02bcc035.png)

## How to install
[Hire me in Upwork](https://www.upwork.com/fl/mage2pro), and I will: 
- install and configure the module properly on your website
- answer your questions
- solve compatiblity problems with third-party checkout, shipping, marketing modules
- implement new features you need 

### 2. Self-installation
```
bin/magento maintenance:enable
rm -f composer.lock
composer clear-cache
composer require mage2pro/markdown:*
bin/magento setup:upgrade
bin/magento cache:enable
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales>
bin/magento maintenance:disable
```

## How to update
```
bin/magento maintenance:enable
composer remove mage2pro/markdown
rm -f composer.lock
composer clear-cache
composer require mage2pro/markdown:*
bin/magento setup:upgrade
bin/magento cache:enable
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales>
bin/magento maintenance:disable
```