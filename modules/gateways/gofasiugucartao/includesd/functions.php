<?php
/**
 * Módulo iugu Cartão para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14946
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=12349
 * @version		1.0.0
 */
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
if(!defined("WHMCS")){die();}
use WHMCS\Database\Capsule;
if(!function_exists('gic_version')){
	function gic_version($int=false){
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'gic_version') -> get( array( 'value','created_at') ) as $gic_version_ ){
			$gic_version				= $gic_version_->value;
			$gic_version_created_at	= $gic_version_->created_at;
		}
		if(!$int){
			return $gic_version;
		}
		if($int){
			return (int)preg_replace("/[^0-9]/", "", $gic_version);
		}
	}
}
if(!function_exists('gic_api_connect')){
	function gic_api_connect(){
		$params = getGatewayVariables('gofasiugucartao');
		if($params['sandbox']){
			$params_api = [
				'api_mode' => 'sandbox',
				'api_token' => $params['sandbox_api_token'],
				'charge_url' => 'https://api.iugu.com/v1',
			];
		}
		if(!$params['sandbox']){
			$params_api = [
				'api_mode' => 'live',
				'api_token' => $params['api_token'],
				'charge_url' => 'https://api.iugu.com/v1',
			];
		}
		return $params_api;
	}
}
if(!function_exists('gic_charge')){
	function gic_charge($postfields){
		$params_api = gic_api_connect();
    	$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $params_api['charge_url'].'/charge',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPHEADER => array(
		    	'Authorization: Basic '.base64_encode((string)$params_api['api_token'].':'),
				'Content-Type: application/json',
				'Accept: application/json',
		  	),
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($postfields),
		));
		$result = json_decode(curl_exec($curl),true);
		$result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['result_code'=>$result_code,'result'=>$result];
	}
}
if(!function_exists('gic_iugu_customer')){
	function gic_iugu_customer($postfields){
		$params_api = gic_api_connect();
    	$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $params_api['charge_url'].'/customers',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPHEADER => array(
		    	'Authorization: Basic '.base64_encode((string)$params_api['api_token'].':'),
				'Content-Type: application/json',
				'Accept: application/json',
		  	),
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($postfields),
		));
		$result = json_decode(curl_exec($curl),true);
		$result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['result_code'=>$result_code,'result'=>$result];
	}
}
if(!function_exists('gic_payment_method')){
	function gic_payment_method($postfields){
		$params_api = gic_api_connect();
    	$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $params_api['charge_url'].'/customers/'.$postfields['iugu_customer'].'/payment_methods',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPHEADER => array(
		    	'Authorization: Basic '.base64_encode((string)$params_api['api_token'].':'),
				'Content-Type: application/json',
				'Accept: application/json',
		  	),
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($postfields['post']),
		));
		$result = json_decode(curl_exec($curl),true);
		$result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['result_code'=>$result_code,'result'=>$result];
	}
}
if(!function_exists('gic_update_stats')){
	function gic_update_stats(){
		$params = getGatewayVariables('gofasiugucartao');
		if($params['sandbox']){
			return;
		}
		$whmcs_url = gic_whmcs_url();
		$setup_admin = gic_setup_admin();
		$query = '?software_id=14946&install_url='.$whmcs_url['admin_url'].'&current_version='.gic_get_local_version().'&installer_email='.$setup_admin['email'].'&installer_firstname='.$setup_admin['firstname'].'&installer_lastname='.$setup_admin['lastname'].'&action=charge'.gic_sysinfo();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_URL, 'https://gofas.net/br/updates/stats.php'.$query);
		$response = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		$return = ['query'=>$query,'response'=>$response,'http_code'=>$http_status];
		return $return;
	}
}
if( !function_exists('gic_get_string_between') ){
	function gic_get_string_between($string, $start, $end){
		$string = " ".$string;
		$ini = strpos($string,$start);
		if ($ini == 0) return "";
		$ini += strlen($start);   
		$len = strpos($string,$end,$ini) - $ini;
		return substr($string,$ini,$len);
	}
}
if( !function_exists('gic_card_add') ){
	function gic_card_add($card,$pay_method_id){
		try {
			Capsule::table('tblcreditcards')->where( 'pay_method_id', $pay_method_id)->delete();
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}
		try {
			Capsule::table('tblpaymethods')->where( 'id', $pay_method_id)->delete();
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}	
		try {
			$createCardPayMethod = createCardPayMethod( // Function available in WHMCS 7.9 and later
				$card['userid'],
				'gofasiugucartao',
				'111111111111'.$card['cclastfour'],
				$card['cardexp'],
				$card['cardtype'],
				NULL, //start date
				$card['cardissuenum'],//NULL, //issue number
				$card['myId']
			);
		}
		catch (Exception $e){
			$error .= $e->getMessage();
		}
		try { Capsule::table('gofasiugucartao')->insert([
			'user_id' => $card['userid'],
			'pay_method_id' => $card['myId'],
			'payment_token' => $card['payment_token']
		]);
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}
		if($error){
			gic_card_del($card['myId']);
			return $error;
		}
		return 'success';
	}
}
if( !function_exists('gic_card_del') ){
	function gic_card_del($pay_method_id){
		try {
			Capsule::table('tblcreditcards')->where( 'pay_method_id', $pay_method_id)->delete();
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}
		try {
			Capsule::table('tblpaymethods')->where( 'id', $pay_method_id)->delete();
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}
		if($error){
			return $error;
		}
		return 'success';
	}
}
if( !function_exists('gic_add_trans') ){
	function gic_add_trans( $user_id, $invoice_id, $amount, $fee, $charge_id, $description ){	
 		$addtransvalues['userid'] = $user_id;
 		$addtransvalues['invoiceid'] = $invoice_id;
 		$addtransvalues['description'] = $description;
 		$addtransvalues['amountin'] = $amount;
 		$addtransvalues['fees'] = $fee;
 		$addtransvalues['paymentmethod'] = 'gofasiugucartao';
 		$addtransvalues['transid'] = $charge_id;
 		$addtransvalues['date'] = date('d/m/Y');
		$addtransresults = localAPI( "addtransaction", $addtransvalues, (int)$params['admin']);
		if( $addtransresults['result'] === 'success'){
			return array('values'=>$addtransvalues, 'result'=>$addtransresults);
		}
		elseif($addtransresults['result'] !== 'success'){
			$error = '<b>Não foi possível gravar a transação.</b>';
			return array('error'=>$error, 'values'=>$addtransvalues, 'result'=>$addtransresults);
		}
	}
}

