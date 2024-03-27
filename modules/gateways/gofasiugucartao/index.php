<?php
/**
 * Módulo iugu Cartão para WHMCS
 * @copyright	2023 Gofas Software
 * @see			https://gofas.net/?p=14946
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=12349
 * @version		1.0.0
 */
if((int)substr(preg_replace('/[^\da-z]/i','',phpversion()),0,2)>=(int)81){
	require_once __DIR__.'/includes/hooks.php';
    require_once __DIR__.'/includes/config.php';
    require_once __DIR__.'/includes/3dsecure.php';
    require_once __DIR__.'/includes/capture.php';
}
if((int)substr(preg_replace('/[^\da-z]/i','',phpversion()),0,2)<=(int)74){
    require_once __DIR__.'/includesd/hooks.php';
    require_once __DIR__.'/includesd/config.php';
    require_once __DIR__.'/includesd/3dsecure.php';
    require_once __DIR__.'/includesd/capture.php';
}