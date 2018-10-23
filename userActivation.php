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

$customerId = 807087177839;
$customerDetails = $shopifyApi->getCustomer($customerId);
$customerState = $customerDetails->customer->state;
$customerEmail = $customerDetails->customer->email;
if ($customerState != "active") 
{
	$customerActive = $shopifyApi->send_invite($customerId, $customerEmail);
	print_r($customerActive);
}
else
{
	echo "Customer is active";
}


?>