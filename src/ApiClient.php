<?php

namespace KammiApiClient;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Eljam\GuzzleJwt\JwtMiddleware;
use Eljam\GuzzleJwt\Manager\JwtManager;
use Eljam\GuzzleJwt\Strategy\Auth\JsonAuthStrategy;

class ApiClient extends Client {

	public $user;
	public $rawToken;
	public $token;
	public static $client;

	public static function getClient() {
		return self::$client;
	}

	public static function setClient($client) {
		self::$client = $client;
	}

	public function __construct($username, $password) {

		//Create your auth strategy
		$authStrategy = new JsonAuthStrategy(
		    [
		        'username' => $username,
		        'password' => $password,
		        'json_fields' => ['username', 'password'],
		    ]
		);
		$baseUri = 'https://api-dev.zephir.pro';

		// Create authClient
		$authClient = new Client(['base_uri' => $baseUri]);

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

		parent::__construct([
		    // Base URI is used with relative requests
		    'base_uri' => 'https://api-dev.zephir.pro',
		    // You can set any number of default request options.
		    'timeout'  => 2.0,
		    // Handlers
			'handler' => $stack,
		]);

		$this->user = array();
		$this->rawToken = array();
		$this->token = null;

		self::$client = $this;
	}

	public function login($username, $password) {
		try {
			$res = $this->post('/v1/token', array(
				'json' => array(
					'username' => $username,
					'password' => $password
				)
			));
			// echo json_encode($res->getBody()->getContents()); die();

			$this->rawToken = json_decode($res->getBody()->getContents(), true);

			// var_dump($this->rawToken); die();
			$this->token = $this->rawToken['token'];

			$data = $res;
		} catch (RequestException $e) {
		    // echo Psr7\str($e->getRequest());
		    if ($e->hasResponse()) {
		        // $data =  Psr7\str($e->getResponse());
		        $data = $e->getResponse();
		    }
		    // die();
		}
		return $data;
	}

}
