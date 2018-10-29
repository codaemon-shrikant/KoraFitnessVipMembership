<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();

$customerID = 838120931439;
$customerDetails = $shopifyApi->getCustomer($customerID);

$shopifyCustomerId = $customerDetails->customer->id;
$customerTag = $customerDetails->customer->tags;
$removeVIPTag = chop($customerTag,"VIP");

$customerDetailsToUpdate = array(
	            'customer' =>
	                array(
	                   'id' => $customerDetails->customer->id,
	                   'tags' => $removeVIPTag,
	                )
	        );
$removeVIPTag = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);

?>;