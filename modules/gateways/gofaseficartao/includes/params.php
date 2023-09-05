<?php
/**
 * Módulo Efí Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=8423
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=8343
 * @version		4.0.0
 */
if (!defined('WHMCS')){die();}
//use WHMCS\Database\Capsule;
if ($params['sandbox']){
    $api_mode = 'sandbox';
    $clientid = $params['sandbox_clientid'];
    $clientsecret = $params['sandbox_clientsecret'];
    $identifier = $params['sandbox_identifier'];
    $charge_url = 'https://api.sandbox.cloud.galaxpay.com.br/v2';
   //$referralToken = '34c8f0bb';
}
if (!$params['sandbox']){
    $api_mode = 'live';
    $clientid = $params['clientid'];
    $clientsecret = $params['clientsecret'];
    $identifier = $params['identifier'];
    $charge_url = 'https://api.galaxpay.com.br/v2';
    //$referralToken = '34c8f0bb';
}