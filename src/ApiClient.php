<?php

namespace KammiApiClient;

use GuzzleHttp\Client;


class ApiClient extends Client {
	
	public function getInstanceParams($params) {

		if(is_array($params)) {
			$res = json_decode(($this->get('/v1/admin/instance/params'))->getBody(), true);

			$data = array_values(array_filter($res,function($v){
				return in_array($v['param'],$res);
			}));

			$data = array_combine(array_column($data,'param'), array_column($data,'valeu'));	
		} else {
			$res = json_decode(($this->get('/v1/admin/instance/params&name='.$params))->getBody(), true);
			$data = array($res[0]['param'] => $res[0]['valeu']);
		}

		return $data;
	}
}
