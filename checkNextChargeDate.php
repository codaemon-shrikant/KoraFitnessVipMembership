<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();

$results = $vipMembership->checkDeactivatedCustomers();
while($deactivatedCustomers = $results->fetch_assoc()) { 
	$shopifyCustomerId = $deactivatedCustomers['shopify_customer_id'];
	$customerDetails = $shopifyApi->getCustomer($deactivatedCustomers['shopify_customer_id']);
	$customerTag = $customerDetails->customer->tags;
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
		                   'tags' => $removeVIPTag
		                )
		        );
		$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag
	}
}

?>