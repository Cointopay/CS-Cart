<?php

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Http;

function fn_cointopay_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cointopay.php')))");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script IN ('cointopay.php')");
}
function  fn_cointopay_validate_order($data)
   {
       $params = array(
       "authentication:1",
       'cache-control: no-cache',
       );
       $ch = curl_init();
       curl_setopt_array($ch, array(
       CURLOPT_URL => 'https://app.cointopay.com/v2REAPI?',
       //CURLOPT_USERPWD => $this->apikey,
       CURLOPT_POSTFIELDS => 'MerchantID='.$data['mid'].'&Call=QA&APIKey=_&output=json&TransactionID='.$data['TransactionID'].'&ConfirmCode='.$data['ConfirmCode'],
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_SSL_VERIFYPEER => false,
       CURLOPT_HTTPHEADER => $params,
       CURLOPT_USERAGENT => 1,
       CURLOPT_HTTPAUTH => CURLAUTH_BASIC
       )
       );
       $response = curl_exec($ch);
       $results = json_decode($response);
       if($results->CustomerReferenceNr)
       {
           return $results;
       }
       echo $response;
       exit();
}
function  fn_cointopay_transactiondetail($data)
{
       $params = array(
       "authentication:1",
       'cache-control: no-cache',
       );
       $ch = curl_init();
       curl_setopt_array($ch, array(
       CURLOPT_URL => 'https://app.cointopay.com/v2REAPI?',
       //CURLOPT_USERPWD => $this->apikey,
       CURLOPT_POSTFIELDS => 'Call=Transactiondetail&MerchantID='.$data['mid'].'&output=json&ConfirmCode='.$data['ConfirmCode'].'&APIKey=a',
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_SSL_VERIFYPEER => false,
       CURLOPT_HTTPHEADER => $params,
       CURLOPT_USERAGENT => 1,
       CURLOPT_HTTPAUTH => CURLAUTH_BASIC
       )
       );
       $response = curl_exec($ch);
       $results = json_decode($response, true);
       /*if($results->CustomerReferenceNr)
       {
           return $results;
       }*/
       return $results;
       exit();
}
function fn_cointopay_get_processor_params()
{
    $processor_params = db_get_field(
        'SELECT ?:payments.processor_params'
        . ' FROM ?:payments'
        . ' LEFT JOIN ?:payment_processors'
        . ' ON ?:payment_processors.processor_id = ?:payments.processor_id'
        . ' WHERE ?:payment_processors.processor_script = ?s'
        . ' AND ?:payments.status = ?s', 'cointopay.php', 'A'
    );

    return !empty($processor_params) ? unserialize($processor_params) : '';
}
function fn_cointopay_flash_encode($input)
{
	return rawurlencode(utf8_encode($input));
}
?>