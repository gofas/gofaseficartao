<?php
/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.2
 */

if( !defined('WHMCS')){ die(''); }
use WHMCS\Database\Capsule;
function gofaseficartao_MetaData(){
    return array(
        'DisplayName' => 'Gofas Efí - Cartão',
        'APIVersion' => '1.1',
    );
}
if(!function_exists('gofaseficartao_config')){
	function gofaseficartao_config(){
		require_once __DIR__.'/functions.php';
		$module_version = '4.0.2';
		$module_version_int = (int)preg_replace("/[^0-9]/", "", $module_version);
		$module_page	= '8423';
		$verify_install = gefic_verifyInstall();
		$whmcs_url = gefic_whmcs_url();
		$check_updates = gefic_verify_module_updates($module_page,$whmcs_url['admin_url'],$module_version);
		if($_REQUEST['resetversion'] === 'gofaseficartao'){ #9
			gefic_reset_local_version();
			header_remove();
			header("Location: ".$whmcs_url['admin_url'].'/configgateways.php?manage=gofaseficartao#m_gofaseficartao',true,303);
			exit;
		}
		//echo '<pre>',print_r($sysinfo),'</pre>';
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','Version')
		->get(['value']) as $data1 ){
			$Version = $data1->value;
		}
		$whmcs_version=(int)preg_replace('/[^\da-z]/i', '',  gefic_get_string_between('#'.$Version, '#', '-'));
		if($whmcs_version<861){
			return [
				'FriendlyName' => [
					'Type' => 'System',
					'Value' => 'Gofas Efí Cartão',
				],
				'separator_1' => [
					'Description' => '
					<div class="gefic_separator" style="padding: 1px 15px 9px;">
					'.(string)gefic_decrypt($check_updates['check']).'
						<div style="margin-left: 10px;">
							<h4 style="padding-top: 5px; color: red;">Gofas Efí Cartão para WHMCS v'.$module_version.' | requer WHMCS versão 8.6.1 ou superior</h4>
							'.$check_updates['message'].'
						</div>
					</div>',
				],
				'footer' => [
					'Description' => '<div class="ggp_section">
					<p>&copy; '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/?p=8423#changelog">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=8423">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Fórum de Suporte" href="https://gofas.net/foruns/">Suporte</a>.</p>
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
				'Value' => 'Gofas Efí Cartão',
				'Size' => '40',
			),
			'separator_1' => array(
				'Description' => '
				<style type="text/css">
				.gefic_section {
					background: #dcdcdc; padding: 10px 15px 1px;
				}
				.gefic_separator {
					background: #dcdcdc; padding: 1px 15px 1px;
				}
				.gefic_separator p {
					font-size: 12px;
						margin: 0px 0px 5px 0px;
				}
				.gefic_required {
					color: #CC0000;
					font-size: 20px;
					line-height: 0;
				}
				.gefic_required_txt {
					color: #CC0000;
				}
				.gefic_optional_txt {
					color: #02bb04;
				}
				#Payment-Gateway-Config-gofaseficartao td.fieldlabel {
					background-color: #fff;
					text-align: right;
					vertical-align: text-top;
				}
				#Payment-Gateway-Config-gofaseficartao td.input-inline {
					display: inline-block;
					float: left;
					clear: left;
				}
				#Payment-Gateway-Config-gofaseficartao td.fieldarea input {
					margin-right: 5px;
				}
				</style>
				<div class="gefic_separator">
				
				'.gefic_decrypt($check_updates['check']).'
					<div style="padding: 10px 10px 20px 10px;">
						<h4 style="padding-top: 5px;">Módulo Efí Cartão para WHMCS v'.$module_version.'</h4>
						'.$check_updates['message'].'
						<h5>Antes de iniciar a configuração, lembre-se de:</h5>
						<p>- Criar um <a style="text-decoration:underline;" target="_blank" href="'.$whmcs_url['admin_url'].'configcustomfields.php">campo personalizado de cliente</a> para CPF e/ou CNPJ, ou se preferir, criar dois campos distintos, um campo apenas para CPF e outro campo para CNPJ. O módulo identifica os campos do perfil do cliente automaticamente.</p>
						<p>- Criar uma Aplicação e obter as credencians <i>Chave Client ID</i> e <i>Chave Client Secret</i> da <a style="text-decoration: underline;" target="_blank" href="https://sistema.gerencianet.com.br/api/introducao">API Efí</a>. Veja <a style="text-decoration: underline;" target="_blank" href="https://s3.amazonaws.com/uploads.gofas.me/wp-content/uploads/2021/02/07004154/Gerencianet_api.png">aqui</a> onde encontrar.</p>
						<p><a style="text-decoration:underline;" target="_blank" href="https://gofas.net/gefic/">Documentação do módulo</a>.<br></p>	
					</div>
		
				</div>',
			),
			// Identificador
			'identifier' => array(
				'FriendlyName' => $opt_num++.'- Identificador da conta<span class="gefic_required">*</span>',
				'Type' => 'text',
				'Size' => '60',
				'Default' => '',
				'Description' => '<span class="gefic_required_txt">(Obrigatório)</span>. <a style="text-decoration: underline;" target="blank" href="https://s3.amazonaws.com/gerencianet-pub-prod-1/printscreen/2023/06/08/palloma.brito/a80fb0-a780fe53-12bf-4d00-a4be-36b0d612bdbc.png" class="block-display-image-parent block-display-image-size-smart">Veja aqui</a> onde localizar o "identificador da conta" no painel de controle Gerencianet.',
			),
			// Client ID
			'clientid' => array(
				'FriendlyName' => $opt_num++.'- Chave Client ID Produção<span class="gefic_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="gefic_required_txt">(Obrigatório)</span>',
			),
			// Client Secret
			'clientsecret' => array(
				'FriendlyName' => $opt_num++.'- Chave Client Secret Produção<span class="gefic_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="gefic_required_txt">(Obrigatório)</span>',
			),
			// Client ID Sandbox
			'clientidsandbox' => array(
				'FriendlyName' => $opt_num++.'- Chave Client ID Desenvolvimento<span class="gefic_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="gefic_required_txt">(Obrigatório)</span>',
			),
			// Client Secret Sandbox
			'clientsecretsandbox' => array(
				'FriendlyName' => $opt_num++.'- Chave Client Secret Desenvolvimento<span class="gefic_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="gefic_required_txt">(Obrigatório)</span>',
			),
			// Testar?
			'sandbox' => array(
				'FriendlyName' => $opt_num++.'- Modo de Testes / Sandbox',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => 'Marque essa opção para utilizar a API Efí em modo "Desenvolvimento" (Homologação). <a style="text-decoration: underline;" href="https://sistema.gerencianet.com.br/api/introducao" target="_blank">Painel da API</a>.',
			),
			// Log
			'log' => array(
				'FriendlyName' => $opt_num++.'- Salvar Logs',
				'Type' => 'yesno',
				'Default' => 'no',
				'Description' => 'Salva informações de diagnóstico em <a target="_blank" style="text-decoration: underline;" href="'.$geficwhmcsadminurl.'systemmodulelog.php">Utilitários > Logs > Log de Módulo</a>. Para funcionar, antes é necessário ativar o debug de módulo clicando em "Ativar Log de Debug". <a target="_blank" style="text-decoration: underline;" href="'.$geficwhmcsadminurl.'systemmodulelog.php">VER LOG</a>.',
			),
			'fee' => array(
				'FriendlyName' => $opt_num++.'- Valor da tarifa Efí',
				'Type' => 'text',
				'Default' => '4.99',
				'Size' => '10',
				'Description'    => '<span class="gefic_optional_txt">(Opcional)</span> Insira o valor percentual da comissão paga à Efí a cada transação via cartão com pagamento confirmado. Essa informação servirá para calcular e preencher o campo "Taxas" (fee) da lista de transações do WHMCS, já que a API Efí  não retorna essa informação. Use ponto(.) para separar casas decimais, ex.: 1.5',
			),
			'minimunamount' => array(
				'FriendlyName' => $opt_num++.'- Valor mínimo',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '5',
				'Description' => '<span class="gefic_optional_txt">(Opcional)</span> Insira o valor mínimo da fatura para permitir pagamento via cartão, 5 equivale à R$ 5,00. O valor mínimo padrão é R$5,00.',
			),
			// Permitir Parcelamento
			'installments' => array(
				'FriendlyName' => $opt_num++.'- Permitir Parcelamento',
				'Type' => 'yesno',
				//'Default' => 'yes',
				'Description' => '<span class="gefic_optional_txt">(Opcional)</span> Com essa opção ativada seu cliente verá opções de parcelamento na fatura quando aplicável.',
			),

			// valor mínimo para parcelamento
			'minimunamountinstallments' => array(
				'FriendlyName' => $opt_num++.'- Valor mínimo para parcelamento (apenas números)',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '100',
				'Description' => '<span class="gefic_optional_txt">(Opcional)</span> Insira o valor mínimo da fatura para permitir Pagamento Parcelado. Se não preenchido o valor mínimo será R$100,00',
			),
		);
		$footer = array('footer' => array(
				'Description' => '<div class="gefic_section">
				<p>&copy; 2016 - '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/blog/">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=7893">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Fórum de Suporte Gratuito" href="https://gofas.net/?p=7856">Fórum de Suporte Gratuito</a>.</p>
				<p style="font-size: 11px;">
				Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net?p=9340">contrato de licença de uso de software</a>.
				</p>
				'.$check_updates['message'].'
				</div>',
			),
		);
		$gefic_config = array_merge($renderize,$footer);
		return $gefic_config;
	}
}