<?php
/**
 * Módulo iugu Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=14946
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=12349
 * @version		1.0.0
 */
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
use WHMCS\Database\Capsule;
require __DIR__.'/functions.php';
if($_POST and !$_POST['error'] ){
	$params = getGatewayVariables('gofasiugucartao');
	$params_api = gic_api_connect();
	$customer = gic_customer($_POST['userid']);
	// Invoice Info
	$GetInvoiceResults			= localAPI('getinvoice',array('invoiceid'=>$_POST['invoiceid'] ),(int)$params['admin']);
	$line_items = array();
	foreach( $GetInvoiceResults['items']['item'] as $Value){
		$line_items[]	= substr( $Value['description'],  0, 80).' | R$ '.number_format( $Value['amount'],  2, ',', '.');	
	}
	$amount = (int)preg_replace("/[^0-9]/", "", $_POST['amount']);
	if($_POST['paymentToken'] and (string)($_POST['paymentToken']) !== (string)'Na'){
		$postfields = [
			'token'=> $_POST['paymentToken'],
			'email'=> $customer['email'],
			'months'=> (int)$_POST['installmentsnum'],
			'items'=> [
				[
					'description'=> (string)(substr( implode("\n",$line_items),  0, 250)),
					'quantity'=> 1,
					'price_cents'=> $amount
				]
			],
			'payer'=> [
				'cpf_cnpj'=> $customer['document'],
				'name'=> $customer['name'],
				'email'=> $customer['email'],
			]
		];
	}
	if((!$_POST['paymentToken'] || (string)($_POST['paymentToken']) === (string)'Na') and !empty($_POST['saved_token'])){
		$postfields = [
			'customer_payment_method_id'=> $_POST['saved_token'],
			'email'=> $customer['email'],
			'months'=> (int)$_POST['installmentsnum'],
			'items'=> [
				[
					'description'=> (string)(substr( implode("\n",$line_items),  0, 250)),
					'quantity'=> 1,
					'price_cents'=> $amount
				]
			],
			'payer'=> [
				'cpf_cnpj'=> $customer['document'],
				'name'=> $customer['name'],
				'email'=> $customer['email'],
			]
		];
	}
	// save card
	if((string)$_POST['storeCard'] === (string)'yes' and $paymentToken and !$_POST['saved_token']){
		$iugu_customer_post = [
			"email"=>$customer['email'],
			"name"=>$customer['names']['firstname'].' '.$customer['names']['lastname'],
		];
		$iugu_customer = gic_iugu_customer($iugu_customer_post);
		if($iugu_customer['result']['id']){
			$iugu_payment_method = gic_payment_method(["iugu_customer"=>$iugu_customer['result']['id'],"post"=>["description"=>$_POST['cardtype']." de ".$customer['name'],"token"=>$paymentToken,"set_as_default"=>true]]);
		}
		if($iugu_customer['result']['errors']){
			$error .= $iugu_customer['result_code'].' '.$iugu_customer['result']['errors'];
		}
		if((int)$iugu_customer['result_code'] !== 200 and !$iugu_customer['result']['errors']){
			$error .= $iugu_customer['result_code'].' '.$iugu_customer['result'];
		}
		if($iugu_payment_method['result']['id']){		
			$card_to_add = [
				'userid'=>$_POST['userid'],
				'cclastfour'=>$_POST['cclastfour'],
				'cardexp'=>$_POST['cardexp'],
				'cardtype'=>$_POST['cardtype'],
				'payment_token'=>$iugu_payment_method['result']['id'],
				'myId'=> (string)((int)$_POST['pay_method_id']+1),
			];
			$gic_add_card = gic_card_add($card_to_add,$_POST['pay_method_id']);
			if((string)$gic_add_card !== (string)'success'){
				$error .= $gic_add_card;
			}
		}
		if($iugu_payment_method['result']['errors']){
			$error .= $iugu_payment_method['result_code'].' '.implode(' - ',$iugu_payment_method['result']['errors']);
		}
		if((int)$iugu_payment_method['result_code'] !== 200 and !$iugu_payment_method['result']['errors']){
			$error .= $iugu_payment_method['result_code'].' '.implode(' - ',$iugu_payment_method['result']);
		}
		if(!$error){
			$postfields['customer_payment_method_id'] = $iugu_payment_method['result']['id'];
			unset($postfields['token']);
		}
	}
	if(((string)$_POST['storeCard'] !== (string)'yes' || (string)$gic_add_card !== (string)'success') and !$_POST['saved_token']){
		$gic_card_del = gic_card_del($_POST['pay_method_id']);
		if((string)$gic_card_del !== (string)'success'){
			$error .= $gic_card_del;
		}
	}

	$charge = gic_charge($postfields);
	// Capturado
	if( (string)$charge['result']['status'] === (string)'authorized'){
		if( (int)$_POST['installmentsnum'] > 1 ){
			$trans_desc = "Pagamento Aprovado - Parcelado em ".(int)$_POST['installmentsnum']."x R$".number_format( $_POST['amount'] / (int)$_POST['installmentsnum'] ,  2, ',', '.')." - ".$_POST['cardtype'].'-'.$_POST['cclastfour'];
		}
		else {
			$trans_desc = "Pagamento Aprovado - ".$_POST['cardtype'].'-'.$_POST['cclastfour'];
		}
		$fee = (float)(($params['amount'] * (float)3.45) / 100);
		$gic_add_trans = gic_add_trans(
			$_POST['userid'],
			$_POST['invoiceid'],
			$_POST['amount'],
			$fee,
			'gic-'.$params_api['api_mode'].'-'.$charge['result']['invoice_id'],
			$trans_desc
			);
			$gic_update_stats = gic_update_stats();
		if($gic_add_trans['error']){
			$error .= $gic_add_trans['error'];
		}
	}
	if($charge['result_code'] !== 200 ){
		$error .= $charge['result_code'].' ';
	}
	if((string)$charge['result']['status'] !== (string)'authorized'){
		$error .= $charge['result']['status'];
		if(!$_POST['saved_token']){
			$gic_card_del = gic_card_del($_POST['pay_method_id']);
			if((string)$gic_card_del !== (string)'success'){
				$error .= $gic_card_del;
			}
		}
	}
	if($charge['result']['errors']){
		//if(!$_POST['saved_token']){
			$gic_card_del = gic_card_del($_POST['pay_method_id']);
			if((string)$gic_card_del !== (string)'success'){
				$error .= $gic_card_del;
			}
		//}
		if( !empty($charge['result']['errors'])){
			$error .= $charge['result']['errors'];
		}
	}
	
}
if($params['log']){	
	$log_request = [
		'post'=>$_POST,
		'params'=> $params,
		'params_api'=> $params_api,
		'customer'=> $customer,
		'postfields'=> $postfields,
	];
	$log_response = [
		 'charge'=> $charge,
		 'charge_capture'=>$charge_capture,
		 'iugu_customer'=>$iugu_customer,
		 'iugu_payment_method'=>$iugu_payment_method,
		 'gic_add_card'=>$gic_add_card,
		 'gic_card_del'=> $gic_card_del,
		 'error'=>$error,
	];
	logModuleCall('gofasiugucartao', 'iframe_payment', ['module_version'=>gic_version(),$log_request],'post',$log_response,'replaceVars');
}
if(!$error){
	$invoice_page =json_encode(gic_whmcs_url('whmcs_url').'viewinvoice.php?id='.$_POST['invoiceid'].'&paymentsuccess=true');
	echo '<script>window.top.location.href='.$invoice_page.'</script>';
}
if($error){
	$invoice_page =json_encode(gic_whmcs_url('whmcs_url').'viewinvoice.php?id='.$_POST['invoiceid'].'&gicerror='.$error);
	echo '<script>window.top.location.href='.$invoice_page.'</script>';
}