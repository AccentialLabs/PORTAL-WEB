<?php

/**
 * PROJETO: Terminator
 * 
 * Configurações extras para a API
 * 
 * @author Rodrigo Salles, Wilson Junior
 */

/**
 * Intervalo de tempo máximo utilizado para validação do TOKEN de segurança,
 * para requisições feitas à API através de aplicações externas.
 *
 * OBS: Intervalo dado em segundos
 */
Configure::write ( 'apiRequestTimeout', 5 );

/**
 * Status possíveis do TOKEN de segurança, que determinam a validade da comunicação com a API
 */
$secureTokenValidationStatus = array (
		'invalid' => 'INVALID_ACCESS',
		'expired' => 'EXPIRED_ACCESS' 
);

Configure::write ( 'secureTokenValidationStatus', $secureTokenValidationStatus );

/**
 * Configuração do ambiente: local; development; homolog; production
 */
$apiEnvironment = 'homolog';
$apiTesting = 'testing';

/**
 * URL utilizada de acordo com o ambiente configurado acima
 */
$environmentURL = array (
		'local' => 'http://terminator.dev/api',
		'development' => 'http://192.168.0.17/terminator/api',
		'testing' => 'http://localhost/work/x_api/',
		'homolog' => 'http://secure.trueone.com.br/t1core',
		'production' => '' 
);

if (! defined ( 'API_ENVIRONMENT_URL' )) {
	define ( 'API_ENVIRONMENT_URL', $environmentURL [$apiTesting] );
}

if (! defined ( 'API_TESTING_URL' )) {
	define ( 'API_TESTING_URL', $environmentURL [$apiTesting] );
}

/**
 * Status possiveis para validação de campos na geração da URL
 */
$paramsValidationStatus = array (
		'invalid_param' => 'REQUIRED_PARAM_FIELD' 
);

Configure::write ( 'paramsValidationStatus', $paramsValidationStatus );

/**
 * Status possiveis para as operações: GET, SAVE, DELETE, EDIT
 */
$crudOperationStatus = array (
		'get' => 'GET_ERROR',
		'save_ok' => 'SAVE_OK',
		'save_error' => 'SAVE_ERROR',
		'save_validation' => 'SAVE_VALIDATION_ERROR',
		'delete_ok' => 'DELETE_OK',
		'delete_error' => 'DELETE_ERROR',
		'delete_invalid_id' => 'DELETE_INVALID_ID' 
);

Configure::write ( 'crudOperationStatus', $crudOperationStatus );

/**
 * Configurações do FTP remoto para upload de arquivos.
 */

$ftpUploadConfig = array (
		'host' => 'ftp.trueone.com.br',
		'user' => 'public',
		'password' => '8I%Mz@2mRQdt',
		'uploadFolder' => 'uploads/',
		'uploadPath' => 'public_html/',
		'url' => 'http://trueone.com.br' 
);

Configure::write ( 'ftpUploadConfig', $ftpUploadConfig );