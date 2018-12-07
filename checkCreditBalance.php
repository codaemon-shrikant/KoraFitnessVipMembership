<?php 
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
require "CurlCall.php";

include "VipMembership.php";

$vipMembership = new VipMembership();

$customerId = $_GET['customer_id'];
$vipMemberDetails = $vipMembership->getVipMemberDetailsByShopifyCustomerId($customerId);

if($vipMemberDetails) {
    // Get  credit amount from db
    $creditAmount = $vipMembership->getCreditAmount($customerId);
    $jsonFormat = $vipMembership->jsonFormatForCreditBalance($creditAmount);
    print_r($jsonFormat);
} 
else 
{
    echo "Customer Not Found";
}
