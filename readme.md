# Módulo Efí Cartão para WHMCS

Módulo de pagamento com cartão de crédito para WHMCS, integrado à API Efí (antiga Gerencianet). Checkout 100% transparente, com parcelamento em até 12x e pagamentos recorrentes, direto nas faturas do seu WHMCS. Desenvolvido pela Gofas Software.

O módulo não armazena dados de cartão no seu WHMCS. Os dados são enviados do navegador do cliente diretamente para os servidores da Efí, sem passar pelo seu sistema.

## Download

Baixe a versão mais recente:

https://github.com/gofas/gofaseficartao/releases/latest/download/gofaseficartao.zip

## Funcionalidades

- **Checkout transparente**: o cliente paga sem sair do seu site, com a sua marca, na página do pedido ou na fatura
- **Altamente seguro**: os dados do cartão vão do navegador do cliente direto para a Efí, sem trafegar pelo seu WHMCS
- **Pagamento parcelado** em até 12x, com recebimento integral de acordo com as configurações da conta Efí
- **Pagamentos recorrentes**: automatiza as capturas de pagamento no cartão do cliente usando o sistema de captura do WHMCS integrado à API Efí
- **Captura de pagamento pelo admin**, direto no painel do WHMCS
- **Valor mínimo** da fatura para permitir pagamento e valor mínimo para permitir parcelamento
- **Cálculo da tarifa** por transação confirmada, preenchendo o campo "Taxas" (fee) da lista de transações do WHMCS
- **Dispensa configuração de campos CPF/CNPJ**: o módulo detecta automaticamente os campos personalizados de clientes
- **Suporte a produção e a testes (sandbox)**
- **Logs de diagnóstico** configuráveis
- **Aviso de atualização** e verificação de versão na própria tela de configuração do módulo

## Requisitos

- WHMCS >= 7.9
- PHP >= 8.1
- Conta Efí (Efí Pay) com API de cartão habilitada
- Credenciais: identificador da conta, Client ID e Client Secret (produção e desenvolvimento)
- Campos personalizados de clientes para CPF e/ou CNPJ e para a data de nascimento

## Instalação

1. Baixe o arquivo pelo link de download e descompacte. Será criada a pasta `gofaseficartao`.
2. Copie a pasta `modules` de dentro de `gofaseficartao` para a raiz da instalação do WHMCS, mesclando com as pastas existentes.
3. Ative o módulo em `Opções > Pagamentos > Portais para Pagamentos > aba All Payment Gateways`.
4. Informe o identificador da conta, o Client ID e o Client Secret.

## Configuração

### Pré configuração

Crie um campo personalizado de cliente para CPF e/ou CNPJ, ou dois campos distintos, um para cada documento. Crie também um campo personalizado para a data de nascimento do cliente. Os campos são detectados automaticamente pelo nome.

Obtenha o identificador da conta e crie uma Aplicação na sua conta Efí para gerar o Client ID e o Client Secret, de produção e de desenvolvimento.

### Opções do módulo

<img src="https://raw.githubusercontent.com/gofas/gofaseficartao/master/docs/img/tela-configuracoes-modulo.png" alt="Tela de configuracoes do modulo" width="640">

- **Identificador da conta**: identificador da sua conta Efí.
- **Chave Client ID Produção** e **Chave Client Secret Produção**: credenciais da aplicação em modo produção.
- **Chave Client ID Desenvolvimento** e **Chave Client Secret Desenvolvimento**: credenciais da aplicação em modo desenvolvimento (testes).
- **Modo de Testes / Sandbox**: usa a API Efí em modo desenvolvimento, para pagamentos de teste.
- **Salvar Logs**: grava informações de diagnóstico em `Utilitários > Logs > Log de Módulo`.
- **Valor da tarifa Efí**: percentual da comissão paga à Efí a cada transação confirmada, usado para preencher o campo "Taxas" (fee) da transação no WHMCS.
- **Valor mínimo**: valor mínimo da fatura para permitir pagamento com cartão. Se vazio, R$ 5,00.
- **Permitir Parcelamento**: exibe as opções de parcelamento na fatura quando aplicável.
- **Valor mínimo para parcelamento**: valor mínimo da fatura para permitir parcelamento. Se vazio, R$ 100,00.
- **Enviar estatísticas de uso (opcional)**: controla o envio identificado das estatísticas de confirmação de pagamento. Desmarcado, as confirmações continuam sendo contabilizadas de forma anônima.

## Informações importantes

- A tarifa do cartão é paga separadamente à Efí, conforme o plano da sua conta.
- Ao utilizar este módulo você concorda em repassar à Gofas Software a comissão de 1% do total de cada pagamento confirmado das cobranças emitidas pelo módulo. O repasse entre contas Efí acontece automaticamente, apenas quando um pagamento com cartão é confirmado.
- Não ative o Debug em modo produção. Use apenas para testes ou diagnóstico.
- Sempre faça backup antes de mudar algo no seu sistema.

## Suporte

Fórum de suporte gratuito: https://gofas.net/foruns/

## Licença

Software proprietário da Gofas Software. O código é público apenas para transparência e consulta; isso não concede licença de uso, modificação ou redistribuição. É vedado modificar, redistribuir, sublicenciar ou realizar engenharia reversa sem autorização prévia por escrito. Veja [LICENSE](LICENSE) e o contrato completo em https://gofas.net/contrato-de-venda-de-licenca-de-uso-de-software/.
