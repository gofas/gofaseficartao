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
	$htmlOutput .= '<script src="https://cdn.jsdelivr.net/gh/efipay/js-payment-token-efi/dist/payment-token-efi.min.js"></script>';
	$htmlOutput .= '<script type="text/javascript" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/ggnc_.js"></script>';
	echo $htmlOutput;
	//echo '<img class="lb-image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">';
	require __DIR__.'/functions.php';
	$params = getGatewayVariables('gofaseficartao');
	$params_api = gefic_api_connect();
	$customer = gefic_customer($_POST['userid']);
	foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'geficwhmcsurl') -> get( array( 'value','created_at') ) as $geficwhmcsurl_ ){
		$geficwhmcsurl					= $geficwhmcsurl_->value;
	}
	$access_token_ = gefic_get_token();
	$access_token = $access_token_['result']['access_token'];
	// Invoice Info
	$GetInvoiceResults			= localAPI('getinvoice',array('invoiceid'=>$_POST['invoiceid'] ),(int)$params['admin']);
	$line_items = array();
	foreach( $GetInvoiceResults['items']['item'] as $Value){
		$line_items[]	= substr( $Value['description'],  0, 80).' | R$ '.number_format( $Value['amount'],  2, ',', '.');	
	}
	//$amount = ((int)$_POST['amount'])*100;
	$amount = (int)preg_replace("/[^0-9]/", "", $_POST['amount']);

	// Cobrança avulsa
	if($_POST['cardissuenum']){
		$card = [
			'myId'=> $_POST['pay_method_id'],
		];
	}
	if(!$_POST['cardissuenum']){
		$card = [
			'myId'=> (string)((int)$_POST['pay_method_id']+1),
			//'hash'=> '',
			'number'=> $_POST['cardnum'],
			'holder'=> $customer['name'],
			'expiresAt'=> $_POST['expiresAt'],
			'cvv'=> $_POST['cccvv'],
		];
	}
	if(!$_POST['cardissuenum'] and (string)$_POST['storeCard'] === (string)'no'){
		$card = [
			//'myId'=> (string)((int)$_POST['pay_method_id']+1),
			//'hash'=> '',
			'number'=> $_POST['cardnum'],
			'holder'=> $customer['name'],
			'expiresAt'=> $_POST['expiresAt'],
			'cvv'=> $_POST['cccvv'],
		];
	}
	$postfields = [
		'items' => [[
			'name'=>(string)(substr( implode("\n",$line_items),  0, 250)),
			'value'=>$amount,
			'amount'=>1
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
			'payment_token'=>'',
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
	if( (string)$charge['result']['Charge']['Transactions']['0']['status'] === (string)'captured'){
		if( (int)$_POST['installmentsnum'] > 1 ){
			$trans_desc = "Pagamento Aprovado - Parcelado em ".(int)$_POST['installmentsnum']."x R$".number_format( $_POST['amount'] / (int)$_POST['installmentsnum'] ,  2, ',', '.')." - ".$_POST['cardtype'].'-'.$_POST['cclastfour'];
		}
		else {
			$trans_desc = "Pagamento Aprovado - ".$_POST['cardtype'].'-'.$_POST['cclastfour'];
		}
		//
		$fee = (($_POST['amount'] * $params['fee']) / 100);
		$gefic_add_trans = gefic_add_trans(
			$_POST['userid'],
			$_POST['invoiceid'],
			$_POST['amount'],
			$fee,
			'gefic-'.$charge['result']['Charge']['galaxPayId'].'-'.$params_api['api_mode'].'-'.$charge['result']['Charge']['Transactions']['0']['galaxPayId'].'.',
			$trans_desc
			);	
		if($gefic_add_trans['error']){
			$error .= $gefic_add_trans['error'];
		}
		// save card
		if((string)$_POST['storeCard'] === (string)'yes' and $charge['result']['Charge']['Transactions']['0']['CreditCard']['Card']['myId'] and !$_POST['cardissuenum']){
			$card_to_add = [
				'userid'=>$_POST['userid'],
				'cclastfour'=>$_POST['cclastfour'],
				'cardexp'=>$_POST['cardexp'],
				'cardtype'=>$_POST['cardtype'],
				'cardissuenum'=>$charge['result']['Charge']['Transactions']['0']['CreditCard']['Card']['galaxPayId'],//$_POST['issuenumber'],
				'myId'=> (string)((int)$_POST['pay_method_id']+1),
			];
			$gefic_add_card = gefic_card_add($card_to_add,$_POST['pay_method_id']);
			if((string)$gefic_add_card !== (string)'success'){
				$error .= $gefic_add_card;
			}
		}
		if(((string)$_POST['storeCard'] !== (string)'yes' || (string)$gefic_add_card !== (string)'success') and !$_POST['cardissuenum']){
			$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
			if((string)$gefic_card_del !== (string)'success'){
				$error .= $gefic_card_del;
			}
		}
	}
	if( (string)$charge['result']['Charge']['Transactions']['0']['status'] !== (string)'captured'){
		$error .= $charge['result']['Charge']['Transactions']['0']['statusDescription'];
		if(!$_POST['cardissuenum']){
			$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
			if((string)$gefic_card_del !== (string)'success'){
				$error .= $gefic_card_del;
			}
		}
	}
	if($charge['result']['error']){
		if(!$_POST['cardissuenum']){
			$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
			if((string)$gefic_card_del !== (string)'success'){
				$error .= $gefic_card_del;
			}
		}
		if( !empty($charge['result']['error']['message'])){
			$error .= $charge['result']['error']['message'];
		}
		if(!empty($charge['result']['error']['details'])){
			$error .= implode(', ',$charge['result']['error']['details']);
		}
	}
	if($charge['result_code'] !== 200 ){
		$error .= $charge['result_code'];
	}
}
if($_POST['error']){
	$error .= $_POST['error'];
	if(!$_POST['cardissuenum'] and $_POST['pay_method_id']){
		$gefic_card_del = gefic_card_del($_POST['pay_method_id']);
		if((string)$gefic_card_del !== (string)'success'){
			$error .= $gefic_card_del;
		}
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
	if($log['post']['cardnum']){
		$log['post']['cardnum'] = '1111 1111 1111 '.$_post['cclastfour'];
	}
	if($log['post']['expiresAt']){
		$log['post']['expiresAt'] = 'xxxx-xx';
	}
	if($log['post']['cardexp']){
		$log['post']['cardexp'] = 'xxxx';
	}
	if($log['post']['cccvv']){
		$log['post']['cccvv'] = 'xxx';
	}
	if($log['postfields']['charge']['PaymentMethodCreditCard']['Card']['number']){
		$log['postfields']['charge']['PaymentMethodCreditCard']['Card']['number'] = 'xxxx xxxx xxxx '.$_post['cclastfour'];
	}
    if($log['postfields']['charge']['PaymentMethodCreditCard']['Card']['expiresAt']){
		$log['postfields']['charge']['PaymentMethodCreditCard']['Card']['expiresAt'] = 'xxxx-xx';
	}
	if($log['postfields']['charge']['PaymentMethodCreditCard']['Card']['cvv']){
    	$log['postfields']['charge']['PaymentMethodCreditCard']['Card']['cvv']= 'xxx';
	}
	if($log['charge']['result']['charge']['Transactions']['0']['CreditCard']['Card']['number']){
		$log['charge']['result']['charge']['Transactions']['0']['CreditCard']['Card']['number'] = 'xxxx xxxx xxxx '.$_post['cclastfour'];
	}
	if($log['charge']['result']['charge']['PaymentMethodCreditCard']['0']['CreditCard']['Card']['number']){
		$log['charge']['result']['charge']['PaymentMethodCreditCard']['0']['CreditCard']['Card']['number'] = 'xxxx xxxx xxxx '.$_post['cclastfour'];
	}
	logModuleCall('gofaseficartao', 'iframe_payment', ['module_version'=>gefic_version(),'request'=>$log_request],'post',['response'=>$log_response],'replaceVars');
}
if(!$error){
	$invoice_page =json_encode($geficwhmcsurl.'/viewinvoice.php?id='.$_POST['invoiceid'].'&paymentsuccess=true');
	echo '<script>window.top.location.href='.$invoice_page.'</script>';
}
if($error){
	$invoice_page =json_encode($geficwhmcsurl.'/viewinvoice.php?id='.$_POST['invoiceid'].'&geficerror='.$error);
	//echo '<script>window.top.location.href='.$invoice_page.'</script>';
}