<?php
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();


$orderDetails = json_decode(file_get_contents('php://input'));

	$orderId = $orderDetails->id;
	$customerId = $orderDetails->customer->id;
	$status = $orderDetails->fulfillments->status;
	$couponCode = $orderDetails->discount_codes[0]->code;


	$file_handle = fopen('my_filename.json', 'w');
    fwrite($file_handle, $orderId. " + ".$customerId." + ".$couponCode);
    fclose($file_handle);

	$vipMembership->checkCoupon($orderId, $customerId, $status, $couponCode);//check coupon from db and webhook response

	$creditUsed = $vipMembership->getAmountFromCoupon($customerId);//amount from coupon table
	
	$creditAmount = $vipMembership->getCreditAmount($customerId);//get credit from db
	
	$vipMembership->updateCredit($customerId, $creditUsed, $creditAmount); //update credit

	$amountRemaining = $creditAmount - $creditUsed;
	$vipMembership->updateVipMemberCredit($customerId, $amountRemaining);//update amount in vipmember table
	

?>