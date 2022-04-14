Icecat
======

Icecat is a PHP library, that assists you in the following 2 procedures:
* Fetching data from the Icecat database using basic product information.
* Parsing this data from the Icecat response, and using them in real life applications.

>
> **v1.0.2:**
>
> - trim() vendor and sku/mpn values

### About Icecat
[Icecat](http://icecat.biz, "Icecat") is an open catalog, providing free access to thousands of product datasheets.
In extend, when taking a subscription, the amount of accessible datasheets are increased.

There is a list of [Icecat sponsor brands](http://icecat.co.uk/en/menu/partners/index.html, "Icecat sponsor brands").


Installation
============

The library can be installed using composer:

```
"progcode/icecat-api": "dev-master"
```

Usage
=====

The class library is, in it's current state easy to be used.

### Result

The [Icecat class](https://github.com/progcode/icecat-api/blob/master/src/Icecat/Api.php) is responsible for parsing the data. It includes a few basic methods, but you can easily create your 
own implementation by implementing the IcecatInterface interface.

```php
// Use the class.
use Src\Icecat\Api;

$icecat = new Api(getenv('ICEACAT_USER'), getenv('ICEACAT_PASS'));
$xml = $icecat->getArticleByMPN('Hewlett Packard Enterprise', '838079-B21', 'HU');
$productDataArray = $icecat->xml2array($xml);

$productData = $icecat->getProductData($productDataArray);

```

Demo is soon available.
