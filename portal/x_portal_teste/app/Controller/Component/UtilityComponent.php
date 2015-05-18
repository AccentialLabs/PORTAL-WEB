<?php

/**
 * 
 * Classe utilit�ria para uso de todas as API's
 * @author Rodrigo Salles, Wilson Junior
 *
 */
class UtilityComponent extends Component {

    private $paramsValidationStatus;
    private $ftpUploadConfig;

    public function __construct() {
        if (!$this->paramsValidationStatus)
            $this->paramsValidationStatus = Configure::read('paramsValidationStatus');
        if (!$this->ftpUploadConfig)
            $this->ftpUploadConfig = Configure::read('ftpUploadConfig');
    }

    public function testandoUtility() {
        return "retorno da funcao de teste";
    }

    /**
     * Decodifica dados para uso na API.
     * 
     * @param string $base64String        	
     * @return array
     */
    public function decodeData($base64String = null) {
        if (!$base64String)
            return null;

        $json = base64_decode($base64String);
        $array = json_decode($json, true);
        return $array;
    }

    /**
     * Codifica dados para uso na API.
     * 
     * @param array $arrayData        	
     * @return string
     */
    public function encodeData($arrayData = null) {
        if (!$arrayData)
            return null;

        $json = json_encode($arrayData);
        $base64String = base64_encode($json);
        return $base64String;
    }

    /**
     * Gera TOKEN de seguran�a para comunica��o entre API's
     * 
     * @return string
     */
    public function generateSecureToken() {
        $timestamp = time();
        $array = array(
            'secureNumbers' => $timestamp
        );
        $json = json_encode($array);
        return base64_encode($json);
    }

    /**
     * Respons�vel por solicitar dados de uma determinada API,
     * segundo os parâmetros enviados.
     *
     * @param string $api,
     *        	$type
     * @param array $params        	
     */
    public function urlRequestToGetData($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/get';
        $apiUrl .= '/' . $type;

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }

