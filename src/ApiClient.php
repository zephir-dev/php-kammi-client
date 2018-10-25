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

	public function getIndemnityTypes()
	{
		$data = $this->get('/v1/indemnities/types')->getBody()->getContents();
		return $data;
	}

}
