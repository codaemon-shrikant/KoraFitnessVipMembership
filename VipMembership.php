<?php
class VipMembership {
	private $mysql_server = "localhost";
	private $mysql_username = "root";
  private $mysql_password = "9VhDB'/L";
  //private $mysql_password = "root";
  private $mysql_database = "koraVipMembership";
	private $conn;
	function __construct(){
		// Create connection
		$this->conn = new mysqli($this->mysql_server, $this->mysql_username, $this->mysql_password,$this->mysql_database);
		// Check connection
		if ($this->conn->connect_error) {
		    die("Connection failed" . $this->conn->connect_error);
		} 
	}	
	function getVipMemberDetails($customerId) {
    $sql = "SELECT * FROM vip_members WHERE customer_id = '".$customerId."' limit 1";
    $result = $this->conn->query($sql);
    return $result->fetch_assoc();
  }
  function addChargeDetails($rechargeCustomerId, $shopifyCustomerId, $subscriptionId, $nextChargeDate, $chargeId=0, $chargeStatus, $chargeCreatedAt, $chargeUpdatedAt)
  { 
    $sql = "INSERT INTO charge_details(recharge_customer_id, shopify_customer_id, subscription_id, charge_id, next_charge_date, created_at, updated_at, status)
                 VALUES ( 
                          '".$rechargeCustomerId."',
                          '".$shopifyCustomerId."',
                          '".$subscriptionId."',
                          '".$chargeId."',
                          '".$nextChargeDate."',
                          '".$chargeCreatedAt."',
                          '".$chargeUpdatedAt."',
                          '".$chargeStatus."')";
              $file_handle = fopen('my_filename.json', 'w');
              fwrite($file_handle, $sql);
              fclose($file_handle);

      $result = $this->conn->query($sql);
   
  }
  function updateVipMemberNextChargeDate($customerId, $nextChargeDate)
  {

    $sql = "UPDATE vip_members SET next_charge_scheduled_at = '".str_replace("T"," ",$nextChargeDate)."' WHERE shopify_customer_id = '".$customerId."'";
    $result =  $this->conn->query($sql);
  }
  function updateVipMemberStatus($customerId, $status)
  {
    $sql = "UPDATE vip_members SET status = "+$status+" WHERE shopify_customer_id = '".$customerId."'";
    $result =  $this->conn->query($sql);
  }
  function checkChargeDate()
     {
       $currentDate =  date('Y-m-d h:i:s');
       $sql = "SELECT * FROM vip_members WHERE next_charge_scheduled_at >= '".$currentDate."' ";
       $result = $this->conn->query($sql);
       $data = $result->fetch_array();
       return $data;
     }
	function addVipMemberDetails($customerId, $shopifyCustomerId, $subscriptionId, $nextChargeDate, $credit) {
		$sql = "INSERT INTO vip_members(customer_id, shopify_customer_id, subscription_id, next_charge_scheduled_at, status, credit_amount) VALUES ('".$customerId."', '".$shopifyCustomerId."', '".$subscriptionId."', '".$nextChargeDate."', 1 , '".$credit."')";
    $result = $this->conn->query($sql); 	}

