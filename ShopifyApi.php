<?php
error_reporting(E_ALL);
class ShopifyApi {
	//private $shop = "korafitness-dev";
    //private $apiKey = "acac9a7e911be70b12ca8c6f4c2c5311";
    //private $password = "5627ff97371c07a2f06ab1ad4540a8c0";
    private $shop = "kora-fit";
    private $apiKey = "fdc6d9234e771474bcfe37c6b2171fff";
    private $password = "47ad4d0787e1812d3db828423a0d81c2";
    private $shopifyBaseURL;

    function __construct() {
    	$this->shopifyBaseURL = "https://" . $this->apiKey . ":" . $this->password . "@" . $this->shop . ".myshopify.com";
    }

    function getCustomer($customerId) {
    	$url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "." ."json";
        $customerDetails = json_decode(file_get_contents($url));
        return $customerDetails;
    }

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
            'Content-Type: application/json'
         );

        //url to apply customer tag
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "." ."json";

        $method = 'PUT';

        $curl = new CurlCall($url, $method, $headers, $data);echo "<br>";
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
        $code = "CHAMPION_" . time(). "_".$totalDiscount."_" . "OFF";
        $discount_code = array (
            'discount_code' =>
            array(
             'code' => $code,
            )
          );
        return $discount_code;
      }
    function price_rules($code, $totalDiscount, $shopifyCustomerId)
      {
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
       
	$starts_at = date('Y-m-d H:i:s');
        $ends_at =  date('Y-m-d ') . date('H:i:s', strtotime("+20 minutes",        strtotime($starts_at)));
	$price_rule = array (
        'price_rule' =>
              array(
                "title" => $code,
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across",
                "value_type" => "percentage",
                "allocation_limit" => 1,
                "usage_limit" => 1,
		"value" => -$totalDiscount,
                "customer_selection" => "prerequisite",
                "prerequisite_customer_ids" => [$shopifyCustomerId],
                "starts_at" => $starts_at,
                "ends_at" => $ends_at
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
        //url to send invite
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "/send_invite.json";

        $customer_invite = json_decode('{
                              "customer_invite": {
                                "to": $customerEmail,
                                "from": "info@codaemonsoftwares.com",
                                "bcc": [
                                  "tay@korafirness.com"
                                ],
                                "subject": "Customer account activation",
                                "custom_message": Hello World
                              }
                            }');
        
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
    function accountInvitation($customerId)
    {
        //url to send invite
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "/account_activation_url.json";
        $method = 'POST';

        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
        $curl = new CurlCall($url, $method, $headers, $data);
        return $curl->execute();
    }
    function changeCustomerState($customerId, $customerDetails)
    {

        $data = json_encode($customerDetails);
        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
        
        //url to apply customer tag
        $url = $this->shopifyBaseURL . "/admin/customers/" .$customerId. "." ."json";
        $method = 'PUT';

        $curl = new CurlCall($url, $method, $headers, $data);
        
        return $curl->execute();
    }
    function removeCouponCode($coupon, $pricingRuleId) {
        $url = $this->shopifyBaseURL . "/admin/price_rules/".$coupon."/discount_codes/".$pricingRuleId.".json";
        $method = 'DELETE';
        //set headers
        $headers = array(
            'APIKEY: '.$this->apiKey,
            'Content-Type: application/json',
         );
        $data = [];
        $curl = new CurlCall($url, $method, $headers, $data);
        return $curl->execute();
    }
}
