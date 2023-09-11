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
add_hook('ClientAreaPage', 1, function($vars) {
	if(stripos($_SERVER['REQUEST_URI'], 'process') and stripos($_SERVER['REQUEST_URI'], 'invoice')){
	    echo '<style>.alert.alert-info.text-center,div#lightbox{display: none;}</style>';
		echo '<style>
		
.loading {
  position: fixed;
  z-index: 999;
  height: 2em;
  width: 2em;
  overflow: show;
  margin: auto;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}

.loading:before {
  content: "";
  display: block;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
    background: radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0, .8));

  background: -webkit-radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0,.8));
}
.loading:not(:required) {
  font: 0/0 a;
  color: transparent;
  text-shadow: none;
  background-color: transparent;
  border: 0;
}

.loading:not(:required):after {
  content:"";
  display: block;
  font-size: 20px;
  width: 1em;
  height: 1em;
  margin-top: -0.5em;
  -webkit-animation: spinner 1500ms infinite linear;
  -moz-animation: spinner 1500ms infinite linear;
  -ms-animation: spinner 1500ms infinite linear;
  -o-animation: spinner 1500ms infinite linear;
  animation: spinner 1500ms infinite linear;
  border-radius: 0.5em;
  -webkit-box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
}
@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@-moz-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@-o-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}	
		</style>';
		echo '<div class="loading">Carregando&#8230;</div>';
	}
	
	return;
});
add_hook('ClientAreaPageViewInvoice', 1, function($vars){
	if($_REQUEST['geficerror']){
		echo '
		<div class="row w-100 mx-auto mb-3" style="max-width: 850px;margin: 15px 0px;">
			<div class="card w-100">
				<div class="card-title py-1 px-2 text-white font-weight-bold bg-danger" style="text-align: center;">
					Erro: '.$_REQUEST['geficerror'].'
				</div>
				<div class="card-text text-center mx-2 mb-3">
					'.Lang::trans('invoicepaymentfailedconfirmation').'
				</div>
			</div>
		</div>';
	}
});
add_hook('ClientAreaPageCreditCardCheckout', 1, function($vars){
	$params = getGatewayVariables('gofaseficartao');
	add_hook('ClientAreaFooterOutput', 1, function($vars){
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'geficwhmcsurl') -> get( array( 'value','created_at') ) as $geficwhmcsurl_ ){
			$geficwhmcsurl					= $geficwhmcsurl_->value;
			$geficwhmcsurl_created_at		= $geficwhmcsurl_->created_at;
		}
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/payment-token-efi.min.js"></script>';
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/ggnc_.js"></script>';
		
		
		require __DIR__.'/functions.php';
		$params_api = gefic_api_connect();
		$htmlOutput .= $params_api['javascript'];

		//$htmlOutput .= '<script type="text/javascript" src="'.$geficwhmcsurl.'/assets/js/jquery.min.js"></script>';

		$params = getGatewayVariables('gofaseficartao');
		$vars_ = json_decode(json_encode($vars));
		//echo '<pre style="height: 250px;">',print_r($vars_),'</pre>';
		if($params['minimunamountinstallments']){
			$minimunamountinstallments = (float)$params['minimunamountinstallments'];
		}
		elseif(!$params['minimunamountinstallments']){
			$minimunamountinstallments = (float)'100.00';
		}
		if($params['installments'] and ( (float)$minimunamountinstallments <= (float)$vars_->invoice->model->total) ){
		 
		 $htmlOutput .= '<input type="hidden" name="installment_" id="installment_" value="yes" />';
		 $htmlOutput .= '<script>sessionStorage.setItem("installment_", "yes");</script>';
		 $options_installments .= '<label class="col-sm-4 control-label">Parcelamento</label><div class="col-sm-6" style="margin-bottom: 15px;"><select id="installmentsSelect" name="installmentsSelect" style="max-width: 320px; width: 320px;" required="" class="form-control">';
		 $options_installments .= '<option value="1">1 x de R$ '.number_format( $vars_->invoice->model->total,	2, ',', '.').'</option>';
		 foreach (range(2, (int)$params['maxinstallments']) as $maxinstallments_){
				$maxinstallments__ = $maxinstallments_++;
				if($maxinstallments__>0){
					$options_installments .= '<option value="'.$maxinstallments__.'">'.$maxinstallments__.' x de R$ '.number_format( $vars_->invoice->model->total / (int)$maxinstallments__ ,	2, ',', '.').'</option>';
				}
			}
		$options_installments .= '</select></div>';
		 $htmlOutput .= "<script>
		 	if(document.getElementById('installment_').value == 'yes'){
				var options_installments = '".$options_installments."';	
				document.getElementById('btnSubmit').insertAdjacentHTML('beforebegin',options_installments);
			}
		 </script>";
		 $htmlOutput .= "<script>
		 	if(document.getElementById('installment_').value == 'yes'){
				var sel = document.getElementById('installmentsSelect');
				sel.addEventListener('change', function (){
							sessionStorage.setItem('installments_', sel.value);
					console.log(sel.value);
	 				 });
			}
		 </script>";
		}
		else {
			 $htmlOutput .= '<input type="hidden" name="installment_" id="installment_" value="no" />';
		}
		$htmlOutput .= '<script type="text/javascript" src="'.$vars['systemurl'].'modules/gateways/gofaseficartao/assets/js/ClientAreaPageCreditCardCheckout.js?v='.time().'"></script>';
		return $htmlOutput;
	});
	//echo '<pre style="height: 200px;">',print_r($vars),'</pre>';
	return array(
		'allowClientsToRemoveCards'=>false,
		//'templatefile'=>'../../modules/gateways/gofaseficartao/templates/invoice-payment',
	);
	
});
add_hook('ClientAreaPageCart', 1, function($vars){
	$params = getGatewayVariables('gofaseficartao');
	if( stripos($_SERVER['REQUEST_URI'], 'cart.php?a=checkout')){
	add_hook('ClientAreaFooterOutput', 1, function($vars){
		$params = getGatewayVariables('gofaseficartao');
		$vars_ = json_decode(json_encode($vars));
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'geficwhmcsurl') -> get( array( 'value','created_at') ) as $geficwhmcsurl_ ){
			$geficwhmcsurl					= $geficwhmcsurl_->value;
			$geficwhmcsurl_created_at		= $geficwhmcsurl_->created_at;
		}
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/payment-token-efi.min.js"></script>';
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/ggnc_.js"></script>';
		
		
		require __DIR__.'/functions.php';
		$params_api = gefic_api_connect();
		$htmlOutput .= $params_api['javascript'];

		if($params['minimunamountinstallments']){
			$minimunamountinstallments = (float)$params['minimunamountinstallments'];
		}
		elseif(!$params['minimunamountinstallments']){
			$minimunamountinstallments = (float)'100.00';
		}
		if($params['installments'] and ( (float)$minimunamountinstallments <= (float)$vars_->rawtotal) ){
		 $htmlOutput .= '<input type="hidden" name="installment_" id="installment_" value="yes" />';
		 $htmlOutput .= '<script>sessionStorage.setItem("installment_", "yes");</script>';
		 $options_installments .= '<div class=""style="margin: 15px 0px 15px 0px;text-align: left;padding-left: 5px;"><label style="margin: 5px 30px 0px 0px;font-size: 100%;">Parcelamento</label><select id="installmentsSelect" name="installmentsSelect"class="field" required="" style="max-width: 680px;">';
		 $options_installments .= '<option value="1">1 x de R$ '.number_format( $vars_->rawtotal,	2, ',', '.').'</option>';
		 foreach (range(2, (int)$params['maxinstallments']) as $maxinstallments_){
					$maxinstallments__ = $maxinstallments_++;
				$options_installments .= '<option value="'.$maxinstallments__.'">'.$maxinstallments__.' x de R$ '.number_format( $vars_->rawtotal / (int)$maxinstallments__ ,	2, ',', '.').'</option>';
		}
		$options_installments .= '</select></div>';
		 $htmlOutput .= "<script>
		 	if(document.getElementById('installment_').value == 'yes'){
				var options_installments = '".$options_installments."';	
				document.getElementById('newCardInfo').insertAdjacentHTML('beforebegin',options_installments);
			}
		 </script>";
		 $htmlOutput .= "<script>
		 	if(document.getElementById('installment_').value == 'yes'){
				var sel = document.getElementById('installmentsSelect');
				sel.addEventListener('change', function (){
							sessionStorage.setItem('installments_', sel.value);
					console.log(sel.value);
	 				 });
			}
		 </script>";
		}
		else {
			 $htmlOutput .= '<input type="hidden" name="installment_" id="installment_" value="no" />';
		}
		$htmlOutput .= '<script type="text/javascript" src="'.$vars['systemurl'].'modules/gateways/gofaseficartao/assets/js/ClientAreaPageCart.js?v='.time().'"></script>';
		return $htmlOutput;
	});
	 }
	return array(
		'allowClientsToRemoveCards'=>true,
	);
 
});
add_hook('ClientAreaPaymentMethods', 1, function($vars){
	return array(
		'allowCreditCard'=>false,
	);
});