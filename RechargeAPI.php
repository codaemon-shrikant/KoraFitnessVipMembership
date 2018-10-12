<?php

class RechargeApi {

	private $rechargeBaseURL = "https://api.rechargeapps.com/";
	private $rechargeToken = "a8cfed1dcbc2394ce19313e3fdb83fc25b69e6b7075f768b6056fddb";

	function createWebhook($topic) {
		$data = array (
		        'address' => 'http://vip.korafitness.com/KoraWebhook/vip_member.php',
				'topic' => $topic
		        );
		$url = $this->rechargeBaseURL.'webhooks';
		$method = "POST";
		$headers = array(
		  'Content-Type: application/json',
		  'X-Recharge-Access-Token: '.$this->rechargeToken,
		  "Accept: application/json"
		);
		$curl = new CurlCall($url, $method, $headers, $data);

		$result = $curl->execute();
		return $result;
	}

	function getCustomer($customerId) {
		$url = $this->rechargeBaseURL.'customers/'.$customerId;
		$method = "GET";
        $headers = array(
          'Content-Type: application/json',
          'X-Recharge-Access-Token: '.$this->rechargeToken,
          "Accept: application/json"
        );

        $curl = new CurlCall($url, $method, $headers, $data);

		$result = $curl->execute();
        return $result;
	}
}
?>