    /**
     * Respons�vel por solicitar dados de uma determinada API,
     * segundo os parâmetros enviados.
     *
     * @param string $api,
     *        	$type
     * @param array $params        	
     */
    public function urlRequestPasswordRecovery($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL;
        $apiUrl .= '/' . $api . '/passwordRecovery';
        $apiUrl .= '/first';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_id'];
        }
    }

    /**
     * Respons�vel por solicitar dados de uma determinada API,
     * segundo os parâmetros enviados.
     *
     * @param string $api,
     *        	$type
     * @param array $params        	
     */
    public function urlRequestWishlist($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/saveWishlist';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_id'];
        }
    }

    /**
     * Respons�vel por solicitar dados de uma determinada API,
     * segundo os parâmetros enviados.
     *
     * @param string $api,
     *        	$type
     * @param array $params        	
     */
    public function urlRequestToLoginData($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/companyLogin';
        $apiUrl .= '/' . $type;

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }

    public function urlRequestToLoginUserData($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/userLogin';
        $apiUrl .= '/' . $type;

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }

    /**
     * Respons�vel por cadastrar/editar dados de uma determinada API,
     * segundo os par�metros enviados.
     *
     * @param string $api        	
     * @param array $params        	
     */
    public function urlRequestToSaveData($api, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/save';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
    }

    /**
     * Respons�vel por excluir dados de uma determinada API,
     * segundo os parâmetros enviados.
     *
     * @param string $api        	
     */
    public function urlRequestToDeleteData($api, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/delete';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_id'];
        }
    }

    /**
     * Responsável por excluir dados de uma determinada API,
     * segundo os parâmetros enviados.
     *
     * @param string $api        	
     */
    public function urlRequestToCheckout($api, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/checkouts';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_id'];
        }
    }

    /**
     * Faz o upload de arquivos para o servidor configurado
     *
     * @param string $folder,
     *        	string $inputName, array $arrayFiles
     * @return array
     */
    public function uploadFile($folder = null, $fileData, $recursive = false) {
        $data = '';
        if ($recursive) {
            foreach ($fileData as $file) {
                $data [] = $this->uploadFile($folder, $file);
            }
            return $data;
            exit();
        } else {
            $ftpConnectionId = ftp_connect($this->ftpUploadConfig ['host']);
            $ftpLogin = ftp_login($ftpConnectionId, $this->ftpUploadConfig ['user'], $this->ftpUploadConfig ['password']);
        }

        $folder = (!$folder ? '' : $folder . '/');

        $fileExt = end(explode('.', $fileData ['name']));
        $renamedRemoteFile = uniqid() . '.' . $fileExt;

        $pathToUpload = $this->ftpUploadConfig ['uploadPath'] . $this->ftpUploadConfig ['uploadFolder'] . $folder . $renamedRemoteFile;

        $localFile = $fileData ['tmp_name'];

        if ($ftpLogin) {
            ftp_pasv($ftpConnectionId, true);
            if (ftp_put($ftpConnectionId, $pathToUpload, $localFile, FTP_BINARY)) {
                $data = $this->ftpUploadConfig ['url'] . '/' . $this->ftpUploadConfig ['uploadFolder'] . $folder . $renamedRemoteFile;
            } else {
                $data = 'UPLOAD_ERROR';
            }
        } else {
            $data = 'NO_CONNECTION';
        }

        ftp_close($ftpConnectionId);
        return $data;
    }

    //TESTE 
    public function x_uploadFile($folder = null, $fileData, $recursive = false) {
        $data = '';
        if ($recursive) {
            foreach ($fileData as $file) {
                $data [] = $this->uploadFile($folder, $file);
            }
            return $data;
            exit();
        } else {
            $ftpConnectionId = ftp_connect($this->ftpUploadConfig ['host']);
            $ftpLogin = ftp_login($ftpConnectionId, $this->ftpUploadConfig ['user'], $this->ftpUploadConfig ['password']);
        }

        $folder = (!$folder ? '' : $folder . '/');

        $fileExt = end(explode('.', $fileData ['name']));
        $renamedRemoteFile = uniqid() . '.' . $fileExt;

        $pathToUpload = $this->ftpUploadConfig ['uploadFolder'] . $folder . $renamedRemoteFile;

        $localFile = $fileData ['tmp_name'];

        if ($ftpLogin) {
            ftp_pasv($ftpConnectionId, true);
            if (ftp_put($ftpConnectionId, $pathToUpload, $localFile, FTP_BINARY)) {
                $data = $this->ftpUploadConfig ['url'] . '/' . $this->ftpUploadConfig ['uploadFolder'] . $folder . $renamedRemoteFile;
            } else {
                $data = 'UPLOAD_ERROR';
            }
        } else {
            $data = 'NO_CONNECTION';
        }

        ftp_close($ftpConnectionId);
        return $data;
    }

    public function adv_uploadFile($folder = null, $fileData, $recursive = false) {
        $data = '';
        if ($recursive) {
            foreach ($fileData as $file) {
                $data [] = $this->uploadFile($folder, $file);
            }
            return $data;
            exit();
        } else {
            $ftpConnectionId = ftp_connect('54.94.182.35');
            $ftpLogin = ftp_login($ftpConnectionId, 'acclabs-ftp', 'ACCftp1000');
        }

        $folder = (!$folder ? '' : $folder . '/');

        $fileExt = end(explode('.', $fileData ['name']));
        $renamedRemoteFile = uniqid() . '.' . $fileExt;

        $pathToUpload = '/var/www/acclabs/adventa/uploads/offers/' . $renamedRemoteFile;

        $localFile = $fileData ['tmp_name'];

        if ($ftpLogin) {
            ftp_pasv($ftpConnectionId, true);
            if (ftp_put($ftpConnectionId, $pathToUpload, $localFile, FTP_BINARY)) {
                $data = 'http://54.94.182.35/adventa/uploads/offers/' . $renamedRemoteFile;
            } else {
                $data = 'UPLOAD_ERROR';
            }
        } else {
            $data = 'NO_CONNECTION';
        }

        ftp_close($ftpConnectionId);
        return $data;
    }

    public function adv_uploadFileComp($folder = null, $fileData, $recursive = false) {
        $data = '';
        if ($recursive) {
            foreach ($fileData as $file) {
                $data [] = $this->uploadFile($folder, $file);
            }
            return $data;
            exit();
        } else {
            $ftpConnectionId = ftp_connect('54.94.182.35');
            $ftpLogin = ftp_login($ftpConnectionId, 'acclabs-ftp', 'ACCftp1000');
        }

        $folder = (!$folder ? '' : $folder . '/');

        $fileExt = end(explode('.', $fileData ['name']));
        $renamedRemoteFile = uniqid() . '.' . $fileExt;

        $pathToUpload = '/var/www/acclabs/adventa/uploads/companies/' . $renamedRemoteFile;

        $localFile = $fileData ['tmp_name'];

        if ($ftpLogin) {
            ftp_pasv($ftpConnectionId, true);
            if (ftp_put($ftpConnectionId, $pathToUpload, $localFile, FTP_BINARY)) {
                $data = 'http://54.94.182.35/adventa/uploads/companies/' . $renamedRemoteFile;
            } else {
                $data = 'UPLOAD_ERROR';
            }
        } else {
            $data = 'NO_CONNECTION';
        }

        ftp_close($ftpConnectionId);
        return $data;
    }

    /**
     * Executa a requisição da URL informada
     * 
     * @param string $url        	
     */
    /*
     * private function curlRequest($url) { $curl = curl_init($url); curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); $data = curl_exec($curl); curl_close($curl); return $data; }
     */
    private function curlRequest($url, $postData) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }

    public function calcFrete($servico, $CEPorigem, $CEPdestino, $peso, $altura = '4', $largura = '12', $comprimento = '16', $valor = '1.00') {
        // //////////////////////////////////////////////
        // Código dos Serviços dos Correios
        // 41106 PAC
        // 40010 SEDEX
        // 40045 SEDEX a Cobrar
        // 40215 SEDEX 10
        // //////////////////////////////////////////////
        // URL do WebService
        $correios = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa=&sDsSenha=&sCepOrigem=" . $CEPorigem . "&sCepDestino=" . $CEPdestino . "&nVlPeso=" . $peso . "&nCdFormato=1&nVlComprimento=" . $comprimento . "&nVlAltura=" . $altura . "&nVlLargura=" . $largura . "&sCdMaoPropria=n&nVlValorDeclarado=" . $valor . "&sCdAvisoRecebimento=n&nCdServico=" . $servico . "&nVlDiametro=0&StrRetorno=xml";
        // Carrega o XML de Retorno
        $xml = simplexml_load_file($correios);

        // Verifica se não há erros
        if ($xml->cServico->Erro == '0') {
            $retorno = $xml->cServico->Valor . "-" . $xml->cServico->PrazoEntrega;
            return $retorno;
        } else {
            return false;
        }
    }

    public function calcData($data_inicial, $data_final) {
        // Usa a fun��o criada e pega o timestamp das duas datas:
        $time_inicial = $this->geraTimestamp($data_inicial);
        $time_final = $this->geraTimestamp($data_final);
        // Calcula a diferen�a de segundos entre as duas datas:
        $diferenca = $time_final - $time_inicial; // 19522800 segundos
        // Calcula a diferen�a de dias
        $dias = (int) floor($diferenca / (60 * 60 * 24)); // 225 dias
        // Exibe uma mensagem de resultado:
        return $dias;
    }

    // Cria uma fun��o que retorna o timestamp de uma data no formato DD/MM/AAAA
    private function geraTimestamp($data) {
        $partes = explode('/', $data);
        return mktime(0, 0, 0, $partes [1], $partes [0], $partes [2]);
    }

    public function calcIdade($data) {

        // Separa em dia, m�s e ano
        list ( $dia, $mes, $ano ) = explode('/', $data);
        // Descobre que dia � hoje e retorna a unix timestamp
        $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        // Descobre a unix timestamp da data de nascimento do fulano
        $nascimento = mktime(0, 0, 0, $mes, $dia, $ano);
        // Depois apenas fazemos o c�lculo j� citado :)
        $idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 365.25);

        return $idade;
    }

    public function formataData($dataC) {
        $data = explode('/', $dataC);
        if (!empty($data [1])) {
            return $data [2] . '/' . $data [1] . '/' . $data [0];
        } else {
            return $dataC;
        }
    }

    //v�lida imagem para fazer upload
    public function x_validaImagem($imagem) {
        $result = '';
        $config ["largura"] = 140;
        $config ["altura"] = 140;
        // recebe imagem upada
        $arquivo = $imagem;
        // captura dimensoes da imagem
        $tamanhos = getimagesize($arquivo ["tmp_name"]);

        // verifica o formato - jpg/png/gif
        if (!eregi("^image/(pjpeg|jpeg|jpg|png|gif|JPEG|JPG|PNG|GIF)$", $arquivo ["type"])) {
            $result = "ERRO_DE_FORMATO";
            return $result;
        }

        // verifica largura - 0
        else if ($tamanhos [0] > $config ["largura"]) {
            $result = "ERRO_DE_LAGURA";
            return $result;
        }

        // Verificar altura
        else if ($tamanhos [1] > $config ["altura"]) {
            $result = "ERRO_DE_ALTURA";
            return $result;
        } else {
            $result = "IMAGEM_OK";
            return $result;
        }
        return $result;
    }

    /**
     * 
     * Fun��o modificada 
     * @author Matheus Odilon  
     * @param unknown $api
     * @param unknown $type
     * @param unknown $params
     */
    public function x_urlRequestToLoginData($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/teste';
        $apiUrl .= '/' . $type;

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }

    public function urlRequestToSendEmailSencUser($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/myTest';
        $apiUrl .= '/' . $type;

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }

    public function shareOffer($params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= 'users' . '/emailTest';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_id'];
        }
    }

    //mudança de status 
    public function paymentStatusChange($params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= 'users' . '/paymentStatusChange';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }

    public function postEmail($api, $type, $params) {

        // PASSO 2
        // CAPTURANDO DADOS DA PERIODICIDADE SELECIONADA PELO USUÁRIO PARA O ENVIO DOS EMAIS E NOTIFICACOES
        // ISSO DEFINE SE JÁ ENVIAREMOS OU AGENDAREMOS 
        $sqlPeriod = "select * from users_preferences where user_id = {$params['user_id']};";
        $paramsPeriod = array('User' => array('query' => $sqlPeriod));
        $period = $this->urlRequestToGetData('users', 'query', $paramsPeriod);


        // VERIFICA QUAL É A PERIODICIDADE
        // CASO SEJA UNITÁRIO JÁ ENVIAMOS O EMAIS
        // CASO SEJA QUALQUER OUTRO, SALVAMOS OS DADOS NECESSARIOS PARA O ENVIO DO EMAIL NA DATA SELECIONADA PELO USUARIO
        if ($period) {
            if ($period[0]['users_preferences']['notifications_periodicity'] == 'UNITARY') {

                // PASSO 5
                // ENVIANDO EMAIL
                //$return = $this->Utility->postEmail('companies', 'companyInactivity', $params);
                $retorno = $this->curlRequest("http://localhost/sesteste/$api/$type.php", $params);

                $body = json_encode($params);
                $dt = date('Y/m/d');
                $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                        . "values({$params['user_id']},"
                        . "000,"
                        . "'" . $data['email_type'] . "',"
                        . "'UNITARY',"
                        . "'{$dt}',"
                        . "'" . $data['api'] . "',"
                        . "'{$body}');";
                $paramsLog = array('User' => array('query' => $sqlLog));
                $saveLog = $this->urlRequestToGetData('users', 'query', $paramsLog);
            } else {
                // TRANSFORMA O ARRAY EM UMA STRING PARA SER SALVA NO BANCO DE DADOS E SER USADO QUANDO NECESSARIO
                $body = json_encode($params);

                // PASSO 3
                // CAPTURA AS INFORMAÇÕES DO EMAIL A SER ENVIADO PARA PODER SER SALVO NO BANCO DE DADOS E USADO QUANDO NECESSARIO
                // A TABELA `MAIL_QUEUES` CONTÉM OS REGISTROS DOS EMAILS A SEREM ENVIADOS E DOS QUE JÁ FORAM AGENDADOS E ENVIADOS
                // SALVA E ESPERA A DATA SELECIONADA PELO USUÁRIO PARA SER ENVIADO
                $sqlSave = "insert into mail_queues(user_id, company_id, email_type, periodicity, status, email_body_infos, api) "
                        . "values({$params['user_id']}, "
                        . "000,"
                        . "'companyInactivity',"
                        . "'{$period[0]['users_preferences']['notifications_periodicity']}',"
                        . "'WAITING',"
                        . " '{$body}', "
                        . "'companies');";
                $paramsSave = array('User' => array('query' => $sqlSave));
                $save = $this->urlRequestToGetData('users', 'query', $paramsSave);
                $retorno = "Email agendado com sucesso";
            }
        }

        return $retorno;
    }

    public function postEmailAutomatic($api, $type, $params) {
        $retorno = $this->curlRequest("http://localhost/sesteste/$api/$type.php", $params);
        return $retorno;
    }

    public function urlRequestToSendAndroidEmail($api, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/newUserAndroid';
        $apiUrl .= '/all';

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
            $apiUrl .= '/' . $this->generateSecureToken();

            // requisitando URL
            $data = $this->curlRequest($apiUrl, $params);
            return $this->decodeData($data);
        } else {
            return $this->paramsValidationStatus ['invalid_id'];
        }
    }

    public function urlRequestCompanyTesteEmail($api, $type, $params) {
        $apiUrl = API_ENVIRONMENT_URL . '/';
        $apiUrl .= $api . '/testeEmail';
        $apiUrl .= '/' . $type;

        if (!empty($params) && is_array($params)) {
            $params = json_encode($params);
            $params = array(
                'params' => $params
            );
        } else {
            return $this->paramsValidationStatus ['invalid_param'];
        }
        $apiUrl .= '/' . $this->generateSecureToken();

        // requisitando URL
        $data = $this->curlRequest($apiUrl, $params);
        return $this->decodeData($data);
    }
    
    public function urlRequestToSendNotification($params){
        
        $retorno = $this->curlRequest("http://localhost/PhpParse/index.php", $params);
        return $retorno;
        
    }

}

?>