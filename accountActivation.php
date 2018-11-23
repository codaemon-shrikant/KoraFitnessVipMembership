<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "GenerateCoupon.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$genrateCoupon = new GenerateCoupon();
$vipMembership = new VipMembership();

$subscriptionDetails = json_decode('{"subscription": {"id": 24216075, "address_id": 18334491, "customer_id": 16401639, "created_at": "2018-10-09T05:23:27", "updated_at": "2018-10-09T05:23:28", 
"next_charge_scheduled_at": "2018-10-09T00:00:00", "cancelled_at": null, "product_title": "VIP Member  Auto renew", "variant_title": "", 
"price": 0.01, "quantity": 1, "status": "ACTIVE", 
"shopify_product_id": 1531595554927, "shopify_variant_id": 13659551989871, "sku": null, "order_interval_unit": "month", "order_interval_frequency": "1", 
"charge_interval_frequency": "1", "cancellation_reason": null, "cancellation_reason_comments": null, "order_day_of_week": null, "order_day_of_month": 0, 
"properties": [], "expire_after_specific_number_of_charges": null, "max_retries_reached": 0, "has_queued_charges": 1}}');

// Shopify User Data
/*$customerDetails =json_decode('{"customer": {
							    "id": 871124238447,
							    "email": "shrikant12@yopmail.com",
							    "accepts_marketing": false,
							    "created_at": "2018-10-23T15:29:24-04:00",
							    "updated_at": "2018-10-23T15:29:24-04:00",
							    "first_name": "Bob",
							    "last_name": "Norman",
							    "orders_count": 1,
							    "state": "disabled",
							    "total_spent": "41.94",
							    "last_order_id": 450789469,
							    "note": null,
							    "verified_email": true,
							    "multipass_identifier": null,
							    "tax_exempt": false,
							    "phone": null,
							    "tags": "",
							    "last_order_name": "#1001",
							    "currency": "USD",
							    "addresses": [
							      {
							        "id": 207119551,
							        "customer_id": 207119551,
							        "first_name": null,
							        "last_name": null,
							        "company": null,
							        "address1": "Chestnut Street 92",
							        "address2": "",
							        "city": "Louisville",
							        "province": "Kentucky",
							        "country": "United States",
							        "zip": "40202",
							        "phone": "555-625-1199",
							        "name": "",
							        "province_code": "KY",
							        "country_code": "US",
							        "country_name": "United States",
							        "default": true
							      }
							    ],
							    "admin_graphql_api_id": "gid://shopify/Customer/207119551",
							    "default_address": {
							      "id": 207119551,
							      "customer_id": 207119551,
							      "first_name": null,
							      "last_name": null,
							      "company": null,
							      "address1": "Chestnut Street 92",
							      "address2": "",
							      "city": "Louisville",
							      "province": "Kentucky",
							      "country": "United States",
							      "zip": "40202",
							      "phone": "555-625-1199",
							      "name": "",
							      "province_code": "KY",
							      "country_code": "US",
							      "country_name": "United States",
							      "default": true
							    }
							  }}');
customer":{"id":741158289519,"email":"yogesh.suryawanshi@codaemonsoftwares.com","accepts_marketing":false,"created_at":"2018-09-04T15:01:48-04:00","updated_at":"2018-10-09T02:17:24-04:00","first_name":"Yogesh","last_name":"Suryawanshi","orders_count":11,"state":"enabled","total_spent":"0.00","last_order_id":657152311407,"note":"","verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":null,"tags":"VIP Customer New","last_order_name":"#1011","addresses":[{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}],"admin_graphql_api_id":"gid:\/\/

\/Customer\/741158289519","default_address":{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}}}
*/

$subscriptionDetails = file_get_contents('php://input');

// Get customer details from recharge 
$customerId =  $subscriptionDetails->subscription->customer_id;;
$rechargeCustomerDetails = $rechargeApi->getCustomer($customerId);
$customerDetails = $shopifyApi->getCustomer($customerId);

//Check if recharge have shopify customer id
if($customerDetails->customer->state == "disabled") 
{
	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $customerDetails->customer->id,
		                   'state' => 'enabled',
		                )
		        );
	$changeCustomerState = $shopifyApi->changeCustomerState($customerDetails->customer->id, $customerDetailsToUpdate);
	//$sendInvite = $shopifyApi->send_invite($customerDetails->customer->id, $customerDetails->customer->email); 
	//$accountInvitation = $shopifyApi->accountInvitation($customerDetails->customer->id);

} 
else 
{
	echo "Customer Not Found";
}

?>