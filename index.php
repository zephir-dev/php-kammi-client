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
$c1 = ApiClientFactory::fromLogin('username', 'password');

// If you already got your token, use this one
$c2 = ApiClientFactory::fromToken(ApiClientFactory::$token);

// After one of those requests has been done, you can access token with 
$token = ApiClientFactory::$token;

// (Beta) This one will automagically request a new token if it's expired
// Warning : It doesn't store the token in ApiClientFactory::$token, but it keeps it internally
$c3 = ApiClientFactory::fromJWTMiddleware('username', 'password');

// Example requests
$r1 = json_decode($c1->get('/v1/users')->getBody(), true);
$r2 = json_decode($c2->get('/v1/users')->getBody(), true);
$r3 = json_decode($c3->get('/v1/users')->getBody(), true);

// Returning data
echo json_encode(array(
	'c1' => $r1,
	'c2' => $r2,
	'c3' => $r3,
));

die();