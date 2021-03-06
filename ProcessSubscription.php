<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();

/*$subscriptionDetails = json_decode('{"subscription": {"id": 24216075, "address_id": 18334491, "customer_id": 16401639, "created_at": "2018-11-23T05:23:27", "updated_at": "2018-10-09T05:23:28", 
"next_charge_scheduled_at": "", "cancelled_at": null, "product_title": "VIP Member  Auto renew", "variant_title": "", 
"price": 0.01, "quantity": 1, "status": "ACTIVE", 
"shopify_product_id": 1531595554927, "shopify_variant_id": 13659551989871, "sku": null, "order_interval_unit": "month", "order_interval_frequency": "1", 
"charge_interval_frequency": "1", "cancellation_reason": null, "cancellation_reason_comments": null, "order_day_of_week": null, "order_day_of_month": 0, 
"properties": [], "expire_after_specific_number_of_charges": null, "max_retries_reached": 0, "has_queued_charges": 1}}');


// Shopify User Data
/*
{"customer":{"id":741158289519,"email":"yogesh.suryawanshi@codaemonsoftwares.com","accepts_marketing":false,"created_at":"2018-09-04T15:01:48-04:00","updated_at":"2018-10-09T02:17:24-04:00","first_name":"Yogesh","last_name":"Suryawanshi","orders_count":11,"state":"enabled","total_spent":"0.00","last_order_id":657152311407,"note":"","verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":null,"tags":"VIP Customer New","last_order_name":"#1011","addresses":[{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}],"admin_graphql_api_id":"gid:\/\/shopify\/Customer\/741158289519","default_address":{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}}}
*/
$subscriptionDetails = json_decode(file_get_contents('php://input'));

$subscriptionId = $subscriptionDetails->subscription->id; 

// Get customer details from recharge 
$customerId =  $subscriptionDetails->subscription->customer_id;
$rechargeCustomerDetails = $rechargeApi->getCustomer($customerId);

//Check if recharge have shopify customer id
if($rechargeCustomerDetails->customer->shopify_customer_id) {

	//Read Requierd data from subscription details
 	$nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at; 
 	if($nextChargeDate == null) {

	    $scheduledAt = str_replace("T"," ", $subscriptionDetails->subscription->created_at);
	    $orderIntervalFrequency = $subscriptionDetails->subscription->order_interval_frequency;
	    $orderIntervalUnit = $subscriptionDetails->subscription->order_interval_unit;
	    $nextChargeDate = date('Y-m-d', strtotime($scheduledAt. ' + '.$orderIntervalFrequency . $orderIntervalUnit . 's'));
	}
    $creditAmount = $subscriptionDetails->subscription->price; 
   

	// Get VIP Membership Details from db
	$vipMemberDetails = $vipMembership->getVipMemberDetails($customerId);
	$shopifyCustomerId = $rechargeCustomerDetails->customer->shopify_customer_id;
	// get shopify customer details
	$shopifyCustomerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
	if ($vipMemberDetails) {
		
		$creditAmount += $vipMemberDetails['credit_amount'];
		$vipMembership->updateVipMemberDetails($customerId, $nextChargeDate, $creditAmount, 1);
    } else {
    	// Add customer Details to the vip_member
		$addCustomerDetails = array(
	            'customer' =>
	                array(
	                   'email' => $shopifyCustomerDetails->customer->email,
	                   'customer_name' => $shopifyCustomerDetails->customer->first_name. ' ' . $shopifyCustomerDetails->customer->last_name,
	                )
	        );
    	$email = $addCustomerDetails['customer']['email'];
		$customer_name = $addCustomerDetails['customer']['customer_name'];
    	$vipMembership->addVipMemberDetails($customerId, $email, $customer_name, $shopifyCustomerId, $subscriptionId, $nextChargeDate, $creditAmount);

	    // Set VIP tag to the customer
		$customerDetailsToUpdate = array(
	            'customer' =>
	                array(
	                   'id' => $shopifyCustomerDetails->customer->id,
	                   'tags' => 'CHAMPION,'.$shopifyCustomerDetails->customer->tags,
	                )
	        );
		$tagResponse = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);
	}
	// Add entry in credit details table 
	$vipMembership->insertCredit($shopifyCustomerId, $creditAmount, $subscriptionDetails->subscription->price, '1');
	//$vipMembership->addSubscriptionDetails($subscriptionDetails);
	$vipMembership->addChargeDetails($customerId, $shopifyCustomerId, $subscriptionId, $nextChargeDate, 0, str_replace("T"," ", $subscriptionDetails->subscription->created_at) , str_replace("T"," ", $subscriptionDetails->subscription->updated_at), "SUCCESS");

	/* add user to to champion subscription list in mailchimp */
	$data = array (
        	    "email_address" => $rechargeCustomerDetails->customer->email,
			    "status"=> "subscribed",
			    "merge_fields" => array(
			        "FNAME"=> $rechargeCustomerDetails->customer->first_name,
			        "LNAME"=> $rechargeCustomerDetails->customer->last_name
			    	)
				);
	$url = 'https://us17.api.mailchimp.com/3.0/lists/77963e01cb/members';
	$method = "POST";
	$headers = array(
	  'Content-Type: application/json',
	  'Authorization: apikey 0b587992823a0cc7e88932280cc50f60-us17',
	  "Accept: application/json"
	);
	$curl = new CurlCall($url, $method, $headers, $data);

	$result = $curl->execute();

} else {
	echo "Customer Not Found";
}
