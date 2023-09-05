/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.0
 */
document.addEventListener('DOMContentLoaded', () => { // Aguarda o carregamento da página para iniciar os scripts
	// Configuração
	var inputIdentificadorConta = document.getElementById("identificadorConta");
	var inputValorTotal = document.getElementById("valorTotal");
	// Dados cartão
	var inputNumeroCartao = document.getElementById("cc-num");
	var inputBandeira = document.getElementById("cardtype");
	var inputMesVencimento = document.getElementById("cc-expm");
	var inputAnoVencimento = document.getElementById("cc-expy");
	var inputCvv = document.getElementById("cc-cvc");
	// Resultados
	var inputParcelas = document.getElementById("installmentsnum");
	var inputPaymentToken = document.getElementById("paymentToken");
	var inputmascaraCartao = document.getElementById("mascaraCartao");
	// Botão de acionamento
	var btnGerarToken = document.getElementById("submit");
	inputNumeroCartao.addEventListener("input",function (){
		/*
		* Identifica a bandeira a partir do número do cartão
		* @params { numeroCartao }
		*/
		if (inputNumeroCartao.value.length >= 15) {
			try {
				EfiJs.CreditCard
					.debugger(true)
					.setCardNumber(inputNumeroCartao.value)
					.verifyCardBrand()
					.then(bandeira => {
						inputBandeira.value = bandeira;
						
						buscarParcelamemto(bandeira); // aciona a função para buscar as opções de parcelamento
					});
			} catch (error) {
				alert(`Erro ao obter a bandeira!\n\nCódigo: ${error.code}\nNome: ${error.error}\nMensagem: ${error.error_description}`);
				console.warn(`Algo deu errado ao verificar a bandeira.\n ${error}`);
			}
		}
		/*
		* FIM - Identifica a bandeira a partir do número do cartão
		*/
	});
	function buscarParcelamemto(bandeira) {
		/*
		* Retorna as informações de parcelamento
		* @params { identificadorConta, ambiente, bandeira, valorTotal }
		*/
		try {
			EfiJs.CreditCard
				.debugger(false)
				.setAccount(inputIdentificadorConta.value)
				.setEnvironment('sandbox') // 'production' or 'sandbox'
				.setBrand(bandeira)
				.setTotal(parseInt(inputValorTotal.value))
				.getInstallments()
				.then(arrayParcelas => {
					let opcoes = '<option value="0">Escolha como deseja pagar</option>';

					for (let index = 0; index < arrayParcelas.installments.length; index++) {
						opcoes += `<option value="${arrayParcelas.installments[index].installment}">${arrayParcelas.installments[index].installment} x de R$${arrayParcelas.installments[index].currency} ${arrayParcelas.installments[index].has_interest === false ? "sem juros" : ""}</option>`;
					}
					inputParcelas.innerHTML = opcoes;
				}).catch(err => {
					alert(`Erro ao buscar as parcelas!\n\nCódigo: ${err.code}\nNome: ${err.error}\nMensagem: ${err.error_description}`);
					throw new Error(`Something went wrong in verifyCardBrand(.\n ${error}`);
				});
		}
		catch (error) {
			alert(`Erro ao buscar as parcelas!\n\nCódigo: ${error.code}\nNome: ${error.error}\nMensagem: ${error.error_description}`);
			throw new Error(`Something went wrong.\n ${error}`);
		}

		/*
		 * FIM - Retorna as informações de parcelamento
		 */
	}

	//$(document).ready(function (){
	//btnGerarToken.addEventListener("click", function () {
		//btnGerarToken.classList.add('disabled');

		//btnGerarToken.innerHTML = '<div class="spinner-border spinner-border-sm text-info" role="status"> <span class="visually-hidden">Loading...</span></div>';

		/*
		* Retorna o payment_token e card_mask
		* @params { identificadorConta, ambiente, {bandeira, numeroCartao, cvv, mesVencimento, anoVencimento, reuse} }
		*/
		try {
			EfiJs.CreditCard
				.debugger(false)
				.setAccount(inputIdentificadorConta.value)
				.setEnvironment('sandbox') // 'production' or 'sandbox'
				.setCreditCardData({
					brand: inputBandeira.value,
                            number: inputNumeroCartao.value,
                            cvv: inputCvv.value,
                            expirationMonth: inputMesVencimento.value,
                            expirationYear: inputAnoVencimento.value,
                            reuse: true
				})
				.getPaymentToken()
				.then(dados => {
					let payment_token = dados.payment_token;
					let card_mask = dados.card_mask;

					inputPaymentToken.value = payment_token;
					mascaraCartao.value = card_mask;

					//btnGerarToken.classList.remove('btn-primary');
					//btnGerarToken.classList.add('btn-success');
					//btnGerarToken.innerHTML = 'Gerar novamente';
					//btnGerarToken.classList.remove('disabled');
				}).catch(err => {
					alert(`Erro ao buscar ao gerar o payment_token!\n\nCódigo: ${err.code}\nNome: ${err.error}\nMensagem: ${err.error_description}`);
					//btnGerarToken.innerHTML = 'Gerar payment_token';
					//btnGerarToken.classList.remove('disabled');
					throw new Error(`Something went wrong in verifyCardBrand(.\n ${error}`);
				});
		} catch (error) {
			alert(`Erro ao buscar ao gerar o payment_token!\n\nCódigo: ${error.code}\nNome: ${error.error}\nMensagem: ${error.error_description}`);
			//btnGerarToken.innerHTML = 'Gerar payment_token';
			//btnGerarToken.classList.remove('disabled');
			throw new Error(`Something went wrong.\n ${error}`);
		}
		/*
		 * FIM - Retorna o payment_token e card_mask
		 */
	//});

	inputValorTotal.addEventListener("input", function () {
		if (inputIdentificadorConta.value.length === 32 && inputNumeroCartao.value.length >= 16 && inputValorTotal.value.length >= 3) {
			buscarParcelamemto(inputBandeira.value);
		}
	});

	inputIdentificadorConta.addEventListener("input", function () {
		if (inputIdentificadorConta.value.length === 32 && inputNumeroCartao.value.length >= 16) {
			buscarParcelamemto(inputBandeira.value);
		}
	});

	inputParcelas.addEventListener("change", function () {
		//btnGerarToken.classList.remove('disabled');
	});
});