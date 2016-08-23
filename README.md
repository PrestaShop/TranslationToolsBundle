# TranslationToolsBundle

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PrestaShop/TranslationToolsBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PrestaShop/TranslationToolsBundle/?branch=master)

Embed Translation dumpers and extractors for PrestaShop eCommerce CMS.
Can be also useful if you need to parse Smarty files.

## Installation

As usual, there is few steps required to install this bundle:

1 **Add this bundle to your project as a composer dependency**:

```javascript
    // composer.json
    {
        // ...
        require: {
            // ...
            "prestashop/translationtools-bundle": "dev-master"
        }
    }
```

2 **Add this bundle to your application kernel**:

```php
    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new PrestaShop\TranslationToolsBundle\TranslationToolsBundle(),
        );

        return $bundles;
    }
```

3 **How to contribute**

This bundle is unit-tested and well covered.
You can execute the tests with this command:

```bash
$ ./vendor/bin/phpunit
```

