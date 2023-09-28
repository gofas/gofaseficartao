<?php
/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.0
 */
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
use WHMCS\Database\Capsule;
if($_POST and !$_POST['error'] ){
	require __DIR__.'/functions.php';
	$params = getGatewayVariables('gofaseficartao');
	$params_api = gefic_api_connect();
	$customer = gefic_customer($_POST['userid']);
	$access_token_ = gefic_get_token();
	$access_token = $access_token_['result']['access_token'];
	// Invoice Info
	$GetInvoiceResults			= localAPI('getinvoice',array('invoiceid'=>$_POST['invoiceid'] ),(int)$params['admin']);
	$line_items = array();
	foreach( $GetInvoiceResults['items']['item'] as $Value){
		$line_items[]	= substr( $Value['description'],  0, 80).' | R$ '.number_format( $Value['amount'],  2, ',', '.');	
	}
	$amount = (int)preg_replace("/[^0-9]/", "", $_POST['amount']);
	if($_POST['paymentToken'] and (string)($_POST['paymentToken']) !== (string)'Na'){
		$paymentToken = $_POST['paymentToken'];
	}
	if(!$_POST['paymentToken'] and !empty($_POST['saved_token'])){
		$paymentToken = $_POST['saved_token'];
	}
	$pAye_e = 'b7ac135895cfb50a2a90cf28fe0d15e0'; // Gofas Software
	//$pAye_e = '4c640ca051ab239b194ed2609967a831'; // Mauricio Gofas
	
	$postfields = [
		'items' => [[
			'name'=>(string)(substr( implode("\n",$line_items),  0, 250)),
			'value'=>$amount,
			'amount'=>1,
			'marketplace'=> [
				'mode'=>1,
        		'repasses'=> [[
        		    'payee_code'=> $pAye_e,
        		    'percentage'=> 100
				]],
			]
		]],
		'payment'=>[
		  'credit_card'=>[
			'customer'=>[
			  'name'=>$customer['name'],
			  'cpf'=>$customer['cpf'],
			  'email'=>$customer['email'],
			  'birth'=>$customer['birthday']['us'],
			  'phone_number'=>$customer['phone'],
			],
			'installments'=>(int)$_POST['installmentsnum'],
			'payment_token'=>$paymentToken,
			'billing_address'=>[
			  'street'=>$customer['address'],
			  'number'=>$customer['number'],
			  'neighborhood'=>$customer['neighborhood'],
			  'zipcode'=>$customer['postcode'],
			  'city'=>$customer['city'],
			  'complement'=>$customer['complement'],
			  'state'=>$customer['state']
			]
		  ]
		]
	];
	$charge = gefic_charge($postfields);
	// Capturado
	if( (string)$charge['result']['data']['status'] === (string)'approved'){
		if( (int)$_POST['installmentsnum'] > 1 ){
			$trans_desc = "Pagamento Aprovado - Parcelado em ".(int)$_POST['installmentsnum']."x R$".number_format( $_POST['amount'] / (int)$_POST['installmentsnum'] ,  2, ',', '.')." - ".$_POST['cardtype'].'-'.$_POST['cclastfour'];
		}
		else {
			$trans_desc = "Pagamento Aprovado - ".$_POST['cardtype'].'-'.$_POST['cclastfour'];
		}
		//
		$fee = (($params['amount'] * $params['fee']) / 100)+0.29;
		$gefic_add_trans = gefic_add_trans(
			$_POST['userid'],
			$_POST['invoiceid'],
			$_POST['amount'],
			$fee,
			'gefic-'.$params_api['api_mode'].'-'.$charge['result']['data']['charge_id'],
			$trans_desc
			);
			$gefic_update_stats = gefic_update_stats();
		if($gefic_add_trans['error']){
			$error .= $gefic_add_trans['error'];
		}
		// save card
		if((string)$_POST['storeCard'] === (string)'yes' and $paymentToken and !$_POST['saved_token']){
			$card_to_add = [
				'userid'=>$_POST['userid'],
				'cclastfour'=>$_POST['cclastfour'],
				'cardexp'=>$_POST['cardexp'],
				'cardtype'=>$_POST['cardtype'],
				'payment_token'=>$paymentToken,//$_POST['issuenumber'],
				'myId'=> (string)((int)$_POST['pay_method_id']+1),
			];
			$gefic_add_card = gefic_card_add($card_to_add,$_POST['pay_method_id']);
			if((string)$gefic_add_card !== (string)'success'){
				$error .= $gefic_add_card;
			}
		}
		if(((string)$_POST['storeCard'] !== (string)'yes' || (string)$gefic_add_card !== (string)'success') and !$_POST['saved_token']){
			$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
			if((string)$gefic_card_del !== (string)'success'){
				$error .= $gefic_card_del;
			}
		}
	}
	if( (string)$charge['result']['data']['status'] !== (string)'approved'){
		$error .= $charge['result']['data']['status'];
		if(!$_POST['saved_token']){
			$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
			if((string)$gefic_card_del !== (string)'success'){
				$error .= $gefic_card_del;
			}
		}
	}
	if($charge['result']['error']){
		if(!$_POST['saved_token']){
			$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
			if((string)$gefic_card_del !== (string)'success'){
				$error .= $gefic_card_del;
			}
		}
		if( !empty($charge['result']['error_description']['property'])){
			$error .= $charge['result']['error_description']['property'];
		}
		if(!empty($charge['result']['error_description']['message'])){
			$error .= $charge['result']['error_description']['message'];
		}
	}
	if($charge['result_code'] !== 200 ){
		$error .= $charge['result_code'];
	}
}
if($params['log']){	
	$log_request = [
		'post'=>$_POST,
		'params'=> $params,
		'access_token'=> $access_token,
		'customer'=> $customer,
		'postfields'=> $postfields,
	];
	$log_response = [
		 'charge'=> $charge,
		 'charge_capture'=>$charge_capture,
		 'gefic_add_card'=>$gefic_add_card,
		 'gefic_card_del'=> $gefic_card_del,
		 'error'=>$error,
	];
	logModuleCall('gofaseficartao', 'iframe_payment', ['module_version'=>gefic_version(),$log_request],'post',[$charge],'replaceVars');
}
if(!$error){
	$invoice_page =json_encode(gefic_whmcs_url('whmcs_url').'/viewinvoice.php?id='.$_POST['invoiceid'].'&paymentsuccess=true');
	echo '<script>window.top.location.href='.$invoice_page.'</script>';
}
if($error){
	$invoice_page =json_encode(gefic_whmcs_url('whmcs_url').'/viewinvoice.php?id='.$_POST['invoiceid'].'&geficerror='.$error);
	echo '<script>window.top.location.href='.$invoice_page.'</script>';
}