if(!function_exists('gic_customer') ){
	function gic_customer($client_id){
		//Determine custom fields id
		$params = getGatewayVariables('gofasiugucartao');
		$client = localAPI('GetClientsDetails',array( 'clientid' => $client_id, 'stats' => false, ), $params['admin']);
		foreach( Capsule::table('tblcustomfields')->where('type','=','client')->get() as $customfield ){
			$customfield_id = $customfield->id;
			$customfield_name = strtolower($customfield->fieldname);
			// cpf
			if(strpos($customfield_name, 'cpf') !== false and strpos($customfield_name,'cnpj') === false){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$cpf_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
				}
			}	
			// cnpj
			if(strpos($customfield_name, 'cnpj') !== false and strpos($customfield_name,'cpf') === false){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$cnpj_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
				}
			}
			// cpf + cnpj
			if( strpos( $customfield_name, 'cpf') !== false and strpos( $customfield_name, 'cnpj') !== false ){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$cpf_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
					$cnpj_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
				}
			}
			// Inscrição Estadual
			if( strpos( $customfield_name, 'inscrição estadual') !== false){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$ie = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
				}
			}
			// Complemento Custom Field
			if( strpos( $customfield_name, 'complemento') !== false){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$complement = $customfieldvalue->value;
				}
			}
			// Número Custom Field
			if( strpos( $customfield_name, 'numero')!== false ||  strpos( $customfield_name, 'número')!== false ){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$number = $customfieldvalue->value;
				}
				if(!$number){
					$number = preg_replace('/[^0-9]/', '', $client['address1']);
				}
			}
			else {
				$number = preg_replace('/[^0-9]/', '', $client['address1']);
			}
			// Emitir Custom Field
			if( strpos( $customfield_name, 'emitir nfe')!== false || strpos( $customfield_name, 'emitir nfse')!== false || strpos( $customfield_name, 'emitir nfs-e')!== false || strpos( $customfield_name, 'emitir nf-e')!== false){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$issue_nfe = $customfieldvalue->value;
				}
				if(!$issue_nfe){
					$issue_nfe = false;
				}
			}
			// nascimento
			if( strpos( $customfield_name, 'nascimento') ){
				foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $client_id) -> get( array( 'value') ) as $customfieldvalue ){
					$birt_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
					$birthday_pre			= preg_replace('/[^\da-z]/i', '', $birt_customfield_value);
					if(strlen($birthday_pre) === 8){
						$birth_ = $birthday_pre;
					}
					elseif( strlen($birthday_pre) === 7 ){
						$birth_ = '0'.$birthday_pre;
					}
					$birth_Y					= substr($birth_, -4);
					$birth_m					= substr($birth_, 2, -4);
					$birth_d					= substr($birth_, 0, -6);
					$birthday_us = $birth_Y.'-'.$birth_m.'-'.$birth_d; // 2021-02-20
					$birthday_br = $birth_d.'/'.$birth_m.'/'.$birth_Y; // 20/02/2021
					$birthday_raw = $customfieldvalue->value;
				}
			}
			foreach(Capsule::table('tblcustomfieldsvalues')->where('fieldid','=',$customfield_id)->where('relid','=',$client_id)->get(array('value')) as $customfieldvalue ){
				$custom_fields[$customfield_name] = $customfieldvalue->value;
			}
		}
		//
		// Cliente possui CPF e CNPJ
		// CPF com 1 nº a menos, adiciona 0 antes do documento
		if( strlen( $cpf_customfield_value ) === 10 ){
			$cpf = '0'.$cpf_customfield_value;
		}
		// CPF com 11 dígitos
		elseif( strlen( $cpf_customfield_value ) === 11){
			$cpf = $cpf_customfield_value;
		}
		// CNPJ no campo de CPF com um dígito a menos
		elseif( strlen( $cpf_customfield_value ) === 13 ){
			$cpf = false; 
			$cnpj = '0'.$cpf_customfield_value;
		}
		// CNPJ no campo de CPF
		elseif( strlen( $cpf_customfield_value ) === 14 ){
			$cpf 				= false;
			$cnpj				= $cpf_customfield_value;
		}
		// cadastro não possui CPF
		elseif( !$cpf_customfield_value || strlen( $cpf_customfield_value ) !== 10 || strlen($cpf_customfield_value) !== 11 || strlen( $cpf_customfield_value ) !== 13 || strlen($cpf_customfield_value) !== 14 ){	
			$cpf = false;
		}
		// CNPJ com 1 nº a menos, adiciona 0 antes do documento
		if( strlen($cnpj_customfield_value) === 13 ){
			$cnpj = '0'.$cnpj_customfield_value;
		}
		// CNPJ com nº de dígitos correto
		elseif( strlen($cnpj_customfield_value) === 14 ){
			$cnpj = $cnpj_customfield_value;
		}
		// Cliente não possui CNPJ
		elseif( !$cnpj_customfield_value and strlen( $cnpj_customfield_value ) !== 14 and strlen($cnpj_customfield_value) !== 13 and strlen( $cpf_customfield_value ) !== 13 and strlen( $cpf_customfield_value ) !== 14  ){
			$cnpj = false;
		}

		if( ( $cpf and $cnpj ) or ( !$cpf and $cnpj ) ){
			if( $client['companyname'] ){
				$name	= $client['companyname'];
			}
			elseif( !$client['companyname'] ){
				$name	= $client['firstname'].' '.$client['lastname'];
			}
			$doc_type	= 'J';
			$document	= $cnpj;
		}
		elseif( $cpf and !$cnpj ){
			$name	= $client['firstname'].' '.$client['lastname'];
			$doc_type	= 'F';
			$document	= $cpf;
		}
		/// Formated Array
		$customer=[
			'id'=>$client_id,
			'email'=>$client['email'],
			'name'=>$name,
			'names'=>['firstname'=>$client['firstname'],'lastname'=>$client['lastname'],'companyname'=>$client['companyname']],
			'address'=>str_replace(',','',preg_replace('/[0-9]+/i','',$client['address1'],1)),
			'number'=>$number,
			'neighborhood'=>$client['address2'],
			'complement'=>$complement,
			'city'=>$client['city'],
			'state'=>$client['state'],
			'postcode'=>preg_replace("/[^\da-z]/i", "",$client['postcode']),
			'phone'=>preg_replace('/[^\da-z]/i', '', $client['phonenumber']),
			'doc_type'=>$doc_type,
			'document'=>$document,
			'ie'=>$ie,
			'issue_nfe'=>$issue_nfe,
			'birthday'=>['raw'=>$birthday_raw,'br'=>$birthday_br,'us'=>$birthday_us],
			'custom_fields'=>$custom_fields,
		];
		return $customer;
	}
}

