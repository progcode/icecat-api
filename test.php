<?php

/**
 * Include Dotenv library to pull config options from .env file.
 */
if(file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::create(__DIR__, '.env');

    $dotenv->load();
}

use Src\Icecat\Api;

$icecat = new Api(getenv('ICEACAT_USER'), getenv('ICEACAT_PASS'));
$xml = $icecat->getArticleByMPN('Hewlett Packard Enterprise', '838079-B21', 'HU');
$productDataArray = $icecat->xml2array($xml);

$productData = $icecat->getProductData($productDataArray);

echo "<pre>";
var_dump($productData);
echo "</pre>";

exit();
