<?php

namespace KammiApiClient;

use GuzzleHttp\Client;


class ApiClient extends Client {

	public function __construct($options) {
		parent::__construct($options);

		// $res = json_decode(($this->post('/v1/token/validate'))->getBody(), true);
		// if(!empty($res['user']) && $res['user']['matri'] > 0) {

		// } else {

		// }
	}

	public function getInstanceParams($params = array()) {

		$data = array();

		if(is_array($params)) {
			$res = json_decode($this->get('/v1/admin/instance/params')->getBody()->getContents(), true);

			if(count($params) === 0) {
				$data = $res;
			} else if (count($params) > 0) {
				$data = array_values(array_filter($res,function($v) use($params) {
					return in_array($v['param'],$params);
				}));
			}
		} else {
			$res = json_decode($this->get('/v1/admin/instance/params?name='.$params)->getBody()->getContents(), true);
			$data = $res;
		}

		$data = array_combine(array_column($data,'param'), array_column($data,'valeu'));
		return $data;
	}

	public function postInstanceParams($post) {
		$data = array();
		$data = json_decode($this->post('/v1/admin/instance/params', array('json' => $post))->getBody()->getContents(), true);
		$data = array_combine(array_column($data,'param'), array_column($data,'valeu'));
		return $data;
	}
	
    public function sendToApi( $method, $url )
    {
        $this->result       = $this->request($method, $url )->getBody()->getContents();
        $jsonDecodedResult  = json_decode($this->result, true);
        
        if( !empty($jsonDecodedResult) ){
            return $jsonDecodedResult;
        }
        
        if(strstr($this->result, 'xdebug') )
        {
            $position = strpos($this->result, '<br />');
            
            echo substr($this->result, $position);
            
            $this->result = trim( substr($this->result, 0, $position) );
            
            return json_decode( trim(substr($this->result, 0, $position)), true);
        }
        
        return false;
    }
    
    public function debugResult()
    {
        // DEBUG
        foreach( json_decode($this->result, true) as $resultCat => $resultContent )
        {
            echo "<div onclick=\"$('#".$resultCat."-debug').toggle();\">".$resultCat."</div>";
            echo "<pre id=\"".$resultCat."-debug\" style=\"display: none;\">";
            var_dump($resultContent);
            echo "</pre>";
        }
        
        return;
    }	

}
