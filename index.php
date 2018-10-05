<?php
// Mandatory for localhost. See : https://curl.haxx.se/docs/caextract.html
// ini_set('curl.cainfo', __DIR__.'/cacert.pem');
// ini_set('openssl.cafile', __DIR__.'/cacert.pem');

// If this does not work, manually set your php.ini file with those
// curl.cainfo="/path/to/certificate/cacert.pem"
// openssl.cafile="/path/to/certificate/cacert.pem"

ini_set('display_errors', 'On');
error_reporting(E_ALL);

use KammiApiClient\ApiClientFactory;

require_once('./vendor/autoload.php');

$data = array();

// Fatal error if no init. Defaults on dev api server.
ApiClientFactory::init();

// This returns an authenticated client with token header prepared
$c = ApiClientFactory::fromLogin('damien.parbhakar', 'zephir');

$data = $c->getInstanceParams();

// var_dump($data);
// Returning data
echo json_encode($data);

die();