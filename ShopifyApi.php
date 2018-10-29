<?php
error_reporting(E_ALL);
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
    function generate_code($totalDiscount,$customerId)
      {
        $code = "VIP_" . time(). "_".$totalDiscount."_" . "OFF";
        $discount_code = array (
            'discount_code' =>
            array(
             'code' => $code,
            )
          );
        return $discount_code;
      }
    function price_rules($code, $totalDiscount)
      {
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
       $price_rule = array (
        'price_rule' =>
              array(
                "title" => $code,
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across",
                "value_type" => "percentage",
                "value" => -$totalDiscount,
                "customer_selection" => "all",
                "starts_at" => "2018-10-01T17:59:10Z"
              )
            );
        return $price_rule;
      }
      function price_rule_id($price_rules)
      {
        $url = $this->shopifyBaseURL. "/admin/price_rules.json";
        $method = 'POST';

        $data = $price_rules;

        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );

        $curl = new CurlCall($url, $method, $headers, $data);
        return $curl->execute();
      }
      
    function createDiscount($generateCode, $rule_id)
    {
        //url to apply generate coupon
        $url = $this->shopifyBaseURL . "/admin/price_rules/" .$rule_id. "/" ."discount_codes.json";
        $method = 'POST';

        $data = $generateCode;

        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );

        $curl = new CurlCall($url, $method, $headers, $data);
        return $curl->execute();
    }
   function send_invite($customerId, $customerEmail)
    {
        //url to apply generate coupon
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "/send_invite.json";
        $customer_invite = array(
        'customer_invite' =>
              array(
                "to" => $customerEmail,
                "from" => "monika541992@gmail.com",
                "subject" => "Welcome to my new shop",
                "custom_message" => "My awesome new store"
              )
            );
        $data = $customer_invite;
        $method = 'POST';

        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
        $curl = new CurlCall($url, $method, $headers, $data);
        return $curl->execute();
    }

}