<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();




$chargeDetails = json_decode(file_get_contents('php://input'));

$subscriptionId = $chargeDetails->charge->line_items[0]->subscription_id;//subscription_id from charge paid response. 
//print_r($subscriptionId);echo "<br>subscriptionId";
$rechargeCustomerId = $chargeDetails->charge->customer_id;
$vipMemberDetails = $vipMembership->getVipMemberDetails($rechargeCustomerId);
$shopifyCustomerId = $vipMemberDetails['shopify_customer_id'];

$vipMembership->addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionId, $chargeDetails->charge->id, $chargeDetails->charge->status, str_replace("T"," ",$chargeDetails->charge->created_at) , str_replace("T"," ",$chargeDetails->charge->updated_at));//add charge details in db

$creditAmount = $chargeDetails->charge->line_items[0]->price;

$customerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
$customerTag = $customerDetails->customer->tags;
    
if ($chargeDetails->charge->status == "SUCCESS") //if charge status is SUCCESS 
{	
	$creditAmount += $vipMemberDetails['credit_amount'];
	

	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => 'VIP,'.$customerDetails->customer->tags,
		                )
		        );
	$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag
    
    $subscriptionDetails = $rechargeApi->getSubscriptionDetails($subscriptionId);//subscription details from recharge

    $file_handle = fopen('my_filename.json', 'w');
    fwrite($file_handle, json_encode($subscriptionDetails));
    fclose($file_handle);

    $nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at;

    $vipMembership->updateVipMember($shopifyCustomerId, $creditAmount, $nextChargeDate, 1);//update vip_membership table next_charge_date

}
else
{

	$vipMembership->updateFailedVipMember($shopifyCustomerId, 0);//update status to 0

	$removeVIPTag = chop($customerTag,"VIP");

	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => $removeVIPTag,
		                )
		        );
	$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag
}


?>