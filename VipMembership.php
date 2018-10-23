<?php
class VipMembership {
	private $mysql_server = "localhost";
	private $mysql_username = "root";
	private $mysql_password = "root";
	private $mysql_database = "korafitness";
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

	function addVipMemberDetails($customerId, $shopifyCustomerId, $nextChargeDate, $credit) {
		$sql = "INSERT INTO vip_members(customer_id, shopify_customer_id, next_charge_scheduled_at, status, credit_amount) VALUES ('".$customerId."', '".$shopifyCustomerId."', '".$nextChargeDate."', 1 , '".$credit."')";
        $result = $this->conn->query($sql); 	}

 	function updateVipMemberDetails($customerId, $nextChargeDate, $credit, $status) {
 		$sql = "UPDATE vip_members SET next_charge_scheduled_at = '".$nextChargeDate."', status = '".$status."', credit_amount = '".$credit."' WHERE customer_id = '".$customerId."'";
    $result =  $this->conn->query($sql);
 	}
  function updateVipMemberCredit($customerId, $amountRemaining) {

    $sql = "UPDATE vip_members SET credit_amount = '".$amountRemaining."' WHERE customer_id = '".$customerId."'";
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
  function insertCoupon($order_id, $code, $customerId, $totalDiscount, $amount)
  {
    $sql = "INSERT INTO coupon(shopify_customer_id, order_id, code, value, credit_used, discount_type, applies_to_product_type, duration, duration_usage_limit, restrict_by_email, status, usage_limit, starts_at, ends_at) VALUES ('".$customerId."', '".$order_id."', '".$code."', '".$totalDiscount."', '".$credit_used."' , 1 ,'1','1','11','1','1','1','2018-10-12 17:26:35','2018-10-12 17:26:35')";
    $result = $this->conn->query($sql);
  }
 	function updateCoupons($order_id, $code) {
    $sql = "UPDATE coupon SET code = '".$code."' WHERE order_id = '".$order_id."'";
    $result =  $this->conn->query($sql);
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
  function checkCoupon($orderId, $customerId, $status, $couponCode)
  {
    $sql = "SELECT * FROM coupon WHERE shopify_customer_id = '".$customerId."' AND code = '".$couponCode."' limit 1";
    $result = $this->conn->query($sql);
    $data = $result->fetch_assoc();
    if ($data > 0) 
    {
      $sql = "UPDATE coupon SET order_id = '".$orderId."' WHERE shopify_customer_id = '".$customerId."'";
      $result =  $this->conn->query($sql);
    }
    else
    {
      echo "Invalid Coupon";
    }
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
