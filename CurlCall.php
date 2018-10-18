<?php

class CurlCall {
	private $url, $method, $data, $headers;
	
	function __construct($url, $method, $headers, $data) { 
		$this->url = $url;
		$this->method = $method;
		$this->data = $data;
		$this->header = $headers;
	}

	function execute() {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
		if($this->method === 'POST' || $this->method === 'PUT')
		{
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->data));
		}
		else
		{
			curl_setopt($curl, CURLOPT_GETFIELDS, json_encode($this->data));
		}

		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);

		$result = curl_exec($curl);
		curl_close($curl);
		return json_decode($result);
	}
}
?>
