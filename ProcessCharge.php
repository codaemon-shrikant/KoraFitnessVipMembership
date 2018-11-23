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
/*
$chargeDetails = json_decode('{"charge": {"address_id": 20054040, "last_name": "Jadhav", "subtotal_price": 0.5, "shipping_lines": [], "sub_total": null, "updated_at": "2018-11-02T05:45:16", "total_weight": 0, 
"customer_hash": "178671963cd47cbdff39503a", "processed_at": "2018-11-02T05:45:16", "id": 96427368, "first_name": "Shrikant16", "discount_codes": [], "note": null, 
"total_line_items_price": "0.50", "customer_id": 17867196, "type": "RECURRING", "email": "shrikant16@yopmail.com", 
"scheduled_at": "2018-11-02T00:00:00", "status": "FAILED", "total_tax": 0.0, 
"billing_address": {"province": "Maharashtra", "city": "Pune", "first_name": "Shrikant16", "last_name": "Jadhav", "zip": "411028", "country": "India", "address1": "Pune 16", 
"address2": "", "company": "Codaemon16", "phone": ""}, "tax_lines": 0.0, "tags": "Subscription, Subscription Recurring Order", "shopify_order_id": null, 
"total_discounts": "0.0", "line_items": [{"sku": "", "vendor": "korafitness-dev", "grams": 0, "shopify_variant_id": "13659551989871", "title": "VIP Member", 
"price": "0.50", "variant_title": "1", 
"subscription_id": 25816866, "shopify_product_id": "1531595554927", "properties": [{"name": "shipping_interval_frequency", "value": "1"}, 
{"name": "shipping_interval_unit_type", "value": "Months"}], "quantity": 1}], "total_price": "0.50", "created_at": "2018-11-02T03:48:31", "total_refunds": null, "note_attributes": [], 
"shipping_address": {"province": "Maharashtra", "city": "Pune", "first_name": "Shrikant16", "last_name": "Jadhav", "zip": "411028", "country": "India", 
"address1": "Pune 16", "address2": "", "company": "Codaemon16", "phone": ""}, "client_details": {"user_agent": null, "browser_ip": null}, "shipments_count": null}}');

$subscriptionDetails = json_decode('{"subscription":{"address_id":20054040,"cancellation_reason":null,"cancellation_reason_comments":null,"cancelled_at":null,"charge_interval_frequency":"1",
"created_at":"2018-10-30T09:21:00","customer_id":17867196,"expire_after_specific_number_of_charges":null,"has_queued_charges":0,"id":25816866,"max_retries_reached":0,
"next_charge_scheduled_at":null,"order_day_of_month":0,"order_day_of_week":null,"order_interval_frequency":"1","order_interval_unit":"day","price":0.5,
"product_title":"VIP Member","properties":[{"name":"shipping_interval_frequency","value":"1"},{"name":"shipping_interval_unit_type","value":"Months"}],
"quantity":1,"shopify_product_id":1531595554927,"shopify_variant_id":13659551989871,"sku":"","status":"ACTIVE","updated_at":"2018-10-31T10:59:31","variant_title":"1"}}'); 
*/
$chargeDetails = json_decode(file_get_contents('php://input'));

$subscriptionId = $chargeDetails->charge->line_items[0]->subscription_id;//subscription_id from charge paid response. 
$rechargeCustomerId = $chargeDetails->charge->customer_id;
$vipMemberDetails = $vipMembership->getVipMemberDetails($rechargeCustomerId);
$shopifyCustomerId = $vipMemberDetails['shopify_customer_id'];
//print_r($shopifyCustomerId);
    
$subscriptionDetails = $rechargeApi->getSubscriptionDetails($subscriptionId);//subscription details from recharge

$nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at;
   
if($nextChargeDate == null) {

    $scheduledAt = str_replace("T"," ",$chargeDetails->charge->scheduled_at);
    $orderIntervalFrequency = $subscriptionDetails->subscription->order_interval_frequency;
    $orderIntervalUnit = $subscriptionDetails->subscription->order_interval_unit;
    $nextChargeDate = date('Y-m-d h:m:s', strtotime($scheduledAt. ' + '.$orderIntervalFrequency . $orderIntervalUnit . 's'));
}

$vipMembership->addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionId, str_replace("T"," ",$nextChargeDate), $chargeDetails->charge->id, str_replace("T"," ",$chargeDetails->charge->created_at) , str_replace("T"," ",$chargeDetails->charge->updated_at), $chargeDetails->charge->status);//add charge details in db

$creditAmount = $chargeDetails->charge->line_items[0]->price;
$customerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
$customerTag = $customerDetails->customer->tags;
    /*$file_handle = fopen('my_filename.json', 'w');
    fwrite($file_handle, file_get_contents('php://input').json_encode($subscriptionDetails));
    fclose($file_handle);
    */
if ($chargeDetails->charge->status == "SUCCESS") //if charge status is SUCCESS 
{
	$creditAmount += $vipMemberDetails['credit_amount'];
	$tags = $explode(",",$customerTag);
	$newTags =array();
	foreach ($tags as $key => $value) {
		$value = trim($value);
		array_push($newTags,$value);
    }
    if(!in_array('VIP', $newTags)) {
    	$addVIPTag = implode(",", $newTags);

    	$customerDetailsToUpdate = array (
		            "customer" =>
		                array(
		                	"id" => $shopifyCustomerId,
		                   	"tags" => "VIP,". $addVIPTag
		                )
		        );

    	//print_r(json_encode($customerDetailsToUpdate),true);

		$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//add the VIP tag
		
    }
	$vipMembership->updateVipMember($shopifyCustomerId, $creditAmount, $nextChargeDate, 1);//update vip_membership table next_charge_date

}
else
{
	$vipMembership->updateFailedVipMember($shopifyCustomerId, 0);//update status to 0
    $tags = explode(",",$customerTag);
	
	$newTags =array();
	foreach ($tags as $key => $value) {
		$value = trim($value);
		array_push($newTags, $value);
    }
    
    if(in_array('VIP', $newTags)) {
    	unset($newTags[array_search('VIP', $newTags)]);
    	
    	$removeVIPTag = implode(", ", $newTags);

    	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' =>$removeVIPTag 
		                )
		        );
		$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag
		print_r($updatedCustomerTags); 
	}
}

?>