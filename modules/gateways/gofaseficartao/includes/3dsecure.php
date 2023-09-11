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
function gofaseficartao_3dsecure($params){
	define('CLIENTAREA', true);
	require __DIR__.'/functions.php';
	foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'geficwhmcsurl') -> get( array( 'value','created_at') ) as $geficwhmcsurl_ ){
		$geficwhmcsurl					= $geficwhmcsurl_->value;
		$geficwhmcsurl_created_at		= $geficwhmcsurl_->created_at;
	}
    $url = $geficwhmcsurl.'/modules/gateways/gofaseficartao/includes/iframe.php';
	if( $params['amount'] >= $params['minimunamount']){
		$Params = json_decode( json_encode($params), true);
		$params_api = gefic_api_connect();
		$pay_method_id = $Params['payMethod']['payment']['pay_method_id'];
		$invoice_duedate					= $params['duedate'];
		if( (int)date('Ymd', strtotime($params['duedate'])) >= (int)date('Ymd') ){
			$billet_duedate			= date('Y-m-d', strtotime($invoice_duedate));
		}
		elseif( $invoice_duedate < date('Y-m-d') and !$days_for_due ){
			$billet_duedate			= date('Y-m-d', strtotime('+1 day'));	
		}
		$customer = gefic_customer($params['clientdetails']['id']);
		$postfields = array(
				'userid'=>$params['clientdetails']['id'],
				'invoiceid'=>$params['invoiceid'],
				'amount'=>$params['amount'],
				'payerName'=>$customer['name'],
				'payerCpfCnpj' => $customer['document'],
				'address'=>preg_replace('/[0-9]+/i', '', $params['clientdetails']['address1']),
				'addressNumber'=> $address_number, //preg_replace('/[^0-9]/', '', $params['clientdetails']['address1']),
				'addressComplement'=> $address_complement,
				'neighborhood'=> $params['clientdetails']['address2'],
				'city'=>$params['clientdetails']['city'],
				'state'=>$params['clientdetails']['state'],
				'postcode'=>$params['clientdetails']['postcode'],
				'phonenumber'=>$params['clientdetails']['phonenumber'],
				'email'=>$params['clientdetails']['email'],
				'cclastfour'=>$params['clientdetails']['cclastfour'],
				'cardissuenum'=>$params['cardissuenum'],
				'cardnum'=>$params['cardnum'],
				'expiresAt'=> '20'.substr($params['cardexp'], 2, 2)."-".substr($params["cardexp"], 0, 2),
				'cardexp'=>$params['cardexp'],
				'cccvv'=>$params['cccvv'],
				'cardtype'=>$params['cardtype'],
				'pay_method_id' => $pay_method_id,
				//'credit_card_id'=>$credit_card_id,
			);
			$htmlOutput = '<form method="post" action="' . $url . '">';
			foreach ($postfields as $k => $v){
        		$htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    		}
			
			//$htmlOutput .= '<input type="hidden" name="cardHash" id="cardHash" value="" />';			
			$htmlOutput .= '<input type="hidden" name="identificadorConta" id="identificadorConta" value="'.$params['identifier'].'" />';
			
			$htmlOutput .= '<input type="hidden" name="valorTotal" id="valorTotal" value="'.(int)((int)preg_replace("/[^0-9]/","",$params['amount'])).'" />';
						
			$htmlOutput .= '<input type="hidden" name="storeCard" id="storeCard" value="yes" />';
			$htmlOutput .= '<input type="hidden" name="paymentToken" id="paymentToken" value="" />';
			$htmlOutput .= '<input type="hidden" name="installmentsnum" id="installmentsnum" value="1" />';
			$htmlOutput .= '<input type="hidden" name="mascaraCartao" id="mascaraCartao" value="" />';
			
			$htmlOutput .= '<input type="hidden" name="error" id="error" value="" />';

			$htmlOutput .= '<input type="hidden" name="numeroCartao" id="numeroCartao" value="'.$params['cardnum'].'" />';
			
			$htmlOutput .= '<input type="hidden" name="ccexp" id="ccexp" value="'.$params["cardexp"].'" />';
			$htmlOutput .= '<input type="hidden" name="mesVencimento" id="mesVencimento" value="'.substr($params["cardexp"], 0, 2).'" />';
			$htmlOutput .= '<input type="hidden" name="anoVencimento" id="anoVencimento" value="20'.substr($params['cardexp'], 2, 2).'" />';
			
			$htmlOutput .= '<input type="hidden" name="cvv" id="cvv" value="'.$params['cccvv'].'" />';
			$htmlOutput .= '<input type="hidden" name="bandeira" id="bandeira" value="'.$params['cardtype'].'" />';
			if($params_api['api_mode'] === 'live'){
				$environment = 'production';
			}
			else{
				$environment = $params_api['api_mode'];
			}
			$htmlOutput .= '<input type="hidden" name="environment" id="environment" value="'.$environment.'" />';
			
			
    		$htmlOutput .= '</form>';
			$htmlOutput .= $params_api['javascript'];
			$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/payment-token-efi.min.js"></script>';
			$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/ggnc_.js"></script>';

			if($params['sandbox']){
				$environment = 'false';
			}
			elseif(!$params['sandbox']){
				$environment = 'true';
			}
			$htmlOutput .= '<script type="text/javascript">
				document.getElementById("storeCard").value = sessionStorage.getItem("nostore");
				if(sessionStorage.getItem("installments_") > 1 ){
					document.getElementById("installmentsnum").value = sessionStorage.getItem("installments_");
				}
		</script>';
    		return $htmlOutput;
	}
	elseif( $params['amount'] < $params['minimunamount']){
		$error .= 'O valor mínimo para utilizar esse método de pagamento é '.number_format( $params['minimunamount'] ,  2, ',', '.').'.';
		$error .= '<br><a target="_top" style="color: #a94442;" href="'.$geficwhmcsurl.'/viewinvoice.php?id='.$params['invoiceid'].'" >Clique aqui e selecione outro método de pagamento</a>.';
		$invoice_page =json_encode($geficwhmcsurl.'/viewinvoice.php?id='.$_POST['invoiceid'].'&paymentfailed=true');
		$error .= '<script>
		function gefic_redir_to_invoice(){
			window.top.location.href='.$invoice_page.'
		}
		</script>';
		$htmlOutput = '<form method="post" action="' . $url . '">';
		$htmlOutput .= '<input type="hidden" name="error" id="error" value="'.base64_encode($error).'" />';
    	$htmlOutput .= '</form>';
		return $htmlOutput;
	}	
}