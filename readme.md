# Módulo Efí Cartão para WHMCS

[![versão](https://img.shields.io/github/v/release/gofas/gofaseficartao?label=vers%C3%A3o&color=005071&style=flat-square)](https://github.com/gofas/gofaseficartao/releases/latest)
[![downloads](https://img.shields.io/endpoint?url=https%3A%2F%2Fgofas.net%2Fwp-json%2Fgofas%2Fv1%2Fbadge%2Fgofaseficartao&style=flat-square)](https://github.com/gofas/gofaseficartao/releases/latest)
[![suporte](https://img.shields.io/badge/suporte-f%C3%B3rum%20gratuito-ff8700?style=flat-square)](https://gofas.net/foruns/)

Módulo de pagamento com cartão de crédito para WHMCS, integrado à API Efí (antiga Gerencianet). Checkout 100% transparente, com parcelamento em até 12x e pagamentos recorrentes, direto nas faturas do seu WHMCS. Desenvolvido pela Gofas Software.

O módulo não armazena dados de cartão no seu WHMCS. Os dados são enviados do navegador do cliente diretamente para os servidores da Efí, sem passar pelo seu sistema.

## Sumário

- [Download](#download)
- [Funcionalidades](#funcionalidades)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Informações importantes](#informações-importantes)
- [Suporte](#suporte)
- [Licença](#licença)

## Download

**[Baixar a versão mais recente](https://github.com/gofas/gofaseficartao/releases/latest/download/gofaseficartao.zip)**

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

<img src="https://raw.githubusercontent.com/gofas/gofaseficartao/master/docs/img/tela-configuracoes-modulo-4.2.0.png" alt="Tela de configuracoes do modulo" width="640">

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

O código deste módulo é público para transparência e auditoria. Isso não transfere a titularidade nem concede licença livre de uso: o software é de propriedade da Gofas Software, protegido pela Lei 9.609/98 e pelos tratados de direitos autorais.

Trechos do [contrato de licença de uso](https://gofas.net/contrato-de-venda-de-licenca-de-uso-de-software/) que se aplicam diretamente a este repositório:

- **Não redistribuir**: é proibido o aluguel, o arrendamento, o empréstimo, a cessão e o licenciamento do software a terceiros, total ou parcial, assim como o fornecimento de serviços de hospedagem comercial do software (Cláusula 10ª, §3º).
- **Não modificar**: é vedado qualquer procedimento que implique engenharia reversa, descompilação, desmontagem, tradução, adaptação ou modificação do software, bem como qualquer alteração não autorizada de suas funcionalidades (Cláusula 10ª, §2º).
- **Módulo alterado perde o suporte**: a Gofas não se responsabiliza por defeitos decorrentes de alteração do software, de operação por pessoas não autorizadas ou da integração com softwares de terceiros (Cláusula 10ª, §7º). O suporte é uma cortesia e não é garantido pela licença (Cláusula 7ª, §1º).
