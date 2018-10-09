<?php
    include('config.php');
    include('common_functions.php');
    include('db_conn.php');
    include('customer_tag.php');

    //$subscriptionDetails = json_encode(file_get_contents('php://input'));

    $subscriptionDetails = json_encode('{"subscription": {"id": 24216075, "address_id": 18334491, "customer_id": 16401639, "created_at": "2018-10-09T05:23:27", "updated_at": "2018-10-09T05:23:28", 
"next_charge_scheduled_at": "2018-10-09T00:00:00", "cancelled_at": null, "product_title": "VIP Member  Auto renew", "variant_title": "", 
"price": 0.01, "quantity": 1, "status": "ACTIVE", 
"shopify_product_id": 1531595554927, "shopify_variant_id": 13659551989871, "sku": null, "order_interval_unit": "month", "order_interval_frequency": "1", 
"charge_interval_frequency": "1", "cancellation_reason": null, "cancellation_reason_comments": null, "order_day_of_week": null, "order_day_of_month": 0, 
"properties": [], "expire_after_specific_number_of_charges": null, "max_retries_reached": 0, "has_queued_charges": 1}}');
    vipMembers();
    addCustomerTag($subscriptionDetails.subscription.customer_id);


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
         );
        
        //url to apply customer tag
        $url = "https://" . $apiKey . ":" . $password . "@" . $shop . ".myshopify.com" . "/admin/customers/" .$customerId. "." ."json";

        $result = sendCurlCall($url, 'PUT', $headers, $data);
        if(!$result)
        {
            die("Connection Failure");
        }
        print_r($result);
        curl_close($curl);
    }
?>