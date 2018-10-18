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

header("Access-Control-Allow-Origin: *");

/*$subscriptionDetails = json_decode('{"subscription": {"id": 24216075, "address_id": 18334491, "customer_id": 16401639, "created_at": "2018-10-09T05:23:27", "updated_at": "2018-10-09T05:23:28", 
"next_charge_scheduled_at": "2018-10-09T00:00:00", "cancelled_at": null, "product_title": "VIP Member  Auto renew", "variant_title": "", 
"price": 0.01, "quantity": 1, "status": "ACTIVE", 
"shopify_product_id": 1531595554927, "shopify_variant_id": 13659551989871, "sku": null, "order_interval_unit": "month", "order_interval_frequency": "1", 
"charge_interval_frequency": "1", "cancellation_reason": null, "cancellation_reason_comments": null, "order_day_of_week": null, "order_day_of_month": 0, 
"properties": [], "expire_after_specific_number_of_charges": null, "max_retries_reached": 0, "has_queued_charges": 1}}');

// Shopify User Data
/*
{"customer":{"id":741158289519,"email":"yogesh.suryawanshi@codaemonsoftwares.com","accepts_marketing":false,"created_at":"2018-09-04T15:01:48-04:00","updated_at":"2018-10-09T02:17:24-04:00","first_name":"Yogesh","last_name":"Suryawanshi","orders_count":11,"state":"enabled","total_spent":"0.00","last_order_id":657152311407,"note":"","verified_email":true,"multipass_identifier":null,"tax_exempt":false,"phone":null,"tags":"VIP Customer New","last_order_name":"#1011","addresses":[{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}],"admin_graphql_api_id":"gid:\/\/

\/Customer\/741158289519","default_address":{"id":802227650671,"customer_id":741158289519,"first_name":"Yogesh1","last_name":"Suryawanshi1","company":"","address1":"Pune","address2":"Test","city":"Pune","province":"Maharashtra","country":"India","zip":"431116","phone":"","name":"Yogesh1 Suryawanshi1","province_code":"MH","country_code":"IN","country_name":"India","default":true}}}
*/
$order_id = $_GET['order_id'];
$defaultDiscountinPercentage = $_GET['default_discount'];
$cartTotal = $_GET['cart_total'];
$customerId = $_GET['customer_id'];

//$cartDetails = file_get_contents('php://input');

// Get customer details from recharge 
//$customerId =  $subscriptionDetails->subscription->customer_id;
//$rechargeCustomerDetails = $rechargeApi->getCustomer($customerId);
//Check if recharge have shopify customer id

if($customerId) {
    //Read Requierd data from subscription details
    //$shopifyCustomerId = $rechargeCustomerDetails->customer->shopify_customer_id;

    // Get  credit amount from db
    $creditAmount = $vipMembership->getCreditAmount($customerId);
    $amountAfterDiscount = $genrateCoupon->calculateDiscount($defaultDiscountinPercentage, $cartTotal);//calculate amount after default discount
    //$remainingValueToPay = abs($creditAmount - $amountAfterDiscount);

    if($creditAmount > 0) {
        if ($creditAmount < $amountAfterDiscount) 
          { 
            
            $creditDiscount = $genrateCoupon->CreditDiscount($creditAmount, $cartTotal);
            $totalDiscount = $genrateCoupon->TotalDiscount($defaultDiscountinPercentage, $creditDiscount);

            $code = generateToken($totalDiscount, $customerId);
            $creditBalance = 0; //Remaining Balance
            $amount = $creditAmount; //Credit from db
            $creditPercent = $creditDiscount;

            $vipMembership->updateCoupons($order_id, $code);
            $vipMembership->updateCreditDetails($customerId, $creditBalance, $amount, '0');
            $vipMembership->updateVipMemberCredit($customerId, $creditBalance);
            $jsonFormat = $vipMembership->jsonFormat($code, $amount, $creditPercent, $totalDiscount, $creditBalance);
            print_r($jsonFormat);
          } 
        else 
          {  
            $amountToUseFromCredit = $amountAfterDiscount;
            $totalDiscount = $genrateCoupon->CreditDiscountFor100percent();
            
            $code =  generateToken($totalDiscount, $customerId);
            
            $creditBalance = $creditAmount - $amountToUseFromCredit; 
            $amount = $amountToUseFromCredit; //Credit from db
            $creditPercent = $amount * 100;

            $vipMembership->updateCoupons($order_id, $code);
            $vipMembership->updateVipMemberCredit($customerId, $creditBalance);
            $vipMembership->updateCreditDetails($customerId, $creditBalance, $amount, '0');

            $jsonFormat = $vipMembership->jsonFormat($code, $amount, $creditPercent, $totalDiscount, $creditBalance);
            print_r($jsonFormat);
        }
    } else {
        $jsonFormat = $vipMembership->jsonFormat(null, 0, 0, $defaultDiscountinPercentage, $creditAmount);
            print_r($jsonFormat);
    }

} else {
    echo "Customer Not Found";
}

function generateToken($totalDiscount, $shopifyCustomerId) {
    global $shopifyApi;
        
    $generateCode = $shopifyApi->generate_code($totalDiscount);//generate coupon code for tottal discount
        
    $code = $generateCode['discount_code']['code'];
    $price_rules = $shopifyApi->price_rules($code, $totalDiscount, $shopifyCustomerId);//generate price rules
    $price_rule_id = $shopifyApi->price_rule_id($price_rules);//generate price rule id
    $rule_id = $price_rule_id->price_rule->id;//get price rule id
    $createCoupon = $shopifyApi->createDiscount($generateCode, $rule_id);//generate discount in shopify'
    return $code;
}
