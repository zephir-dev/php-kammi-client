<?php
namespace KammiApiClient;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Eljam\GuzzleJwt\JwtMiddleware;
use Eljam\GuzzleJwt\Manager\JwtManager;
use Eljam\GuzzleJwt\Strategy\Auth\JsonAuthStrategy;

class ApiClientFactory {

	public static $env;
	public static $client;
	public static $token;

	public static function getClient() {
		return self::$client;
	}

	public static function setClient($client) {
		self::$client = $client;
	}

	public static function init($opt = null) {
		$scheme = 'https';
		self::$env['host'] = $opt['host'] ?? 'api-dev.zephir.pro';

		if(self::$env['host'] == 'localhost') {
			$scheme = 'http';
		}

		self::$env['X-Client-Url'] = $opt['X-Client-Url'] ?? 'z-dev.zephir.pro';
		self::$env['base_uri'] =  $scheme.'://' . self::$env['host'];
	}

	public static function fromToken($token) {
		self::setClient(new ApiClient([
			'base_uri' => self::$env['base_uri'],
			'headers' => [
				'Authorization' => 'Bearer '.$token,
				'X-Client-Url' => self::$env['X-Client-Url'],
			]
		]));
		self::$token = $token;
		return self::getClient();
	}

	public static function fromLogin($username, $password) {
		$client = new ApiClient([
			'base_uri' => self::$env['base_uri'],
			'headers' => [
				'X-Client-Url' => self::$env['X-Client-Url'],
			]
		]);

		try {
			$res = $client->post('/v1/token', array(
				'json' => array(
					'username' => $username,
					'password' => $password
				)
			));

			$token = json_decode($res->getBody()->getContents(), true);

			return self::fromToken($token['token']);

		} catch (RequestException $e) {
		    if ($e->hasResponse()) {
		        return $e->getResponse();
		    }
		}
	}

	public static function fromJWTMiddleware($username, $password) {
		//Create your auth strategy
		$authStrategy = new JsonAuthStrategy(
		    [
		        'username' => $username,
		        'password' => $password,
		        'json_fields' => ['username', 'password'],
		    ]
		);

		// Create authClient
		$authClient = new ApiClient(['base_uri' => self::$env['base_uri']]);

		//Create the JwtManager
		$jwtManager = new JwtManager(
		    $authClient,
		    $authStrategy,
		    [
		        'token_url' => '/v1/token',
		        'token_key' => 'token',
        		'expire_key' => 'expires_in', # de
		    ]
		);

		// Create a HandlerStack
		$stack = HandlerStack::create();

		// Add middleware
		$stack->push(new JwtMiddleware($jwtManager));

		self::setClient(new ApiClient([
		    // Base URI is used with relative requests
		    'base_uri' => self::$env['base_uri'],
		    // You can set any number of default request options.
		    'timeout'  => 2.0,
		    // Handlers
			'handler' => $stack,
		]));

		return self::getClient();
	}
}
