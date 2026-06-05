# Gofas Efí Cartão

Módulo de gateway de pagamento para WHMCS que integra cobranças via cartão de crédito através da API Efí (EFI Pay). Desenvolvido pela Gofas Software.

## Funcionalidades

- Pagamento com cartão de crédito (tokenização)
- Parcelamento configurável
- Captura automática ou manual
- Suporte a 3DS

## Requisitos

- WHMCS 7.x ou superior
- PHP 8.x
- Conta Efí (EFI Pay) com módulo de cartão habilitado
- Credenciais: Client ID, Client Secret e certificado `.p12`

## Instalação

1. Copiar `modules/gateways/` para o `modules/gateways/` do WHMCS
2. Ativar em **Configurações > Formas de Pagamento**
3. Informar credenciais e certificado

## Changelog

Ver [changelog.md](changelog.md).
