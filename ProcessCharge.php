<?php 
error_reporting(E_ALL);
require "CurlCall.php";

include "RechargeAPI.php";
include "ShopifyApi.php";
include "VipMembership.php";

$shopifyApi = new shopifyApi();
$rechargeApi = new RechargeApi();
$vipMembership = new VipMembership();

$chargeDetails = json_decode(file_get_contents('php://input'));
$file_handle = fopen('my_filename.json', 'w');

/*$chargeDetails = json_decode('{  
   "charge":{  
      "address_id":19248493,
      "last_name":"Test",
      "subtotal_price":1500.0,
      "shipping_lines":[  
         {  
            "price":"0.00",
            "code":"free-shipping",
            "title":"Standard Shipping 2"
         }
      ],
      "sub_total":null,
      "updated_at":"2018-10-17T09:20:51",
      "total_weight":0,
      "customer_hash":"17191222dd96fe7697d18c51",
      "processed_at":"2018-10-17T09:20:49",
      "id":91964104,
      "first_name":"Recharge",
      "discount_codes":[  

      ],
      "line_items":[  
         {  
            "sku":"ayyyy",
            "vendor":"Example Storeeeeeee",
            "grams":0,
            "shopify_variant_id":"24960088774",
            "title":"api_test  Auto renew",
            "price":"500.00",
            "variant_title":"1 / 3",
            "subscription_id":25492524,
            "shopify_product_id":"7743503814",
            "properties":[  
               {  
                  "name":"shipping_interval_frequency",
                  "value":"1"
               },
               {  
                  "name":"shipping_interval_unit_type",
                  "value":"Weeks"
               }
            ],
            "quantity":3
         }
      ],
      "total_price":"1500.00",
      "created_at":"2018-10-17T09:13:54",
      "total_refunds":null,
      "note_attributes":[  

      ],
      "shipments_count":1
   }
}');
*/
$subscriptionId = $chargeDetails->charge->line_items[0]->subscription_id;//subscription_id from charge paid response. 

$rechargeCustomerId = $chargeDetails->charge->customer_id;
fwrite($file_handle, $rechargeCustomerId ." ".$subscriptionId);
fclose($file_handle);
$vipMemberDetails = $vipMembership->getVipMemberDetails($rechargeCustomerId);
$shopifyCustomerId = $vipMemberDetails['shopify_customer_id'];
$subscriptionDetails = $rechargeApi->getSubscriptionDetails($subscriptionId);//subscription details from recharge


$vipMembership->addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionDetails, $chargeDetails);//add charge details in db
$nextChargeDate = $subscriptionDetails->subscription->next_charge_scheduled_at;

$customerDetails = $shopifyApi->getCustomer($shopifyCustomerId);
$customerTag = $customerDetails->customer->tags;


if ($chargeDetails->charge->status == "SUCCESS") //if charge status is SUCCESS 
{	
	
	$vipMembership->updateVipMemberNextChargeDate($shopifyCustomerId, $nextChargeDate);//update vip_membership table next_charge_date
	$checkChargeDate = $vipMembership->checkChargeDate();
	/*$vipCustomerId = $checkChargeDate['shopify_customer_id'];
	 if ($vipCustomerId) 
	 {
	 	$vipMembership->updateVipMemberStatus($vipCustomerId, 1);
	 }
	*/
	 $customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => 'VIP,'.$removeVIPTag,
		                )
		        );
	$vipMembership->updateVipMemberStatus($vipCustomerId, 1);

}
else
{
	$removeVIPTag = chop($customerTag,"VIP");

	$customerDetailsToUpdate = array(
		            'customer' =>
		                array(
		                   'id' => $shopifyCustomerId,
		                   'tags' => $removeVIPTag,
		                )
		        );
	$updateStatus = $vipMembership->updateVipMemberStatus($shopifyCustomerId, 0);//update status to 0
}

$updateedCustomerTags = $shopifyApi->updateCustomer($shopifyCustomerId, $customerDetailsToUpdate);//remove the VIP tag

 

?>