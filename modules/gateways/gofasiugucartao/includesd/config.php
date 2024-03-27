<?php
/**
 * Módulo iugu Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=14946
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=12349
 * @version		1.0.0
 */

if( !defined('WHMCS')){ die(''); }
use WHMCS\Database\Capsule;
function gofasiugucartao_MetaData(){
    return array(
        'DisplayName' => 'Gofas iugu - cartão',
        'APIVersion' => '1.1',
    );
}
if(!function_exists('gofasiugucartao_config')){
	function gofasiugucartao_config(){
		require_once __DIR__.'/functions.php';
		$module_version = '1.0.0';
		$module_version_int = (int)preg_replace("/[^0-9]/", "", $module_version);
		$module_page	= '14946';
		$verify_install = gic_verifyInstall();
		$whmcs_url = gic_whmcs_url();
		$check_updates = gic_verify_module_updates($module_page,$whmcs_url['admin_url'],$module_version);
		if($_REQUEST['resetversion'] === 'gofasiugucartao'){ #9
			gic_reset_local_version();
			header_remove();
			header("Location: ".$whmcs_url['admin_url'].'/configgateways.php?manage=gofasiugucartao#m_gofasiugucartao',true,303);
			exit;
		}
		//echo '<pre>',print_r($sysinfo),'</pre>';
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','Version')
		->get(['value']) as $data1 ){
			$Version = $data1->value;
		}
		$whmcs_version=(int)preg_replace('/[^\da-z]/i', '',  gic_get_string_between('#'.$Version, '#', '-'));
		if($whmcs_version<861){
			return [
				'FriendlyName' => [
					'Type' => 'System',
					'Value' => 'Gofas iugu - cartão',
				],
				'separator_1' => [
					'Description' => '
					<div class="gic_separator" style="padding: 1px 15px 9px;">
					'.(string)gic_decrypt($check_updates['check']).'
						<div style="margin-left: 10px;">
							<h4 style="padding-top: 5px; color: red;">Gofas iugu - cartão para WHMCS v'.$module_version.' | requer WHMCS versão 8.6.1 ou superior</h4>
							'.$check_updates['message'].'
						</div>
					</div>',
				],
				'footer' => [
					'Description' => '<div class="ggp_section">
					<p>&copy; '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/?p=14946#changelog">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=14946">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Suporte Gratuito" href="https://gofas.net?p=12349">↗ Suporte Gratuito</a>.</p>
					<p style="font-size: 11px;">
					Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net/?p=9340">contrato de licença de uso de software</a>.
					</p>
					'.$check_updates['message'].'
					</div>',
				],
			];
		}		
		// Options count
		$opt_num = 1;
		/// Display Options	
		$renderize = array(
			// Nome de exibição amigável para o gateway
			'FriendlyName' => array(
				'Type' => 'System',
				'Value' => 'Gofas iugu - cartão',
				'Size' => '40',
			),
			'separator_1' => array(
				'Description' => '
				<style type="text/css">
				.gic_section {
					background: #dcdcdc; padding: 10px 15px 1px;
				}
				.gic_separator {
					background: #dcdcdc; padding: 1px 15px 1px;
				}
				.gic_separator p {
					font-size: 12px;
						margin: 0px 0px 5px 0px;
				}
				.gic_required {
					color: #CC0000;
					font-size: 20px;
					line-height: 0;
				}
				.gic_required_txt {
					color: #CC0000;
				}
				.gic_optional_txt {
					color: #02bb04;
				}
				#Payment-Gateway-Config-gofasiugucartao td.fieldlabel {
					background-color: #fff;
					text-align: right;
					vertical-align: text-top;
				}
				#Payment-Gateway-Config-gofasiugucartao td.input-inline {
					display: inline-block;
					float: left;
					clear: left;
				}
				#Payment-Gateway-Config-gofasiugucartao td.fieldarea input {
					margin-right: 5px;
				}
				</style>
				<div class="gic_separator">
				
				'.gic_decrypt($check_updates['check']).'
					<div style="padding: 10px 10px 20px 10px;">
						<h4 style="padding-top: 5px;">Módulo iugu cartão para WHMCS v'.$module_version.'</h4>
						'.$check_updates['message'].'
						<p><a style="text-decoration:underline;" target="_blank" href="https://gofas.net/gic/">Documentação do módulo</a>.<br></p>	
					</div>
		
				</div>',
			),
			'account_id' => array(
				'FriendlyName' => $opt_num++.'- ID da Conta na Iugu<span class="gic_required">*</span>',
				'Type' => 'text',
				'Size' => '50',
				'Default' => '',
				'Description' => '<a target="_blank" style="text-decoration:underline;" href="https://alia.iugu.com/settings/account/general_information">O ID de sua conta pode ser encontrado aqui</a>',
			),
			'api_token' => array(
				'FriendlyName' => $opt_num++.'- API token produção<span class="gic_required">*</span>',
				'Type' => 'password',
				'Size' => '50',
				'Default' => '',
				'Description' => '<a target="_blank" style="text-decoration:underline;" href="https://alia.iugu.com/settings/account/api_integration">Obter API token</a>',
			),
			'sandbox_api_token' => array(
				'FriendlyName' => $opt_num++.'- API token teste<span class="gic_required">*</span>',
				'Type' => 'password',
				'Size' => '50',
				'Default' => '',
				'Description' => '<a target="_blank" style="text-decoration:underline;" href="https://alia.iugu.com/settings/account/api_integration">Obter API token</a>',
			),
			'separator_2' => array(
				'Description' => '<span><a target="_blank" style="text-decoration:underline;" href="https://dev.iugu.com/reference/autentica%C3%A7%C3%A3o#criando-suas-chaves-de-api-api-tokens-via-painel">Veja aqui como criar suas chaves de API (API Tokens) via painel iugu</a></span>',
			),
			// Sandbox
			'sandbox' => array(
				'FriendlyName' => $opt_num++.'- <i>Sandbox</i>',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => 'Ative essa opção para gerar cobranças em modo de teste.',
			),
			// Log
			'log' => array(
				'FriendlyName' => $opt_num++.'- Salvar Logs',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => 'Salva informações de diagnóstico em <a target="_blank" style="text-decoration: underline;" href="'.$whmcs_url['admin_url'].'/systemmodulelog.php">Utilitários > Logs > Log de Módulo</a>. Para funcionar, antes é necessário ativar o debug de módulo clicando em "Ativar Log de Debug". <a target="_blank" style="text-decoration: underline;" href="'.$whmcs_url['admin_url'].'/systemmodulelog.php">VER LOG</a>.',
			),
			// minimum amount
			'minimunamount' => array(
				'FriendlyName' => $opt_num++.'- Valor mínimo',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '5',
				'Description' => 'Insira o valor total mínimo da fatura para permitir pagamento via cartão. Formato: Decimal, separado por ponto. Não deve ser menor que o valor da tarifa aplicada à sua conta iugu.',
			),
			// Permitir Parcelamento
			'installments' => array(
				'FriendlyName' => $opt_num++.'- Permitir Parcelamento',
				'Type' => 'yesno',
				//'Default' => 'yes',
				'Description' => '<span class="gic_optional_txt">(Opcional)</span> Com essa opção ativada seu cliente verá opções de parcelamento na fatura quando aplicável.',
			),
			// valor mínimo para parcelamento
			'minimunamountinstallments' => array(
				'FriendlyName' => $opt_num++.'- Valor mínimo para parcelamento (apenas números)',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '100',
				'Description' => '<span class="gic_optional_txt">(Opcional)</span> Insira o valor mínimo da fatura para permitir Pagamento Parcelado. Se não preenchido o valor mínimo será R$100,00',
			),
			// máximo de parcelas
    	    'maxinstallments' => array(
    	        'FriendlyName' =>  $opt_num++.'- Máximo de parcelas',
    	        'Type' => 'dropdown',
				'Default' => '2',
    	        'Options' => array(
    	            '2' => 'Até 2 parcelas',
    	            '3' => 'Até 3 parcelas',
    	            '4' => 'Até 4 parcelas',
					'5' => 'Até 5 parcelas',
					'6' => 'Até 6 parcelas',
					'7' => 'Até 7 parcelas',
					'8' => 'Até 8 parcelas',
					'9' => 'Até 9 parcelas',
					'10' => 'Até 10 parcelas',
					'11' => 'Até 11 parcelas',
					'12' => 'Até 12 parcelas',
    	        ),
    	        'Description' => '<span class="ggpc_optional_txt">(Opcional)</span> Selecione o número máximo de parcelas permitido. Esse valor deve ser o mesmo nas <a href="https://alia.iugu.com/settings/account/credit_card/edit" target="_blank" style="text-decoration: underline"> configurações de cartão de crédito da sua conta iugu</a></span>',
    	    ),
		);
		$footer = array('footer' => array(
				'Description' => '<div class="gic_section">
				<p>&copy; 2016 - '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/blog/">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=14946">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Suporte Gratuito" href="https://gofas.net/?p=12349">↗ Suporte Gratuito</a>.</p>
				<p style="font-size: 11px;">
				Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net?p=9340">contrato de licença de uso de software</a>.
				</p>
				'.$check_updates['message'].'
				</div>',
			),
		);
		$gic_config = array_merge($renderize,$footer);
		return $gic_config;
	}
}