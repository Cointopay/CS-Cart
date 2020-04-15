<?php

// Preventing direct access to the script, because it must be included by the "include" directive. The "BOOTSTRAP" constant is declared during system initialization.
defined('BOOTSTRAP') or die('Access denied');

// Here are two different contexts for running the script.
if (defined('PAYMENT_NOTIFICATION')) 
{
    // callback
    if ($mode == 'process') 
    {
        $Notification = "";
        $type = "E";
        $paymentStatus = isset($_GET['status']) ? $_GET['status'] : 'failed';
        $notEngough = isset($_GET['notenough']) ? $_GET['notenough'] : '2';
        $transactionID = isset($_GET['TransactionID']) ? $_GET['TransactionID'] : '';
        $orderID = isset($_GET['order_id']) ? $_GET['order_id'] : '';

        $pp_response["transaction_id"] = $transactionID;
        $order_id = $orderID;
        if(isset($_GET['ConfirmCode']))
        {
           $pp = fn_cointopay_get_processor_params();
           $data = [
                       'mid' => $pp['merchant_id'] ,
                       'TransactionID' =>  $transactionID ,
                       'ConfirmCode' => $_GET['ConfirmCode']
                   ];
		    $transactionData = fn_cointopay_transactiondetail($data);
			if(200 !== $transactionData['status_code']){
				echo $transactionData['message'];
				 exit;
			}
			else{
				if($transactionData['data']['Security'] != $_GET['ConfirmCode']){
					echo "Data mismatch! ConfirmCode doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['CustomerReferenceNr'] != $_GET['CustomerReferenceNr']){
					echo "Data mismatch! CustomerReferenceNr doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['TransactionID'] != $_GET['TransactionID']){
					echo "Data mismatch! TransactionID doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['AltCoinID'] != $_GET['AltCoinID']){
					echo "Data mismatch! AltCoinID doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['MerchantID'] != $_GET['MerchantID']){
					echo "Data mismatch! MerchantID doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['coinAddress'] != $_GET['CoinAddressUsed']){
					echo "Data mismatch! coinAddress doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['SecurityCode'] != $_GET['SecurityCode']){
					echo "Data mismatch! SecurityCode doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['inputCurrency'] != $_GET['inputCurrency']){
					echo "Data mismatch! inputCurrency doesn\'t match";
					exit;
				}
				elseif($transactionData['data']['Status'] != $_GET['status']){
					echo "Data mismatch! status doesn\'t match. Your order status is ".$transactionData['data']['Status'];
					exit;
				}

				}
          /* $response = fn_cointopay_validate_order($data);
           
           if($response->Status !== $_GET['status'])
           {
               echo "We have detected different order status. Your order status is ".$response->Status;
               exit;
           }
           if($response->CustomerReferenceNr == $_GET['CustomerReferenceNr'])
           {*/
                //if paid
                if($paymentStatus == 'paid' && $notEngough == '0')
                {
                    $pp_response["order_status"] ='C';
                    $Notification = "Congartulations! Your order has been completed.";
                    $type = "I";
                }
            
                else if ($paymentStatus == 'paid' || $notEngough == '1') 
                {
                    $pp_response["order_status"] = 'P';
                    $pp_response["reason_text"] = "Paid Notenough";
                    $Notification = "Your order hase been placed but there was problem with your payment (notenough). Please, contact site admin.";
                    $type = "O";
                } 
                elseif ($paymentStatus == 'failed') 
                {
                    $pp_response["order_status"] = 'F';
                    $pp_response["reason_text"] = "The payment transaction failed";
                    $Notification = "Sorry! Your payment failed!";
                    $type = "O";
                }
                else
                {
                    $pp_response["order_status"] = 'F';
                    $pp_response["reason_text"] = "The payment transaction failed";
                    $Notification = "Sorry! Your payment failed!";
                    $type = "O";
                }
                
                if (fn_check_payment_script('cointopay.php', $order_id)) 
                {
                    fn_finish_payment($order_id, $pp_response, false);
                    fn_set_notification($type,'Order Payment Notification',$Notification);
                    //fn_redirect("index.php?dispatch=checkout.complete&orderid==$order_id");
                    fn_order_placement_routines('route', $order_id);
                }
           }
        /*}
        else
        {
            die('We have detected changes in your order. Your order has been halted.');
        } */
        
    }
    exit;
} 
else 
{
    $callbackUrl = fn_url("payment_notification.process?payment=cointopay&order_id=".$order_info['order_id']."", AREA, 'current');
	$callbackUrl = fn_cointopay_flash_encode($callbackUrl);
    $account_info = $order_info['payment_method']['processor_params'];
    // customer have placed the order
    $merchantID= $account_info['merchant_id'];
    $securityCode= $account_info['secret_key'];
	if (empty($merchantID) || empty($securityCode)){
            die('CredentialsMissing');
	}
    
	$params = array(
        "authentication:1",
        'cache-control: no-cache',
        );

    $ch = curl_init();
    curl_setopt_array($ch, array(
    CURLOPT_URL => 'https://app.cointopay.com/MerchantAPI?Checkout=true',
    //CURLOPT_USERPWD => $this->apikey,
    CURLOPT_POSTFIELDS => 'SecurityCode=' .$securityCode.'&MerchantID='.$merchantID.'&Amount=' . number_format($order_info['total'], 2, '.', '').'&AltCoinID=1&output=json&inputCurrency=USD&CustomerReferenceNr='.$order_info['order_id'].'&transactionconfirmurl='.$callbackUrl.'&transactionfailurl='.$callbackUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => $params,
    CURLOPT_USERAGENT => 1,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC
    )
    );
    $redirect = curl_exec($ch);
    $results = json_decode($redirect);
	if (is_string($results)){
            die('BadCredentials: '.$results);
	}
    if($results->RedirectURL)
    {
       //fn_create_payment_form($results->RedirectURL, '', 'Cointopay', false);
        header("Location: ".$results->RedirectURL."");
    }
    echo $redirect;
    exit;
}
exit;

?>