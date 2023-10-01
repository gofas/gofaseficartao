<?php
/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.0
 */
use WHMCS\Database\Capsule;
function gofaseficartao_capture($params){
	require __DIR__.'/functions.php';
	$Params = json_decode( json_encode($params), true);
	$pay_method_id = $Params['payMethod']['payment']['pay_method_id'];
	foreach( Capsule::table('gofaseficartao') -> where('pay_method_id','=',$pay_method_id)->get(['payment_token']) as $saved_token_ ){
		$saved_token					= $saved_token_->payment_token;
	}
	$params_api = gefic_api_connect();
	$customer = gefic_customer($params['clientdetails']['userid']);
	$GetInvoiceResults			= localAPI('getinvoice',array('invoiceid'=>$params['invoiceid'] ), (int)$params['admin'] );
	$line_items = array();
	foreach( $GetInvoiceResults['items']['item'] as $Value){
		$line_items[]	= substr( $Value['description'],  0, 80).' | R$ '.number_format( $Value['amount'],  2, ',', '.');	
	}
	$amount = (int)preg_replace("/[^0-9]/", "", $params['amount']);
	$pAye_e = 'b7ac135895cfb50a2a90cf28fe0d15e0';
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
			],
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
			'installments'=> 1,
			'payment_token'=>$saved_token,
			'billing_address'=>[
			  'street'=>$customer['address'],
			  'number'=>$customer['number'],
			  'neighborhood'=>$customer['neighborhood'],
			  'zipcode'=>$customer['postcode'],
			  'city'=>$customer['city'],
			  'complement'=>$customer['complement'],
			  'state'=>$customer['state']
			],
		  ]
		]
	  ];
	$charge = gefic_charge($postfields);
	logModuleCall('gofaseficartao', 'capture_payment', ['module_version'=>gefic_version(),$params,$_POST,$postfields],'post',[$charge],'replaceVars');
	if( $charge['result']['error']){
		if( !empty($charge['result']['error_description']['property'])){
			$error .= $charge['result']['error_description']['property'];
		}
		if(!empty($charge['result']['error_description']['message'])){
			$error .= $charge['result']['error_description']['message'];
		}
	}
	if( (string)$charge['result']['data']['status'] !== (string)'approved'){
		$declined = true;
	}
	if((string)$charge['result']['data']['status'] === (string)'approved'){
		$fee = (($params['amount'] * $params['fee']) / 100)+0.29;
		$gefic_update_stats = gefic_update_stats();
		return array(
            'status' => 'success',
            'transid' => 'gefic-'.$params_api['api_mode'].'-'.$charge['result']['data']['charge_id'],
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
				'declinereason' => $charge['result']['error_description']['message'],
                'rawdata' => $charge,
         );
	}
}