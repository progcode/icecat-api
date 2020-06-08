<?php
namespace Src\Icecat;

use GuzzleHttp\Exception\RequestException;

/**
 * Class Api
 *
 * PHP Interface to the Icecat XML Interface
 */
class Api
{
    /**
     * The base URL used for all requests
     *
     * @var string
     */
    protected $apiBaseUrl = 'https://data.icecat.biz';


    /**
     * The endpoint relative to base URL for all XML requests
     *
     * @var string
     */
    protected $xmlEndpoint = 'xml_s3/xml_server3.cgi';

    /**
     * The endpoint relative to base URL for direct ID request
     *
     * @var string
     */
    protected $idEndpoint  = 'export/level4';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle     = null;
    /**
     * @var array
     */
    protected $headers = array(
        'Accept-Encoding: gzip'
    );
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var
     */
    private $username;
    /**
     * @var
     */
    private $password;

    private $productDataAttributes;
    private $productDescriptions;
    private $productImages;

    /**
     * Constructor with Icecat Username and Password
     *
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->init();
    }
    /**
     * Init Guzzle Instance
     */
    protected function init()
    {
        $this->guzzle = new \GuzzleHttp\Client(array(
            'base_uri' => $this->apiBaseUrl,
            'auth' => [$this->username, $this->password],
            'headers'        => $this->headers,
            'decode_content' => true
        ));
    }

    /**
     * @param $params
     * @return bool|\SimpleXMLElement
     */
    protected function request($endpoint, $params)
    {
        try {
            $response = $this->guzzle->get($endpoint,
                array(
                    'query' => $params
                ));
        } catch (RequestException $e) {
            if ($this->debug) {
                print $e->getMessage() . "\n";
                if ($e->hasResponse()) {
                    print $e->getResponse()->getBody()->getContents() . "\n";
                }
            }

            return false;
        }

        return new \SimpleXMLElement($response->getBody()->getContents());
    }

    /**
     * Queries article by EAN
     *
     * @param $ean
     * @return bool|\SimpleXMLElement
     */
    public function getArticleByEAN($ean, $lang='HU')
    {
        $params = array(
            'ean_upc' => $ean,
            'lang' => $lang
        );

        return $this->request($this->xmlEndpoint, $params);
    }

    /**
     * Queries article by vendor and manufacturer part no
     *
     * @param $vendor
     * @param $mpn
     * @param string $lang
     * @return bool|\SimpleXMLElement
     */
    public function getArticleByMPN($vendor, $mpn, $lang='HU')
    {
        $params = array(
            'prod_id' => $mpn,
            'vendor' => $vendor,
            'lang' => $lang,
            'output' => 'productxml'
        );

        return $this->request($this->xmlEndpoint, $params);
    }

    /**
     * Queries article by icecat ID
     *
     * @param $icecatId
     * @param string $lang
     * @return bool|\SimpleXMLElement
     */
    public function getArticleById($icecatId, $lang='HU') {

        $icecatId   = trim($icecatId);
        $lang       = trim($lang);
        $url = $this->idEndpoint . "/$lang/$icecatId.xml";

        return $this->request($url, []);
    }

    /**
     * Checks the returned XML response
     * Does it contain an error message, throws an exception
     *
     * @param \SimpleXMLElement $response
     * @return bool
     */
    public function isValidProduct(\SimpleXMLElement $response)
    {
        if (isset($response->Product->attributes()->ErrorMessage)) {
            return false;
        }

        return true;
    }

    /**
     * @param $xmlObject
     * @param array $out
     * @return array|bool
     */
    public function xml2array ( $xmlObject, $out = array () )
    {
        if(is_object($xmlObject)) {
            foreach ( (array) $xmlObject as $index => $node )
                $out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;

            return $out;
        }

        return false;
    }

    /**
     * Get Product from data && set attributes
     *
     * @param $xmlData
     * @return bool|mixed
     */
    public function getProductData($xmlData)
    {
        if($xmlData) {
            if(!empty($xmlData["Product"])){
                $this->setProductDataAttributes($xmlData["Product"]["@attributes"]);
            }

            if(!empty($xmlData["Product"]["ProductDescription"])) {
                $this->setProductDescriptions($xmlData["Product"]["ProductDescription"]["@attributes"]);
            }

            return $xmlData["Product"];
        }

        return false;
    }

    /**
     * Setter - set product attributes
     *
     * @param $productData
     */
    public function setProductDataAttributes($productData)
    {
        $this->productDataAttributes = $productData;
    }

    /**
     * Setter - set product descriptions
     *
     * @param $productData
     */
    public function setProductDescriptions($productData)
    {
        $this->productDescriptions = $productData;
    }

    /**
     * Setter - set product images
     *
     * @param $productImages
     */
    public function setProductImages($productImages)
    {
        $this->productImages = $productImages;
    }

    /**
     * Get product data
     *
     * @return bool|mixed
     */
    public function getProductDataAttributes()
    {
        $productDataAttributes = $this->productDataAttributes;
        if($productDataAttributes) {
            return $productDataAttributes;
        }

        return false;
    }

    /**
     * Get product name
     *
     * @return bool|mixed
     */
    public function getProductName()
    {
        $productDataAttributes = $this->productDataAttributes;
        if($productDataAttributes) {
            return $productDataAttributes["Title"];
        }

        return false;
    }

    /**
     * Get product images
     *
     * @return array|bool
     */
    public function getProductImages()
    {
        $productDataAttributes = $this->productDataAttributes;
        if($productDataAttributes) {
            $productImages = array(
                'HighPic' => $productDataAttributes["HighPic"],
                'Pic500x500' => $productDataAttributes["Pic500x500"],
                'ThumbPic' => $productDataAttributes["ThumbPic"]
            );

            $this->setProductImages($productImages);
            return $productImages;
        }

        return false;
    }

    /**
     * Json encode images array for save to DB
     *
     * @return bool|false|string
     */
    public function getProductImagesJson()
    {
        $productImages = $this->productImages;
        if($productImages) {
            return json_encode($productImages);
        }

        return false;
    }

    /**
     * Get product descriptions
     *
     * @return bool|mixed
     */
    public function getProductDescriptions()
    {
        $productDescriptions = $this->productDescriptions;
        if($productDescriptions) {
            return $productDescriptions;
        }

        return false;
    }
}
