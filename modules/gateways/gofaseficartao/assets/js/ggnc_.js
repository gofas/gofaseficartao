/**
 * Módulo Gofas Gerencinet Cartão para WHMCS
 * @author		Gofas Software
 * @see			https://gofas.net/?p=8423
 * @copyright	2016 - 2020 https://gofas.net
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		3.1.2
 */
/*
 *	Cartões aceitos:
 *
 *	visa // Bandeira Visa
 *	mastercard // Bandeira MasterCard
 *	jcb // Bandeira JCB
 *	diners // Bandeira Dinners
 *	amex // Bandeira AmericanExpress
 *	discover // Bandeira Discover
 *	elo // Bandeira Elo
 *	aura // Bandeira Aura
 *
**/

document.addEventListener('DOMContentLoaded', () => { 
	
	//document.getElementById("btnSubmit").disabled = true;
	//document.getElementById("btnSubmit").style.cursor = "not-allowed";

    $gn.ready(function (checkout){
		$("#installments_options").on('classChanged',function (){	
 
  		checkout.getInstallments(parseInt($("#valorTotal").val()),$("#bandeira").val(), function(error, response){
    	if(error){
      	// Trata o erro ocorrido
			//return error;
      		console.log(error);
    	}
		else {
      		// Insere o parcelamento no site
			//return response;
			//console.log(response);
			var options = '';
            for (i = 0; i < response.data.installments.length; i++){
				options += '<option value="' + response.data.installments[i].installment + '">' + response.data.installments[i].installment + 'x de R$' + response.data.installments[i].currency + '</option>';
			}
            $('#installments_options').html(options);
			document.getElementById("installments_options_pre").style.display = "none";
			document.getElementById("installments_options").style.display = "block";
    		}
  		});
	});
		
		$("#btnSubmit").click(function (){
						
			// Cartão
            var numero_cartao = $("#inputCardNumber").val();
            var codigo_seguranca = $("#inputCardCvv").val();
            var vencimento = $("#inputCardExpiry").val();
			
			// @rodrigogomes 26/05/2017
			var vencimento	= vencimento.replace( /[^0-9]/g, '');
			var mes_vencimento = ( vencimento.substring( 0, 2 ) );

			if( vencimento.length === 4 ){
			    var ano_vencimento = '20' + vencimento.slice( -2 );
				var cc_exp_y = vencimento.slice( -2 );
			}
			else {
			    var ano_vencimento = vencimento.slice( -4 );
				var cc_exp_y = vencimento.slice( -2 );
			}
			// fim da contribuição
			
			document.getElementById("cc_exp_m").value = mes_vencimento;
			document.getElementById("cc_exp_y").value = cc_exp_y;
			
			
			
			var cc_num_class = document.getElementById("numeroCartao").className; // Nome da classe do input numeroCartao
			
			if( cc_num_class.match(/visa/g) ){ card_type = "visa"; }
			else if( cc_num_class.match(/mastercard/g) ){ card_type = "mastercard";}
			else if( cc_num_class.match(/jcb/g) ){ card_type = "jcb"; }
			else if( cc_num_class.match(/diners/g) ){ card_type = "diners"; }
			else if( cc_num_class.match(/amex/g) ){ card_type = "amex"; }
			else if( cc_num_class.match(/discover/g) ){ card_type = "discover"; }
			else if( cc_num_class.match(/elo/g) ){ card_type = "elo"; }
			else if( cc_num_class.match(/aura/g) ){ card_type = "aura";}
			
			var bandeira = card_type;
			document.getElementById("bandeira").value = card_type;
			

			// Outras infos
			var system_url = $("#system_url").val();
			
			if( $("#debug").val() == "1" )  { // Debug
				console.log("Nº Cartão: " + numero_cartao );
				console.log("Vencimento: " + mes_vencimento + "/" + ano_vencimento);
				console.log("Código de Seguranca: " + codigo_seguranca);
				console.log("Bandeira: " + card_type );
			}

            var callback = function (error, response){
				
				document.getElementById("btnSubmit").disabled = true;
				document.getElementById("btnSubmit").style.cursor = "not-allowed";
				document.getElementById("lightbox").style.display = "block";
				document.getElementById("lightboxloading").style.display = "block";
				//document.getElementById("lightboxloading").innerHTML = "Processando o pagamento...";
				
                if(error){
                    
					  if( $("#debug").val() == "1" )  { // Debug
					  	console.log("Erro 1: " + error.code + ": " + error.error_description);
					  }
                      
					  document.getElementById("lightboxloading").style.display = "none";
					  document.getElementById("lightbox").style.display = "none";
					  document.getElementById("cc_prof_form_row").style.display = "none";
					  document.getElementById("numeroCartao").style.border = "1px solid #cccccc";
					  
					  if( error.code == 3500058 ){
						  
						 document.getElementById("simple_error").innerHTML = '<span id="error1">Verifique <br>o cartão</span>';
						 document.getElementById("numeroCartao").style.border = "1px solid #BF0000";
						 document.getElementById("btnSubmit").disabled = false;
						 document.getElementById("btnSubmit").style.cursor = "pointer";
						 document.getElementById("btnSubmit").style.filter = "grayscale(0)";
						 document.getElementById("btnSubmit").style.opacity = "1";
						
						$("#simple_error").fadeIn(700, function(){
        					window.setTimeout(function(){
            					$('#simple_error').fadeOut();
								//document.getElementById("numeroCartao").style.border = "1px solid #cccccc";
       						}, 5000);
    					});			
						 						
					  }
					  else if( error.code != 3500058 ){
						  document.getElementById("btnSubmit").innerHTML = '<span id="error">Verifique os dados e tente novamente.<br/>Se o erro persistir, <a target="_blank" href="'+system_url+'submitticket.php">contacte o suporte</a> informando o erro:<br/>' + error.error_description + '</span>';
						  document.getElementById("btnSubmit").disabled = false;
						  document.getElementById("btnSubmit").style.cursor = "pointer";
						  document.getElementById("btnSubmit").style.filter = "grayscale(0)";
						  document.getElementById("btnSubmit").style.opacity = "1";
						  document.getElementById("cc_prof_form_row").style.display = "block";
						 
					  }
					  
                } else {
					
					if( $("#debug").val() == "1" )  { // Debug
						console.log("Token: " + response.data.payment_token ); // Debug
					}
					
					document.getElementById("simple_error").style.display = "none";
					var postProfile = $("#postprofile").val();
					var installments_val = $("#installments").val();
					var is_recurring = $("#ggnc_subscribe").val();
					
					if( postProfile == ''){
						var post_data = {"payment_token": response.data.payment_token, "installments": installments_val, "is_recurring": is_recurring};
					}
					else if( postProfile == 'true'){
						var name = $("#name").val();
						
						var post_data = {
										"payment_token": response.data.payment_token,
										"name": name,
										"email": $("#email").val(),
										"telefone": $("#telefone").val(),
										"cpf": $("#cpf").val(),
										"cnpj": $("#cnpj").val(),
										"birthday": $("#birthday").val(),
										"endereco": $("#endereco").val(),
										"numero": $("#numero").val(),
										"bairro": $("#bairro").val(),
										"cep": $("#cep").val(),
										"cidade": $("#cidade").val(),
										"estado": $("#estado").val(),
										"brand": $("#bandeira").val(),
                						"cc": $("#numeroCartao").val(),
                						"cvv": $("#cvv").val(),
                						"exp_m":$("#cc_exp_m").val(),
										"exp_y":$("#cc_exp_y").val(),
                						"cc_st": $("#cc_store_w").val(),
										"is_recurring": is_recurring,
										"installments": installments_val,//document.getElementById("installments").value,
										"invoice_id":$("#invoice_id").val(),
										"discount": $("#discount").val(),
										"discount": $("#discount").val(),
										"package_amount":$("#package_amount").val(),
										"package_id":$("#package_id").val(),
										"billingInterval":$("#billingInterval").val(),
										"plan_name":$("#plan_name").val(),
										};					
						}
						//console.log(post_data);
						$.ajax({
                        	url: system_url + "modules/gateways/gofasgerencianetcartao/includes/execute.php",
                        	data: post_data,
                        	type: "post",
                        	dataType: "json",
                        	success: function (resposta){
                            if(resposta.code == 200 && resposta.data.subscription_id ){
								
							    document.getElementById("lightboxloading").style.display = "none";
								document.getElementById("lightbox").style.display = "none";
								document.getElementById("payment_section").innerHTML = '<p id="sucess">Assinatura criada com sucesso!<br>Aguarde a confirmação por email.</p>';
								 // Alinha a confirmação ao topo
								 if( postProfile == 'true'){
								 	$('html, body').animate({
    									scrollTop: $('html, body').offset().top
									}, 500);
								 }
								
								if( $("#debug").val() == "1" )  { // Debug
									str = JSON.stringify(resposta, null, 4);
									console.log("Resposta completa: " + str );
								}			
                            } 
							else if(resposta.code == 200 && !resposta.data.subscription_id ){
								 document.getElementById("lightboxloading").style.display = "none";
								 document.getElementById("lightbox").style.display = "none";
								 document.getElementById("payment_section").innerHTML = '<p id="sucess">Pagamento Realizado!<br>Aguarde a confirmação por email.</p>';
								 // Alinha a confirmação ao topo
								if( postProfile == 'true'){
								 	$('html, body').animate({
    									scrollTop: $('html, body').offset().top
									}, 500);
								 }
								 
								 if( $("#debug").val() == "1" )  { // Debug
									 str = JSON.stringify(resposta, null, 4);
								 	console.log("Resposta completa: " + str );
								 }
							}
							else {
								//alert("Ocorreu um erro - 2 - Mensagem: " + resposta.code)
								if( $("#debug").val() == "1" )  { // Debug
									 str = JSON.stringify(resposta, null, 4);
								 	console.log("Resposta completa: " + str );
									console.log("Erro 2: " + response.data.payment_token ); // Debug
								 }
								
								// !!! Erros do cadastro
								if(JSON.stringify(resposta.error_description.property).includes("name")){
								//if( resposta.responseText && resposta.responseText.debug.indexOf("Nome") >= 0){
									document.getElementById("name_error").innerHTML = '<span id="error1">Verifique <br>o Nome</span>';
						 			document.getElementById("name").style.border = "1px solid #BF0000";
									var error = 'Nome inválido';
									var animateToError = "#name_error";
									
									$("#name_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#name_error').fadeOut();
											//document.getElementById("name").style.border = "1px solid #cccccc";
       									}, 10000);
    								});	
								}
								if(JSON.stringify(resposta.error_description.property).includes("email")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("E-mail") >= 0){
									document.getElementById("email_error").innerHTML = '<span id="error1">Verifique <br>o E-mail</span>';
						 			document.getElementById("email").style.border = "1px solid #BF0000";
									var error = 'Email inválido';
									var animateToError = "#email_error";
									
									$("#email_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#email_error').fadeOut();
											//document.getElementById("email").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("phone")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("Telefone") >= 0){
									document.getElementById("telefone_error").innerHTML = '<span id="error1">Verifique <br>o Telefone</span>';
						 			document.getElementById("telefone").style.border = "1px solid #BF0000";
									var error = 'Telefone inválido';
									var animateToError = "#telefone_error";
									
									$("#telefone_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#telefone_error').fadeOut();
											//document.getElementById("telefone").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("cpf")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("CPF") >= 0){
									document.getElementById("cpf_error").innerHTML = '<span id="error1">Verifique <br>o CPF</span>';
						 			document.getElementById("cpf").style.border = "1px solid #BF0000";
									var error = 'CPF inválido';
									var animateToError = "#cpf_error";
									
									$("#cpf_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cpf_error').fadeOut();
											//document.getElementById("cpf").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("cnpj")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("CNPJ") >= 0){
									document.getElementById("cnpj_error").innerHTML = '<span id="error1">Verifique <br>o CNPJ</span>';
						 			document.getElementById("cnpj").style.border = "1px solid #BF0000";
									var error = 'CNPJ inválido';
									var animateToError = "#cnpj_error";
									
									$("#cnpj_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cnpj_error').fadeOut();
											//document.getElementById("cnpj").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("birth")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("Data de Nascimento") >= 0){
									document.getElementById("birth_error").innerHTML = '<span id="error1">Verifique a<br>Data de Nasc.</span>';
						 			document.getElementById("birthday").style.border = "1px solid #BF0000";
									var error = 'Data de nascimento inválida';
									var animateToError = "#birth_error";
									
									$("#birth_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#birth_error').fadeOut();
											//document.getElementById("birthday").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("street")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("Rua") >= 0){
									document.getElementById("street_error").innerHTML = '<span id="error1">Verifique o<br>Endereço</span>';
						 			document.getElementById("endereco").style.border = "1px solid #BF0000";
									var error = 'Rua - Logradouro inválido';
									var animateToError = "#street_error";
									
									$("#street_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#street_error').fadeOut();
											//document.getElementById("endereco").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(JSON.stringify(resposta.error_description.property).includes("billing_address") && JSON.stringify(resposta.error_description.property).includes("number")){
								//if( resposta.responseText && resposta.responseText.debug.indexOf("Número") >= 0){
									document.getElementById("number_error").innerHTML = '<span id="error1">Verifique<br>o Número</span>';
						 			document.getElementById("numero").style.border = "1px solid #BF0000";
									var error = 'Endereço - Número inválido';			
									var animateToError = "#number_error";
									
									$("#number_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#number_error').fadeOut();
											//document.getElementById("numero").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("neig")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("Bairro") >= 0){
									document.getElementById("bairro_error").innerHTML = '<span id="error1">Verifique<br>o Bairro</span>';
						 			document.getElementById("bairro").style.border = "1px solid #BF0000";
									var error = 'Bairro inválido';			
									var animateToError = "#bairro_error";
									
									$("#bairro_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#bairro_error').fadeOut();
											//document.getElementById("bairro").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("postcode")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("CEP") >= 0){
									document.getElementById("cep_error").innerHTML = '<span id="error1">Verifique<br>o CEP</span>';
						 			document.getElementById("cep").style.border = "1px solid #BF0000";
									var error = 'CEP inválido';			
									var animateToError = "#cep_error";
									
									$("#cep_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cep_error').fadeOut();
											//document.getElementById("cep").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("city")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("Cidade") >= 0){
									document.getElementById("cidade_error").innerHTML = '<span id="error1">Verifique<br>a Cidade</span>';
						 			document.getElementById("cidade").style.border = "1px solid #BF0000";
									var error = 'Cidade inválida';			
									var animateToError = "#cidade_error";
									
									$("#cidade_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cidade_error').fadeOut();
											//document.getElementById("cidade").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(JSON.stringify(resposta.error_description.property).includes("state")){
								//if(resposta.responseText && resposta.responseText.debug.indexOf("Estato") >= 0){
									document.getElementById("estado_error").innerHTML = '<span id="error1">Verifique<br>o Estato</span>';
						 			document.getElementById("estado").style.border = "1px solid #BF0000";
									var error = 'Estado inválido';			
									var animateToError = "#estado_error";
									
									$("#estado_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#estado_error').fadeOut();
											//document.getElementById("estado").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								if(!error){
									var error = JSON.stringify(resposta.error_description.message);
								}
								document.getElementById("postprofile").value = 'true';
								document.getElementById("lightboxloading").style.display = "none";
					  			document.getElementById("lightbox").style.display = "none";
								document.getElementById("btnSubmit").disabled = false;
								document.getElementById("btnSubmit").style.cursor = "pointer";
								document.getElementById("btnSubmit").style.filter = "grayscale(0)";
								document.getElementById("btnSubmit").style.opacity = "1";
								document.getElementById("cc_prof_form_row").style.display = "block";							
					  			document.getElementById("cc_prof_form_row_error").innerHTML = '<div id="error"><span id="error_exc">!</span><h4>'+ error +'</h4><p>Corrija abaixo o seu cadastro</p></div>';
								
								$('html, body').animate({scrollTop: $(animateToError).offset().top}, 500);
								
								//  </ Erros do cadastro
							
								if( $("#debug").val() == "1" )  { // Debug
									str = JSON.stringify(resposta, null, 4);
									console.log("Resposta completa: " + str );
									console.log("Erro 2.2: " + JSON.stringify(resposta.error_description.property) );
									if(JSON.stringify(resposta.error_description.property).includes("number")){
										console.log("Erro 2.2: number");
									}
								}
								
                            	}
                        	},
							// sucesso end
						
							error: function (resposta){
							
								if( $("#debug").val() == "1" )  {
									str = JSON.stringify(resposta);
									console.log( "Erro 3 - str: " + str ); // Debug
									console.log("Erro 3.1 - resposta.responseText : " + String(resposta) + resposta.responseText );
								}
							
								document.getElementById("postprofile").value = 'true';
								document.getElementById("lightboxloading").style.display = "none";
					  			document.getElementById("lightbox").style.display = "none";
								document.getElementById("btnSubmit").disabled = false;
								document.getElementById("btnSubmit").style.cursor = "pointer";
								document.getElementById("btnSubmit").style.filter = "grayscale(0)";
								document.getElementById("btnSubmit").style.opacity = "1";
								document.getElementById("cc_prof_form_row").style.display = "block";							
					  			document.getElementById("cc_prof_form_row_error").innerHTML = '<div id="error"><span id="error_exc">!</span><h4>'+ error +'</h4><p>Corrija abaixo o seu cadastro</p></div>';
								
								// Alinha o erro ao topo
								$('html, body').animate({
    								scrollTop: $("#cc_prof_form_row_error").offset().top
								}, 500);
								
								// < Erros do cadastro
								if(resposta.responseText && resposta.responseText.debug.indexOf("Nome") >= 0){
									document.getElementById("name_error").innerHTML = '<span id="error1">Verifique <br>o Nome</span>';
						 			document.getElementById("name").style.border = "1px solid #BF0000";
									
									$("#name_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#name_error').fadeOut();
											//document.getElementById("name").style.border = "1px solid #cccccc";
       									}, 10000);
    								});	
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("E-mail") >= 0){
									document.getElementById("email_error").innerHTML = '<span id="error1">Verifique <br>o E-mail</span>';
						 			document.getElementById("email").style.border = "1px solid #BF0000";
									
									$("#email_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#email_error').fadeOut();
											//document.getElementById("email").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Telefone") >= 0){
									document.getElementById("telefone_error").innerHTML = '<span id="error1">Verifique <br>o Telefone</span>';
						 			document.getElementById("telefone").style.border = "1px solid #BF0000";
									
									$("#telefone_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#telefone_error').fadeOut();
											//document.getElementById("telefone").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("CPF") >= 0){
									document.getElementById("cpf_error").innerHTML = '<span id="error1">Verifique <br>o CPF</span>';
						 			document.getElementById("cpf").style.border = "1px solid #BF0000";
									
									$("#cpf_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cpf_error').fadeOut();
											//document.getElementById("cpf").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("CNPJ") >= 0){
									document.getElementById("cnpj_error").innerHTML = '<span id="error1">Verifique <br>o CNPJ</span>';
						 			document.getElementById("cnpj").style.border = "1px solid #BF0000";
									
									$("#cnpj_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cnpj_error').fadeOut();
											//document.getElementById("cnpj").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Data de Nascimento") >= 0){
									document.getElementById("birth_error").innerHTML = '<span id="error1">Verifique a<br>Data de Nasc.</span>';
						 			document.getElementById("birthday").style.border = "1px solid #BF0000";
									
									$("#birth_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#birth_error').fadeOut();
											//document.getElementById("birthday").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Rua") >= 0){
									document.getElementById("street_error").innerHTML = '<span id="error1">Verifique o<br>Endereço</span>';
						 			document.getElementById("endereco").style.border = "1px solid #BF0000";
									
									$("#street_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#street_error').fadeOut();
											//document.getElementById("endereco").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Número") >= 0){
									document.getElementById("number_error").innerHTML = '<span id="error1">Verifique<br>o Número</span>';
						 			document.getElementById("numero").style.border = "1px solid #BF0000";
									
									$("#number_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#number_error').fadeOut();
											//document.getElementById("numero").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Bairro") >= 0){
									document.getElementById("bairro_error").innerHTML = '<span id="error1">Verifique<br>o Bairro</span>';
						 			document.getElementById("bairro").style.border = "1px solid #BF0000";
									
									$("#bairro_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#bairro_error').fadeOut();
											//document.getElementById("bairro").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("CEP") >= 0){
									document.getElementById("cep_error").innerHTML = '<span id="error1">Verifique<br>o CEP</span>';
						 			document.getElementById("cep").style.border = "1px solid #BF0000";
									
									$("#cep_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cep_error').fadeOut();
											//document.getElementById("cep").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Cidade") >= 0){
									document.getElementById("cidade_error").innerHTML = '<span id="error1">Verifique<br>a Cidade</span>';
						 			document.getElementById("cidade").style.border = "1px solid #BF0000";
									
									$("#cidade_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#cidade_error').fadeOut();
											//document.getElementById("cidade").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								if(resposta.responseText && resposta.responseText.debug.indexOf("Estato") >= 0){
									document.getElementById("estado_error").innerHTML = '<span id="error1">Verifique<br>o Estato</span>';
						 			document.getElementById("estado").style.border = "1px solid #BF0000";
									
									$("#estado_error").fadeIn(700, function(){
        								window.setTimeout(function(){
            								$('#estado_error').fadeOut();
											//document.getElementById("estado").style.border = "1px solid #cccccc";
       									}, 10000);
    								});
								}
								
								//  </ Erros do cadastro
                        	},
							// erro end
                    	});

					
                }
            }
            checkout.getPaymentToken({
                brand: bandeira,
                number: numero_cartao,
                cvv: codigo_seguranca,
                expiration_month: mes_vencimento,
                expiration_year: ano_vencimento
            }, callback);
        })
	})
}
);