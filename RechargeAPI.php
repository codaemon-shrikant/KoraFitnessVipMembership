<?php

class RechargeApi {

	private $rechargeBaseURL = "https://api.rechargeapps.com/";
	private $rechargeToken = "a8cfed1dcbc2394ce19313e3fdb83fc25b69e6b7075f768b6056fddb";
	//private $rechargeToken = "8aa8caed77d15b28c6cc83a75e29a7cf98df6c82b7d657162f768afb";

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
        $data = [];

        $curl = new CurlCall($url, $method, $headers, $data);

		$result = $curl->execute();
        return $result;
	}
	function getSubscriptionDetails($subscriptionId) {
		$url = $this->rechargeBaseURL.'subscriptions/'.$subscriptionId;
		$method = "GET";
		$data = [];
        $headers = array(
          'Content-Type: application/json',
          'X-Recharge-Access-Token: '.$this->rechargeToken,
          "Accept: application/json"
        );

        $curl = new CurlCall($url, $method, $headers, $data);

		$result = $curl->execute();
        return $result;
	}
	function testWebhook ($webhookId) {
		//https://api.rechargeapps.com/webhooks/<webhook_id>/test
		$url = $this->rechargeBaseURL.'webhooks/'.$webhookId."/test";
		$headers = array(
		  'Content-Type: application/json',
		  'X-Recharge-Access-Token: '.$this->rechargeToken,
		  "Accept: application/json"
		);
		$method = 'POST';
		$data = [];
		$curl = new CurlCall($url, $method, $headers, $data);
		$result = $curl->execute();
		print_r($result);
	}
	function getWebhooks() {
		$url = $this->rechargeBaseURL.'webhooks';
		$headers = array(
		  'Content-Type: application/json',
		  'X-Recharge-Access-Token: '.$this->rechargeToken,
		  "Accept: application/json"
		);
		$method = 'GET';
		$data = [];
		$curl = new CurlCall($url, $method, $headers, $data);
		$result = $curl->execute();
		print_r($result);
	}
}
?>
