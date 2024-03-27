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
function gofasiugucartao_3dsecure($params){
	define('CLIENTAREA', true);
	require __DIR__.'/functions.php';
    $url = gic_whmcs_url('whmcs_url').'modules/gateways/gofasiugucartao/includes/iframe.php';
	if( $params['amount'] >= $params['minimunamount']){
		$Params = json_decode( json_encode($params), true);
		$params_api = gic_api_connect();
		$pay_method_id = $Params['payMethod']['payment']['pay_method_id'];
		$invoice_duedate = $params['duedate'];
		foreach( Capsule::table('gofasiugucartao') -> where('pay_method_id','=',$pay_method_id)->get(['payment_token']) as $saved_token_ ){
			$saved_token					= $saved_token_->payment_token;
		}
		$customer = gic_customer($params['clientdetails']['id']);
		$postfields = array(
				'userid'=>$params['clientdetails']['id'],
				'invoiceid'=>$params['invoiceid'],
				'amount'=>$params['amount'],
				'payerName'=>$customer['name'],
				'cclastfour'=>substr($params['cardnum'],-4),
				'cardexp'=>$params['cardexp'],
				'cardtype'=>$params['cardtype'],
				'pay_method_id' => $pay_method_id,
				'saved_token'=>$saved_token,
			);
			$htmlOutput = '<form id="payment-form" method="post" action="' . $url . '">';
			foreach ($postfields as $k => $v){
        		$htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    		}
			$htmlOutput .= '<input type="hidden" name="storeCard" id="storeCard" value="yes" />';
			$htmlOutput .= '<input type="hidden" name="paymentToken" id="paymentToken" value="'.$saved_token.'" />';
			$htmlOutput .= '<input type="hidden" name="installmentsnum" id="installmentsnum" value="1" />';
			$htmlOutput .= '<input type="hidden" name="identificadorConta" id="identificadorConta" value="'.$params['account_id'].'" />';
    		$htmlOutput .= '</form>';
			$htmlOutput .= $params_api['javascript'];
			$htmlOutput .= '<script type="module" src="<script type="text/javascript" src="https://js.iugu.com/v2"></script>';
			//$htmlOutput .= '<script type="module" src="'.gic_whmcs_url('whmcs_url').'modules/gateways/gofasiugucartao/assets/js/iugu.js"></script>';
			$htmlOutput .= '<script type="text/javascript">
			    document.getElementById("storeCard").value = sessionStorage.getItem("nostore");
				document.getElementById("paymentToken").value = sessionStorage.getItem("paymentToken_");
				if(sessionStorage.getItem("installments_") > 1 ){
					document.getElementById("installmentsnum").value = sessionStorage.getItem("installments_");
				}
		</script>';
    		return $htmlOutput;
	}
	elseif( $params['amount'] < $params['minimunamount']){
		$error .= 'O valor mínimo para utilizar esse método de pagamento é '.number_format( $params['minimunamount'] ,  2, ',', '.').'.';
		$error .= '<br><a target="_top" style="color: #a94442;" href="'.gic_whmcs_url('whmcs_url').'/viewinvoice.php?id='.$params['invoiceid'].'" >Clique aqui e selecione outro método de pagamento</a>.';
		$invoice_page =json_encode(gic_whmcs_url('whmcs_url').'/viewinvoice.php?id='.$_POST['invoiceid'].'&paymentfailed=true');
		$error .= '<script>
		function gic_redir_to_invoice(){
			window.top.location.href='.$invoice_page.'
		}
		</script>';
		$htmlOutput = '<form method="post" action="' . $url . '">';
		$htmlOutput .= '<input type="hidden" name="error" id="error" value="'.base64_encode($error).'" />';
    	$htmlOutput .= '</form>';
		return $htmlOutput;
	}	
}