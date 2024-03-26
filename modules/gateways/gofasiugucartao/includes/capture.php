<?php
/**
 * Módulo iugu Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=14946
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=12349
 * @version		1.0.0
 */
use WHMCS\Database\Capsule;
function gofasiugucartao_capture($params){
	require __DIR__.'/functions.php';
	$Params = json_decode( json_encode($params), true);
	$pay_method_id = $Params['payMethod']['payment']['pay_method_id'];
	foreach( Capsule::table('gofasiugucartao') -> where('pay_method_id','=',$pay_method_id)->get(['payment_token']) as $saved_token_ ){
		$saved_token					= $saved_token_->payment_token;
	}
	$params_api = gic_api_connect();
	$customer = gic_customer($params['clientdetails']['userid']);
	$GetInvoiceResults			= localAPI('getinvoice',array('invoiceid'=>$params['invoiceid'] ), (int)$params['admin'] );
	$line_items = array();
	foreach( $GetInvoiceResults['items']['item'] as $Value){
		$line_items[]	= substr( $Value['description'],  0, 80).' | R$ '.number_format( $Value['amount'],  2, ',', '.');	
	}
	$amount = (int)preg_replace("/[^0-9]/", "", $params['amount']);
	$postfields = [
		'customer_payment_method_id'=> $saved_token,
		'email'=> $customer['email'],
		//'months'=> (int)$_POST['installmentsnum'],
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
	$charge = gic_charge($postfields);
	logModuleCall('gofasiugucartao', 'capture_payment', ['module_version'=>gic_version(),$params,$_POST,$postfields],'post',[$charge],'replaceVars');
	if($charge['result']['errors']){
		$error .= $charge['result']['errors'];
	}
	if( (string)$charge['result']['status'] !== (string)'authorized'){
		$declined = true;
	}
	if( (string)$charge['result']['status'] === (string)'authorized'){
		$fee = (($params['amount'] * $params['fee']) / 100)+0.29;
		$gic_update_stats = gic_update_stats();
		return array(
            'status' => 'success',
            'transid' => 'gic-'.$params_api['api_mode'].'-'.$charge['result']['invoice_id'],
			'fee' => $fee,
			'gatewayid' => NULL,
			'rawdata' => $charge
        );
	}
	if($error){
		return array(
            'status' => 'error',
            'rawdata' => $charge,
         );
	}
	if($declined){
		return array(
                'status' => 'declined',
				'declinereason' => $charge['result']['errors'],
                'rawdata' => $charge,
         );
	}
}