 	function updateVipMemberDetails($customerId, $nextChargeDate, $credit, $status) {
 		$sql = "UPDATE vip_members SET next_charge_scheduled_at = '".$nextChargeDate."', status = '".$status."', credit_amount = '".$credit."' WHERE customer_id = '".$customerId."'";
    $result =  $this->conn->query($sql);
 	}
  function updateVipMemberCredit($customerId, $amountRemaining) {

    $sql = "UPDATE vip_members SET credit_amount = '".$amountRemaining."' WHERE shopify_customer_id = '".$customerId."'";
    $result =  $this->conn->query($sql);
  }
 	function addSubscriptionDetails($subscriptionDetails) {
      $expire_after_specific_number_of_charges = !empty($subscriptionDetails->subscription->expire_after_specific_number_of_charges) ? "'$subscriptionDetails->subscription->expire_after_specific_number_of_charges'" : "NULL";
                      
      $sql = "INSERT INTO subscriptions(next_charge_scheduled_at, address_id, customer_id, created_at, updated_at, cancelled_at, variant_title, shopify_product_id, shopify_variant_id, sku, charge_interval_frequency, cancellation_reason, cancellation_reason_comments, order_day_of_week, order_day_of_month, max_retries_reached, has_queued_charges, properties, product_title, price, quantity, status, order_interval_unit, expire_after_specific_number_of_charges,order_interval_frequency)
                 VALUES ( '".str_replace("T"," ",$subscriptionDetails->subscription->next_charge_scheduled_at)."',
                          '".$subscriptionDetails->subscription->address_id."',
                          '".$subscriptionDetails->subscription->customer_id."',
                          '".str_replace("T"," ",$subscriptionDetails->subscription->created_at)."',
                          '".str_replace("T"," ",$subscriptionDetails->subscription->updated_at)."',
                          '2018-10-09 05:23:28',
                          '".$subscriptionDetails->subscription->variant_title."',
                          '".$subscriptionDetails->subscription->shopify_product_id."',
                          '".$subscriptionDetails->subscription->shopify_variant_id."',
                          '".$subscriptionDetails->subscription->sku."',
                          '".$subscriptionDetails->subscription->charge_interval_frequency."',
                          '".$subscriptionDetails->subscription->cancellation_reason."',
                          '".$subscriptionDetails->subscription->cancellation_reason_comments."',
                          '".$subscriptionDetails->subscription->order_day_of_week."',
                          '".$subscriptionDetails->subscription->order_day_of_month."',
                          '".$subscriptionDetails->subscription->max_retries_reached."',
                          '".$subscriptionDetails->subscription->has_queued_charges."',
                          '".$subscriptionDetails->subscription->properties."',
                          '".$subscriptionDetails->subscription->product_title."',
                          '".$subscriptionDetails->subscription->price."',
                          '".$subscriptionDetails->subscription->quantity."',
                          '".$subscriptionDetails->subscription->status."',
                          '".$subscriptionDetails->subscription->order_interval_unit."',
                          ".$expire_after_specific_number_of_charges.",
                          '".$subscriptionDetails->subscription->order_interval_frequency."')";

      $result = $this->conn->query($sql);
 	}
  function insertCredit($customerId, $creditBalance, $amount, $status) {
      $sql = "INSERT INTO credits(customer_id, credit_balance, amount_used, status) VALUES ('".$customerId."', '".$creditBalance."', '".$amount."' ,'".$status."')";
      $result = $this->conn->query($sql);
  }

  function insertCoupon($code, $customerId, $totalDiscount, $amount)
  {
    $sql = "INSERT INTO coupon(shopify_customer_id, code, value, credit_used, discount_type, applies_to_product_type, duration, duration_usage_limit, restrict_by_email, status, usage_limit, starts_at, ends_at) VALUES ('".$customerId."', '".$code."', '".$totalDiscount."', '".$amount."' , 1 ,'1','1','11','1','1','1','2018-10-12 17:26:35','2018-10-12 17:26:35')";
    $result = $this->conn->query($sql);
  }
 	
  function updateCredit($customerId, $creditUsed, $creditAmount) {
 		$sql = "UPDATE credits SET amount_used = '".$creditUsed."',credit_balance = '".$creditAmount."',status = 0 WHERE customer_id = '".$customerId."'";
		$result = $this->conn->query($sql);
 	}
  function jsonFormat($code, $amount, $creditPercent, $totalDiscount, $creditBalance)
  {
    $data = 
              array(
                "coupon_code" => $code,
                "credit_used" => $amount,
                "credit_percent" => $creditPercent,
                "total_discount" => $totalDiscount,
                "remaining_credit" => $creditBalance
              );
    $jsonData = json_encode($data);
    return $jsonData;
  }
  function getCreditAmount($customerId) {
    $sql = "SELECT credit_amount FROM vip_members WHERE shopify_customer_id = '".$customerId."' limit 1";
    $result = $this->conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['credit_amount'];
  }
  function checkCoupon($customerId, $couponCode)
  {
    $sql = "SELECT * FROM coupon WHERE shopify_customer_id = '".$customerId."' AND code = '".$couponCode."' limit 1";
    
    $result = $this->conn->query($sql);
    $data = $result->fetch_assoc();
    return $data;
  }
  function updateOrderIDinCoupon($id, $orderId) {
    echo $sql = "UPDATE coupon SET order_id = '".$orderId."' WHERE id = '".$id."'";
    $result =  $this->conn->query($sql);
  }
  
  function getAmountFromCoupon($customerId)
  {
    $sql = "SELECT credit_used FROM coupon WHERE shopify_customer_id = '".$customerId."' limit 1";
    $result = $this->conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['credit_used'];
  }

}
?>
