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
    public static $user;
    public static $data;

    public static function getClient() {
        return self::$client;
    }

    public static function setClient($client) {
        self::$client = $client;
    }

    public static function init($opt = null) {
        $scheme = 'https';
        self::$env['host'] = $opt['host'];

        if(self::$env['host'] == 'localhost') {
            $scheme = 'http';
        }

        self::$env['X-Client-Url'] = $opt['X-Client-Url'];
        
        if( strstr(self::$env['host'], '://') !== false ){
            self::$env['base_uri'] =  self::$env['host'];
        }
        else {
            self::$env['base_uri'] =  $scheme.'://' . self::$env['host'];
        }
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

    public static function fromLogin( $username, $password, $params=false ){
        $client = new ApiClient([
            'base_uri' => self::$env['base_uri'],
            'headers' => [
                'X-Client-Url' => self::$env['X-Client-Url'],
            ]
        ]);

        try {
            $resContent = $client->sendToApi( 'post', '/v1/token', [
                'username' => $username,
                'password' => $password,
                'params'   => $params, 
            ]);
            
            if( !empty($resContent['user']) ){
                self::$user = $resContent['user'];
            }
            
            
            self::$data = $resContent;
            
            
            return self::fromToken($resContent['token']);

        } catch( RequestException $e ){
            if ($e->hasResponse()) {
                return $e->getResponse();
            }
        }
    }
    
    public static function fromGoogleToken( $googleToken, $params=false )
    {
        $client = new ApiClient([
            'base_uri' => self::$env['base_uri'],
            'headers' => [
                'X-Client-Url' => self::$env['X-Client-Url'],
            ]
        ]);
        
        try {
            $resContent = $client->sendToApi( 'post', '/v1/token/google/'. urlencode($googleToken), $params );
            
            if( !empty($resContent['user']) ){
                self::$user = $resContent['user'];
            }
            
            self::$data = $resContent;
            
            return self::fromToken($resContent['token']);
        } 
        catch( RequestException $e ) 
        {
            if( $e->hasResponse() ){
                return $e->getResponse();
            }
        }
    }

    public static function fromMicrosoftToken($microsoftToken, $params = false)
    {
        $client = new ApiClient([
            'base_uri' => self::$env['base_uri'],
            'headers' => [
                'X-Client-Url' => self::$env['X-Client-Url'],
            ]
        ]);

        try
        {
            $params = [
                'microsoftToken'    => $microsoftToken,
                'params'            => $params
            ];

            $resContent = $client->sendToApi('post', '/v1/token/microsoft', $params);

            if (!empty($resContent['user']))
            {
                self::$user = $resContent['user'];
            }

            self::$data = $resContent;

            return self::fromToken($resContent['token']);
        }
        catch (RequestException $e) 
        {
            if( $e->hasResponse() ){
                return $e->getResponse();
            }
        }
    }

    public static function loginFormData()
    {
        $client = new ApiClient([
            'base_uri' => self::$env['base_uri'],
            'headers' => [
                'X-Client-Url' => self::$env['X-Client-Url'],
            ]
        ]);
        
        try {
            $res    = $client->get( '/v1/token/login/data' );
            $data   = json_decode($res->getBody()->getContents(), true);
            
            return $data;
        } 
        catch( RequestException $e ) 
        {
            if( $e->hasResponse() ){
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
