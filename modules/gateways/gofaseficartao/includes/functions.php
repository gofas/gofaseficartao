<?php
/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.0
 */
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
if(!defined("WHMCS")){die();}
use WHMCS\Database\Capsule;
if(!function_exists('gefic_version')){
	function gefic_version($int=false){
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'gefic_version') -> get( array( 'value','created_at') ) as $gefic_version_ ){
			$gefic_version				= $gefic_version_->value;
			$gefic_version_created_at	= $gefic_version_->created_at;
		}
		if(!$int){
			return $gefic_version;
		}
		if($int){
			return (int)preg_replace("/[^0-9]/", "", $gefic_version);
		}
	}
}
if(!function_exists('gefic_api_connect')){
	function gefic_api_connect(){
		$params = getGatewayVariables('gofaseficartao');
		if($params['sandbox']){
			$params_api = [
				'api_mode' => 'sandbox',
				'clientid' => $params['clientidsandbox'],
				'clientsecret' => $params['clientsecretsandbox'],
				'identifier' => $params['identifier'],
				'charge_url' => 'https://sandbox.gerencianet.com.br/v1/',
				'javascript' => '<script type="text/javascript">var s=document.createElement("script");s.type="text/javascript";var v=parseInt(Math.random()*1000000);s.src="https://sandbox.gerencianet.com.br/v1/cdn/'. $params['identifier'].'/"+v;s.async=false;s.id="'. $params['identifier'].'";if(!document.getElementById("'. $params['identifier'].'")){document.getElementsByTagName("head")[0].appendChild(s);};$gn={validForm:true,processed:false,done:{},ready:function(fn){$gn.done=fn;}};</script>',
				'environment'=> 'sandbox',
				
			];
		}
		if(!$params['sandbox']){
			$params_api = [
				'api_mode' => 'live',
				'clientid' => $params['clientid'],
				'clientsecret' => $params['clientsecret'],
				'identifier' => $params['identifier'],
				'charge_url' => 'https://api.gerencianet.com.br/v1/',
				'javascript' => '<script type="text/javascript">var s=document.createElement("script");s.type="text/javascript";var v=parseInt(Math.random()*1000000);s.src="https://api.gerencianet.com.br/v1/cdn/'. $params['identifier'].'/"+v;s.async=false;s.id="'. $params['identifier'].'";if(!document.getElementById("'. $params['identifier'].'")){document.getElementsByTagName("head")[0].appendChild(s);};$gn={validForm:true,processed:false,done:{},ready:function(fn){$gn.done=fn;}};</script>',
				'environment'=> 'production',
			];
		}
		return $params_api;
	}
}
if( !function_exists('gefic_get_token') ){
	function gefic_get_token(){
		$params_api = gefic_api_connect();
		$curl = curl_init($params_api['charge_url'].'authorize');
		$client_id=$params_api['clientid'];
		$client_secret=$params_api['clientsecret'];
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Basic '. base64_encode("$client_id:$client_secret"),
			'Content-Type: application/json',
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($curl, CURLOPT_POST, true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('grant_type'=>'client_credentials','partner_token'=>'baaf5b95d55433890bd835cf006772b9462bde8f',)));
  		$json = json_decode(curl_exec($curl), true);
		if($json['access_token']){
			return array('access_token'=>$json['access_token']);
		}
		else {
			if($json){
	  			$error	.= 'Erro: '.implode(', ', $json);
			}
			return array('error'=> $error, 'debug'=> $json);
		}
	}
}
if( !function_exists('gefic_charge') ){
	function gefic_charge($postfields){
		$params_api = gefic_api_connect();
    	$access_token = gefic_get_token($params_api['charge_url'],$params_api['clientid'],$params_api['clientsecret']);
		$curl = curl_init($params_api['charge_url'].'charge/one-step');
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token['access_token'], 'Content-Type: application/json', 'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f'));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($curl, CURLOPT_POST, 1);
  		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postfields));
		$result = json_decode(curl_exec($curl),true);
		$result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['result_code'=>$result_code,'result'=>$result];
	}
}
if( !function_exists('gefic_refund') ){
	function gefic_refund($charge_id,$access_token){
		$params_api = gefic_api_connect();
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $params_api['charge_url'].'/charges/'.$charge_id.'/galaxPayId/reverse',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'PUT',
			//CURLOPT_POSTFIELDS =>'[]',
			CURLOPT_HTTPHEADER => array(
			  'Authorization: Bearer '.$access_token,
			  'AuthorizationPartner: '.base64_encode($params_api['galaxIdPartner'].':'. $params_api['galaxHashPartner']),
			  'Content-Type: application/json'
			),
		  ));
		$result = json_decode(curl_exec($curl),true);
		$result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['result_code'=>$result_code,'result'=>$result];
	}
}
if( !function_exists('gefic_get_string_between') ){
	function gefic_get_string_between($string, $start, $end){
		$string = " ".$string;
		$ini = strpos($string,$start);
		if ($ini == 0) return "";
		$ini += strlen($start);   
		$len = strpos($string,$end,$ini) - $ini;
		return substr($string,$ini,$len);
	}
}
if( !function_exists('gefic_card_add') ){
	function gefic_card_add($card,$pay_method_id){
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
			Capsule::table('gofaseficartao')->where('pay_method_id','=',$pay_method_id)->delete();
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}	
		try {
			$createCardPayMethod = createCardPayMethod( // Function available in WHMCS 7.9 and later
				$card['userid'],
				'gofaseficartao',
				'111111111111'.$card['cclastfour'],
				$card['cardexp'],
				$card['cardtype'],
				NULL,
				NULL,
				$card['payment_token']
			);
		}
		catch (Exception $e){
			$error .= $e->getMessage();
		}////
		try { Capsule::table('gofaseficartao')->insert(
			[
			'user_id' =>$card['userid'],
			'pay_method_id' => $pay_method_id+1,
			'payment_token' => $card['payment_token'],
		]);
		}
		catch (\Exception $e){
			$error .= $e->getMessage();
		}/////
		if($error){
			gefic_card_del($card['myId']);
			return $error;
		}
		return 'success';
	}
}
if( !function_exists('gefic_card_del') ){
	function gefic_card_del($pay_method_id){
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
			Capsule::table('gofaseficartao')->where('pay_method_id','=',$pay_method_id)->delete();
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
if( !function_exists('gefic_add_trans') ){
	function gefic_add_trans( $user_id, $invoice_id, $amount, $fee, $charge_id, $description ){	
 		$addtransvalues['userid'] = $user_id;
 		$addtransvalues['invoiceid'] = $invoice_id;
 		$addtransvalues['description'] = $description;
 		$addtransvalues['amountin'] = $amount;
 		$addtransvalues['fees'] = $fee;
 		$addtransvalues['paymentmethod'] = 'gofaseficartao';
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

if(!function_exists('gefic_customer')){
	function gefic_customer($client_id){
		//Determine custom fields id
		$params = getGatewayVariables('gofaseficartao');
		$client = localAPI('GetClientsDetails',array( 'clientid' => $client_id, 'stats' => false, ), (int)gefic_setup_admin()['id']);
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
		elseif(!$cpf_customfield_value || strlen( $cpf_customfield_value ) !== 10 || strlen($cpf_customfield_value) !== 11 || strlen( $cpf_customfield_value ) !== 13 || strlen($cpf_customfield_value) !== 14 ){	
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
		elseif(!$cnpj_customfield_value and strlen( $cnpj_customfield_value ) !== 14 and strlen($cnpj_customfield_value) !== 13 and strlen( $cpf_customfield_value ) !== 13 and strlen( $cpf_customfield_value ) !== 14  ){
			$cnpj = false;
		}

		if( ( $cpf and $cnpj ) or ( !$cpf and $cnpj ) ){
			if( $client['companyname'] ){
				$name	= $client['companyname'];
			}
			elseif(!$client['companyname'] ){
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
			'cpf'=>$cpf,
			'cnpj'=>$cnpj,
			'ie'=>$ie,
			'issue_nfe'=>$issue_nfe,
			'birthday'=>['raw'=>$birthday_raw,'br'=>$birthday_br,'us'=>$birthday_us],
			'custom_fields'=>$custom_fields,
		];
		return $customer;
	}
}
// Admin functions
if(!function_exists('gefic_whmcs_url') ){
	function gefic_whmcs_url($type='all'){
        $info=[];
        $self = App::self();
		$info['root_dir'] = '/'.gefic_get_string_between(gefic_get_protected_property(gefic_get_protected_property(gefic_get_protected_property(gefic_get_protected_property($self, 'clientTemplate'), 'config'),'configFile'),'path'),'/','/templates/');
		$info['whmcs_url'] = App::getSystemUrl();
		$info['admin_path'] = gefic_get_protected_property($self, 'customadminpath');
        $info['admin_url'] = $info['whmcs_url'].$info['admin_path'];
		if((string)$type===(string)'all'){
			return $info;
		}
        return $info[$type];
	}
}
if( !function_exists('gefic_get_embed') ){
	function gefic_get_embed($page_id,$referer,$module_version){
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
if(!function_exists('gefic_encrypt')){
	function gefic_encrypt($q) {
	    $encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
	    return openssl_encrypt($q, $encryptionMethod, $secretHash);
	}
}
if(!function_exists('gefic_decrypt')){
	function gefic_decrypt($q){
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
	    return openssl_decrypt($q, $encryptionMethod, $secretHash);
	}
}
if( !function_exists('gefic_get_version') ){
	function gefic_get_version($page_id,$referer,$module_version){
		$currentUser = new \WHMCS\Authentication\CurrentUser;
		$admin_ = json_decode(json_encode($currentUser->admin()),true);
		$admin = ['email'=>$admin_['email'],'firstname'=>$admin_['firstname'],'lastname'=>$admin_['lastname']];
		$query = 'https://gofas.net/br/updates/?software='.$page_id.'&referer='.$referer.'&version='.$module_version.'&email='.$admin['email'].'&firstname='.$admin['firstname'].'&lastname='.$admin['lastname'].gefic_sysinfo();
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
if(!function_exists('gefic_sysinfo')){
	function gefic_sysinfo(){
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
if(!function_exists('gefic_verify_module_updates')){
	function gefic_verify_module_updates($page_id,$referer,$module_version){
		foreach( Capsule::table('tblconfiguration')->where('setting','=','gefic_version')->get(['value','created_at','updated_at']) as $version_ ){
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
			$get_version = gefic_get_version($page_id,$referer,$module_version);
			$get_embed	 = gefic_get_embed($page_id,$referer,$module_version);
			
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and strtotime($updated_at) < strtotime("-1 day")){
			$get_version = gefic_get_version($page_id,$referer,$module_version);
			$get_embed	 = gefic_get_embed($page_id,$referer,$module_version);
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and (string)$module_version !== (string)$local_version){
			$get_version = gefic_get_version($page_id,$referer,$module_version);
			$get_embed	 = gefic_get_embed($page_id,$referer,$module_version);
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
			$embed		  = gefic_encrypt($get_embed['embed']);
			$created_at		= date("Y-m-d H:i:s");
			$updated_at		= date("Y-m-d H:i:s");

			try { Capsule::table('tblconfiguration')->insert(array(
				'setting' => 'gefic_version',
				'value' => json_encode([
					'local_version'=>$module_version,
					'last_version'=>$get_version['version'],
					'check'=>gefic_encrypt($get_embed['embed']),
					'admin'=>gefic_current_admin(),
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
				Capsule::table('tblconfiguration')->where('setting','gefic_version')->update([
					'value' => json_encode([
						'local_version'=>$module_version,
						'last_version'=>$available_version,
						'check'=>gefic_encrypt($get_embed['embed']),
						'admin'=>gefic_current_admin(),
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
				Capsule::table('tblconfiguration')->where('setting','gefic_version')->update([
					'value' => json_encode([
						'local_version'=>$module_version,
						'last_version'=>$available_version,
						'check'=>gefic_encrypt($get_embed['embed']),
						'admin'=>gefic_current_admin(),
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
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gefic_whmcs_url('admin_url').'/configgateways.php?manage=gofaseficartao&resetversion=gofaseficartao#m_gofaseficartao">verificar agora</a>.</p>';
		}
		if( $available_version_int > $module_version_int ){
			$message .= '<p style="font-size: 14px; color: red;"><i class="fas fa-exclamation-triangle"></i> Atualização disponível, verifique a <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">versão '.$available_version.'</a>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gefic_whmcs_url('admin_url').'/configgateways.php?manage=gofaseficartao&resetversion=gofaseficartao#m_gofaseficartao">verificar agora</a>.</p>';$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gefic_whmcs_url('admin_url').'/configgateways.php?manage=gofaseficartao&resetversion=gofaseficartao#m_gofaseficartao">verificar agora</a>.</p>';$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gefic_whmcs_url('admin_url').'/configgateways.php?manage=gofaseficartao&resetversion=gofaseficartao#m_gofaseficartao">verificar agora</a>.</p>';
		}
		if( $available_version_int < $module_version_int ){
			$message = '<p style="font-size: 14px; color: orange;"><i class="fas fa-exclamation-triangle"></i> Você está executando uma versão Beta desse módulo.<br>Baixar versão estável: <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">v'.$available_version.'</a>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.gefic_whmcs_url('admin_url').'/configgateways.php?manage=gofaseficartao&resetversion=gofaseficartao#m_gofaseficartao">verificar agora</a>.</p>';
		}
		return [
			'version'=>$version,
			'get_version'=>$get_version,
			'message' => $message,
			'check'=> $embed,
			'error' => $error,
		];
	}
}if(!function_exists('gefic_version')){
	function gefic_version($opt=1){
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'gefic_version') -> get( array( 'value','created_at') ) as $gefic_version_ ){
			$gefic_version				= $gefic_version_->value;
			$gefic_version_created_at	= $gefic_version_->created_at;
		}
		if($opt=1){ // local_version string
			$version = json_decode($gefic_version, true);
			return $version['local_version'];
		}
		if($opt=2){ // local_version integer
			$version = json_decode($gefic_version, true);
			return (int)preg_replace("/[^0-9]/", "", $version['local_version']);
		}
		if($opt=3){ // full
			return$gefic_version;
		}
	}
}
if(!function_exists('gefic_tbladmins')){
	function gefic_tbladmins(){
		foreach( Capsule::table('tbladmins') -> get() as $tbladmins_ ){
			$tbladmins[$tbladmins_->id] = $tbladmins_->id.' - '.$tbladmins_->firstname.' '.$tbladmins_->lastname.' ('.$tbladmins_->username.')';
		}
		return $tbladmins;
	}
}
if(!function_exists('gefic_tblticketdepartments')){
	function gefic_tblticketdepartments(){
		$tblticketdepartments[] = '';
		foreach( Capsule::table('tblticketdepartments') -> get() as $tblticketdepartments_ ){
			$tblticketdepartments_id			= $tblticketdepartments_->id;
			$tblticketdepartments_name			= $tblticketdepartments_->name;
			$tblticketdepartments[]				= $tblticketdepartments_id.' - '.$tblticketdepartments_name;
		}
		return $tblticketdepartments;
	}
}
if(!function_exists('gefic_verifyInstall')){
	function gefic_verifyInstall(){
		if( !Capsule::schema()->hasTable('gofaseficartao') ){
			try {
				Capsule::schema()->create('gofaseficartao', function($table){
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
if(!function_exists('gefic_current_admin')){
	function gefic_current_admin(){
		$currentUser = new \WHMCS\Authentication\CurrentUser;
		$admin = json_decode(json_encode($currentUser->admin()),true);
		return $admin;
	}
}
if(!function_exists('gefic_encrypt')){
	function gefic_encrypt($q) {
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
		return openssl_encrypt($q, $encryptionMethod, $secretHash);
	}
}
if(!function_exists('gefic_decrypt')){
	function gefic_decrypt($q){
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
		return openssl_decrypt($q, $encryptionMethod, $secretHash);
	}
}
if(!function_exists('gefic_get_protected_property')){
	function gefic_get_protected_property($object, $property){
	    $reflectedClass = new \ReflectionClass($object);
	    $reflection = $reflectedClass->getProperty($property);
	    $reflection->setAccessible(true);
	    return $reflection->getValue($object);
	}
}
if(!function_exists('gefic_reset_local_version')){
	function gefic_reset_local_version(){
        try{
	        Capsule::table('tblconfiguration')->where('setting','=','gefic_version')->delete();
	        return 'sucess';
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
}}
if(!function_exists('gefic_setup_admin')){
	function gefic_setup_admin($key=NULL){
	foreach( Capsule::table('tblconfiguration')->where('setting','=','gefic_version')->get(['value']) as $version_ ){
		$version		= json_decode($version_->value, true);
		if($key){
			$admin			= $version['admin'][$key];
		}
		else{
			$admin			= $version['admin'];
		}
	}
	return $admin;
}}