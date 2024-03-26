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
	if($_REQUEST['gicerror']){
		echo '
		<div class="row w-100 mx-auto mb-3" style="max-width: 850px;margin: 15px auto;">
			<div class="card w-100">
				<div class="card-title py-1 px-2 text-white font-weight-bold bg-danger" style="text-align: center;">
					Erro: '.$_REQUEST['gicerror'].'
				</div>
				<div class="card-text text-center mx-2 mb-3">
					'.Lang::trans('invoicepaymentfailedconfirmation').'
				</div>
			</div>
		</div>';
	}
});
add_hook('ClientAreaPageCreditCardCheckout', 1, function($vars){ // Página de pagamento de faturas avulças
	$params = getGatewayVariables('gofasiugucartao');
	add_hook('ClientAreaFooterOutput', 1, function($vars){
		$params = getGatewayVariables('gofasiugucartao');
		require_once __DIR__.'/functions.php';
		$params_api = gic_api_connect();
		$vars_ = json_decode(json_encode($vars));
		$customer = gic_customer($vars_->clientsdetails->userid);
		//echo '<pre>',print_r($customer),'</pre>';
		$htmlOutput .= $params_api['javascript'];
		if($params['minimunamountinstallments']){
			$minimunamountinstallments = (float)$params['minimunamountinstallments'];
		}
		elseif(!$params['minimunamountinstallments']){
			$minimunamountinstallments = (float)'100.00';
		}
		if($params['installments'] and ( (float)$minimunamountinstallments <= (float)$vars_->invoice->model->total) ){
			$htmlOutput .= '<input type="hidden" name="installment_" id="installment_" value="yes" />';
			$htmlOutput .= '<script>sessionStorage.setItem("installment_", "yes");</script>';
			$options_installments .= '<label style="float: left;" class="col-sm-4 control-label">Parcelamento</label><div class="col-sm-6" style="margin-bottom: 15px; float: left;"><select id="installmentsSelect" name="installmentsSelect" style="max-width: 320px; width: 320px;" required="" class="form-control">';
		 	$options_installments .= '<option value="1">1 x de R$ '.number_format( $vars_->invoice->model->total,	2, ',', '.').'</option>';
		 	foreach (range(2, (int)$params['maxinstallments']) as $maxinstallments_){
				$maxinstallments__ = $maxinstallments_++;
				$options_installments .= '<option value="'.$maxinstallments__.'">'.$maxinstallments__.' x de R$ '.number_format( $vars_->invoice->model->total / (int)$maxinstallments__ ,	2, ',', '.').'</option>';
			}
			$options_installments .= '</select></div>';
		
			$htmlOutput .= "<script>
			 	if(document.getElementById('installment_').value == 'yes'){
					var options_installments = '".$options_installments."';	
					document.getElementById('btnSubmitContainer').insertAdjacentHTML('beforebegin',options_installments);
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
		if($params['sandbox']){
			$setTestMode = 'true';
		}
		if(!$params['sandbox']){
			$setTestMode = 'false';
		}
		$htmlOutput .= '<script type="text/javascript" src="https://js.iugu.com/v2"></script>';
		$htmlOutput .= '<script type="text/javascript">
			Iugu.setAccountID("'.$params['account_id'].'");
			Iugu.setTestMode('.$setTestMode.');
		</script>';
		$htmlOutput .= '<script type="text/javascript">
		document.getElementById("inputCardNumber").addEventListener("keyup", gic_cardNumber);
		document.getElementById("inputCardCvv").addEventListener("keyup", gic_cardNumber);
		document.getElementById("inputCardExpiry").addEventListener("keyup", gic_cardNumber);
		function gic_cardNumber(){
			var cardNumber = document.getElementById("inputCardNumber").value;
			var CardCvv = document.getElementById("inputCardCvv").value;
			var CardExpiry = document.getElementById("inputCardExpiry").value.replace(/\D/g,"");
			var mes_vencimento = (CardExpiry.substring(0,2));
			if( CardExpiry.length == 4 ){
			    var ano_vencimento = "20"+CardExpiry.slice(-2);
			}
			else {
			    var ano_vencimento = CardExpiry.slice(-4);
			}
			if(cardNumber.length>8 && CardCvv.length>2){
				if(CardExpiry.length>3){
					try {
						document.getElementById("btnSubmit").disabled = true;
						document.getElementById("btnSubmit").innerHTML = "Aguarde um momento...";
						console.log("cardNumber:"+cardNumber);
						
						// Identifica bandeira do cartão
						var cardBrand = Iugu.utils.getBrandByCreditCardNumber(cardNumber);
						console.log("cardBrand: "+cardBrand);
						if (cardBrand !== "undefined") {
							//Gera o payment_token com a bandeira identificada
							try {
								document.getElementById("btnSubmit").disabled = true;
								document.getElementById("btnSubmit").innerHTML = "Aguarde um momento...";
								cc = Iugu.CreditCard(cardNumber, mes_vencimento, ano_vencimento,"'.$customer['names']['firstname'].'", "'.$customer['names']['lastname'].'", CardCvv);
								var createPaymentToken = Iugu.createPaymentToken(cc, function(response) {
									if (response.errors) {
										alert("Erro ao verificar o cartão");
									} else {
										sessionStorage.setItem("paymentToken_",response.id);
										document.getElementById("btnSubmit").disabled = false;
										document.getElementById("btnSubmit").innerHTML = "Enviar Pagamento";
										console.log("Token criado:" + response.id);
									}
									console.log("createPaymentToken: "+JSON.stringify(response));
								});
							} catch (error) {
								alert("Erro "+JSON.stringify(error));
							}
							// Obtém opções de parcelamento
						}
							
					}
					catch (error) {
						console.log("Erro ('.__LINE__.') "+error.code+": "+ error.error+" "+error.error_description);
					}
				}
			}
		}
		</script>';
		$htmlOutput .= '<script type="text/javascript" src="'.$vars['systemurl'].'modules/gateways/gofasiugucartao/assets/js/ClientAreaPageCreditCardCheckout.js?v='.time().'"></script>';
		return $htmlOutput;
	});
	return array(
		'allowClientsToRemoveCards'=>false,
	);
});
add_hook('ClientAreaPageCart', 1, function($vars){ // Carrinho de compras
	$params = getGatewayVariables('gofasiugucartao');
	add_hook('ClientAreaFooterOutput', 1, function($vars){
		$params = getGatewayVariables('gofasiugucartao');
		require_once __DIR__.'/functions.php';
		$params_api = gic_api_connect();
		$vars_ = json_decode(json_encode($vars));
		//echo '<pre>',print_r($vars_),'</pre>';
		$customer = gic_customer($vars_->client->id);
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
			$options_installments .= '<label style="margin-top:10px;" class="col-sm-4 control-label">Parcelamento</label>';
			$options_installments .= '<div class="col-sm-6" style="margin: 0px 0px 20px 0px;max-width:100%;">';
			$options_installments .= '<select id="installmentsSelect" name="installmentsSelect" style="max-width: 320px; width: 320px;" required="" class="form-control">';
		 	$options_installments .= '<option value="1">1 x de R$ '.number_format($vars_->rawtotal,	2, ',', '.').'</option>';
		 	foreach (range(2, (int)$params['maxinstallments']) as $maxinstallments_){
				$maxinstallments__ = $maxinstallments_++;
				$options_installments .= '<option value="'.$maxinstallments__.'">'.$maxinstallments__.' x de R$ '.number_format($vars_->rawtotal/ (int)$maxinstallments__ ,	2, ',', '.').'</option>';
			}
			$options_installments .= '</select></div>';
		
			$htmlOutput .= "<script>
			 	if(document.getElementById('installment_').value == 'yes'){
					var options_installments = '".$options_installments."';	
					document.getElementById('creditCardInputFields').insertAdjacentHTML('beforeend',options_installments);
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
		if($params['sandbox']){
			$setTestMode = 'true';
		}
		if(!$params['sandbox']){
			$setTestMode = 'false';
		}
		$htmlOutput .= '<script type="text/javascript" src="https://js.iugu.com/v2"></script>';
		$htmlOutput .= '<script type="text/javascript">
			Iugu.setAccountID("'.$params['account_id'].'");
			Iugu.setTestMode('.$setTestMode.');
		</script>';
		
		$htmlOutput .= '<script type="text/javascript">
		document.getElementById("inputCardNumber").addEventListener("keyup", gic_cardNumber);
			document.getElementById("inputCardCVV").addEventListener("keyup", gic_cardNumber);
			document.getElementById("inputCardCVV2").addEventListener("keyup", gic_cardNumber);
			document.getElementById("inputCardExpiry").addEventListener("keyup", gic_cardNumber);
			function gic_cardNumber(){
				var cardNumber = document.getElementById("inputCardNumber").value;
				var CardCvv = document.getElementById("inputCardCVV").value;
				if(CardCvv == "undefined"){
					var CardCvv = document.getElementById("inputCardCVV2").value;
				}
				var CardExpiry = document.getElementById("inputCardExpiry").value.replace(/\D/g,"");
				var mes_vencimento = (CardExpiry.substring(0,2));
				if( CardExpiry.length == 4 ){
				    var ano_vencimento = "20"+CardExpiry.slice(-2);
				}
				else {
				    var ano_vencimento = CardExpiry.slice(-4);
				}
				if(cardNumber.length>8 && CardCvv.length>2){
					if(CardExpiry.length>3){
					try {
						document.getElementById("btnCompleteOrder").disabled = true;
						document.getElementById("btnCompleteOrder").innerHTML = "Aguarde um momento...";
						console.log("cardNumber:"+cardNumber);
						
						// Identifica bandeira do cartão
						var cardBrand = Iugu.utils.getBrandByCreditCardNumber(cardNumber);
						console.log("cardBrand: "+cardBrand);
						if (cardBrand !== "undefined") {
							//Gera o payment_token com a bandeira identificada
							try {
								document.getElementById("btnCompleteOrder").disabled = true;
								document.getElementById("btnCompleteOrder").innerHTML = "Aguarde um momento...";
								cc = Iugu.CreditCard(cardNumber, mes_vencimento, ano_vencimento,"'.$customer['names']['firstname'].'", "'.$customer['names']['lastname'].'", CardCvv);
								var createPaymentToken = Iugu.createPaymentToken(cc, function(response) {
									if (response.errors) {
										alert("Erro ao verificar o cartão");
									} else {
										sessionStorage.setItem("paymentToken_",response.id);
										document.getElementById("btnCompleteOrder").disabled = false;
										document.getElementById("btnCompleteOrder").innerHTML = "Enviar Pagamento";
										console.log("Token criado:" + response.id);
									}
									console.log("createPaymentToken: "+JSON.stringify(response));
								});
							} catch (error) {
								alert("Erro "+JSON.stringify(error));
							}
						}
							
					}
					catch (error) {
						console.log("Erro ('.__LINE__.') "+error.code+": "+ error.error+" "+error.error_description);
					}
				}
			}
		}
		</script>';
		$htmlOutput .= '<script type="text/javascript" src="'.$vars['systemurl'].'modules/gateways/gofasiugucartao/assets/js/ClientAreaPageCreditCardCheckout.js?v='.time().'"></script>';
		return $htmlOutput;
	});
	return array(
		'allowClientsToRemoveCards'=>false,
	);
});
add_hook('ClientAreaPaymentMethods', 1, function($vars){
	return array(
		'allowCreditCard'=>false,
	);
});
add_hook('AdminInvoicesControlsOutput', 1, function($vars) {
    $htmlOutput = '';
    if ($vars['paymentmethod'] == 'gofasiugucartao') {
		//$htmlOutput .= '<style>#cardcvv, label[for=cardcvv] {display: none;}</style>';
    }
    return $htmlOutput;
});