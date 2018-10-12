<?php

class ShopifyApi {
	private $shop = "korafitness-dev";
    private $apiKey = "acac9a7e911be70b12ca8c6f4c2c5311";
    private $password = "5627ff97371c07a2f06ab1ad4540a8c0";
    private $shopifyBaseURL;

    function __construct() {
    	$this->shopifyBaseURL = "https://" . $this->apiKey . ":" . $this->password . "@" . $this->shop . ".myshopify.com";
    }

    function getCustomer($customerId) {
    	$url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "." ."json";
        $customerDetails = json_decode(file_get_contents($url));
        return $customerDetails;
    }

    //email:yogesh.suryawanshi@codaemonsoftwares.com;fields=email,id
    //$searchBy = email:yogesh.suryawanshi@codaemonsoftwares.com
    //$fieldToSelect = email,id;
    function searchCustomer($searchBy, $fieldToSelect) {
    	$url = $this->shopifyBaseURL . "/admin/customers/search.json?query=".$searchBy;
    	if($fieldToSelect) 
    		$url.= ";fields=".$fieldToSelect;

        $customerDetails = json_decode(file_get_contents($url));
    	return $customerDetails;
    }

    function updateCustomer($customerId, $customerDetails) {
    	$data = $customerDetails;
        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
        
        //url to apply customer tag
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "." ."json";
        $method = 'PUT';

        $curl = new CurlCall($url, $method, $headers, $data);
        echo "curl call send";
        return $curl->execute();
    }

    function createCustomer($customerDetails) {
        $data = $customerDetails;
        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
        
        //url to apply customer tag
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerDetails->customer->id. "." ."json";
        $method = 'PUT';

        $curl = new CurlCall($url, $method, $headers, $data);
        return $curl->execute();
    }
}