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
	$customerDetails = $shopifyApi->getCustomer($customer['shopify_customer_id']);
	$customerTag = $customerDetails->customer->tags;
	$vipMembership->updateFailedVipMember($customer['shopify_customer_id'], 0);//update status to 0

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