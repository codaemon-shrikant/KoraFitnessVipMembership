<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();

/*$subscriptionDetails = json_decode('{"subscription": {"id": 24216075, "address_id": 18334491, "customer_id": 16401639, "created_at": "2018-10-09T05:23:27", "updated_at": "2018-10-09T05:23:28", 
"next_charge_scheduled_at": "2018-10-09T00:00:00", "cancelled_at": null, "product_title": "VIP Member  Auto renew", "variant_title": "", 
"price": 0.01, "quantity": 1, "status": "ACTIVE", 
"shopify_product_id": 1531595554927, "shopify_variant_id": 13659551989871, "sku": null, "order_interval_unit": "month", "order_interval_frequency": "1", 
"charge_interval_frequency": "1", "cancellation_reason": null, "cancellation_reason_comments": null, "order_day_of_week": null, "order_day_of_month": 0, 
"properties": [], "expire_after_specific_number_of_charges": null, "max_retries_reached": 0, "has_queued_charges": 1}}');
*/

// Shopify User Data
/*
{"customer":{"id":741158289519,"email":"yogesh.suryawanshi@codaemonsoftwares.com","accepts_marketing":false,"created_at":"2018-09-04T15:01:48-04:00","updated_at":"2018-10-09T02:17:24-04:00","first_name":"Yogesh","last_name":"Suryawanshi","orders_count":11,"state":"enabled","total_spent":"0.00","last_order_id":657152311407,"note":"","verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":null,"tags":"VIP Customer New","last_order_name":"#1011","addresses":[{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}],"admin_graphql_api_id":"gid:\/\/shopify\/Customer\/741158289519","default_address":{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}}}
*/

$subscriptionDetails = json_decode(file_get_contents('php://input'));

// Get customer details from recharge 
$customerId =  $subscriptionDetails->subscription->customer_id;
$rechargeCustomerDetails = $rechargeApi->getCustomer($customerId);
//Check if recharge have shopify customer id
if($rechargeCustomerDetails->customer->shopify_customer_id) {
	//Read Requierd data from subscription details
 	$nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at; 
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
    	$vipMembership->addVipMemberDetails($customerId, $shopifyCustomerId, $nextChargeDate, $creditAmount);
    }

    // Set VIP tag to the customer
	$customerDetailsToUpdate = array(
            'customer' =>
                array(
                   'id' => $shopifyCustomerDetails->customer->id,
                   'tags' => 'VIP,'.$shopifyCustomerDetails->customer->tags,
                )
        );
	$tagResponse = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);
	// Add entry in credit details table 
	$vipMembership->updateCreditDetails($customerId, $creditAmount, $subscriptionDetails->subscription->price, '1');
	$vipMembership->addSubscriptionDetails($subscriptionDetails);

} else {
	echo "Customer Not Found";
}