if( !function_exists('gic_get_embed') ){
	function gic_get_embed($page_id,$referer,$module_version){
		$query = 'https://gofas.net/cliente/gofas/updates/?embed='.$page_id.'&referer='.$referer.'&version='.$module_version;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_URL, $query);
		$embed = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['embed'=>$embed,'http_code'=>$http_status];
	}
}
if(!function_exists('gic_encrypt')){
	function gic_encrypt($q) {
	    $encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
	    return openssl_encrypt($q, $encryptionMethod, $secretHash);
	}
}
if(!function_exists('gic_decrypt')){
	function gic_decrypt($q){
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
	    return openssl_decrypt($q, $encryptionMethod, $secretHash);
	}
}
if( !function_exists('gic_get_version') ){
	function gic_get_version($page_id,$referer,$module_version){
		$currentUser = new \WHMCS\Authentication\CurrentUser; // Require WHMCS 8.0+
		$admin_ = json_decode(json_encode($currentUser->admin()),true);
		$admin = ['email'=>$admin_['email'],'firstname'=>$admin_['firstname'],'lastname'=>$admin_['lastname']];
		$query = 'https://gofas.net/br/updates/?software='.$page_id.'&referer='.$referer.'&version='.$module_version.'&email='.$admin['email'].'&firstname='.$admin['firstname'].'&lastname='.$admin['lastname'].gic_sysinfo();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_URL, $query);
		$available_version_ = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['version'=>$available_version_,'http_code'=>$http_status];
	}
}
if(!function_exists('gic_sysinfo')){
	function gic_sysinfo(){
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','Version')
		->get(['value']) as $data1 ){
			$Version = $data1->value;
		}
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','CronPHPVersion')
		->get(['value']) as $data1 ){
			$PHPVersion = $data1->value;
		}
		return '&whmcs_version='.$Version.'&php_version='.$PHPVersion;
	}
}
if(!function_exists('gic_verifyInstall')){
	function gic_verifyInstall(){
		if( !Capsule::schema()->hasTable('gofasiugucartao') ){
			try {
				Capsule::schema()->create('gofasiugucartao', function($table){
					// incremented id
					$table->increments('id');
					$table->string('user_id');
					$table->string('pay_method_id');
					$table->string('payment_token');
				});
			}
			catch (\Exception $e){
				$error = "Não foi possível criar a tabela do módulo no banco de dados: {$e->getMessage()}";
			}
		}
		if(!$error){
			return array('success'=>1);
		}
		if($error){
			return array('error'=>$error);
		}
	}
}
if(!function_exists('gic_verify_module_updates')){
	function gic_verify_module_updates($page_id,$referer,$module_version){
		foreach( Capsule::table('tblconfiguration')->where('setting','=','gic_version')->get(['value','created_at','updated_at']) as $version_ ){
			$version		= json_decode($version_->value, true);
			$local_version	= $version['local_version'];
			$last_version	= $version['last_version'];
			$embed			= $version['check'];
			$created_at		= $version_->created_at;
			$updated_at		= $version_->updated_at;
			//$available_version	= (int)preg_replace("/[^0-9]/","",$version['last_version']);
		}
		///// Get
		if(!$version){
			$get_version = gic_get_version($page_id,$referer,$module_version);
			$get_embed	 = gic_get_embed($page_id,$referer,$module_version);
			
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and strtotime($updated_at) < strtotime("-1 day")){
			$get_version = gic_get_version($page_id,$referer,$module_version);
			$get_embed	 = gic_get_embed($page_id,$referer,$module_version);
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and (string)$module_version !== (string)$local_version){
			$get_version = gic_get_version($page_id,$referer,$module_version);
			$get_embed	 = gic_get_embed($page_id,$referer,$module_version);
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and strtotime($updated_at) > strtotime("-1 day")){
			$available_version = $last_version;
		}
		// insert
		if(!$version and $get_version['version'] and $get_embed['embed']){
			$local_version = $module_version;
			$last_version = $get_version['version'];
			$embed		  = gic_encrypt($get_embed['embed']);
			$created_at		= date("Y-m-d H:i:s");
			$updated_at		= date("Y-m-d H:i:s");

			try { Capsule::table('tblconfiguration')->insert(array(
				'setting' => 'gic_version',
				'value' => json_encode([
					'local_version'=>$module_version,
					'last_version'=>$get_version['version'],
					'check'=>gic_encrypt($get_embed['embed']),
					'admin'=>gic_current_admin(),
				]),
				'created_at' => $created_at,
				'updated_at' => $updated_at
			));
			}
			catch (\Exception $e){
				$error .= $e->getMessage();
			}
		}
		// update
		if($version and $get_version['version'] and $get_embed['embed'] and strtotime($updated_at) < strtotime("-1 day") and (
			$available_version !== $module_version ||
			$local_version !== $module_version ||
			$last_version !== $available_version
		)){
			try {
				Capsule::table('tblconfiguration')->where('setting','gic_version')->update([
					'value' => json_encode([
						'local_version'=>$module_version,
						'last_version'=>$available_version,
						'check'=>gic_encrypt($get_embed['embed']),
						'admin'=>gic_current_admin(),
					]),
					'created_at' =>  $created_at,
					'updated_at' => date("Y-m-d H:i:s")]
				);
			}
			catch (\Exception $e){
				$error .= $e->getMessage();
			}
		}
		// update
		if($version and $get_version['version'] and $get_embed['embed'] and (string)$local_version !== (string)$module_version){
			try {
				Capsule::table('tblconfiguration')->where('setting','gic_version')->update([
					'value' => json_encode([
						'local_version'=>$module_version,
						'last_version'=>$available_version,
						'check'=>gic_encrypt($get_embed['embed']),
						'admin'=>gic_current_admin(),
					]),
					'created_at' =>  $created_at,
					'updated_at' => date("Y-m-d H:i:s")]
				);
			}
			catch (\Exception $e){
				$error .= $e->getMessage();
			}
		}
		$module_version_int = (int)preg_replace("/[^0-9]/", "", $module_version);
		$available_version_int = (int)preg_replace("/[^0-9]/", "", $available_version);
		if( $available_version_int === $module_version_int ){
			$message .= '<p style="color: green"><i class="fas fa-check-square"></i> Você está executando a versão mais recente do módulo.</p>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gic_whmcs_url('admin_url').'/configgateways.php?manage=gofasiugucartao&resetversion=gofasiugucartao#m_gofasiugucartao">verificar agora</a>.</p>';
		}
		if( $available_version_int > $module_version_int ){
			$message .= '<p style="font-size: 14px; color: red;"><i class="fas fa-exclamation-triangle"></i> Atualização disponível, verifique a <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">versão '.$available_version.'</a>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gic_whmcs_url('admin_url').'/configgateways.php?manage=gofasiugucartao&resetversion=gofasiugucartao#m_gofasiugucartao">verificar agora</a>.</p>';$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gic_whmcs_url('admin_url').'/configgateways.php?manage=gofasiugucartao&resetversion=gofasiugucartao#m_gofasiugucartao">verificar agora</a>.</p>';$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gic_whmcs_url('admin_url').'/configgateways.php?manage=gofasiugucartao&resetversion=gofasiugucartao#m_gofasiugucartao">verificar agora</a>.</p>';
		}
		if( $available_version_int < $module_version_int ){
			$message = '<p style="font-size: 14px; color: orange;"><i class="fas fa-exclamation-triangle"></i> Você está executando uma versão Beta desse módulo.<br>Baixar versão estável: <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">v'.$available_version.'</a>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gic_whmcs_url('admin_url').'/configgateways.php?manage=gofasiugucartao&resetversion=gofasiugucartao#m_gofasiugucartao">verificar agora</a>.</p>';
		}
		return [
			'version'=>$version,
			'get_version'=>$get_version,
			'message' => $message,
			'check'=> $embed,
			'error' => $error,
		];
	}
}
if(!function_exists('gic_reset_local_version')){
	function gic_reset_local_version(){
        try{
	        Capsule::table('tblconfiguration')->where('setting','=','gic_version')->delete();
	        return 'sucess';
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
}}
if(!function_exists('gic_version')){
	function gic_version($opt=1){
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'gic_version') -> get( array( 'value','created_at') ) as $gic_version_ ){
			$gic_version				= $gic_version_->value;
			$gic_version_created_at	= $gic_version_->created_at;
		}
		if($opt=1){ // local_version string
			$version = json_decode($gic_version, true);
			return $version['local_version'];
		}
		if($opt=2){ // local_version integer
			$version = json_decode($gic_version, true);
			return (int)preg_replace("/[^0-9]/", "", $version['local_version']);
		}
		if($opt=3){ // full
			return$gic_version;
		}
	}
}
if(!function_exists('gic_current_admin')){
	function gic_current_admin(){
		$currentUser = new \WHMCS\Authentication\CurrentUser;
		$admin = json_decode(json_encode($currentUser->admin()),true);
		return $admin;
	}
}
if(!function_exists('gic_tbladmins')){
	function gic_tbladmins(){
		foreach( Capsule::table('tbladmins') -> get() as $tbladmins_ ){
			$tbladmins[$tbladmins_->id] = $tbladmins_->id.' - '.$tbladmins_->firstname.' '.$tbladmins_->lastname.' ('.$tbladmins_->username.')';
		}
		return $tbladmins;
	}
}

