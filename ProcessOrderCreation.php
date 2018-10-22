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

$orderDetails = json_decode('{"orders": 
    {
      "id": 450789469,
      "email": "bob.norman@hostmail.com",
      "token": "b1946ac92492d2347c6235b4d2611184",
  		"discount_applications": 
	        {
	          "type": "discount_code",
	          "value": "100",
	          "value_type": "amount",
	          "allocation_method": "across",
	          "target_selection": "all",
	          "target_type": "line_item",
	          "code": "VIPCUSTOMER100OFF"
	        },
        "fulfillments": 
	      {
	        "id": 255858046,
	        "order_id": 450789469,
	        "status": "success",
		    "processing_method": "direct",
		    "checkout_id": 901414060
	  	  },
	  	   "customer": 
	  	   {
	  	   	"default_address":
	  	   	 {
		        "id": 207119551,
		        "customer_id": 207119551
		  	 }
	  	   }
  	}}');

	$orderId = $orderDetails->orders->id;
	$customerId = $orderDetails->orders->customer->default_address->customer_id;
	$status = $orderDetails->orders->fulfillments->status;
	$couponCode = $orderDetails->orders->discount_applications->code;

	$vipMembership->checkCoupon($orderId, $customerId, $status, $couponCode);//check coupon from db and webhook response

	$creditUsed = $vipMembership->getAmountFromCoupon($customerId);//amount from coupon table
	
	$creditAmount = $vipMembership->getCreditAmount($customerId);//get credit from db
	
	$vipMembership->updateCredit($customerId, $creditUsed, $creditAmount); //update credit

	$amountRemaining = $creditAmount - $creditUsed;
	$vipMembership->updateVipMemberCredit($customerId, $amountRemaining);//update amount in vipmember table
	

?>