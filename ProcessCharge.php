<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();
/*
$chargeDetails = json_decode('{

            "address_id": 10175825,
            "billing_address": {
                "address1": "1933 Manning",
                "address2": "204",
                "city": "los angeles",
                "company": "bootstrap",
                "country": "United States",
                "first_name": "Recharge",
                "last_name": "Test",
                "phone": "2345678890",
                "province": "California",
                "zip": "90025"
            },
            "client_details": {
                "browser_ip": null,
                "user_agent": null
            },
            "created_at": "2018-04-18T18:31:45",
            "customer_hash": "102321926ce78d5818c43eb7",
            "customer_id": 17818803,
            "discount_codes": [
                {
                    "amount": "12.00",
                    "code": "test|jana=12",
                    "type": "percentage"
                }
            ],
            "email": "support123128742981274981247219874@rechargepayments.com",
            "first_name": "test",
            "id": 52528068,
            "last_name": "test123457890",
            "line_items": [
                {
                    "grams": 0,
                    "price": "1000.00",
                    "properties": [],
                    "quantity": 1,
                    "shopify_product_id": "6032558662",
                    "shopify_variant_id": "19099995014",
                    "sku": "",
                    "subscription_id": 25745214,
                    "title": "testing discount",
                    "variant_title": "",
                    "vendor": "Example Storeeeeeee"
                },
                {
                    "grams": 0,
                    "price": "10.00",
                    "properties": [],
                    "quantity": 1,
                    "shopify_product_id": "9056368134",
                    "shopify_variant_id": "33049846854",
                    "sku": "",
                    "subscription_id": 14877530,
                    "title": "prepaid 3 Months shirts with sizes",
                    "variant_title": "Small",
                    "vendor": "Example Store"
                }
            ],
            "note": " next order #1   - Subscription Recurring Order",
            "note_attributes": null,
            "processed_at": "2018-10-08T12:52:49",
            "scheduled_at": "2009-05-05T00:00:00",
            "shipments_count": 1,
            "shipping_address": {
                "address1": "1933 Manning",
                "address2": "204",
                "city": "los angeles",
                "company": "bootstrap",
                "country": "United States",
                "first_name": "Recharge",
                "last_name": "Test",
                "phone": "2345678890",
                "province": "California",
                "zip": "90025"
            },
            "shipping_lines": [
                {
                    "code": "free-shipping",
                    "price": "0.00",
                    "title": "Standard Shipping 2"
                }
            ],
            "shopify_order_id": "629761015896",
            "status": "SUCCESS",
            "sub_total": null,
            "subtotal_price": 888.8,
            "tags": "Subscription, Subscription Recurring Order",
            "tax_lines": 0,
            "total_discounts": "121.20",
            "total_line_items_price": "1010.00",
            "total_price": "888.80",
            "total_refunds": null,
            "total_tax": 0,
            "total_weight": 0,
            "type": "RECURRING",
            "updated_at": "2018-10-15T10:35:07"

 }
');
*/
$chargeDetails = json_decode(file_get_contents('php://input'));

$subscriptionId = $chargeDetails->line_items[0]->subscription_id;//subscription_id from charge paid response. 
$rechargeCustomerId = $chargeDetails->customer_id;
$vipMemberDetails = $vipMembership->getVipMemberDetails($rechargeCustomerId);

$shopifyCustomerId = $vipMemberDetails['shopify_customer_id'];
$subscriptionDetails = $rechargeApi->getSubscriptionDetails($subscriptionId);//subscription details from recharge

$nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at;

$vipMembership->addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionId, str_replace("T"," ",$nextChargeDate) ,  $chargeDetails->id, $chargeDetails->status, str_replace("T"," ",$chargeDetails->created_at) , str_replace("T"," ",$chargeDetails->updated_at));//add charge details in db

$creditAmount = $chargeDetails->line_items[0]->price;

$customerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
$customerTag = $customerDetails->customer->tags;


if ($chargeDetails->status == "SUCCESS") //if charge status is SUCCESS 
{	
	$creditAmount += $vipMemberDetails['credit_amount'];
	$vipMembership->updateVipMember($shopifyCustomerId, $creditAmount, $nextChargeDate, 1);//update vip_membership table next_charge_date

	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => 'VIP,'.$customerDetails->customer->tags,
		                )
		        );
	$updatedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag
}
else
{

	$vipMembership->updateFailedVipMember($shopifyCustomerId, 0);//update status to 0

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