if(!function_exists('gic_whmcs_url')){
	function gic_whmcs_url($type='all'){
        $info=[];
        $self = App::self();
		$info['root_dir'] = '/'.gic_get_string_between(gic_get_protected_property(gic_get_protected_property(gic_get_protected_property(gic_get_protected_property($self, 'clientTemplate'), 'config'),'configFile'),'path'),'/','/templates/');
		$info['whmcs_url'] = App::getSystemUrl();
		$info['admin_path'] = gic_get_protected_property($self, 'customadminpath');
        $info['admin_url'] = $info['whmcs_url'].$info['admin_path'];
		if((string)$type===(string)'all'){
			return $info;
		}
        return $info[$type];
	}
}
if(!function_exists('gic_get_protected_property')){
	function gic_get_protected_property($object, $property){
	    $reflectedClass = new \ReflectionClass($object);
	    $reflection = $reflectedClass->getProperty($property);
	    $reflection->setAccessible(true);
	    return $reflection->getValue($object);
	}
}
if(!function_exists('gic_setup_admin')){
	function gic_setup_admin(){
	foreach( Capsule::table('tblconfiguration')->where('setting','=','gic_version')->get(['value']) as $version_ ){
		$version		= json_decode($version_->value, true);
		$admin			= $version['admin'];
	}
	return $admin;
}}
if(!function_exists('gic_get_local_version')){
	function gic_get_local_version(){
	foreach( Capsule::table('tblconfiguration')->where('setting','=','gic_version')->get(['value']) as $version_ ){
		$version		= json_decode($version_->value, true);
		$local_version			= $version['local_version'];
	}
	return $local_version;
}}