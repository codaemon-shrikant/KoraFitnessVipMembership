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

/*$chargeDetails = json_decode('{"charge": {"address_id": 22327855, "last_name": "Jadhav", "subtotal_price": 0.5, "shipping_lines": [], "sub_total": null, "updated_at": "2018-12-27T03:35:37", "total_weight": 0, "customer_hash": "197411927fbe26f66ad4451e", "processed_at": "2018-12-27T03:35:37", "id": 105395005, "first_name": "Shrikant", "discount_codes": [], "has_uncommited_changes": false, "note": null, "total_line_items_price": "0.50", "customer_id": 19741192, "type": "RECURRING", "email": "shrikant12@yopmail.com", "scheduled_at": "2018-12-27T00:00:00", "status": "SUCCESS", "total_tax": 0.0, "billing_address": {"province": "Maharashtra", "city": "Pune", "first_name": "Shrikant", "last_name": "Jadhav", "zip": "411028", "country": "India", "address1": "425, amanora chembers", "address2": "Hadapsar", "company": "Codaemon", "phone": "8983063699"}, "tax_lines": 0.0, "tags": "Subscription, Subscription Recurring Order", "shopify_order_id": null, "total_discounts": "0.0", "line_items": [{"sku": "Champion", "vendor": "Kora Fitness LLC.", "grams": 0, "shopify_variant_id": "12802571141164", "title": "Champion Membership", "price": "0.50", "variant_title": "", "subscription_id": 28762555, "shopify_product_id": "1456443818028", "properties": [{"name": "shipping_interval_frequency", "value": "1"}, {"name": "shipping_interval_unit_type", "value": "Months"}], "quantity": 1}], "total_price": "0.50", "created_at": "2018-11-27T10:48:18", "total_refunds": null, "note_attributes": [{"name": "SMS Opt-in", "value": "Yes"}], "shipping_address": {"province": "Maharashtra", "city": "Pune", "first_name": "Shrikant", "last_name": "Jadhav", "zip": "411028", "country": "India", "address1": "425, amanora chembers", "address2": "Hadapsar", "company": "Codaemon", "phone": "8983063699"}, "client_details": {"user_agent": null, "browser_ip": null}, "shipments_count": null}}');
/*
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
    $nextChargeDate = date('Y-m-d', strtotime($scheduledAt. ' + '.$orderIntervalFrequency . $orderIntervalUnit . 's'));
}

$vipMembership->addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionId, str_replace("T"," ",$nextChargeDate), $chargeDetails->charge->id, str_replace("T"," ",$chargeDetails->charge->created_at) , str_replace("T"," ",$chargeDetails->charge->updated_at), $chargeDetails->charge->status);//add charge details in db

$creditAmount = $chargeDetails->charge->line_items[0]->price;
$customerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
$customerTag = $customerDetails->customer->tags;

    $file_handle = fopen('processCharge'.date('_Y_m_d_H_i_s').'.json', 'w');
    fwrite($file_handle, file_get_contents('php://input').json_encode($subscriptionDetails));
    fclose($file_handle);

if ($chargeDetails->charge->status == "SUCCESS") //if charge status is SUCCESS 
{
	$creditAmount += $vipMemberDetails['credit_amount'];
	$tags = explode(",",$customerTag);
	$newTags =array();
	foreach ($tags as $key => $value) {
		$value = trim($value);
		array_push($newTags,$value);
    }
    if(!in_array('CHAMPION', $newTags)) {
    	$addVIPTag = implode(",", $newTags);

    	$customerDetailsToUpdate = array (
		            "customer" =>
		                array(
		                	"id" => $shopifyCustomerId,
		                   	"tags" => "CHAMPION,". $addVIPTag
		                )
		        );

    	//print_r(json_encode($customerDetailsToUpdate),true);

		$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//add the VIP tag
		
    }
	$vipMembership->updateVipMember($shopifyCustomerId, $creditAmount, $nextChargeDate, 1);//update vip_membership table next_charge_date
	$vipMembership->insertCredit($shopifyCustomerId, $creditAmount, $chargeDetails->charge->line_items[0]->price, '1');
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
    
    if(in_array('CHAMPION', $newTags)) {
    	unset($newTags[array_search('CHAMPION', $newTags)]);
    	
    	$removeVIPTag = implode(", ", $newTags);

    	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' =>$removeVIPTag 
		                )
		        );
		$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag
	}
}

?>
