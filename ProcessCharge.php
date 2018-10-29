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
$file_handle = fopen('my_filename.json', 'w');
fwrite($file_handle, file_get_contents('php://input'));
fclose($file_handle);

$subscriptionId = $chargeDetails->line_items[0]->subscription_id;//subscription_id from charge paid response. 

$rechargeCustomerId = $chargeDetails->customer_id;
$vipMemberDetails = $vipMembership->getVipMemberDetails($rechargeCustomerId);
$shopifyCustomerId = $vipMemberDetails['shopify_customer_id'];
$subscriptionDetails = $rechargeApi->getSubscriptionDetails($subscriptionId);//subscription details from recharge


$vipMembership->addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionDetails,  $chargeDetails->id, $chargeDetails->status, str_replace("T"," ",$chargeDetails->created_at) , str_replace("T"," ",$chargeDetails->updated_at));//add charge details in db
$nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at;

$customerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
$customerTag = $customerDetails->customer->tags;


if ($chargeDetails->status == "SUCCESS") //if charge status is SUCCESS 
{	
	
	$vipMembership->updateVipMemberNextChargeDate($shopifyCustomerId, $nextChargeDate);//update vip_membership table next_charge_date
	$checkChargeDate = $vipMembership->checkChargeDate();
	/*$vipCustomerId = $checkChargeDate['shopify_customer_id'];
	 if ($vipCustomerId) 
	 {
	 	$vipMembership->updateVipMemberStatus($vipCustomerId, 1);
	 }
	*/
	 $customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => 'VIP,'.$removeVIPTag,
		                )
		        );
	$vipMembership->updateVipMemberStatus($vipCustomerId, 1);

}
else
{
	$removeVIPTag = chop($customerTag,"VIP");

	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => $removeVIPTag,
		                )
		        );
	$updateStatus = $vipMembership->updateVipMemberStatus($shopifyCustomerId, 0);//update status to 0
}

$updateedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag

 

?>