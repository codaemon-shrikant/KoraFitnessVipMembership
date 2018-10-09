<?php
	include('config.php');
	include('common_functions.php');
    include('db_conn.php');
    include('customer_tag.php');

    $subscriptionDetails = json_encode(file_get_contents('php://input'));

    vipMembers();
    updateTag($subscriptionDetails.subscription.customer_id);


    function vipMembers($subscriptionDetails)
    {
    	global $conn;
    	$memberId = $subscriptionDetails.subscription.customer_id;
    	//$data = $_POST();
	    $sql = "SELECT * FROM vip_members WHERE customer_id = '".$memberId."' limit 1";
	    $result = $conn->query($sql);
	    
	    $memberData = $result->fetch_assoc();

	    $date = $subscriptionDetails.subscription.next_charge_scheduled_at; 
      	$creditAmount = $subscriptionDetails.subscription.price;

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
           $result = $conn->query($sql);
        } 
    }

    function addCustomerTag($customerId) {
        // customer tag
        //read all customer tags 
        $customerDetails = json_encode(file_get_contents($url = "https://" . $apiKey . ":" . $password . "@" . $shop . ".myshopify.com" . "/admin/customers/" .$customerId. "." ."json"));
        $data = array(
          	'customer' =>
	         	array(
		           'id' => $customerId,
		           'tags' => $customerDetails.customer.tags+',VIP',
	         	)
        );
        //set headers
        $headers = array(
	        'APIKEY: acac9a7e911be70b12ca8c6f4c2c5311',
	        'Content-Type: application/json',
	     )
        
        //url to apply customer tag
        $url = "https://" . $apiKey . ":" . $password . "@" . $shop . ".myshopify.com" . "/admin/customers/" .$customerId. "." ."json";

        $result = sendCurlCall($url, 'PUT', $headers $data);
    	if(!$result)
	  	{
			die("Connection Failure");
	  	}
	  	print_r($result);
	    curl_close($curl);
    }
?>