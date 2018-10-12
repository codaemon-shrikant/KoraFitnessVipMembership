<?
class VipMembership {
	private $mysql_server = "localhost";
	private $mysql_username = "root";
	private $mysql_password = "9VhDB'/L";
	private $mysql_database = "koraVipMembership";
	private $conn;
	function __construct(){
		// Create connection
		$this->conn = new mysqli($mysql_server, $mysql_username, $mysql_password, $mysql_database);
		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		} 
	}	
	function getVipMemberDetails($customer_id) {
        $sql = "SELECT * FROM vip_members WHERE customer_id = '".$customer_id."' limit 1";
        $result = $this->conn->query($sql);
        
        return $result->fetch_assoc(); 
	}

	function addVipMemberDetails($customerId, $shopifyCustomerId, $nextChargeDate, $credit) {
		$sql = "INSERT INTO vip_members(customer_id, shopify_customer_id, next_charge_scheduled_at, status, credit_amount) VALUES ('".$customerId."', '".$shopifyCustomerId."', '".$date."', 1 , '".$creditAmount."')";
        $result = $this->conn->query($sql);
 	}

 	function updateVipMemberDetails($customerId, $nextChargeDate, $credit, $status) {
 		$sql = "UPDATE vip_members SET next_charge_scheduled_at = '".$nextChargeDate."', status = '".$status."', credit_amount = '".$creditAmount."' WHERE customer_id = '".$customerId."'";
        $result = $conn->query($sql);
 	}

 	function addSubscriptionDetails() {
 		$sql = "INSERT INTO subscriptions(next_charge_scheduled_at, address_id, customer_id, created_at, 
                updated_at, cancelled_at, variant_title, shopify_product_id, shopify_variant_id, sku,
                 charge_interval_frequency, cancellation_reason, cancellation_reason_comments, 
                 order_day_of_week, order_day_of_month, max_retries_reached, has_queued_charges, properties, 
                 product_title, price, quantity, status, order_interval_unit, expire_after_specific_number_of_charges)
                 VALUES ( '".$subscriptionDetails->subscription->next_charge_scheduled_at."','".$subscriptionDetails->subscription->address_id."',
                          '".$subscriptionDetails->subscription->customer_id."','".$subscriptionDetails->subscription->updated_at."',
                          '".$subscriptionDetails->subscription->cancelled_at."','".$subscriptionDetails->subscription->product_title."',
                          '".$subscriptionDetails->subscription->variant_title."','".$subscriptionDetails->subscription->quantity."',
                          '".$subscriptionDetails->subscription->status."','".$subscriptionDetails->subscription->shopify_product_id."',
                          '".$subscriptionDetails->subscription->shopify_variant_id."','".$subscriptionDetails->subscription->charge_interval_frequency."',
                          '".$subscriptionDetails->subscription->cancellation_reason."','".$subscriptionDetails->subscription->cancellation_reason_comments."',
                          '".$subscriptionDetails->subscription->order_day_of_week."','".$subscriptionDetails->subscription->order_day_of_month."',
                          '".$subscriptionDetails->subscription->max_retries_reached."','".$subscriptionDetails->subscription->has_queued_charges."',
                          '".$subscriptionDetails->subscription->properties."')";

        $result = $conn->query($sql);
 	}

 	function updateCoupons() {

 	}

 	function updateCreditDetails($customerId, $remainingBalance, $amount, $status) {
 		$sql = "INSERT INTO credits(customer_id, credit_balance, amount_used, status) VALUES ('".$customerId."', '".$remainingBalance."', '".$amount."' ,'".$status."')";
		$result = $conn->query($sql);
 	}

	// store shopify customer id in database
    function vipMembers($subscriptionDetails, $shopifyCustomerId)
    {
        global $conn;
        $memberId = $subscriptionDetails->subscription->customer_id;
        //$data = $_POST();
        $sql = "SELECT * FROM vip_members WHERE customer_id = '".$memberId."' limit 1";
        $result = $this->conn->query($sql);
        
        $memberData = $result->fetch_assoc();

        $date = $subscriptionDetails->subscription->next_charge_scheduled_at; 
        $creditAmount = $subscriptionDetails->subscription->price;

        if ($memberData) 
        {
            // update existing credit by adding subscription price in it.
            $credit_amount += $memberData->credit_amount;
            
            $sql = "UPDATE vip_members SET next_charge_scheduled_at = '".$date."', status = '0', credit_amount = '".$creditAmount."' WHERE customer_id = '".$memberId."'";
            $result = $conn->query($sql);
           
        } 
        else 
        {
           $sql = "INSERT INTO vip_members(customer_id, next_charge_scheduled_at, status, credit_amount) VALUES ('".$memberId."', '".$date."', 1 ,'".$creditAmount."')";
           $result = $this->conn->query($sql);
        } 
    }
}
?>