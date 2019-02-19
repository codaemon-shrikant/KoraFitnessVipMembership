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

$getVIPMemberDetails = $vipMembership->getVipMembers();

while($vipMemberDetails = $getVIPMemberDetails->fetch_array()) { 
		$shopifyCustomerId = $vipMemberDetails['shopify_customer_id'];
		$shopifyCustomerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
		$vipMembership->updateCustomerDetails($shopifyCustomerDetails);

}

?>