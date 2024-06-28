/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.0
 */
function inputstorefunc(){
  	var checkBox = document.getElementById("nostore");
	var geficCheckIcon = document.getElementById("geficCheckIcon");
	var paymentToken_ = document.getElementById("paymentToken").value;
	//if (paymentToken_ !== "undefined") {
		sessionStorage.setItem("paymentToken_",paymentToken_);
	//}
  	if(checkBox.value == "yes"){
		sessionStorage.setItem("nostore", "no");
		checkBox.value = "no";
		geficCheckIcon.className = "geficCheckIconOff fas fa-check"
	}
	else if(checkBox.value == "no"){
	 	sessionStorage.setItem("nostore", "yes");
		checkBox.value = "yes";
		geficCheckIcon.className = "geficCheckIcon fas fa-check"
  	}
	console.log("nostore: "+sessionStorage.getItem("nostore"));
}
function gefic_inputs(){
	sessionStorage.setItem("nostore", "yes");
	sessionStorage.setItem('installments_', 1);
	sessionStorage.setItem('paymentToken_', "Na");
	var inputDescriptionContainer = document.getElementById('inputDescriptionContainer');	
	var gefic_input = '<style>.geficCheckIconOff:hover:before {border: 2px solid #3e89c5;padding: 4px;}.geficCheckIcon:before {background-color: #3e89c5; font-size: 11px; color: #ffffff; padding: 5px; border: 1px solid #3e89c5; line-height: 0; border-radius: 50%; margin: 1px;}.geficCheckIconOff:before {background-color: #ffffff; font-size: 11px; color: #ffffff; padding: 5px; border: 1px solid #c6c3bf; line-height: 0; border-radius: 50%; margin: 1px;}</style><label class="col-sm-4 control-label"></label><div class="col-sm-8" onclick="inputstorefunc();" style="margin-bottom: 15px;margin-top: 6px;cursor: pointer;"><i id="geficCheckIcon" class="geficCheckIcon fas fa-check"></i><span>&nbsp;&nbsp;Automatizar pagamentos </span></div><input type="hidden" id="nostore" value="yes"><input type="hidden" name="paymentToken" id="paymentToken" value="NA">';
	inputDescriptionContainer.innerHTML = gefic_input;// + inputDescriptionContainer.innerHTML;
}
window.onload = gefic_inputs();