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

$orderDetails = file_get_contents('php://input');
$file_handle = fopen('my_filename.json', 'w');
    fwrite($file_handle, $orderDetails);
    fclose($file_handle);

	$orderId = $orderDetails->id;
	$customerId = $orderDetails->customer->id;
	$status = $orderDetails->fulfillments->status;
	$couponCode = $orderDetails->discount_codes->0->code;

	$vipMembership->checkCoupon($orderId, $customerId, $status, $couponCode);//check coupon from db and webhook response

	$creditUsed = $vipMembership->getAmountFromCoupon($customerId);//amount from coupon table
	
	$creditAmount = $vipMembership->getCreditAmount($customerId);//get credit from db
	
	$vipMembership->updateCredit($customerId, $creditUsed, $creditAmount); //update credit

	$amountRemaining = $creditAmount - $creditUsed;
	$vipMembership->updateVipMemberCredit($customerId, $amountRemaining);//update amount in vipmember table
	

?>