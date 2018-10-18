<?php
class GenerateCoupon {
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
		    die("Connection failed" . $conn->connect_error);
		} 
	}	
  function getCreditAmount($shopifyCustomerId) {
    $sql = "SELECT credit_amount FROM vip_members WHERE shopify_customer_id = '".$shopifyCustomerId."' limit 1";
    $result = $this->conn->query($sql);
    $data = $result->fetch_assoc();
    return $data['credit_amount'];
  }
  function calculateDiscount($defaultDiscountinPercentage, $cartTotal)
  {
    $percentAmount = ($defaultDiscountinPercentage / 100) * $cartTotal;
    $amountAfterDiscount = $cartTotal - $percentAmount;
    return $amountAfterDiscount;
  }
  function CreditDiscount($creditAmount, $cartTotal)
  {
    $creditDiscount = ($creditAmount/$cartTotal) * 100;
    return $creditDiscount;
  }
  function CreditDiscountFor100percent($creditAmount, $cartTotal)
  {
    $creditDiscount = 100;
    return $creditDiscount;
  }
  function TotalDiscount($defaultDiscountinPercentage, $creditDiscount)
  {
    $totalDiscount = $defaultDiscountinPercentage + $creditDiscount;
    return $totalDiscount;
  }

}
?>