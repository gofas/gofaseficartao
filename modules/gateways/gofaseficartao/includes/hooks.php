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
		$params = getGatewayVariables('gofaseficartao');
		require_once __DIR__.'/functions.php';
		$params_api = gefic_api_connect();
		$vars_ = json_decode(json_encode($vars));
		//echo '<pre>',print_r($vars_),'</pre>';
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'geficwhmcsurl') -> get( array( 'value','created_at') ) as $geficwhmcsurl_ ){
			$geficwhmcsurl					= $geficwhmcsurl_->value;
			$geficwhmcsurl_created_at		= $geficwhmcsurl_->created_at;
		}
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
			$options_installments .= '<label style="float:left;" class="col-sm-4 control-label">Parcelamento</label><div class="col-sm-6" style="margin-bottom: 20px; float:left;">';
			$options_installments .= '<select id="installmentsSelect" name="installmentsSelect" style="max-width: 320px; width: 320px;" required="" class="form-control">';
			$options_installments .= '<option value=1>Digite o cartão para ver as opções</option>'; 
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
			 $htmlOutput .= '<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", gefic_on_load);
		function gefic_on_load(){
			var cardType = "'.strtolower($vars_->existingCardType).'";
			var total = '.(int)($vars_->invoice->model->total*100).';
			var inputCardCvv = document.getElementById("inputCardCvv").value;
			console.log("cardType: "+cardType);
			console.log("total: "+total);
			
			if (cardType !== "undefined") {
				// Obtém opções de parcelamento
				try {
					EfiJs.CreditCard
						.setAccount("'.$params['identifier'].'")
						.setEnvironment("'.$params_api['environment'].'") // "production" or "sandbox"
						.setBrand(cardType)
						.setTotal(total)
						.getInstallments()
						.then(installments => {
							console.log("Parcelas", installments);
							let opcoes = "<option value=1>1 x de R$'.number_format($vars_->invoice->model->total,  2, ',', '.').' sem juros</option>";
        	    			for (let index = 1; index < installments.installments.length; index++) {
        	    			    opcoes += `<option value="${installments.installments[index].installment}">${installments.installments[index].installment} x de R$${installments.installments[index].currency} ${installments.installments[index].has_interest === false ? "sem juros" : ""}</option>`;
        	    			}
        	    			document.getElementById("installmentsSelect").innerHTML = opcoes;
							document.getElementById("btnSubmit").disabled = false;
							sessionStorage.removeItem("paymentToken_");
							document.getElementById("btnSubmit").disabled = false;

						}).catch(err => {
							console.log("Código: ", err.code);
							console.log("Nome: ", err.error);
							console.log("Mensagem: ", err.error_description);
						});
				}
				catch (error) {
					console.log("Código: ", error.code);
					console.log("Nome: ", error.error);
					console.log("Mensagem: ", error.error_description);
				}
			}
		};
		</script>';
		}
		else {
			 $htmlOutput .= '<input type="hidden" name="installment_" id="installment_" value="no" />';
		}
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/payment-token-efi.min.js"></script>';
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/ggnc.js"></script>';
		
		
		$htmlOutput .= '<script type="text/javascript">
		document.getElementById("inputCardNumber").addEventListener("keyup", gefic_cardNumber);
		document.getElementById("inputCardCvv").addEventListener("keyup", gefic_cardNumber);
		document.getElementById("inputCardExpiry").addEventListener("keyup", gefic_cardNumber);
		function gefic_cardNumber(){
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
						console.log("cardNumber:"+cardNumber);
						EfiJs.CreditCard
							.setCardNumber(cardNumber)
							.verifyCardBrand()
							.then(brand => {
								console.log("Bandeira: ", brand);
								if (brand !== "undefined") {
									// Gerar o payment_token com a bandeira identificada
										try {
											document.getElementById("btnSubmit").disabled = true;
											EfiJs.CreditCard
												.setAccount("'.$params['identifier'].'")
												.setEnvironment("'.$params_api['environment'].'") // "production" or "sandbox"
												.setCreditCardData({
													brand: brand,
													number: cardNumber,
													cvv: CardCvv,
													expirationMonth: mes_vencimento,
													expirationYear: ano_vencimento,
													reuse: true
												})
												.getPaymentToken()
												.then(data => {
													const payment_token = data.payment_token;
													const card_mask = data.card_mask;
													console.log("payment_token", payment_token);
													console.log("card_mask", card_mask);
													
													sessionStorage.setItem("paymentToken_",payment_token);
													document.getElementById("btnSubmit").disabled = false;

												}).catch(err => {
													console.log("Erro "+err.code+": "+ err.error+" "+err.error_description);
												});
										} catch (error) {
											alert("Erro "+error.code+": "+ error.error+" "+error.error_description);
										}
									// Obtém opções de parcelamento
									try {
										EfiJs.CreditCard
											.setAccount("'.$params['identifier'].'")
											.setEnvironment("'.$params_api['environment'].'") // "production" or "sandbox"
											.setBrand(brand)
											.setTotal('.(int)($vars_->invoice->model->total*100).')
											.getInstallments()
											.then(installments => {
												console.log("Parcelas", installments);
												let opcoes = "<option value=1>1 x de R$'.number_format($vars_->invoice->model->total,  2, ',', '.').' sem juros</option>";
                	            				for (let index = 1; index < installments.installments.length; index++) {
                	            				    opcoes += `<option value="${installments.installments[index].installment}">${installments.installments[index].installment} x de R$${installments.installments[index].currency} ${installments.installments[index].has_interest === false ? "sem juros" : ""}</option>`;
                	            				}
                	            				document.getElementById("installmentsSelect").innerHTML = opcoes;
											}).catch(err => {
												console.log("Erro "+err.code+": "+ err.error+" "+err.error_description);
											});
									} catch (error) {
										alert("Erro "+error.code+": "+ error.error+" "+error.error_description);
									}
								}
							}).catch(err => {
								console.log("Erro "+err.code+": "+ err.error+" "+err.error_description);
							});
					}
					catch (error) {
						console.log("Erro "+error.code+": "+ error.error+" "+error.error_description);
					}
				}
			}
		}
		</script>';
		
		$htmlOutput .= '<script type="text/javascript" src="'.$vars['systemurl'].'modules/gateways/gofaseficartao/assets/js/ClientAreaPageCreditCardCheckout.js?v='.time().'"></script>';
		return $htmlOutput;
	});
	return array(
		'allowClientsToRemoveCards'=>false,
	);
});
add_hook('ClientAreaPageCart', 1, function($vars){
	$params = getGatewayVariables('gofaseficartao');
	add_hook('ClientAreaFooterOutput', 1, function($vars){
		$params = getGatewayVariables('gofaseficartao');
		require_once __DIR__.'/functions.php';
		$params_api = gefic_api_connect();
		$vars_ = json_decode(json_encode($vars));
		//echo '<pre>',print_r($vars_),'</pre>';
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'geficwhmcsurl') -> get( array( 'value','created_at') ) as $geficwhmcsurl_ ){
			$geficwhmcsurl					= $geficwhmcsurl_->value;
			$geficwhmcsurl_created_at		= $geficwhmcsurl_->created_at;
		}
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/payment-token-efi.min.js"></script>';
		$htmlOutput .= '<script type="module" src="'.$geficwhmcsurl.'/modules/gateways/gofaseficartao/assets/js/ggnc.js"></script>';
		if(!empty($vars_->clientsdetails->cctype)){
			$htmlOutput .= '<script type="text/javascript">
				document.addEventListener("DOMContentLoaded", gefic_on_load);
				window.onload = gefic_on_load();
				function gefic_on_load(){
					document.getElementById("btnCompleteOrder").disabled = true;
					var cardType = "'.strtolower($vars_->clientsdetails->cctype).'";
					var total = '.(int)($vars_->rawtotal*100).';
					var inputCardCVV2 = document.getElementById("inputCardCVV2").value;
					console.log("cardType: "+cardType);
					console.log("total: "+total);
					if (cardType !== "undefined") {
						// Obtém opções de parcelamento
						try {
							EfiJs.CreditCard
								.setAccount("'.$params['identifier'].'")
								.setEnvironment("'.$params_api['environment'].'") // "production" or "sandbox"
								.setBrand(cardType)
								.setTotal(total)
								.getInstallments()
								.then(installments => {
									console.log("Parcelas", installments);
									let opcoes = "<option value=1>1 x de R$'.number_format($vars_->rawtotal,  2, ',', '.').' sem juros</option>";
        			    			for (let index = 1; index < installments.installments.length; index++) {
        			    			    opcoes += `<option value="${installments.installments[index].installment}">${installments.installments[index].installment} x de R$${installments.installments[index].currency} ${installments.installments[index].has_interest === false ? "sem juros" : ""}</option>`;
        			    			}
        			    			document.getElementById("installmentsSelect").innerHTML = opcoes;
									document.getElementById("btnCompleteOrder").disabled = false;
									sessionStorage.removeItem("paymentToken_");
									document.getElementById("btnCompleteOrder").disabled = false;
								}).catch(err => {
									console.log("Erro "+err.code+": "+ err.error+" "+err.error_description);
								});
						}
						catch (error) {
							console.log("Erro "+error.code+": "+ error.error+" "+error.error_description);
						}
					}
				};
			</script>';
		}

			////
		$htmlOutput .= '<script type="text/javascript">
			document.getElementById("inputCardNumber").addEventListener("keyup", gefic_cardNumber);
			document.getElementById("inputCardCVV").addEventListener("keyup", gefic_cardNumber);
			document.getElementById("inputCardCVV2").addEventListener("keyup", gefic_cardNumber);
			document.getElementById("inputCardExpiry").addEventListener("keyup", gefic_cardNumber);
			function gefic_cardNumber(){
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
							console.log("cardNumber:"+cardNumber);
							EfiJs.CreditCard
								.setCardNumber(cardNumber)
								.verifyCardBrand()
								.then(brand => {
									console.log("Bandeira: ", brand);
									if (brand !== "undefined") {
										// Gerar o payment_token com a bandeira identificada
											try {
												EfiJs.CreditCard
													.setAccount("'.$params['identifier'].'")
													.setEnvironment("'.$params_api['environment'].'") // "production" or "sandbox"
													.setCreditCardData({
														brand: brand,
														number: cardNumber,
														cvv: CardCvv,
														expirationMonth: mes_vencimento,
														expirationYear: ano_vencimento,
														reuse: true
													})
													.getPaymentToken()
													.then(data => {
														const payment_token = data.payment_token;
														const card_mask = data.card_mask;
														console.log("payment_token", payment_token);
														console.log("card_mask", card_mask);

														sessionStorage.setItem("paymentToken_",payment_token);
														document.getElementById("btnCompleteOrder").disabled = false;
														var input_payment_token = "<input type=hidden name=paymentToken id=paymentToken value="+payment_token+">";
														document.getElementById("frmPayment").insertAdjacentHTML("afterbegin",input_payment_token);
													}).catch(err => {
														console.log("Erro "+err.code+": "+ err.error+" "+err.error_description);
													});
											} catch (error) {
												alert("Erro "+error.code+": "+ error.error+" "+error.error_description);
											}
										// Obtém opções de parcelamento
										if(payment_token !== "undefined"){
											try {
												EfiJs.CreditCard
													.setAccount("'.$params['identifier'].'")
													.setEnvironment("'.$params_api['environment'].'") // "production" or "sandbox"
													.setBrand(brand)
													.setTotal('.(int)($vars_->rawtotal*100).')
													.getInstallments()
													.then(installments => {
														console.log("Parcelas", installments);
														let opcoes = "<option value=1>1 x de R$'.number_format($vars_->rawtotal,  2, ',', '.').' sem juros</option>";
        	        	            					for (let index = 1; index < installments.installments.length; index++) {
        	        	            					    opcoes += `<option value="${installments.installments[index].installment}">${installments.installments[index].installment} x de R$${installments.installments[index].currency} ${installments.installments[index].has_interest === false ? "sem juros" : ""}</option>`;
        	        	            					}
        	        	            					document.getElementById("installmentsSelect").innerHTML = opcoes;
													}).catch(err => {
														console.log("Erro "+err.code+": "+ err.error+" "+err.error_description);
													});
											}
											catch (error) {
												alert("Erro "+error.code+": "+ error.error+" "+error.error_description);
											}
										}
									}
								}).catch(err => {
									//alert("Erro "+err.code+": "+ err.error+" "+err.error_description);
								});
						}
						catch (error) {
							//alert("Erro "+error.code+": "+ error.error+" "+error.error_description);
						}
					}
				}
			}
		</script>';
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
		$options_installments .= '<option value=1>Digite o cartão para ver as opções</option>'; 
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
		$htmlOutput .= '<script type="text/javascript" src="'.$vars['systemurl'].'modules/gateways/gofaseficartao/assets/js/ClientAreaPageCreditCardCheckout.js?v='.time().'"></script>';
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
    if ($vars['paymentmethod'] == 'gofaseficartao') {
		//$htmlOutput .= '<style>#cardcvv, label[for=cardcvv] {display: none;}</style>';
    }
    return $htmlOutput;
});