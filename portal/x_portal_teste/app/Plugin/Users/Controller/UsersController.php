<?php

error_reporting(0);
App::uses('UsersAppController', 'Users.Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Vendor', 'facebook/facebook');
App::import('Vendor', 'barcode/barcode.inc');

/**
 * Users Controller
 *
 */
class UsersController extends UsersAppController {

    /**
     * Serviços de entrega do Correios 
     */
    public $servicePAC = '41106';
    public $serviceSEDEX = '40010';
    public $serviceSEDEXaCobrar = '40045';
    public $helpers = array('Crumb');

    /**
     * variaveis do facebook
     * @var array
     */
    protected $offerFilters = array(
        'gender' => array(),
        'religion' => array(),
        'political' => array(),
        'location' => array(),
        'age_group' => array(),
        'relationship_status' => array(),
    );

    /**
     * Exibe lista de ofertas disponiveis de acordo com parametros
     * DESEJOS, PERSONALIZADA, POR EMPRESA OU PUBLICAS
     * @return void
     */
    public function home() {

        //verifica se usuario esta vendo ofertas vindo de desejo 
        if ($this->Session->check('ofertasIds')) {
            //mostra oferta de desejos
            $data = date('Y-m-d');
            $limit = 5;
            $update = true;

            //total de ofertas
            $params = array('Offer' => array('conditions' => array('Offer.status' => 'ACTIVE', 'Offer.id' => $this->Session->read('ofertasIds')), 'order' => array('Offer.id' => 'DESC')));
            $contador = $this->Utility->urlRequestToGetData('offers', 'count', $params);


            //verifica se esta fazendo uma requisicao ajax
            if (!empty($this->request->data['limit'])) {
                $render = true;
                $this->layout = '';
                $limit = $_POST['limit'] + 2;
                if ($limit > $contador) {
                    $limit = $contador;
                    $update = false;
                }
            }

            $params = array('Offer' => array('conditions' => array('Offer.status' => 'ACTIVE', 'Offer.id' => $this->Session->read('ofertasIds')), 'order' => array('Offer.id' => 'DESC'), 'limit' => $limit));
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
        } else if ($this->Session->read('HomeOfertas') == 'personalizado') {

            $data = date('Y-m-d');
            $limit = 5;
            $update = true;

            //verifica se usuario esta vendo oferta personalizada
            if ($this->Session->check('Ofertas-Assinaturas')) {
                if ($this->Session->read('Ofertas-Assinaturas.hdAllCompany') == 1) {
                    //ofertas de empresa					
                    $params = array('Offer' => array('conditions' => array('Offer.company_id' => $this->Session->read('Ofertas-Assinaturas.hdIdCOmpany'), 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
                    $contador = $this->Utility->urlRequestToGetData('offers', 'count', $params);
                } else {
                    $params = $this->ofertas_perfil($this->Session->read('Ofertas-Assinaturas.hdIdCOmpany'));
                    $contador = count($params);
                }
            } else {
                $params = array('OffersUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data), 'order' => array('OffersUser.id' => 'DESC')), 'Offer');
                $contador = $this->Utility->urlRequestToGetData('offers', 'count', $params);
            }

            //verifica se esta fazendo uma requisicao ajax
            if (!empty($this->request->data['limit'])) {
                $render = true;
                $this->layout = '';
                $limit = $_POST['limit'] + 2;
                if ($limit > $contador) {
                    $limit = $contador;
                    $update = false;
                }
            }

            //verifica se usuario esta vendo oferta personalizada
            if ($this->Session->check('Ofertas-Assinaturas')) {

                if ($this->Session->read('Ofertas-Assinaturas.hdAllCompany') == 1) {
                    //ofertas de empresa
                    $params = array('Offer' => array('conditions' => array('Offer.company_id' => $this->Session->read('Ofertas-Assinaturas.hdIdCOmpany'), 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data), 'order' => array('Offer.id' => 'DESC'), 'limit' => $limit));
                    $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
                } else {
                    $offers = $this->ofertas_perfil($this->Session->read('Ofertas-Assinaturas.hdIdCOmpany'), true, $limit);
                }
            } else {
                $params = array('OffersUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data), 'order' => array('OffersUser.offer_id' => 'DESC'), 'limit' => $limit), 'Offer');
                $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
            }
        } else {
            //mostra oferta publica
            $data = date('Y-m-d');
            $limit = 5;
            $update = true;

            //total de ofertas
            $params = array('Offer' => array('conditions' => array('public' => 'ACTIVE', 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data), 'order' => array('Offer.id' => 'DESC')));
            $contador = $this->Utility->urlRequestToGetData('offers', 'count', $params);

            //verifica se esta fazendo uma requisicao ajax
            if (!empty($this->request->data['limit'])) {
                $render = true;
                $this->layout = '';
                $limit = $_POST['limit'] + 2;
                if ($limit > $contador) {
                    $limit = $contador;
                    $update = false;
                }
            }

            $params = array('Offer' => array('conditions' => array('public' => 'ACTIVE', 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data), 'order' => array('Offer.id' => 'DESC'), 'limit' => $limit));
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
        }

        //verifica quantidade de assinaturas			
        $params = array('CompaniesUser' => array('conditions' => array('CompaniesUser.user_id' => $this->Session->read('userData.User.id'), 'CompaniesUser.status' => 'ACTIVE')));
        $assinaturas = $this->Utility->urlRequestToGetData('companies', 'count', $params);
        if ($assinaturas < 5)
            $assinaturas = true;
        else
            $assinaturas = false;

        //verifica se perfil esta completo
        $completarPerfil = $this->verificaPerfil();

        $this->set(compact('offers', 'limit', 'contador', 'update', 'assinaturas', 'completarPerfil'));
        if (!empty($render))
            $this->render('Elements/ajax_ofertas');
    }

    /**
     * Exibe detalhes da oferta
     * @return void
     */
    public function viewOffer($offerId = null, $public = false) {
        $this->Session->write('id', $offerId);
        if ($offerId == null) {
            $this->redirect(array('controller' => 'users', 'action' => 'home', 'plugin' => 'users'));
        } else {
            //verifica se � requisicao ajax - trabalhando metricas
            if ($this->request->is('ajax')) {
                $this->Session->write('Carrinho.Opcoes.metricas.' . $this->request->data['chave'], $this->request->data['valor']);
                pr($_SESSION['Carrinho']);
                exit;
            }

            $params = array('Offer' => array('conditions' => array('Offer.id' => $offerId)), 'OffersComment', 'OffersPhoto', 'Company');
            $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);

            if (!empty($offer['OffersComment'])) {
                $i = 0;
                foreach ($offer['OffersComment'] as $comentario) {
                    $id_usuario = $comentario['user_id'];
                    $params = array('User' => array('fields' => array('User.name'), 'conditions' => array('User.id' => $id_usuario)));
                    $user = $this->Utility->urlRequestToGetData('users', 'first', $params);
                    $offer['OffersComment'][$i]['nome'] = $user['User']['name'];
                    $i++;
                }
            }

            //calculando dias que estao faltando para finalizar oferta
            $dataInicio = date('d/m/Y');
            $dataFinal = date('d/m/Y', strtotime($offer['Offer']['ends_at']));
            $dias = $this->Utility->calcData($dataInicio, $dataFinal);
            $offer['Offer']['dias'] = $dias;
        }
        //verifica se � oferta publica	
        if ($public == true) {
            $this->Session->write('Carrinho.public', true);
        }
        $title_for_layout = $offer['Offer']['title'];

        $this->set(compact('title_for_layout'));
        $this->set('offers', $offer);
    }

    public function cad_perfil($id_oferta = null) {
        $this->layout = 'login';

        $mensagem = null;
        if ($this->request->is('post')) {

            # valida campo em branco ou com placeholders			
            $placeholders = array('Nome', 'E-mail', 'Senha');
            foreach ($this->request->data['User'] as $field) {
                if (empty($field) || in_array($field, $placeholders)) {
                    $this->set('mensagem', 'Dados inválidos. Tente novamente.');
                    return;
                }
            }

            #valida campo email		
            $input = $this->request->data['User']['email'];
            if (!$this->validaEmail($input)) {
                $this->set('mensagem', 'Email inválido. Tente novamente.');
                return;
            }


            $params = array('User' => array('conditions' => array('User.email' => $this->request->data['User']['email'])));
            $verificaEmail = $this->Utility->urlRequestToGetData('users', 'count', $params);
            if ($verificaEmail == true) {
                $mensagem = "Esse email ja esta cadastrado no TRUEONE";
            } else {
                $params = $this->request->data;

                $params['User']['date_register'] = date('Y/m/d');
                $params['User']['status'] = 'ACTIVE';
                unset($params['id_oferta']);

                $user = $this->Utility->urlRequestToSaveData('users', $params);

                //cria sessao com dados do usuario				
                $this->Session->write('sessionLogado', true);
                $this->Session->write('userData', $user['data']);
                if ($user) {

                    unset($params['User']['date_register']);
                    unset($params['User']['status']);
                    //pr($params);exit;
                    //envia email de boas vindas
                    $user_email = $this->Utility->urlRequestToLoginUserData('users', 'first', $params);

                    if ($this->Session->check('offerIDBeforePurchasing')) { // tentou compra antes de logar (levamos o cara de volta)
                        $offerID = $this->Session->read('offerIDBeforePurchasing');
                        $this->Session->delete('offerIDBeforePurchasing');

                        $this->Session->write('Carrinho.AssinaEmpresa', true);
                        $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'detail_purchasing', $offerID));
                    } else {
                        $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'home'));
                    }
                } else {
                    $mensagem = "Ocorreu um erro no teu cadastro";
                }

                /*
                  if(!empty($this->request->data['id_oferta'])){
                  $this->Session->write('Carrinho.AssinaEmpresa', true);
                  $this->redirect(array('controller'=>'users', 'plugin'=>'users', 'action'=>'detail_purchasing', $this->request->data['id_oferta']));
                  }else{
                  $this->redirect(array('controller'=>'users', 'plugin'=>'users', 'action'=>'home'));
                  }
                 */
            }
        }
        $this->set(compact('mensagem', 'id_oferta'));
    }

    /**
     * Seleciona endereco para entrega do produto
     * @return void
     */
    public function shippingAddress($cep = null) {
        if ($this->request->is('post') || $this->Session->check('Carrinho')) {

            if (!empty($this->request->data['id_oferta'])) {
                $params = array('Offer' => array('conditions' => array('Offer.id' => $this->request->data['id_oferta'])), 'OffersComment', 'OffersPhoto');
                $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);
                $this->Session->write('Carrinho.Oferta', $offer['Offer']);
            }


            //verifica se calcula cep (ajax)
            if (isset($this->request->data['cep'])) {

                $render = true;
                $this->layout = '';
                $frete = $this->calculaFrete($this->request->data['cep']);
                if (!$frete == false) {
                    $frete = explode("-", $frete);
                    $frete[0] = str_replace(',', '.', $frete[0]);

                    $this->Session->write('Carrinho.Oferta.value_frete', $frete[0]);
                    $this->Session->write('Carrinho.Oferta.prazo_entrega', $frete[1]);

                    $valorFinal = $this->Session->read('Carrinho.Oferta.value_total') + $this->Session->read('Carrinho.Oferta.value_frete');
                    $this->Session->write('Carrinho.Oferta.value_final', $valorFinal);
                } else {
                    $this->Session->write('Carrinho.Oferta.value_frete', '');
                    $this->Session->write('Carrinho.Oferta.prazo_entrega', '');
                    $this->Session->write('Carrinho.Oferta.value_final', '');
                }
            } else {
                if (!empty($this->request->data)) {

                    if ($this->request->data['quantidade'] == 'quantidade') {
                        $this->request->data['quantidade'] = 1;
                    }
                }

                $this->Session->write('Carrinho.Opcoes', $this->request->data);
            }
        }
        if (!empty($render))
            $this->render('Elements/ajax_cep');
    }

    /**
     * Seleciona endereco para entrega do produto
     * @return void
     */
    public function purchaseDetails() {
        $erro = false;

        if ($this->request->is('post') || $this->Session->check('Carrinho')) {

            //numero do pedido
            $params = array('Checkout' => array('order' => array('Checkout.id' => 'DESC')));
            $checkout = $this->Utility->urlRequestToGetData('payments', 'first', $params);
            $ultimaCompra = $checkout['Checkout']['id'] + 1;

            //verifica se � requisao ajax
            if (isset($this->request->data['quantidade'])) {
                $this->Session->write('Carrinho.Opcoes.quantidade', $this->request->data['quantidade']);
                $render = true;
                $this->layout = '';
            } else {
                $this->Session->write('Carrinho.Endereco', $this->request->data);
            }
            if (!$this->Session->write('Carrinho.Oferta.value_frete') == '') {
                $frete = $this->calculaFrete($this->Session->read('Carrinho.Endereco.cep'));

                if ($frete == false) {
                    $erro = true;
                } else {
                    $frete = explode("-", $frete);
                    $frete[0] = str_replace(',', '.', $frete[0]);

                    $this->Session->write('Carrinho.Oferta.value_frete', $frete[0]);
                    $this->Session->write('Carrinho.Oferta.prazo_entrega', $frete[1]);

                    $valorFinal = $this->Session->read('Carrinho.Oferta.value_total') + $this->Session->read('Carrinho.Oferta.value_frete');
                    $this->Session->write('Carrinho.Oferta.value_final', $valorFinal);
                }
            }
        } else {
            $this->redirect(array('controller' => 'users', 'action' => 'home', 'plugin' => 'users'));
        }
        $this->set(compact('erro', 'ultimaCompra'));
        if (!empty($render))
            $this->render('Elements/ajax_quantidade');
    }

    /**
     * Pagamento da compra realizada
     * @return void
     */
    public function checkout_() {
        if ($this->Session->read('Carrinho.Oferta.value_frete') == '' || $this->Session->read('Carrinho.Opcoes.quantidade') == '') {
            $this->redirect(array('controller' => 'users', 'action' => 'purchaseDetails', 'plugin' => 'users'));
        }
        if ($this->Session->check('Carrinho')) {

            //verifica se oferta � publica			
            if ($this->Session->read('Carrinho.Oferta.public') == 'ACTIVE') {
                $params = array('CompaniesUser' => array('conditions' => array('CompaniesUser.company_id' => $this->Session->read('Carrinho.Oferta.company_id'), 'CompaniesUser.user_id' => $this->Session->read('userData.User.id'))));
                $countSignatures = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                //verifica se usuario ja esta assinado a empresa
                if (!$countSignatures == true) {
                    $params = array('CompaniesUser' => array('company_id' => $this->Session->read('Carrinho.Oferta.company_id'), 'user_id' => $this->Session->read('userData.User.id'), 'status' => 'ACTIVE', 'last_status' => 'ACTIVE', 'date_register' => date('Y/m/d')));
                    //assina usuario a empresa que fez oferta publica									
                    $signature = $this->Utility->urlRequestToSaveData('companies', $params);
                }
            }

            //criando array para insert de pagamentos							
            $arrayParams = array('Checkout' =>
                array(
                    'Checkout.user_id' => $this->Session->read('userData.User.id'),
                    'Checkout.company_id' => $this->Session->read('Carrinho.Oferta.company_id'),
                    'Checkout.payment_method_id' => 3,
                    'Checkout.offer_id' => $this->Session->read('Carrinho.Oferta.id'),
                    'Checkout.payment_state_id' => 14,
                    'Checkout.unit_value' => $this->Session->read('Carrinho.Oferta.value'),
                    'Checkout.total_value' => $this->Session->read('Carrinho.Oferta.value_total'),
                    'Checkout.amount' => $this->Session->read('Carrinho.Opcoes.quantidade'),
                    'Checkout.shipping_value' => $this->Session->read('Carrinho.Oferta.value_frete'),
                    'Checkout.shipping_type' => 'CORREIOS',
                    'Checkout.delivery_time' => $this->Session->read('Carrinho.Oferta.prazo_entrega'),
                    'Checkout.metrics' => 'teste',
                    'Checkout.address' => $this->Session->read('Carrinho.Endereco.endereco'),
                    'Checkout.city' => $this->Session->read('Carrinho.Endereco.cidade'),
                    'Checkout.zip_code' => $this->Session->read('Carrinho.Endereco.cep'),
                    'Checkout.state' => $this->Session->read('Carrinho.Endereco.uf'),
                    'Checkout.district' => $this->Session->read('Carrinho.Endereco.bairro'),
                    'Checkout.number' => $this->Session->read('Carrinho.Endereco.numero'),
                    'Checkout.date' => date('Y-m/d')
                )
            );
            $pagamento = $this->Utility->urlRequestToSaveData('payments', $arrayParams);


            //chamada do moip 
            $arrayParams = array('Payments' => array(
                    'parcelamento_juros' => 'ACTIVE',
                    'key' => 'SKMQ5HKQFTFRIFQBJEOROIGM70I6QVIN9KA5YIWB',
                    'token' => 'WOA4NBQ2AUMHJQ2NJIA6Q6X4ECXHFJUR',
                    'idUnique' => $pagamento['Checkout']['id'],
                    'reason' => $this->Session->read('Carrinho.Oferta.title'),
                    'value' => $this->Session->read('Carrinho.Oferta.value_final'),
                    'setPlayer' => array(
                        'name' => $this->Session->read('userData.User.name'),
                        'email' => $this->Session->read('userData.User.email'),
                        'payerId' => $this->Session->read('userData.User.id'),
                        'billingAddress' => array(
                            'address' => $this->Session->read('Carrinho.Endereco.endereco'),
                            'number' => $this->Session->read('Carrinho.Endereco.numero'),
                            'complement' => '',
                            'city' => $this->Session->read('Carrinho.Endereco.cidade'),
                            'neighborhood' => $this->Session->read('Carrinho.Endereco.bairro'),
                            'state' => $this->Session->read('Carrinho.Endereco.uf'),
                            'country' => 'BRA',
                            'zipCode' => $this->Session->read('Carrinho.Endereco.cep'),
                            'phone' => '(11)9950-0989'
                        )
                    )
                )
            );

            $token = $this->Utility->urlRequestToCheckout('payments', $arrayParams);
            $this->set('token', $token);
        }
    }

    /**
     * Compras realizadas pelo usuario
     * Insert de comentarios
     * @return void
     */
    public function minhasCompras() {
        $update = true;

        //verifica se � requisicao ajax
        if (!empty($this->request->data['id_oferta'])) {



            //recebe linha do comentario para update e id da oferta
            $idComentario = $this->request->data['id_comentario'];
            $idOferta = $this->request->data['id_oferta'];

            if ($idComentario == 0) {
                $arrayParams = array(
                    'OffersComment' => array(
                        'offer_id' => $idOferta,
                        'user_id' => $this->Session->read('userData.User.id'),
                        'title' => $this->request->data['opiniao'],
                        'description' => $this->request->data['textarea'],
                        'evaluation' => '-',
                        'date_register' => date('Y/m/d'),
                        'status' => 'INACTIVE'
                    ),
                );
            } else {
                $arrayParams = array(
                    'OffersComment' => array(
                        'id' => $idComentario,
                        'title' => $this->request->data['opiniao'],
                        'description' => $this->request->data['textarea'],
                        'evaluation' => '-'
                    ),
                );
            }
            $updateStatus = $this->Utility->urlRequestToSaveData('offers', $arrayParams);
        }


        //contador
        $limit = 3;
        $params = array('Checkout' => array('conditions' => array('Checkout.user_id' => $this->Session->read('userData.User.id'), 'NOT' => array('Checkout.payment_state_id' => array('999', '14')))));
        $contador = $this->Utility->urlRequestToGetData('payments', 'count', $params);

        //verifica se esta fazendo uma requisicao ajax
        if (!empty($this->request->data['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST['limit'] + 4;
            if ($limit > $contador) {
                $limit = $contador;

                //nao chama mais atualizacao	
                $update = false;
            }
        }

        $params = array('Checkout' => array('conditions' => array('Checkout.user_id' => $this->Session->read('userData.User.id'), 'NOT' => array('Checkout.payment_state_id' => array('999', '14'))), 'order' => array('Checkout.id' => 'DESC'), 'limit' => $limit), 'Offer', 'PaymentState', 'PaymentMethod', 'Company');
        $checkouts = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        if (is_array($checkouts)) {
            foreach ($checkouts as $checkout) {

                //verificando se usuario ja comentou a oferta
                $idOferta = $checkout['Checkout']['offer_id'];
                $idEmpresa = $checkout['Checkout']['company_id'];

                $params = array('OffersComment' => array('conditions' => array('OffersComment.offer_id' => $idOferta, 'OffersComment.user_id' => $this->Session->read('userData.User.id'))));
                $comentario = $this->Utility->urlRequestToGetData('offers', 'first', $params);

                $params = array('Company' => array('conditions' => array('Company.id' => $idEmpresa)));
                $empresa = $this->Utility->urlRequestToGetData('companies', 'first', $params);

                if (!$comentario == true)
                    $comentario = false;
                $checkout['Checkout']['comment'] = $comentario;
                $checkout['Company'] = $empresa['Company'];
                $checks[] = $checkout;
            }
        }else {
            $checks = "NENHUMA COMPRA OU PEDIDO ENCONTRADO";
        }


        $this->set(compact('checks', 'limit', 'contador', 'update'));
        if (!empty($render))
            $this->render('Elements/ajax_compras');
    }

    /**
     * Assinaturas realizadas pelo usuario	
     * @return void
     */
    public function signatures() {

        //total de empresas
        $params = array('CompaniesUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'Company.status' => 'ACTIVE', 'CompaniesUser.status' => 'ACTIVE'), 'order' => array('CompaniesUser.id' => 'DESC')), 'Company');
        $contador = $this->Utility->urlRequestToGetData('companies', 'count', $params);


        //verifica se � mudanca de status
        if (!empty($this->request->data['update']) == 'true') {

            //update - linha existe
            if ($this->request->data['up'] == 0) {
                $arrayParams = array(
                    'CompaniesUser' => array(
                        'id' => $this->request->data['id'],
                        'status' => $this->request->data['status']
                    ),
                );
                //insert - linha nao existe
            } else {
                $arrayParams = array(
                    'CompaniesUser' => array(
                        'user_id' => $this->Session->read('userData.User.id'),
                        'company_id' => $this->request->data['id'],
                        'status' => 'ACTIVE',
                        'date_register' => date('Y/m/d'),
                        'status' => 'ACTIVE'
                    ),
                );
                $data = date('Y/m/d');
                $offersCompany = $this->ofertas_perfil($this->request->data['id'], null, null, true);
                foreach ($offersCompany as $offerSignature) {
                    $offerId = $offerSignature['Offer']['id'];
                    $params = array('OffersUser' => array('conditions' => array('OffersUser.user_id' => $this->Session->read('userData.User.id'), 'offer_id' => $offerId)));
                    $oferta = $this->Utility->urlRequestToGetData('users', 'count', $params);

                    if (!$oferta > 0) {
                        $query = "INSERT INTO offers_users values(NULL, '{$offerId}', '{$this->Session->read('userData.User.id')}', '{$data}', 'facebook - portal')";
                        $params = array('User' => array('query' => $query));
                        $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    }
                }
            }

            $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
            //pr($updateStatus);exit;								
        }
        //verifica se esta fazendo uma requisicao ajax para pesquisa
        if (!empty($this->request->data['ref']) == 'true') {
            $render = true;
            $this->layout = '';
            $busca = $this->request->data['pesquisa'];
            $params = array('Company' => array('conditions' => array('Company.status' => 'ACTIVE', 'Company.fancy_name LIKE' => "%{$busca}%"), 'order' => array('Company.id' => 'DESC')));
            $companiesA = $this->Utility->urlRequestToGetData('companies', 'all', $params);
            if ($companiesA) {
                foreach ($companiesA as $company) {
                    $params = array('CompaniesUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'company_id' => $company['Company']['id'], 'Company.status' => 'ACTIVE'), 'order' => array('CompaniesUser.id' => 'DESC')), 'Company');
                    $companiesUser = $this->Utility->urlRequestToGetData('companies', 'first', $params);
                    if ($companiesUser) {
                        $companies[] = $companiesUser;
                    } else {
                        $companies[] = $company;
                    }
                }
            } else {
                $companies = "NENHUMA EMPRESA ENCONTRADA";
            }
        } else {
            $data = date('Y/m/d');
            $params = array('CompaniesUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'Company.status' => 'ACTIVE', 'CompaniesUser.status' => 'ACTIVE'), 'order' => array('CompaniesUser.id' => 'DESC')), 'Company');
            $companiesUser = $this->Utility->urlRequestToGetData('companies', 'all', $params);

            //pegando quantidade de ofertas de empresa total e de acordo com perfil
            foreach ($companiesUser as $company) {
                //quantidade de ofertas de empresa
                $params = array('Offer' => array('conditions' => array('Offer.company_id' => $company['Company']['id'], 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
                $ofertas = $this->Utility->urlRequestToGetData('offers', 'count', $params);
                $company['Company']['ofertas'] = $ofertas;

                //pegando ofertas de acordo com perfil
                $ofertas_perfil = $this->ofertas_perfil($company['Company']['id']);
                $quantidade_perfil = count($ofertas_perfil);
                $company['Company']['ofertas_perfil'] = $quantidade_perfil;
                $companies[] = $company;
            }
        }

        $this->set(compact('companies', 'limit', 'contador'));
        if (!empty($render))
            $this->render('Elements/ajax_assinaturas');
    }

    /**
     * Efetua login de usuario no portal
     * @return void
     */
    public function login() {
        $this->layout = 'login';

        $facebook = new Facebook(
                array(
            'appId' => '447466838647107',
            'secret' => 'b432e357dd19491aa8d45acd7074b2f6',
                )
        );

        // obtem o id do usuario
        $user = $facebook->getUser();

        if ($user) { // usuario logado
            try {
                // Obtem dados do usuario logado
                $user_profile = $facebook->api('/me');
                $photo = "https://graph.facebook.com/$user/picture";
                //pr($photo);exit;	        		        
                //verifica se usuario ja existe no trueone
                $params = array('FacebookProfile' => array('conditions' => array('FacebookProfile.facebook_id' => $user)), 'User');
                $facebookProfile = $this->Utility->urlRequestToGetData('users', 'first', $params);

                if (is_array($facebookProfile)) {
                    //verifica se precisa fazer atualizacao na tabela facebook_profiles		       		
                    if ($facebookProfile['FacebookProfile']['updated_time'] < date('Y/m/d h:i:s', strtotime($user_profile['updated_time']))) {
                        if (empty($user_profile['relationship_status']))
                            $user_profile['relationship_status'] = '';
                        if (empty($user_profile['religion']))
                            $user_profile['religion'] = '';
                        if (empty($user_profile['political']))
                            $user_profile['political'] = '';
                        if (empty($user_profile['updated_time']))
                            $user_profile['updated_time'] = '';
                        if (empty($user_profile['location']))
                            $user_profile['location']['name'] = '';
                        $params = array('User' => array('id' => $facebookProfile['FacebookProfile']['user_id']),
                            'FacebookProfile' => array(
                                'id' => $facebookProfile['FacebookProfile']['id'],
                                'facebook_id' => $user,
                                'user_id' => $facebookProfile['FacebookProfile']['user_id'],
                                'first_name' => $user_profile['name'],
                                'last_name' => $user_profile['last_name'],
                                'email' => $user_profile['email'],
                                'gender' => $user_profile['gender'],
                                'profile_link' => $user_profile['link'],
                                'birthday' => date('Y/m/d', strtotime($user_profile['birthday'])),
                                'location' => $user_profile['location']['name'],
                                'relationship_status' => $user_profile['relationship_status'],
                                'religion' => $user_profile['religion'],
                                'political' => $user_profile['political'],
                                'updated_time' => $user_profile['updated_time']
                            )
                        );

                        $updateUsuario = $this->Utility->urlRequestToSaveData('users', $params);

                        //cria sessao com dados da empresa				
                        $this->Session->write('sessionLogado', true);
                        $this->Session->write('userData', $updateUsuario['data']);

                        if ($this->Session->check('offerIDBeforePurchasing')) { // tentou compra antes de logar (levamos o cara de volta)
                            $offerID = $this->Session->read('offerIDBeforePurchasing');
                            $this->Session->delete('offerIDBeforePurchasing');

                            $this->Session->write('Carrinho.AssinaEmpresa', true);
                            $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'detail_purchasing', $offerID));
                        } else {
                            $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'home'));
                        }
                    } else {

                        //cria sessao com dados da empresa				
                        $this->Session->write('sessionLogado', true);
                        $this->Session->write('userData', $facebookProfile);

                        if ($this->Session->check('offerIDBeforePurchasing')) { // tentou compra antes de logar (levamos o cara de volta)
                            $offerID = $this->Session->read('offerIDBeforePurchasing');
                            $this->Session->delete('offerIDBeforePurchasing');

                            $this->Session->write('Carrinho.AssinaEmpresa', true);
                            $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'detail_purchasing', $offerID));
                        } else {
                            $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'home'));
                        }
                    }
                } else {
                    if (empty($user_profile['relationship_status']))
                        $user_profile['relationship_status'] = '';
                    if (empty($user_profile['religion']))
                        $user_profile['religion'] = '';
                    if (empty($user_profile['political']))
                        $user_profile['political'] = '';
                    if (empty($user_profile['updated_time']))
                        $user_profile['updated_time'] = '';
                    if (empty($user_profile['location']))
                        $user_profile['location']['name'] = '';

                    $params = array(
                        'User' => array(
                            'name' => $user_profile['name'],
                            'password' => $user_profile['email'],
                            'email' => $user_profile['email'],
                            'gender' => $user_profile['gender'],
                            'birthday' => date('Y/m/d', strtotime($user_profile['birthday'])),
                            'photo' => $photo,
                            'status' => 'ACTIVE',
                            'date_register' => date('Y/m/d')
                        ),
                        'FacebookProfile' => array(
                            'facebook_id' => $user,
                            'name' => $user_profile['name'],
                            'first_name' => $user_profile['first_name'],
                            'last_name' => $user_profile['last_name'],
                            'email' => $user_profile['email'],
                            'gender' => $user_profile['gender'],
                            'profile_link' => $user_profile['link'],
                            'birthday' => date('Y/m/d', strtotime($user_profile['birthday'])),
                            'location' => $user_profile['location']['name'],
                            'relationship_status' => $user_profile['relationship_status'],
                            'religion' => $user_profile['religion'],
                            'political' => $user_profile['political'],
                            'updated_time' => $user_profile['updated_time']
                        )
                    );

                    $insertUsuario = $this->Utility->urlRequestToSaveData('users', $params);

                    //cria sessao com dados da empresa				
                    $this->Session->write('sessionLogado', true);
                    $this->Session->write('userData', $insertUsuario['data']);

                    if ($this->Session->check('offerIDBeforePurchasing')) { // tentou compra antes de logar (levamos o cara de volta)
                        $offerID = $this->Session->read('offerIDBeforePurchasing');
                        $this->Session->delete('offerIDBeforePurchasing');

                        $this->Session->write('Carrinho.AssinaEmpresa', true);
                        $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'detail_purchasing', $offerID));
                    } else {
                        $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'home'));
                    }
                }
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        } else {
            $params = array(
                'scope' => 'email, user_about_me, user_birthday, user_hometown, user_location, user_relationships, user_religion_politics'
            );
            $loginUrl = $facebook->getLoginUrl($params);
        }

        if ($this->request->is('post')) {
            if (!empty($this->request->data['Company']['emailPass'])) {

                $array['User']['status'] = 'ACTIVE';
                $array['User']['email'] = $this->request->data['User']['emailPass'];
                $resultado = $this->Utility->urlRequestToGetData('users', 'first', $array);

                if ($resultado) {
                    $senha = time();
                    $arraySenha = array('User' => array('id' => $resultado['User']['id'], 'password' => $senha));

                    $save = $this->Utility->urlRequestToSaveData('users', $arraySenha);
                    $mensagem = "Sua nova senha {$senha}";

                    /*
                     * TESTAR TESTAR TESTAR TESTAR 
                     * TESTAR TESTAR TESTAR TESTAR
                     */
//                    $this->Email->from = 'wilanetonet@ig.com.br';
//                    $this->Email->to = 'wca_junior2@ig.com.br';
//                    $this->Email->subject = 'Nova senha do teu cadastro';
//                    $this->Email->send($mensagem);
                }
            } else {

                $array = $this->request->data['User'];

                //tratando array para fazer login corretamente com condicoes							
                $array['status'] = 'ACTIVE';
                $array['password'] = $this->securityForm($array['password']);
                $array['email'] = $this->securityForm($array['email']);
                $array['password'] = md5($array['password']);

                //arruma ARRAY de acordo com FIND do cakephp
                $array = array('User' => array('conditions' => $array));
                $resultado = $this->Utility->urlRequestToGetData('users', 'first', $array);

                if (is_array($resultado)) {
                    if (!empty($resultado['User']['password'])) {
                        if ($resultado['User']['password'] == md5($this->request->data['User']['password'])) {

                            //cria sessao com dados da empresa				
                            $this->Session->write('sessionLogado', true);
                            $this->Session->write('userData', $resultado);

                            if ($this->Session->check('offerIDBeforePurchasing')) { // tentou compra antes de logar (levamos o cara de volta)
                                $offerID = $this->Session->read('offerIDBeforePurchasing');
                                $this->Session->delete('offerIDBeforePurchasing');

                                $this->Session->write('Carrinho.AssinaEmpresa', true);
                                $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'detail_purchasing', $offerID));
                            } else {
                                $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'home'));
                            }
                        } else {
                            $this->Session->setFlash('Dados inválidos. Tente novamente.');
                        }
                    } else {
                        $this->Session->setFlash('Dados inválidos. Tente novamente.');
                    }
                } else {
                    $this->Session->setFlash('Dados inválidos. Tente novamente.');
                }
            }
        }

        $this->set(compact('loginUrl'));
    }

    public function logoof() {
        session_destroy();
        $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'testa'));
    }

    public function detail_purchasing($id = null) {
        if (!$this->Session->check('userData.User')) {
            //$this->redirect(array('controller'=>'users', 'plugin'=>'users', 'action'=>'cad_perfil', $this->request->data['id_oferta']));
            # redirecionar para login, n�o para cadastro de perfil.
            $this->Session->write('offerIDBeforePurchasing', $this->request->data['id_oferta']);
            $this->redirect(array('controller' => 'users', 'plugin' => 'users', 'action' => 'login'));
        }
        if (!$this->Session->check('access')) {
            if (!empty($id)) {
                $this->request->data['id_oferta'] = $id;
                $this->Session->write('access', true);
            }
        }
        //session_destroy();exit;				
        $one = false;
        $frete = false;
        $erro = false;
        $render = false;
        $token = 0;

        //verifica se requisicao � valida
        if ($this->Session->check('Carrinho') || !empty($this->request->data['id_oferta'])) {

            //verifica se � requisicao ajax
            if ($this->request->is('ajax')) {

                $render = true;
                $this->layout = '';

                //ajax selecionando endereco
                if (!empty($this->request->data['excluir_endereco'])) {
                    $query = "DELETE FROM aditional_addresses_users WHERE id = '{$this->request->data['id_endereco']}'";
                    $params = array('User' => array('query' => $query));
                    $delEnd = $this->Utility->urlRequestToGetData('users', 'query', $params);
                }
                //ajax selecionando endereco
                if (!empty($this->request->data['seleciona_endereco'])) {
                    $params = array('AditionalAddressesUser' => array('conditions' => array('id' => $this->request->data['id_endereco'])));
                    $endereco = $this->Utility->urlRequestToGetData('users', 'first', $params);

                    $this->Session->write('Carrinho.Endereco.id', $endereco['AditionalAddressesUser']['id']);
                    $this->Session->write('Carrinho.Endereco.endereco', $endereco['AditionalAddressesUser']['address']);
                    $this->Session->write('Carrinho.Endereco.cidade', $endereco['AditionalAddressesUser']['city']);
                    $this->Session->write('Carrinho.Endereco.cep', $endereco['AditionalAddressesUser']['zip_code']);
                    $this->Session->write('Carrinho.Endereco.estado', $endereco['AditionalAddressesUser']['state']);
                    $this->Session->write('Carrinho.Endereco.bairro', $endereco['AditionalAddressesUser']['district']);
                    $this->Session->write('Carrinho.Endereco.complemento', $endereco['AditionalAddressesUser']['complement']);
                    $this->Session->write('Carrinho.Endereco.numero', $endereco['AditionalAddressesUser']['number']);
                    $this->Session->write('Carrinho.Endereco.descricao', $endereco['AditionalAddressesUser']['label']);
                }
                //ajax add enderecos
                if (!empty($this->request->data['add_endereco'])) {

                    //verifica se usuario ja tem endereco cadastrado no perfil
                    $params = array('User' => array('conditions' => array('User.id' => $this->Session->read('userData.User.id'))));
                    $verificaEndUser = $this->Utility->urlRequestToGetData('users', 'first', $params);

                    if (empty($verificaEndUser['User']['zip_code'])) {
                        $params = array('User' => array('id' => $this->Session->read('userData.User.id'), 'zip_code' => $this->Session->read('Carrinho.Endereco.cep'), 'address' => $this->Session->read('Carrinho.Endereco.endereco'), 'complement' => $this->Session->read('Carrinho.Endereco.complemento'), 'district' => $this->Session->read('Carrinho.Endereco.bairro'), 'city' => $this->Session->read('Carrinho.Endereco.cidade'), 'state' => $this->Session->read('Carrinho.Endereco.estado'), 'number' => $this->Session->read('Carrinho.Endereco.numero')));
                        //executa acao de insert ou update															
                        $endUser = $this->Utility->urlRequestToSaveData('users', $params);
                    }
                    //verificando se endereco ja existe
                    $params = array('AditionalAddressesUser' => array('conditions' => array('zip_code' => $this->Session->read('Carrinho.Endereco.cep'), 'number' => $this->Session->read('Carrinho.Endereco.numero'), 'complement' => $this->Session->read('Carrinho.Endereco.complemento')), 'fields' => array('id')));
                    $verEnd = $this->Utility->urlRequestToGetData('users', 'first', $params);

                    //verifica se vai editar ou fazer insert de endereco
                    if (is_array($verEnd)) {
                        $params = array('AditionalAddressesUser' =>
                            array('id' => $verEnd['AditionalAddressesUser']['id'],
                                'user_id' => $this->Session->read('userData.User.id'),
                                'label' => $this->Session->read('Carrinho.Endereco.descricao'),
                                'address' => $this->Session->read('Carrinho.Endereco.endereco'),
                                'complement' => $this->Session->read('Carrinho.Endereco.complemento'),
                                'district' => $this->Session->read('Carrinho.Endereco.bairro'),
                                'city' => $this->Session->read('Carrinho.Endereco.cidade'),
                                'state' => $this->Session->read('Carrinho.Endereco.estado')
                            )
                        );
                    } else {
                        $query = "INSERT INTO aditional_addresses_users 
												VALUES(NULL, 
													  '{$this->Session->read('userData.User.id')}', 
													  '{$this->Session->read('Carrinho.Endereco.descricao')}', 
													  '{$this->Session->read('Carrinho.Endereco.endereco')}', 
													  '{$this->Session->read('Carrinho.Endereco.numero')}', 
													  '{$this->Session->read('Carrinho.Endereco.complemento')}', 
													  '{$this->Session->read('Carrinho.Endereco.bairro')}',
													  '{$this->Session->read('Carrinho.Endereco.cidade')}',
													  '{$this->Session->read('Carrinho.Endereco.estado')}',
													  '{$this->Session->read('Carrinho.Endereco.cep')}')";

                        $params = array('User' => array('query' => $query));
                    }
                    //executa acao de insert ou update					
                    $ends = $this->Utility->urlRequestToGetData('users', 'query', $params);
                }
                //ajax metricas
                if (!empty($this->request->data['chave'])) {
                    //$this->Session->write("Carrinho.Opcoes.metricas.{$this->request->data['chave']}",  $this->request->data['valor']);
                    if (isset($_SESSION['Carrinho']['Opcoes']['metricas'][$this->request->data['chave']])) {
                        $_SESSION['Carrinho']['Opcoes']['metricas'][$this->request->data['chave']] = $this->request->data['valor'];
                    }
                    exit;
                }
                //ajax quantidade
                if (!empty($this->request->data['quantidade'])) {
                    $this->Session->write('Carrinho.Opcoes.quantidade', $this->request->data['quantidade']);
                    $frete = $this->calculaFrete($this->Session->read('Carrinho.Endereco.cep'));
                }
                //ajax numero
                if (!empty($this->request->data['numero'])) {
                    $this->Session->write('Carrinho.Endereco.numero', $this->request->data['numero']);
                    exit;
                }
                //ajax endereco
                if (!empty($this->request->data['endereco'])) {
                    $this->Session->write('Carrinho.Endereco.endereco', $this->request->data['endereco']);
                    exit;
                }
                //ajax bairro
                if (!empty($this->request->data['bairro'])) {
                    $this->Session->write('Carrinho.Endereco.bairro', $this->request->data['bairro']);
                    exit;
                }
                //ajax cidade
                if (!empty($this->request->data['cidade'])) {
                    $this->Session->write('Carrinho.Endereco.cidade', $this->request->data['cidade']);
                    exit;
                }
                //ajax estado
                if (!empty($this->request->data['estado'])) {
                    $this->Session->write('Carrinho.Endereco.estado', $this->request->data['estado']);
                    exit;
                }
                //ajax descricao
                if (!empty($this->request->data['descricao'])) {
                    $this->Session->write('Carrinho.Endereco.descricao', $this->request->data['descricao']);
                    exit;
                }
                //ajax complemento
                if (!empty($this->request->data['complemento'])) {
                    $this->Session->write('Carrinho.Endereco.complemento', $this->request->data['complemento']);
                    exit;
                }
                //ajax para definir tipo de pagamento da compra realizada
                if (!empty($this->request->data['tipo_pagamento'])) {
                    $this->Checkout(false, false, true);
                    exit;
                }
                //ajax cep
                if (!empty($this->request->data['cep'])) {
                    $cep = str_replace('-', '', $this->request->data['cep']);
                    $frete = $this->calculaFrete($this->request->data['cep']);

                    if (!$frete == false) {
                        $cURL = curl_init("http://cep.correiocontrol.com.br/{$cep}.json");
                        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                        $resultado = curl_exec($cURL);
                        curl_close($cURL);

                        //pegando endereco
                        $endereco = json_decode($resultado, true);
                        if (is_array($endereco)) {
                            $this->Session->write('Carrinho.Endereco.endereco', $endereco['logradouro']);
                            $this->Session->write('Carrinho.Endereco.cidade', $endereco['localidade']);
                            $this->Session->write('Carrinho.Endereco.cep', $endereco['cep']);
                            $this->Session->write('Carrinho.Endereco.estado', $endereco['uf']);
                            $this->Session->write('Carrinho.Endereco.bairro', $endereco['bairro']);
                            $this->Session->write('Carrinho.Endereco.complemento', '');
                            $this->Session->write('Carrinho.Endereco.numero', '');
                        } else {
                            $erro = true;
                        }
                    } else {
                        
                    }
                }
                //ajax finalizando compra e retornando token
                if (!empty($this->request->data['compra'])) {
                    $render = false;
                    $renderToken = true;
                    $token = $this->Checkout(false, true);
                    // vepr($token);exit;
                    echo $token['token'];
                    exit;
                    //$this->set('token', $token['token']);
                    //echo "<input type=\"button\" id=\"boleto\" class=\"btn\" value=\"Clique para gerar o boleto bancário\" onClick=\"window.open('https://desenvolvedor.moip.com.br/sandbox/Instrucao.do?token={$token['token']}')\">";
                    //if(!empty($render))$this->render('Elements/ajax_token');
                    //exit;								
                }
            } else {

                //verifica se � a primeira vez que esta sendo acessada para criacao de sessao carrinho
                if (!empty($this->request->data['id_oferta'])) {

                    //primeiro acesso
                    $one = true;
                    $params = array('Offer' => array('conditions' => array('Offer.id' => $this->request->data['id_oferta'])), 'OffersComment', 'OffersPhoto');
                    $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);
                    $this->Session->write('Carrinho.Oferta', $offer['Offer']);
                    if ($offer['Offer']['percentage_discount'] > 0) {
                        $valor = ($offer['Offer']['value'] * (100 - $offer['Offer']['percentage_discount'])) / 100;
                        $this->Session->write('Carrinho.Oferta.value', $valor);
                    }

                    $this->Session->write('Carrinho.Opcoes.quantidade', '1');
                    $total = $this->Session->read('Carrinho.Opcoes.quantidade') * $this->Session->read('Carrinho.Oferta.value');
                    //$total  = number_format($total,2,',','.');
                    $this->Session->write('Carrinho.Oferta.value_total', $total);


                    //trabalhando metricas
                    if (!empty($offer['Offer']['metrics'])) {
                        $metricas = json_decode($offer['Offer']['metrics'], true);
                        $chaves = array_keys($metricas);
                        foreach ($chaves as $chave) {
                            if (empty($_SESSION['Carrinho']['Opcoes']['metricas'][$chave])) {
                                $this->Session->write('Carrinho.Opcoes.metricas.' . $chave, '');
                            }
                        }
                    }

                    //verifica se usuario ja tem CEP cadastrado - cria sessao e calcula frete
                    if (!$this->Session->read('userData.User.zip_code') == false) {
                        $frete = $this->calculaFrete($this->Session->read('userData.User.zip_code'));

                        //pega dados do endereco					
                        $this->Session->write('Carrinho.Endereco.endereco', $this->Session->read('userData.User.address'));
                        $this->Session->write('Carrinho.Endereco.cidade', $this->Session->read('userData.User.city'));
                        $this->Session->write('Carrinho.Endereco.cep', $this->Session->read('userData.User.zip_code'));
                        $this->Session->write('Carrinho.Endereco.estado', $this->Session->read('userData.User.state'));
                        $this->Session->write('Carrinho.Endereco.bairro', $this->Session->read('userData.User.district'));
                        $this->Session->write('Carrinho.Endereco.numero', $this->Session->read('userData.User.number'));
                        $this->Session->write('Carrinho.Endereco.descricao', 'Endereco cadastrado no PERFIL');
                    }
                    //vai no checkout para pegar id do pedido			
                    $id_pedido = $this->Checkout(true);
                    $this->Session->write('Carrinho.Oferta.id_pedido', $id_pedido);
                }
            }

            //calculando frete se for necessario
            if (!$this->Session->read('Carrinho.Endereco.cep') == false && $erro == false) {
                $frete = $this->calculaFrete($this->Session->read('Carrinho.Endereco.cep'));
            }

            //verifica se precisa tratar cep 
            if (!$frete == false) {
                $frete = explode("-", $frete);
                $frete[0] = str_replace(',', '.', $frete[0]);

                $this->Session->write('Carrinho.Oferta.value_frete', $frete[0]);
                $this->Session->write('Carrinho.Oferta.prazo_entrega', $frete[1]);

                $valorFinal = $this->Session->read('Carrinho.Oferta.value_total') + $this->Session->read('Carrinho.Oferta.value_frete');

                $this->Session->write('Carrinho.Oferta.value_final', $valorFinal);
            } else {
                $this->Session->write('Carrinho.Oferta.value_frete', '');
                $this->Session->write('Carrinho.Oferta.prazo_entrega', '');
                $this->Session->write('Carrinho.Oferta.value_final', '');
                if ($one == false)
                    $erro = true;
            }
        }

        //pegando enderecos do dito cujo
        $params = array('AditionalAddressesUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'))));

        $enderecos = $this->Utility->urlRequestToGetData('users', 'all', $params);

        //verifica se empresa optou por frete fixo
        $params = array('CompanyPreference' => array('fields' => array('CompanyPreference.use_correios_api', 'CompanyPreference.delivery_time', 'CompanyPreference.shipping_value'), 'conditions' => array('CompanyPreference.company_id' => $this->Session->read('Carrinho.Oferta.company_id'))));
        $companyPreference = $this->Utility->urlRequestToGetData('companies', 'first', $params);
        if (is_array($companyPreference)) {
            if ($companyPreference['CompanyPreference']['use_correios_api'] != 1) {
                $this->Session->write('Carrinho.Oferta.value_frete', $companyPreference['CompanyPreference']['shipping_value']);
                $this->Session->write('Carrinho.Oferta.prazo_entrega', $companyPreference['CompanyPreference']['delivery_time']);
                $valueTotal = $this->Session->read('Carrinho.Oferta.value_total') + $this->Session->read('Carrinho.Oferta.value_frete');
                $this->Session->write('Carrinho.Oferta.value_final', $valueTotal);
            }
        }
        //pr($_SESSION['Carrinho']['Oferta']);exit;
        $this->set(compact('erro', 'token', 'enderecos'));
        if (!empty($render))
            $this->render('Elements/ajax_detail_purchasing');
    }

    /**
     * Executa funcao de checkout 
     * retornando id da compra e token
     * @return string
     */
    private function Checkout($requisition = false, $token = false, $pagamento = false) {
        if ($requisition == true) {
            //gerando numero do pedido			
            $arrayParams = array('Checkout' =>
                array(
                    'Checkout.user_id' => $this->Session->read('userData.User.id'),
                    'Checkout.company_id' => $this->Session->read('Carrinho.Oferta.company_id'),
                    'Checkout.payment_method_id' => 3,
                    'Checkout.offer_id' => $this->Session->read('Carrinho.Oferta.id'),
                    'Checkout.payment_state_id' => 14,
                    'Checkout.unit_value' => '0',
                    'Checkout.total_value' => '0',
                    'Checkout.amount' => '0',
                    'Checkout.shipping_value' => '0',
                    'Checkout.shipping_type' => 'CORREIOS',
                    'Checkout.delivery_time' => '',
                    'Checkout.metrics' => '',
                    'Checkout.address' => '',
                    'Checkout.city' => '',
                    'Checkout.zip_code' => '',
                    'Checkout.state' => '',
                    'Checkout.district' => '',
                    'Checkout.number' => '',
                    'Checkout.complement' => '',
                    'Checkout.date' => date('Y-m/d'),
                    'Checkout.transaction_moip_code' => 0,
                    'Checkout.installment' => 0
                )
            );
            $pagamento = $this->Utility->urlRequestToSaveData('payments', $arrayParams);
            $dados = $pagamento['Checkout']['id'];
        } else if ($token == true) {

            //trabalhando metricas
            if ($this->Session->check('Carrinho.Opcoes.metricas')) {
                $metricas = json_encode($this->Session->read('Carrinho.Opcoes.metricas'));
            } else {
                $metricas = '';
            }


            //verifica se usuario ja � assinante da empresa que fez a oferta										
            $params = array('CompaniesUser' => array('conditions' => array('CompaniesUser.company_id' => $this->Session->read('Carrinho.Oferta.company_id'), 'CompaniesUser.user_id' => $this->Session->read('userData.User.id'))));
            $countSignatures = $this->Utility->urlRequestToGetData('companies', 'count', $params);

            //verifica se usuario ja esta assinado a empresa
            if (!$countSignatures == true) {
                $params = array('CompaniesUser' => array('company_id' => $this->Session->read('Carrinho.Oferta.company_id'), 'user_id' => $this->Session->read('userData.User.id'), 'status' => 'ACTIVE', 'last_status' => 'ACTIVE', 'date_register' => date('Y/m/d')));
                //assina usuario a empresa que fez oferta publica									
                $signature = $this->Utility->urlRequestToSaveData('companies', $params);

                //pegando ofertas de empresa assinada e jogando para usuario de acordo com o perfil
                $offersCompany = $this->ofertas_perfil($this->Session->read('Carrinho.Oferta.company_id'), null, null, true);
                foreach ($offersCompany as $offerSignature) {
                    $offerId = $offerSignature['Offer']['id'];
                    $params = array('OffersUser' => array('conditions' => array('OffersUser.user_id' => $this->Session->read('userData.User.id'), 'offer_id' => $offerId)));
                    $oferta = $this->Utility->urlRequestToGetData('users', 'count', $params);

                    if (!$oferta > 0) {
                        $data = date('Y/m/d');
                        $query = "INSERT INTO offers_users values(NULL, '{$offerId}', '{$this->Session->read('userData.User.id')}', '{$data}', 'facebook - portal')";
                        $params = array('User' => array('query' => $query));
                        $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    }
                }
            }

            //atualizando dados do pedido e gerando token
            $query = "UPDATE checkouts set 
								unit_value='{$this->Session->read('Carrinho.Oferta.value')}', 
								total_value='{$this->Session->read('Carrinho.Oferta.value_total')}',
								amount='{$this->Session->read('Carrinho.Opcoes.quantidade')}',
								shipping_value='{$this->Session->read('Carrinho.Oferta.value_frete')}',								
								delivery_time='{$this->Session->read('Carrinho.Oferta.prazo_entrega')}',
								metrics='{$metricas}',
								address='{$this->Session->read('Carrinho.Endereco.endereco')}',
								city='{$this->Session->read('Carrinho.Endereco.cidade')}',
								zip_code='{$this->Session->read('Carrinho.Endereco.cep')}',
								state='{$this->Session->read('Carrinho.Endereco.estado')}',
								district='{$this->Session->read('Carrinho.Endereco.bairro')}',
								number='{$this->Session->read('Carrinho.Endereco.numero')}' WHERE id = {$this->Session->read('Carrinho.Oferta.id_pedido')}";

            $params = array('User' => array('query' => $query));
            $checkout = $this->Utility->urlRequestToGetData('users', 'query', $params);

            //pegando email de contato de empresa
            $company_id = $this->Session->read('Carrinho.Oferta.company_id');
            $params = array('Company' => array('fields' => array('login_moip'), 'conditions' => array('Company.id' => $company_id)));
            $email_empresa = $this->Utility->urlRequestToGetData('companies', 'first', $params);

            //chamada do moip 
            $arrayParams = array('Payments' => array(
                    'email_empresa' => $email_empresa['Company']['login_moip'],
                    'porcentagem' => '2.00',
                    'parcelamento' => $this->Session->read('Carrinho.Oferta.parcels'),
                    'parcelamento_juros' => $this->Session->read('Carrinho.Oferta.parcels_off_impost'),
                    'key' => '11PB4FPN68M1FE8MAPWUDIMEHFIGM8P6DMSBNXZZ',
                    'token' => 'JK75V6UGKYYUZR2ICVHJSSLD687UEJ9H',
                    'idUnique' => $this->Session->read('Carrinho.Oferta.id_pedido'),
                    'reason' => $this->Session->read('Carrinho.Oferta.title'),
                    'value' => $this->Session->read('Carrinho.Oferta.value_final'),
                    'setPlayer' => array(
                        'name' => $this->Session->read('userData.User.name'),
                        'email' => $this->Session->read('userData.User.email'),
                        'payerId' => $this->Session->read('userData.User.id'),
                        'billingAddress' => array(
                            'address' => $this->Session->read('Carrinho.Endereco.endereco'),
                            'number' => $this->Session->read('Carrinho.Endereco.numero'),
                            'complement' => '',
                            'city' => $this->Session->read('Carrinho.Endereco.cidade'),
                            'neighborhood' => $this->Session->read('Carrinho.Endereco.bairro'),
                            'state' => $this->Session->read('Carrinho.Endereco.estado'),
                            'country' => 'BRA',
                            'zipCode' => $this->Session->read('Carrinho.Endereco.cep'),
                            'phone' => '(11)3929-2201'
                        )
                    )
                )
            );
            //pr($_SESSION['Carrinho']);exit;											
            $dados = $this->Utility->urlRequestToCheckout('payments', $arrayParams);

            if (!empty($dados['ID'])) {
                //atualizando dados com numero do pedido gerado pelo MOIP
                $query = "UPDATE checkouts set 								
								transaction_moip_code='{$dados['ID']}' WHERE id = {$this->Session->read('Carrinho.Oferta.id_pedido')}";
                $params = array('User' => array('query' => $query));
                $checkout = $this->Utility->urlRequestToGetData('users', 'query', $params);
            }
        } elseif ($pagamento == true) {
            //atualizando dados com numero do pedido gerado pelo MOIP
            $query = "UPDATE checkouts set 								
								payment_method_id='{$this->request->data['tipo_pagamento']}', installment='{$this->request->data['vezes_pagamento']}'  WHERE id = {$this->Session->read('Carrinho.Oferta.id_pedido')}";
            $params = array('User' => array('query' => $query));
            $checkout = $this->Utility->urlRequestToGetData('users', 'query', $params);
        }
        return $dados;
    }

    public function pagamento_mobile() {
        
    }

    public function pedidos_assinaturas($cancelar_solicitacoes = null) {
        $limit = 4;
        $update = true;

        $data = date('Y-m-d');
        $user_id = $this->Session->read('userData.User.id');

        if ($cancelar_solicitacoes == 'cancelar') {
            //retirando novos convites depois de visualizados									
            $query = "UPDATE companies_invitations_users SET status = 'INACTIVE' WHERE user_id = {$user_id} and status='WAIT'";
            $params = array('User' => array('query' => $query));
            $convites_lidos = $this->Utility->urlRequestToGetData('users', 'query', $params);
        }



        //verifica se esta cancelando convite (antes de chamar query)
        if (!empty($this->request->data['excluir_convite'])) {
            $render = true;
            $this->layout = '';
            $id = $this->request->data['id'];

            //cancelando convite solicitado
            $params = array('CompaniesInvitationsUser' => array('id' => $id, 'status' => 'INACTIVE'));
            $excluir = $this->Utility->urlRequestToSaveData('users', $params);
        }

        //verifica se esta cancelando assinatura (antes de chamar query)
        if (!empty($this->request->data['assinar_empresa'])) {
            $data = date('Y/m/d');
            $render = true;
            $this->layout = '';
            $id = $this->request->data['id'];
            $id_empresa = $this->request->data['id_empresa'];

            //aceitando convite solicitado
            $params = array('CompaniesInvitationsUser' => array('id' => $id, 'status' => 'ACTIVE'));
            $assinar = $this->Utility->urlRequestToSaveData('users', $params);

            //assinando usuario na empresa
            $params = array('CompaniesUser' => array('company_id' => $id_empresa, 'user_id' => $user_id, 'status' => 'ACTIVE', 'last_status' => 'ACTIVE', 'date_register' => date('Y/m/d')));
            $signature = $this->Utility->urlRequestToSaveData('companies', $params);

            //pegando ofertas de acordo com o perfil
            $offersCompany = $this->ofertas_perfil($id_empresa, null, null, true);
            foreach ($offersCompany as $offerSignature) {
                $offerId = $offerSignature['Offer']['id'];
                $params = array('OffersUser' => array('conditions' => array('OffersUser.user_id' => $this->Session->read('userData.User.id'), 'offer_id' => $offerId)));
                $oferta = $this->Utility->urlRequestToGetData('users', 'count', $params);

                if (!$oferta > 0) {
                    $query = "INSERT INTO offers_users values(NULL, '{$offerId}', '{$this->Session->read('userData.User.id')}', '{$data}', 'facebook - portal')";
                    $params = array('User' => array('query' => $query));
                    $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                }
            }
        }


        $params = array('CompaniesInvitationsUser' => array('conditions' => array('CompaniesInvitationsUser.user_id' => $user_id, 'CompaniesInvitationsUser.status' => 'WAIT')));
        $contador = $this->Utility->urlRequestToGetData('users', 'count', $params);

        //verifica se esta fazendo uma requisicao ajax
        if (!empty($this->request->data['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST['limit'] + 4;
            if ($limit > $contador) {
                $limit = $contador;
                $update = false;
            }
        }


        $params = array('CompaniesInvitationsUser' => array('conditions' => array('CompaniesInvitationsUser.user_id' => $user_id, 'CompaniesInvitationsUser.status' => 'WAIT'), 'order' => array('CompaniesInvitationsUser.id' => 'DESC'), 'limit' => $limit), 'CompaniesInvitationsFilter', 'Companies');
        $convitesAll = $this->Utility->urlRequestToGetData('users', 'all', $params);

        if (is_array($convitesAll)) {
            foreach ($convitesAll as $convite) {
                $id_empresa = $convite['CompaniesInvitationsFilter']['company_id'];

                //pegando dados de empresa
                $params = array('Company' => array('fields' => array('Company.fancy_name', 'Company.description', 'phone', 'email'), 'conditions' => array('Company.id' => $id_empresa)));
                $empresa = $this->Utility->urlRequestToGetData('companies', 'first', $params);
                $convite['CompaniesInvitationsFilter']['company'] = $empresa['Company']['fancy_name'];
                $convite['CompaniesInvitationsFilter']['description'] = $empresa['Company']['description'];
                $convite['CompaniesInvitationsFilter']['phone'] = $empresa['Company']['phone'];
                $convite['CompaniesInvitationsFilter']['email'] = $empresa['Company']['email'];

                //quantidade de ofertas de empresa
                $params = array('Offer' => array('conditions' => array('Offer.company_id' => $id_empresa, 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
                $ofertas = $this->Utility->urlRequestToGetData('offers', 'count', $params);
                $convite['CompaniesInvitationsFilter']['ofertas'] = $ofertas;

                //pegando ofertas de acordo com perfil
                $ofertas_perfil = $this->ofertas_perfil($id_empresa);
                $quantidade_perfil = count($ofertas_perfil);

                $convite['CompaniesInvitationsFilter']['ofertas_perfil'] = $quantidade_perfil;

                $convites[] = $convite;
            }
        } else {
            $convites = 'VOCE NAO TEM CONVITES NO MOMENTO';
        }

        $this->set(compact('convites', 'update', 'limit', 'contador'));
        if (!empty($render)) {
            $this->render('Elements/ajax_pedidos_assinaturas');
        } else {

            //retirando novos convites depois de visualizados									
            $query = "UPDATE companies_invitations_users SET preview = 'ACTIVE' WHERE user_id = {$user_id}";
            $params = array('User' => array('query' => $query));
            $convites_lidos = $this->Utility->urlRequestToGetData('users', 'query', $params);
        }
    }

    public function wishlist($page = null, $id = null) {
        //verifica se usuario vai visualizar ofertas para desejo realizado
        if ($page == 'offers') {

            //pegando todas as ofertas direcionadas para desejo realizado			
            $params = array('UsersWishlistCompany' => array('fields' => array('UsersWishlistCompany.offer_id'), 'conditions' => array('UsersWishlistCompany.wishlist_id' => $id, 'UsersWishlistCompany.offer_id > ' => 0)));
            $ofertas = $this->Utility->urlRequestToGetData('companies', 'all', $params);

            foreach ($ofertas as $offer) {
                $offers[] = $offer['UsersWishlistCompany']['offer_id'];
            }
            $this->Session->write('ofertasIds', $offers);

            $this->redirect(array('controller' => 'users', 'action' => 'home', 'plugin' => 'users'));
        } else if ($page == 'cadastro') {
            //verifica se � feito requisicao ajax para carregar subcategorias
            if ($this->request->is('ajax')) {
                if (!empty($this->request->data['categoria'])) {
                    $render = true;
                    $this->layout = '';
                    $arrayParams = array(
                        'CompaniesSubCategory' => array('conditions' => array('CompaniesSubCategory.category_id' => $this->request->data['categoria']), array('CompaniesCategory'))
                    );

                    $sub_categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);
                }
                $this->set(compact('sub_categorias'));
                if (!empty($render))
                    $this->render('Elements/ajax_categorias_wishlist');
            }else {
                $arrayParams = array(
                    'CompaniesCategory' => array(
                    )
                );
                $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);

                if ($this->request->is('post')) {
                    $cad_desejo = $this->Utility->urlRequestWishlist('users', 'all', $this->request['data']);
                    if ($cad_desejo['status'] == 'SAVE_OK') {
                        $this->redirect(array('controller' => 'users', 'action' => 'wishlist', 'plugin' => 'users'));
                    } else {
                        $mensagem = "Ocorreu um erro no cadastro. Tente novamente";
                    }
                }
                $this->set(compact('categorias', 'mensagem'));
                $this->render('cad_wishlist');
            }
        } else {
            $limit = 4;
            $update = true;

            $data = date('Y-m-d');
            $user_id = $this->Session->read('userData.User.id');


            $params = array('UsersWishlist' => array('conditions' => array('UsersWishlist.user_id' => $user_id, 'UsersWishlist.status' => 'ACTIVE')));
            $contador = $this->Utility->urlRequestToGetData('users', 'count', $params);


            //verifica se esta cancelando desejo (antes de chamar query)
            if (!empty($this->request->data['excluir_desejo'])) {
                $render = true;
                $this->layout = '';
                $id = $this->request->data['id'];

                //cancelando desejo solicitado
                $params = array('UsersWishlist' => array('id' => $id, 'status' => 'INACTIVE'));
                $excluir = $this->Utility->urlRequestToSaveData('users', $params);
            }


            //verifica se esta fazendo uma requisicao ajax
            if (!empty($this->request->data['limit'])) {
                $render = true;
                $this->layout = '';
                $limit = $_POST['limit'] + 4;
                if ($limit > $contador) {
                    $limit = $contador;
                    $update = false;
                }
            }

            $params = array('UsersWishlist' => array('conditions' => array('UsersWishlist.user_id' => $user_id, 'UsersWishlist.status' => 'ACTIVE'), 'order' => array('UsersWishlist.id' => 'DESC'), 'limit' => $limit), 'User', 'CompaniesCategory', 'CompaniesSubCategory', 'UsersWishlistCompany');
            $lista_desejos = $this->Utility->urlRequestToGetData('users', 'all', $params);

            $this->set(compact('lista_desejos', 'update', 'limit', 'contador'));

            if (!empty($render))
                $this->render('Elements/ajax_desejos');
        }
    }

    public function sucesso($page = null) {
        $mensagem = "PAGINA INVALIDA";
        $link = "HOME";
        $descLink = array('controller' => 'users', 'plugin' => 'users', 'action' => 'home');

        switch ($page) {
            case "compras":
                $mensagem = "Compra realizada! <br> Maiores detalhes em";
                $link = "Minhas Compras";
                $descLink = array('controller' => 'users', 'plugin' => 'users', 'action' => 'minhasCompras');
                break;
        }

        $this->set(compact('mensagem', 'link', 'descLink'));
    }

    public function dados_cadastrais() {


        if ($this->request->is('ajax')) {
            if (!empty($this->request->data['cep_buscar'])) {
                $cURL = curl_init("http://cep.correiocontrol.com.br/{$this->request->data['cep_buscar']}.json");
                curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                $resultado = curl_exec($cURL);
                curl_close($cURL);
                echo $resultado;
                exit;
            }
        } else if ($this->request->is('post')) {
            $this->request->data['User']['birthday'] = date('Y-m-d', strtotime($this->request->data['User']['birthday']));
            $arrayParams = array('User' => $this->request->data['User']);
            $editar = $this->Utility->urlRequestToSaveData('users', $arrayParams);
            $this->Session->write('userData.User', $editar['data']['User']);
        }
    }

    public function new_password() {
        $this->layout = 'login';
        if ($this->request->data) {
            $arrayParams = array(
                'User' => array(
                    'fields' => array('User.id', 'User.name', 'User.email'),
                    'conditions' => array('User.email' => $this->request->data['User']['email'])
                )
            );

            $nova_senha = $this->Utility->urlRequestPasswordRecovery('users', 'first', $arrayParams);

            if (!$nova_senha) {
                $mensagem = 'Email informado nao foi encontrado no trueone';
            } else {
                $mensagem = 'Senha alterada com sucesso, verifique sua caixa de entrada';
            }
            $this->set(compact('mensagem'));
        }
    }

    /**
     * Executa calculo de frete
     * chamando funcao que trabalha API dos correios
     * @return string
     */
    private function calculaFrete($cepUser) {
        $total = $this->Session->read('Carrinho.Opcoes.quantidade') * $this->Session->read('Carrinho.Oferta.value');
        $totalPeso = $this->Session->read('Carrinho.Opcoes.quantidade') * $this->Session->read('Carrinho.Oferta.weight');

        $this->Session->write('Carrinho.Oferta.value_total', $total);
        $this->Session->write('Carrinho.Oferta.weight_total', $totalPeso);

        //pegando CEP de origem(empresa) e fazendo calculo de frete
        $params = array('Company' => array('fields' => array('Company.zip_code'), 'conditions' => array('id' => $this->Session->read('Carrinho.Oferta.company_id'))));
        $cep = $this->Utility->urlRequestToGetData('companies', 'first', $params);
        $cep = $cep['Company']['zip_code'];
        $frete = $this->Utility->calcFrete('41106', $cep, $cepUser, $this->Session->read('Carrinho.Oferta.weight_total'));
        if ($frete == false) {
            return false;
        } else {
            return $frete;
        }
    }

    /**
     * Verifica se perfil de usuario esta completo	 
     * @return bool
     */
    private function verificaPerfil() {
        if ($this->Session->check('userData.User')) {
            //verificando se perfil esta completo ou nao			
            if ($_SESSION['userData']['User']['gender'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['birthday'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['address'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['city'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['zip_code'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['state'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['district'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['number'] == '') {
                return true;
            } else if ($_SESSION['userData']['User']['photo'] == 'http://acclabs.accential.com.br/uploads/img-users/male.jpg') {
                return true;
            } else if ($_SESSION['userData']['User']['photo'] == 'http://acclabs.accential.com.br/uploads/img-users/female.jpg') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Retorna ofertas personalizadas de acordo com perfil de usuario - variaveis - facebook	 
     * @return bool
     */
    private function ofertas_perfil($id_empresa = null, $lista_ofertas = false, $limit = null, $assinatura_oferta_perfil = null) {
        $data = date('Y/m/d');
        $ofertas_personalizadas = array();

        //quantidade de ofertas para perfil de usuario logado				
        if (!$this->Session->check('userData.FacebookProfile')) {
            $political = '';
            $relationship_status = '';
            $religion = '';
            $location = '';
        } else {
            $political = $this->Session->read('userData.FacebookProfile.political');
            $relationship_status = $this->Session->read('userData.FacebookProfile.relationship_status');
            $religion = $this->Session->read('userData.FacebookProfile.religion');
            $location = $this->Session->read('userData.FacebookProfile.location');
        }

        if ($assinatura_oferta_perfil == true) {
            $params = array('Offer' => array('conditions' => array('Offer.company_id' => $id_empresa, 'Offer.status' => 'ACTIVE', 'Offer.public' => 'INACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)), 'OffersFilter');
        } else {
            $params = array('Offer' => array('conditions' => array('Offer.company_id' => $id_empresa, 'Offer.status' => 'ACTIVE', 'Offer.public' => 'INACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data), 'order' => 'Offer.id DESC'), 'OffersFilter');
        }

        $ofertas_perfil = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        foreach ($ofertas_perfil as $oferta_perfil) {
            if (is_array($oferta_perfil['OffersFilter'][0])) {
                foreach ($this->offerFilters as $key => $value) {
                    if (!empty($oferta_perfil['OffersFilter'][0][$key])) {
                        $array = explode(',', $oferta_perfil['OffersFilter'][0][$key]);
                        if (!in_array($this->Session->read('userData.FacebookProfile.' . $key), $array)) {
                            unset($oferta_perfil);
                        }
                    }
                }
            } else {
                unset($oferta_perfil);
            }
            if (is_array($oferta_perfil)) {
                $ofertas_personalizadas[] = $oferta_perfil;
            }
        }

        if ($limit > 0 && count($ofertas_personalizadas) >= $limit) {
            $i = 0;
            for ($i = 0; $limit > $i; $i++) {
                $ofertas[$i] = $ofertas_personalizadas[$i];
            }
        } else {
            $ofertas = $ofertas_personalizadas;
        }
        return $ofertas;
    }

    // Define uma fun��o que poder� ser usada para validar e-mails usando regexp
    public function validaEmail($email) {
        $conta = "^[a-zA-Z0-9\._-]+@";
        $domino = "[a-zA-Z0-9\._-]+.";
        $extensao = "([a-zA-Z]{2,4})$";

        $pattern = $conta . $domino . $extensao;

        if (ereg($pattern, $email))
            return true;
        else
            return false;
    }

    /**
     * Vitrine mostra todas as oferta
     * caso $page = 'personalizadas':
     *  vitrine irá mostrar ofertas da empresa que tem
     * o id igual à = $compId
     * 
     * Caso $page = '':
     * vitrine carrega ofertas normalmente sem filtro algum
     * 
     * @param type $page
     * @param type $compId
     */
    public function testa($page, $compId) {
        $this->layout = 'users';
        $pageName = "VITRINE";
        //Quando clicado no menu, redirecionamos para 
        //a tela de dados cadastrais do usuario
        if ($page == 'meus-dados') {
            
        }

        $data = date("Y-m-d");

        /**
         * Ajax para carregar oferta, 
         * carrega de 3 em 3
         */
        if ($page == 'ajax-vitrine') {

            $limite = $this->request->data['limite'];
            $limite = $limite + 5;

            $params = array(
                'Offer' => array(
                    'conditions' => array(
                        'public' => 'ACTIVE',
                        'Offer.status' => 'ACTIVE',
                        'Offer.begins_at <= ' => $data,
                        'Offer.ends_at >= ' => $data
                    ),
                    'order' => array(
                        'Offer.id' => 'DESC'
                    ),
                    'limit' => $limite
                )
            );
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);

            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                
                //evaluation
                 $query3 = "select sum(offers_comments.evaluation) total, count(*) qtd from offers inner join offers_comments on offers_comments.offer_id = offers.id where offers.id = {$offer['Offer']['id']};";
                 $filterParams3 = array(
                    'User' => array(
                        'query' => $query3
                    )
                );
                 $offer['evaluation'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams3);
                 $offers2[] = $offer;
            }

            $this->set(compact("limite", 'offers2'));
            $this->render('Elements/ajax_vitrine');
        }

        /*
         * Faz a pesquisa das ofertas filtradas por categorias
         * Recebe o ID da categoria e retorna a lista de ofertas 
         * 
         */
        if ($page == 'offers-category') {

            $id = $this->request->data['id'];
            $data = date('Y-m-d');
            $query2 = "select * from offers where id in (select offer_id from offers_extra_infos where category_id = {$id}) and ends_at >= '{$data}' and public = 'ACTIVE';";
            $filterParams2 = array(
                'User' => array(
                    'query' => $query2
                )
            );
            $offersG = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

            $offers = null;
            $i = 0;
            foreach ($offersG as $offer) {
                $offers[$i]['Offer'] = $offer['offers'];
                $i++;
            }

            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                
                //evaluation
                 $query3 = "select sum(offers_comments.evaluation) total, count(*) qtd from offers inner join offers_comments on offers_comments.offer_id = offers.id where offers.id = {$offer['Offer']['id']};";
                 $filterParams3 = array(
                    'User' => array(
                        'query' => $query3
                    )
                );
                 $offer['evaluation'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams3);
                $offers2[] = $offer;
            }


            $limite = 8;
            $this->set(compact("limite", 'offers2'));
            $this->render('Elements/ajax_vitrine');
        }

        /*
         * Faz a pesquisa de ofertas filtradas por perfil
         */
        if ($page == 'offers-profile') {
            $gender = $this->request->data['gender'];
            $relationshipStatus = $this->request->data['status'];
            $religion = $this->request->data['religion'];
            $political = $this->request->data['political'];
            $age = $this->request->data['age'];
            $location = $this->request->data['locat'];


            $perfil = null;
            $perfil['Profile']['gender'] = "{$gender}";
            $perfil['Profile']['political'] = "{$political}";
            $perfil['Profile']['location'] = "{$location}";
            $perfil['Profile']['religion'] = "{$religion}";
            $perfil['Profile']['relationship_status'] = "{$relationshipStatus}";

            $offersByProfile = $this->searchOfferByProfile($perfil);
            $offers = null;
            $i = 0;
            foreach ($offersByProfile as $of) {
                $offers[$i]['Offer'] = $of['Offer'];
                $i++;
            }

            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                
                //evaluation
                 $query3 = "select sum(offers_comments.evaluation) total, count(*) qtd from offers inner join offers_comments on offers_comments.offer_id = offers.id where offers.id = {$offer['Offer']['id']};";
                 $filterParams3 = array(
                    'User' => array(
                        'query' => $query3
                    )
                );
                $offer['evaluation'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams3);
              $offers2[] = $offer;
            }

            $limite = 8;
            $this->set(compact('limite', 'offers2', 'perfil'));
            $this->render('Elements/ajax_vitrine');
        }

        if ($page == 'search-offers') {
            $title = $this->request->data['search'];
            $data = date('Y-m-d');

            $params = array(
                'Offer' => array(
                    'conditions' => array(
                        'public' => 'ACTIVE',
                        'Offer.status' => 'ACTIVE',
                        'Offer.begins_at <= ' => $data,
                        'Offer.ends_at >= ' => $data,
                        'Offer.title LIKE' => "%$title%"
                    ),
                    'order' => array(
                        'Offer.id' => 'DESC'
                    )
                )
            );
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);

            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                
                //evaluation
                 $query3 = "select sum(offers_comments.evaluation) total, count(*) qtd from offers inner join offers_comments on offers_comments.offer_id = offers.id where offers.id = {$offer['Offer']['id']};";
                 $filterParams3 = array(
                    'User' => array(
                        'query' => $query3
                    )
                );
                 $offer['evaluation'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams3);
               $offers2[] = $offer;
            }

            $limite = 20;

            $this->set(compact('limite', 'offers2'));
            $this->render('Elements/ajax_vitrine');
        }

        $compId = $_REQUEST['compId'];
        $wishId = $_REQUEST['wishId'];
        /*
         * Ofertas de determinada empresa
         */
        $id = 0;
        $base = array(
            'citrus' => array(
                "offer"
            ),
            'conditions' => array(
                "Offer.id",
                "{$id}"
            )
        );
        $replacements = array(
            'citrus' => array(
                'pineapple'
            ),
            'berries' => array(
                'blueberry'
            )
        );



        // $basket = array_replace_recursive($base, $replacements);
        $data = date('Y-m-d');
        $limit = 8;
        $update = true;

        // total de ofertas
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    'public' => 'ACTIVE',
                    'Offer.status' => 'ACTIVE',
                    'Offer.begins_at <= ' => $data,
                    'Offer.ends_at >= ' => $data
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                )
            )
        );
        $contador = $this->Utility->urlRequestToGetData('offers', 'count', $params);

        // verifica se esta fazendo uma requisicao ajax
        if (!empty($this->request->data ['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST ['limit'] + 2;
            if ($limit > $contador) {
                $limit = $contador;
                $update = false;
            }
        }


        if (empty($compId)) {
            $params = array(
                'Offer' => array(
                    'conditions' => array(
                        //'public' => 'ACTIVE',
                        'Offer.status' => 'ACTIVE',
                        'Offer.begins_at <= ' => $data,
                        'Offer.ends_at >= ' => $data
                    ),
                    'order' => array(
                        'Offer.id' => 'DESC'
                    ),
                    'limit' => $limit
                )
            );
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);

            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                
                //evaluation
                 $query3 = "select sum(offers_comments.evaluation) total, count(*) qtd from offers inner join offers_comments on offers_comments.offer_id = offers.id where offers.id = {$offer['Offer']['id']};";
                 $filterParams3 = array(
                    'User' => array(
                        'query' => $query3
                    )
                );
                 $offer['evaluation'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams3);
               $offers2[] = $offer;
            }

        } else {
            $offers = $this->ofertas_perfil($compId);
            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                
                //evaluation
                 $query3 = "select sum(offers_comments.evaluation) total, count(*) qtd from offers inner join offers_comments on offers_comments.offer_id = offers.id where offers.id = {$offer['Offer']['id']};";
                 $filterParams3 = array(
                    'User' => array(
                        'query' => $query3
                    )
                );
                $offer['evaluation'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams3);
                $offers2[] = $offer;
            }

        }

        /**
         * retorna todas as categorias 
         */
        $categParams = array(
            'CompaniesCategory' =>
            array(
                'conditions' => array(
                )),
        );
        $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $categParams);

        $perfilParam = array(
            'OffersFilter' =>
            array(
                'conditions' => array(
                )),
        );
        $filtros = $this->Utility->urlRequestToGetData('offers', 'all', $perfilParam);

        //variaveis que recebem o texto
        $religion = '';
        $status = '';
        $political = '';
        $age = '';
        $locale = '';

        //variaveis que recebem array 
        $relationshipStatus = null;
        $politicals = null;
        $ages = null;
        $locales = null;
        $religares = null;

        //transforma todos os registros em textos 
        foreach ($filtros as $filtro) {
            $religion = $religion . $filtro['OffersFilter']['religion'];
            $status = $status . $filtro['OffersFilter']['relationship_status'];
            $political = $political . $filtro['OffersFilter']['political'];
            $age = $age . $filtro['OffersFilter']['age_group'];
            $locale = $locale . $filtro['OffersFilter']['location'];
        }

        //transforma textos em array
        $religares = explode(",", $religion);
        $religares = array_unique($religares);

        $relationshipStatus = explode(",", $status);
        $relationshipStatus = array_unique($relationshipStatus);

        $politicals = explode(",", $political);
        $politicals = array_unique($politicals);

        $ages = explode(",", $age);
        $ages = array_unique($ages);

        $locales = explode(",", $locale);
        $locales = array_unique($locales);

        $firstLogin = $this->Session->read('firstlogin');

        if (!empty($this->Session->read('userData.User'))) {
            $this->usersNews();
        }
        $this->set(compact('firstLogin', 'pageName', 'offers', 'limit', 'contador', 'update', 'offers2', 'categorias', 'filtros', 'religares', 'relationshipStatus', 'politicals', 'ages', 'locales'));
    }

    public function offersDetail($offerId, $page) {
        $this->layout = 'users';
        $pageName = "DETALHES DA OFERTA";
        /*         * Caso venha uma chamada do form pelo botão comprar
         * Redireciona para a tela de checkout
         */

        if (!empty($page)) {
            if ($page == 'continuar-compra') {

                //Estatisticas - Guarda clique do usuario quando usuario vai diretor para checkout
                $statisticsQuery = "select * from offers_statistics where offer_id=" . $offerId . ";";
                $statisticsParams = array(
                    'User' => array(
                        'query' => $statisticsQuery
                    )
                );
                $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);

                $statisticsQuery2 = "update offers_statistics set checkouts_click = checkouts_click+1 where id = " . $statistics[0]['offers_statistics']['id'] . ";";
                $statisticsParams2 = array(
                    'User' => array(
                        'query' => $statisticsQuery2
                    )
                );
                $statistics2 = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams2);
                //
                //recupera metricas do detalhe
                $metricas[tamanho] = $this->request->data['metricaY'];
                $metricas[cor] = $this->request->data['metricaX'];

                //recupera offer por id
                $params = array('Offer' => array('conditions' => array('Offer.id' => $offerId)), 'OffersComment', 'OffersPhoto', 'Company');
                $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);


                //só recura as métricas caso as mesmas não tenham sido salvas ainda
                //usado no passo direto da vitrine para o checkout
                if (empty($metricas['cor'])) {
                    //busca metricas para mostrar ao usuario
                    $query = "select * from  offers_metrics inner join offers_domains on offers_metrics.offer_metrics_y_id = offers_domains.id
                        inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id where offers_metrics.offer_id = $offerId;";
                    $filterParams = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);

                    //bussca metricas para mostrar ao usuario
                    $query = "select * from  offers_metrics inner join offers_domains on offers_metrics.offer_metrics_x_id = offers_domains.id
                        inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id where offers_metrics.offer_id = $offerId;";
                    $filterParams2 = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $filter2 = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

                    //Salva metricas na sessao
                    $this->Session->write('Carro.metric1', $filter);
                    $this->Session->write('Carro.metric2', $filter2);
                }

                //salva oferta na sessao
                $this->Session->write('Carro.Offer', $offer);
                //salva metricas na sessao
                $this->Session->write('Carro.Opcoes.metricas', $metricas);

                $this->redirect(array(
                    'controller' => 'users',
                    'action' => 'realizarCompra',
                    'plugin' => 'users'
                ));
            }
        }

        /* , usaremos devido ao compartilhamento pelo facebook,           
         * devemos usar uma url que possa ser recebida e entedida pelo sistema
         */
        $id = $_REQUEST['offer'];
        if (!empty($id)) {

            //Estatisticas - Registra o clique do usuario
            $statisticsQuery = "select * from offers_statistics where offer_id=" . $id . ";";
            $statisticsParams = array(
                'User' => array(
                    'query' => $statisticsQuery
                )
            );
            $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);

            $statisticsQuery2 = "update offers_statistics set details_click = details_click+1 where id = " . $statistics[0]['offers_statistics']['id'] . ";";
            $statisticsParams2 = array(
                'User' => array(
                    'query' => $statisticsQuery2
                )
            );
            $statistics2 = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams2);
            //

            $params = array('Offer' => array('conditions' => array('Offer.id' => $id)), 'OffersComment', 'OffersPhoto', 'Company');
            $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);

            $query = "select * from  offers_metrics inner join offers_domains on offers_metrics.offer_metrics_y_id = offers_domains.id
                        inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id where offers_metrics.offer_id = $id;";
            $filterParams = array(
                'User' => array(
                    'query' => $query
                )
            );
            $filter = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);

            $query = "select * from  offers_metrics inner join offers_domains on offers_metrics.offer_metrics_x_id = offers_domains.id
                        inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id where offers_metrics.offer_id = $id;";
            $filterParams2 = array(
                'User' => array(
                    'query' => $query
                )
            );
            $filter2 = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

            $query = "select * from offers_domains inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id;";
            $filterParams = array(
                'User' => array(
                    'query' => $query
                )
            );

            $cores = null;
            $domains = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);

            //
            $query2 = "select * from offers_extra_infos where offer_id = {$id};";
            $filterParams2 = array(
                'User' => array(
                    'query' => $query2
                )
            );
            $extraInfos = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

            //Captura nome dos usuarios de cada comentário
            if (!empty($offer['OffersComment'])) {
                $i = 0;
                foreach ($offer['OffersComment'] as $comentario) {
                    $id_usuario = $comentario['user_id'];
                    $params = array('User' => array('fields' => array('User.name'), 'conditions' => array('User.id' => $id_usuario)));
                    $user = $this->Utility->urlRequestToGetData('users', 'first', $params);
                    $offer['OffersComment'][$i]['nome'] = $user['User']['name'];
                    $i++;
                }
            }
        } else {
            /**
             * Mandando via function
             */
            $params = array('Offer' => array('conditions' => array('Offer.id' => $offerId)), 'OffersComment', 'OffersPhoto', 'Company');
            $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);

            $query = "select * from  offers_metrics inner join offers_domains on offers_metrics.offer_metrics_y_id = offers_domains.id
                        inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id where offers_metrics.offer_id = $id;";
            $filterParams = array(
                'User' => array(
                    'query' => $query
                )
            );
            $filter = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);

            $query = "select * from  offers_metrics inner join offers_domains on offers_metrics.offer_metrics_x_id = offers_domains.id
                        inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id where offers_metrics.offer_id = $id;";
            $filterParams2 = array(
                'User' => array(
                    'query' => $query
                )
            );
            $filter2 = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

            $query = "select * from offers_domains inner join offers_attributes on offers_domains.offer_attribute_id = offers_attributes.id;";
            $filterParams = array(
                'User' => array(
                    'query' => $query
                )
            );

            $cores = null;
            $domains = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);
        }

        //Adicionando oferta ao carrinho
        $offer['extra'] = $extraInfos['0']['offers_extra_infos'];
        $this->Session->write('Carro.Offer', $offer);
        $this->set(compact('filter', 'domains', 'filter2', 'extraInfos', 'user2', 'pageName'));
        $this->set(compact('offer'));

        /**
         * AVALIAÇÃO
         * A lógica das estrelas define que notas com total decimal são dividas no 
         * número antes da virgula e um após.
         * A quantidade das primeiras estrelas são definidas pelo número anterior a virgula,
         * Ex: Nota 3 = 3 estrelas cheias
         * Já as estrelas quebradas são definidas pelo número após a vírgula.
         * Então, uma nota 3,5 ficará (nota => [0] => 3, [1] => 5)
         * Os números após a virgula são considerados:
         * menor ou igual a 5 => Estrela meia cheia
         * maior que 5 => Estrela cheia
         * Para quem vir a editar esse código futuramente,
         *  conto pra que melhore essa lógica.
         */
    }

    public function wish($page) {
        $this->layout = 'users';
        $pageName = "Wishlist";
        if (empty($_SESSION ['userData'])) {
            $this->redirect(array(
                'controller' => 'users',
                'action' => 'testa',
                'plugin' => 'users'
            ));
        }

        /**
         * mostra ofertas 
         * relacionadas ao desejo
         */
        if ($page == 'offers') {

            $wishId = $this->request->data['id'];

            $paramsUWC = array(
                'UsersWishlistCompany' => array(
                    'conditions' => array(
                        'UsersWishlistCompany.wishlist_id' => $wishId,
                        'UsersWishlistCompany.status' => 'ACTIVE'
                    )
                ),
                'Offer'
            );

            $offersWish = $this->Utility->urlRequestToGetData('users', 'all', $paramsUWC);

            $this->set(compact('offersWish'));
            $this->render('Elements/ajax_wish_offers');
        }

        /*
         * Recupera o desejo que irá ser editado
         * e retorna tela onde são mostrados os dados
         */
        if ($page == 'editar') {
            $id = $this->request->data['id'];

            $arrayParams = array(
                'UsersWishlist' =>
                array(
                    'conditions' => array(
                        'UsersWishlist.id' => $id
                    )),
            );

            $desejo = $this->Utility->urlRequestToGetData('users', 'all', $arrayParams);
            $this->set(compact('desejo'));
            $this->layout = '';
            $this->render('Elements/ajax_wish_edita');
        }

        /**
         * Recupera os dados do desejo editado e salva
         */
        if ($page == 'updateWish') {
            $id = $this->request->data['id'];
            $titulo = $this->request->data['titulo'];
            $descricao = $this->request->data['descricao'];
            $dataLimite = $this->request->data['data'];

            $arrayParams = array(
                'UsersWishlist' => array(
                    'id' => $id,
                    'name' => $titulo,
                    'description' => $descricao,
                //'ends_at' => $dataLimite
                )
            );

            $updateWishlist = $this->Utility->urlRequestToSaveData('users', $arrayParams);
        }

        if ($page == 'requisicao') {
            if (!empty($this->request->data['categoria'])) {
                $render = true;
                $this->layout = '';
                $arrayParams = array(
                    'CompaniesSubCategory' => array('conditions' => array('CompaniesSubCategory.category_id' => $this->request->data['categoria']), array('CompaniesCategory'))
                );

                $sub_categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);
            }
            $this->set(compact('sub_categorias'));
            if (!empty($render))
                $this->render('Elements/ajax_categorias_wishlist');
        }

        if ($page == 'excluir') {
            $id = $_REQUEST['id'];

            $params = array('UsersWishlist' => array('id' => $id, 'status' => 'INACTIVE'));
            $excluir = $this->Utility->urlRequestToSaveData('users', $params);
        }

        if ($page == 'save') {

            $data = date('Y-m-d');
            $param['UsersWishlist']['name'] = $this->request->data['UsersWishlist']['name'];
            $param['UsersWishlist']['description'] = $this->request->data['UsersWishlist']['description'];
            $param['UsersWishlist']['ends_at'] = $this->request->data['UsersWishlist']['ends_at'];
            $param['UsersWishlist']['category_id'] = $this->request->data['UsersWishlist']['category_id'];
            $param['UsersWishlist']['sub_category_id'] = $this->request->data['UsersWishlist']['sub_category_id'];
            $param['UsersWishlist']['date_register'] = $data;
            $param['UsersWishlist']['status'] = 'ACTIVE';
            $param['UsersWishlist']['user_id'] = $this->Session->read('userData.User.id');

            $save = $this->Utility->urlRequestToSaveData('users', $param);

            //mandando desejo do usuario para empresas
            $par = array('Company' => array('fields' => array('id'), 'conditions' => array('category_id' => 1, 'sub_category_id' => 5, 'status' => 'ACTIVE')));
            $empresas = $this->Utility->urlRequestToGetData('companies', 'all', $par);

            foreach ($empresas as $empresa) {
                $arrayParams = array(
                    'UsersWishlistCompany' => array(
                        'id' => NULL,
                        'company_id' => $empresa['Company']['id'],
                        'wishlist_id' => $save['data']['UsersWishlist']['id'],
                        'user_id' => $save['data']['UsersWishlist']['user_id'],
                        'status' => 'WAIT',
                        'offer_id' => NULL
                    )
                );
                $save_companies_wishlist = $this->Utility->urlRequestToSaveData('users', $arrayParams);
            }

            $this->redirect(array(
                'controller' => 'users',
                'action' => 'wish',
                'plugin' => 'users'
            ));
        }

        $params = array('UsersWishlist' => array('conditions' => array('UsersWishlist.user_id' => $this->Session->read('userData.User.id'), 'UsersWishlist.status' => 'ACTIVE'), 'order' => array('UsersWishlist.id' => 'DESC')), 'User', 'CompaniesCategory', 'CompaniesSubCategory', 'UsersWishlistCompany');
        $lista_desejos = $this->Utility->urlRequestToGetData('users', 'all', $params);


//            $params = array('OffersUser' => array('conditions' => array('OffersUser.user_id' => 278, 'OffersUser.')));
//            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
        //Retorna ofertas para desejo
        $des = $lista_desejos;
        $desejos[] = '';
        foreach ($des as $desejo) {
            $params = array('UsersWishlistCompany' =>
                array(
                    'conditions' => array(
                        'UsersWishlistCompany.wishlist_id' => $desejo['UsersWishlist']['id'],
                        'UsersWishlistCompany.status' => 'ACTIVE'
            )));
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
            $desessss[$desejo['UsersWishlist']['id']]['QtdOffers'] = count($offers);
            $desejos[] = $desessss;
        }


        $query = "select name from users_wishlists ORDER BY date_register DESC LIMIT 10;";
        $filterParams = array(
            'User' => array(
                'query' => $query
            )
        );
        $top = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);

        $arrayParams = array(
            'CompaniesCategory' => array(
            )
        );
        $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);
        $news = $this->usersNews();

        $this->set(compact('top'));
        $this->set(compact('lista_desejos', 'categorias', 'offers', 'desejos', 'pageName'));
    }

    public function compras($page) {
        $this->layout = 'users';
        $pageName = "Compras";
        if (empty($_SESSION ['userData'])) {
            $this->redirect(array(
                'controller' => 'users',
                'action' => 'testa',
                'plugin' => 'users'
            ));
        }

        /**
         * Salva edição do comentario
         */
        if ($page == 'update-comment') {
            $commentId = $this->request->data['id'];
            $title = $this->request->data['title'];
            $description = $this->request->data['desc'];

            $sql = "UPDATE offers_comments SET description='$description', title='$title' WHERE id=$commentId;";
            $params2 = array('User' => array('query' => $sql));
            $upComment = $this->Utility->urlRequestToGetData('users', 'query', $params2);
        }

        /**
         * Verifica se usuario já comentou essa oferta
         * Caso não, retornamos false e usuario pode comentar
         * Case sim, retornamos true e mostramos uma mensagem de oferta ja comentada
         */
        if ($page == 'valida-comentario') {

            $offer_id = $this->request->data['oferta_id'];
            $trueOrFalse = '';

            $params = array('OffersComment' => array('conditions' => array('OffersComment.offer_id' => $offer_id, 'OffersComment.user_id' => $this->Session->read('userData.User.id'))));
            $comentarios = $this->Utility->urlRequestToGetData('offers', 'all', $params);


            if (empty($comentarios)) {
                $trueOrFalse = 'nao';
            } else {
                $trueOrFalse = 'sim';
            }

            $this->set(compact('trueOrFalse', 'comentarios'));
            $this->render('Elements/ajax_compras_valida');
        }

        /**
         * Grava o comentario do usuario 
         * sobre determinado oferta
         * o ID é capturado no click
         * o TITULO é capturado no input
         * a DESCRICAO é capturada no input
         */
        if ($page == 'comenta-oferta') {
            $offer_id = $this->request->data['oferta_id'];
            $title = $this->request->data['titulo'];
            $description = $this->request->data['comentario'];
            $nota = $this->request->data['nota'];
            //$offer_id = 202;
            $data = date('Y-m-d');

            $arrayParams = array(
                'OffersComment' => array(
                    'offer_id' => $offer_id,
                    'title' => $title,
                    'description' => $description,
                    'date_register' => $data,
                    'status' => 'ACTIVE',
                    'evaluation' => $nota,
                    'user_id' => $this->Session->read('userData.User.id')
                ),
            );
            $saveComment = $this->Utility->urlRequestToSaveData('offers', $arrayParams);
        }

        /*
         * Pagina criada para recuperar dados de uma determinada compra
         * e permitir que seja regerado o Boleto
         */
        if ($page == 'regenerateBillet') {
            $checkId = $this->request->data['id'];

            $query = 'select * from checkouts_extra_infos where checkout_id = ' . $checkId . ';';
            $params = array(
                'User' => array(
                    'query' => $query
                )
            );
            $infos = $this->Utility->urlRequestToGetData('users', 'query', $params);
            $this->set(compact('infos'));
            $this->render('Elements/ajax_compras_boleto');
        }

        $params = array('Checkout' => array('conditions' => array('Checkout.user_id' => $this->Session->read('userData.User.id'), 'NOT' => array('Checkout.payment_state_id' => array('999', '14'))), 'order' => array('Checkout.id' => 'DESC')), 'Offer', 'PaymentState', 'PaymentMethod', 'Company');
        $checkouts = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        foreach ($checkouts as $check) {
            $arrayParams = array(
                'Company' => array('conditions' =>
                    array('Company.id' => $check['Checkout']['company_id'])
                )
            );
            $company = $this->Utility->urlRequestToGetData('companies', 'first', $arrayParams);

            $check['Company'] = $company['Company'];
            $checks[] = $check;
        }

        $news = $this->usersNews();
        $this->set(compact('checks', 'pageName'));
        $this->set(compact('checkouts'));
    }

    public function assinaturas($page, $compId) {
        $this->layout = 'users';
        $pageName = "Assinaturas";

        if (empty($_SESSION ['userData'])) {
            $this->redirect(array(
                'controller' => 'users',
                'action' => 'testa',
                'plugin' => 'users'
            ));
        }

        //ofertas da empresa
        if ($page == 'offersDirect') {

            $this->redirect(array(
                'controller' => 'users',
                'action' => 'testa?compId=' . $compId,
                'plugin' => 'users'));
        }

        //BUSCA DE EMPRESAS POR NOME
        if ($page == 'requisicao') {
            if (!empty($this->request->data['busca'])) {

                $busca = $this->request->data['busca'];
                $params = array('Company' => array('conditions' => array('Company.status' => 'ACTIVE', 'Company.fancy_name LIKE' => "%{$busca}%"), 'order' => array('Company.id' => 'DESC')));
                $companiesV = $this->Utility->urlRequestToGetData('companies', 'all', $params);

                foreach ($companiesV as $company) {
                    $data = date('Y-m-d');
                    $params = array('Offer' => array('conditions' => array('Offer.company_id' => $company['Company']['id'], 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
                    $ofertas = $this->Utility->urlRequestToGetData('offers', 'count', $params);
                    $company['Company']['ofertas'] = $ofertas;

                    $companies[] = $company;
                }

                $this->set(compact('companies'));
                $this->render('Elements/ajax_signatures');
            }
        }

        //DESATIVAR ASSINATURA
        if ($page == 'excluir') {
            $id = $_REQUEST['id'];

            $_SESSION['userNotLogged'] = "usuario não logado";

            $arrayParams = array(
                'CompaniesUser' => array(
                    'id' => $id,
                    'status' => 'INACTIVE'
                ),
            );
            $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
        }

        //ASSINA EMPRESA
        if ($page == 'assina') {
            $id = $_REQUEST['id'];

            $arrayParams = array(
                'CompaniesUser' =>
                array(
                    'conditions' => array(
                        'CompaniesUser.user_id' => $this->Session->read('userData.User.id'),
                        'CompaniesUser.company_id' => $id
                    )),
            );
            $assinado = $this->Utility->urlRequestToGetData('users', 'first', $arrayParams);

            //Se o usuario já tiver assinado a empresa 
            //nós somente mudamos o status
            if ($assinado) {

                $arrayParams = array(
                    'CompaniesUser' => array(
                        'id' => $assinado['CompaniesUser']['id'],
                        'status' => 'ACTIVE'
                    )
                );

                $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
            } else {

                $arrayParams = array(
                    'CompaniesUser' => array(
                        'user_id' => $this->Session->read('userData.User.id'),
                        'company_id' => $id,
                        'status' => 'ACTIVE',
                        'date_register' => date('Y-m-d')
                    )
                );

                $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
            }


            $params = array('CompaniesUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'Company.status' => 'ACTIVE', 'CompaniesUser.status' => 'ACTIVE'), 'order' => array('CompaniesUser.id' => 'DESC')), 'Company');
            $companiesUser = $this->Utility->urlRequestToGetData('companies', 'all', $params);

            foreach ($companiesUser as $company) {
                $data = date('Y-m-d');
                $params = array('Offer' => array('conditions' => array('Offer.company_id' => $company['Company']['id'], 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
                $ofertas = $this->Utility->urlRequestToGetData('offers', 'count', $params);
                $company['Company']['ofertas'] = $ofertas;

                $companies[] = $company;
            }
            $this->set(compact('companies'));
            $this->render('Elements/ajax_signatures');
        }

        $params = array('CompaniesUser' => array('conditions' => array('user_id' => $this->Session->read('userData.User.id'), 'Company.status' => 'ACTIVE', 'CompaniesUser.status' => 'ACTIVE'), 'order' => array('CompaniesUser.id' => 'DESC')), 'Company');
        $companiesUser = $this->Utility->urlRequestToGetData('companies', 'all', $params);

        foreach ($companiesUser as $company) {
            $data = date('Y-m-d');
            $params = array('Offer' => array('conditions' => array('Offer.company_id' => $company['Company']['id'], 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
            $ofertas = $this->Utility->urlRequestToGetData('offers', 'count', $params);
            $company['Company']['ofertas'] = $ofertas;

            //pegando ofertas de acordo com perfil
            $ofertas_perfil = $this->ofertas_perfil($company['Company']['id']);
            $quantidade_perfil = count($ofertas_perfil);
            $company['Company']['ofertas_perfil'] = $quantidade_perfil;

            $companies[] = $company;
        }



        //ENVIO DE EMAIL - TROCAR PARA OFFERS
        $arrayParams = array(
            'Email' => array(
                'email' => 'matheusodilon0@gmail.com',
                'texto' => 'aquivemocorpodotexto'
            )
        );

        //$email = $this->Utility->shareOffer($arrayParams);
        $news = $this->usersNews();
        $this->set(compact('email', 'ofertas_perfil', 'pageName'));
        $this->set(compact('companies'));
    }

    public function formataData2($dataH) {
        $data = explode('/', $dataH);
        if (!empty($data[1])) {
            return $data[2] . '-' . $data[0] . '-' . $data[1];
        } else {
            return 'ERRO - CONVERT DATA';
        }
    }

    /**
     * 
     * Function criada para auxilio no envio de emails 
     * Detalhe da oferta - 
     */
    public function enviaEmail($page) {

        if ($page == 'shareOffer') {

            $offerId = $this->request->data['offerId'];
            $link = $this->request->data['link'];

            $params = array('Offer' => array('conditions' => array('Offer.id' => $offerId)), 'OffersComment', 'OffersPhoto', 'Company');
            $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);

            $email = $this->request->data['email'];
            $this->layout = 'users';
            $arrayParams = array(
                'Email' => array(
                    'email' => $email,
                    'texto' => $offer['Offer']['description'],
                    'link' => $link . '?offer=' . $offerId,
                    'photo' => $offer['Offer']['photo']
                )
            );

            $enviado = $this->Utility->shareOffer($arrayParams);
        }
    }

    public function teste($page) {

        $data = date("Y-m-d");

        $array = array(
            'CompaniesUser' => array(
                'user_id' => 276,
                'company_id' => 119,
                'status' => 'ACTIVE',
                'date_register' => $data
            )
        );

        $retorno = $this->Utility->urlRequestToSaveData('companies', $array);

        if ($page == 'ajax-vitrine') {

            $limite = $this->request->data['limite'];
            $limite = $limite + 2;

            $params = array(
                'Offer' => array(
                    'conditions' => array(
                        'public' => 'ACTIVE',
                        'Offer.status' => 'ACTIVE',
                        'Offer.begins_at <= ' => $data,
                        'Offer.ends_at >= ' => $data
                    ),
                    'order' => array(
                        'Offer.id' => 'DESC'
                    ),
                    'limit' => $limite
                )
            );
            $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);

            $offers2 = null;
            foreach ($offers as $offer) {
                $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
                $filterParams2 = array(
                    'User' => array(
                        'query' => $query2
                    )
                );
                $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
                $offers2[] = $offer;
            }

            $this->set(compact("limite", 'offers2'));
            $this->render('Elements/ajax_vitrine');
        }
        $limit = 6;
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    'public' => 'ACTIVE',
                    'Offer.status' => 'ACTIVE',
                    'Offer.begins_at <= ' => $data,
                    'Offer.ends_at >= ' => $data
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
                'limit' => $limit
            )
        );
        $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        $offers2 = null;
        foreach ($offers as $offer) {
            $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
            $filterParams2 = array(
                'User' => array(
                    'query' => $query2
                )
            );
            $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
            $offers2[] = $offer;
        }

        $facebook = new Facebook(
                array(
            'appId' => '447466838647107',
            'secret' => 'b432e357dd19491aa8d45acd7074b2f6',
                )
        );

        // obtem o id do usuario
        $user = $facebook->getUser();

        $params = array(
            'scope' => 'email, user_about_me, user_birthday, user_hometown, user_location, user_relationships, user_religion_politics'
        );
        $loginUrl = $facebook->getLoginUrl($params);

        $userFlow = '';
        if ($user) {
            $userFlow = 1;
        } else {
            $userFlow = 2;
        }

        //
//        $query = "UPDATE offers_extra_infos SET recurrence = 1 WHERE offer_id = 208;";
//        $paramExtras = array(
//            'User' => array(
//                'query' => $query
//            )
//        );
//        $infosExtras = $this->Utility->urlRequestToGetData('users', 'query', $paramExtras);
        //

        $news = $this->usersNews();

        $statisticsQuery = "select * from offers_statistics where offer_id=214;";
        $statisticsParams = array(
            'User' => array(
                'query' => $statisticsQuery
            )
        );
        $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);


        $getOffer = array(
            'User' => array(
                'conditions' => array(
                    'Offer.id' => 214
                )
            )
        );
        $offer = $this->Utility->urlRequestToGetData('offers', 'first', $getOffer);


        $arrayParams = array('Checkout' =>
            array(
                'Checkout.user_id' => $this->Session->read('userData.User.id'),
                'Checkout.company_id' => 119,
                'Checkout.payment_method_id' => 3,
                'Checkout.offer_id' => 214,
                'Checkout.payment_state_id' => 14,
                'Checkout.unit_value' => '0',
                'Checkout.total_value' => '0',
                'Checkout.amount' => '0',
                'Checkout.shipping_value' => '0',
                'Checkout.shipping_type' => 'CORREIOS',
                'Checkout.delivery_time' => '',
                'Checkout.metrics' => '',
                'Checkout.address' => '',
                'Checkout.city' => '',
                'Checkout.zip_code' => '',
                'Checkout.state' => '',
                'Checkout.district' => '',
                'Checkout.number' => '',
                'Checkout.complement' => '',
                'Checkout.date' => date('Y-m-d'),
                'Checkout.transaction_moip_code' => 0,
                'Checkout.installment' => 0
            )
        );
        //$pagamento = $this->Utility->urlRequestToSaveData('payments', $arrayParams);
        //$code_number = '090102030405060708';
        //$barcord = new barCodeGenrator;


        $data1 = new DateTime('2014-10-01');
        $data2 = new DateTime('1994-08-11');

        $intervalo = $data1->diff($data2);

        $comps = $this->compNews();

        $par = array('Company' => array('fields' => array('id'), 'conditions' => array('category_id' => 1, 'sub_category_id' => 5, 'status' => 'ACTIVE')));
        $empresas = $this->Utility->urlRequestToGetData('companies', 'all', $par);

//

        $arrayParams222 = array('Checkout' =>
            array(
                'Checkout.user_id' => 289,
                'Checkout.company_id' => 119,
                'Checkout.payment_method_id' => 3,
                'Checkout.offer_id' => 222,
                'Checkout.payment_state_id' => 14,
                'Checkout.unit_value' => '0',
                'Checkout.total_value' => '0',
                'Checkout.amount' => '0',
                'Checkout.shipping_value' => '0',
                'Checkout.shipping_type' => 'CORREIOS',
                'Checkout.delivery_time' => '',
                'Checkout.metrics' => '',
                'Checkout.address' => '',
                'Checkout.city' => '',
                'Checkout.zip_code' => '',
                'Checkout.state' => '',
                'Checkout.district' => '',
                'Checkout.number' => '',
                'Checkout.complement' => '',
                'Checkout.date' => date('Y-m/d'),
                'Checkout.transaction_moip_code' => 0,
                'Checkout.installment' => 0
            )
        );
        $pagamento = $this->Utility->urlRequestToSaveData('payments', $arrayParams222);

        $this->set(compact('pagamento', 'cad_desejo', 'comps', 'intervalo', 'ofertas_perfil', 'offers2', 'offers', 'limit', 'retorno', 'loginUrl', 'userFlow', 'news', 'statistics', 'pagamento', 'barcode'));
    }

    public function ajaxLogin($page) {

        if ($page == 'first') {
            $this->layout = '';
            $this->render('Elements/ajax_login_first');
        }

        //mostra tela de esqueci minha senha
        if ($page == 'show-forgot-pass') {
            $this->layout = 'users';
            $this->render('Elements/ajax_login_forgot_pass');
        }

        //recupera senha e envia para email do usuario
        if ($page == 'recover-pass') {
            $arrayParams = array(
                'User' => array(
                    'fields' => array('User.id', 'User.name', 'User.email'),
                    'conditions' => array('User.email' => $this->request->data['email'])
                )
            );

            $nova_senha = $this->Utility->urlRequestPasswordRecovery('users', 'first', $arrayParams);

            if (!$nova_senha) {
                $mensagem = 'SAVE_ERROR';
            } else {
                $mensagem = 'SAVE_OK';
            }
            $this->set(compact('mensagem'));
            $this->render('Elements/ajax_login_pass_return');
        }

        //mostra tela de cadastro de usuario
        if ($page == 'show-create') {
            $this->layout = 'users';
            $this->render('Elements/ajax_login_create');
        }

        //salva usuario na base de dados
        if ($page == 'createUser') {

            $nome = $this->request->data['nome'];
            $email = $this->request->data['email'];
            $pass = $this->request->data['pass'];

            $params = array('User' => array('conditions' => array('User.email' => $email)));
            $verificaEmail = $this->Utility->urlRequestToGetData('users', 'count', $params);

            if ($verificaEmail == true) {
                $user['status'] = 'SAVE_ERROR';
            } else {

                $params['User']['name'] = $nome;
                $params['User']['email'] = $email;
                $params['User']['password'] = $pass;
                $params['User']['date_register'] = date('Y/m/d');
                $params['User']['status'] = 'ACTIVE';
                $params['User']['photo'] = "http://54.94.182.35/adventa/portal/app/webroot/img/unknow_user.jpg";

                $user = $this->Utility->urlRequestToSaveData('users', $params);

                //cria sessao com dados do usuario
                $this->Session->write('firstlogin', true);
                $this->Session->write('sessionLogado', true);
                $this->Session->write('userData', $user['data']);
                if ($user) {
                    unset($params['User']['date_register']);
                    unset($params['User']['status']);
                    //pr($params);exit;
                    //envia email de boas vindas
                    $user_email = $this->Utility->urlRequestToLoginUserData('users', 'first', $params);
                }
            }
            $this->usersNews();
            $this->set(compact('user'));

            //criando o 'using user'
            $usingUser = "insert into users_using(user_id, mobile, android, ios) values({$user['data']['User']['id']}, 'INACTIVE','INACTIVE','INACTIVE');";
            $queryParams = array(
                'User' => array(
                    'query' => $usingUser
                )
            );
            $usingUserParam = $this->Utility->urlRequestToGetData('users', 'query', $queryParams);

            //criando o users_preferences
            $sqlPref = "insert into users_preferences(user_id, notifications_periodicity) values({$user['data']['User']['id']}, 'UNITARY');";
            $paramsPref = array(
                'User' => array(
                    'query' => $sqlPref
                )
            );
            $createUserPref = $this->Utility->urlRequestToGetData('users', 'query', $paramsPref);

            $params = '';
            $params['userName'] = $nome;
            $params['userEmail'] = $email;
            $params['pwd'] = $pass;
            $this->changeLogin('newUser', $params);
            //$_SESSION['userIsLogged'] = true;

            $this->render('Elements/ajax_login_create_return');
        }

        if ($page == 'login') {
            $this->layout = '';

            $facebook = new Facebook(
                    array(
                'appId' => '447466838647107',
                'secret' => 'b432e357dd19491aa8d45acd7074b2f6',
                    )
            );

            // obtem o id do usuario
            $user = $facebook->getUser();

            $params = array(
                'scope' => 'email, user_about_me, user_birthday, user_hometown, user_location, user_relationships, user_religion_politics'
            );
            $loginUrl = $facebook->getLoginUrl($params);


            $this->set(compact('loginUrl'));

            $this->render('Elements/ajax_login_layout');
            // $this->render('Elements/ajax_login_first');
        }

        if ($page == 'logar') {
            $this->layout = 'users';
            $arrayParams = array(
                'User' => array(
                    'conditions' => array(
                        'User.email' => $this->request->data['email'],
                        'User.password' => md5($this->request->data['pass']),
                        'User.status' => 'ACTIVE'
                    )
                )
            );

            $usuario = $this->Utility->urlRequestToGetData('users', 'first', $arrayParams);

            //capturando preferencias do usuario
            $preferenceQuery = "select * from users_preferences where user_id=289;";
            $preferenceParam = array(
                'User' => array(
                    'query' => $preferenceQuery
                )
            );
            $preferences = $this->Utility->urlRequestToGetData('users', 'query', $preferenceParam);
            $preferencias = $preferences[0];

            if (!empty($usuario)) {
                $usuario['User']['login'] = 'LOGGED';
            }

            $this->Session->write("userPreferences", $preferencias['users_preferences']);
            $this->Session->write('userData', $usuario);
            $this->usersNews();
            $this->set(compact('usuario'));

            $this->render('Elements/ajax_log');
        }
    }

    //CHECKOUT
    public function realizarCompra($page) {
        $this->layout = 'users';
        $pageName = "FINALIZAR COMPRA";

        $_SESSION['userIsLogged'] = true;

        if (empty($_SESSION ['userData'])) {
            $this->redirect(array(
                'controller' => 'users',
                'action' => 'testa',
                'plugin' => 'users'
            ));
        }

        /**
         * Salva as métricas do pedido caso o usuario tenha ido da vitrine diretamente 
         * para a tela de checkout 
         */
        if ($page == 'saveMetrics') {

            $metricas[tamanho] = $this->request->data['tamanho'];
            $metricas[cor] = $this->request->data['cor'];

            $this->Session->write('Carro.Opcoes.metricas', $metricas);
        }

        /*
         * Atualiza tabela frete com mudança de endereço
         */
        if ($page == 'escolheEnd') {

            $qtd = $this->request->data['quantidade'];
            $cepOrigem = $this->request->data['origem'];
            $idEndereco = $this->request->data['endereco'];

            $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.id' => $idEndereco)));
            $endereco = $this->Utility->urlRequestToGetData('users', 'first', $params);
            $cepDestino = $endereco['AditionalAddressesUser']['zip_code'];

            $offer = $this->Session->read('Carro.Offer');

            //frete PAC
            $fretePac = explode("-", $this->x_calculaFrete('41106', $cepOrigem, $cepDestino, ($offer['Offer']['weight'] * $qtd)));

            //frete SEDEX 
            $freteSedex = explode("-", $this->x_calculaFrete('40010', $cepOrigem, $cepDestino, ($offer['Offer']['weight'] * $qtd)));

            //frete SEDEX a cobrar 
            $freteSedexCobrar = explode("-", $this->x_calculaFrete('40045', $cepOrigem, $cepDestino, ($offer['Offer']['weight'] * $qtd)));


            $this->set(compact('fretePac', 'freteSedex', 'freteSedexCobrar'));
            $this->layout = '';
            $this->render('Elements/ajax_check_escolhe_end');
        }

        /* Atualiza valor total quando há mudança na seleção do frete 
         */
        if ($page == 'mudaFrete') {
            $frete = str_replace(",", ".", $this->request->data['frete']);
            $valor = $this->request->data['valor'];
            $valorTotal = $valor + $frete;
            $this->Session->write('Carro.Oferta.value_total', $valorTotal);
            $this->set(compact('frete', 'valor'));
            $this->layout = '';
            $this->render('Elements/ajax_checkout_total');
        }

        /*
         * Acionado via ajax
         * Atualiza conteudo da tabela de frete e do preço total
         * É acionado por requisição ajax 
         * quando a quantidade do produto é somada ou diminuida
         * 
         */
        if ($page == 'mudaQtd') {

            $qtd = $this->request->data['quantidade'];
            $cepOrigem = $this->request->data['origem'];
            $cepDestino = $this->request->data['destino'];
            $valor = $this->request->data['valor'];

            $offer = $this->Session->read('Carro.Offer');

            //frete PAC
            $fretePac = explode("-", $this->x_calculaFrete('41106', $cepOrigem, $cepDestino, ($offer['Offer']['weight'] * $qtd)));

            //frete SEDEX 
            $freteSedex = explode("-", $this->x_calculaFrete('40010', $cepOrigem, $cepDestino, ($offer['Offer']['weight'] * $qtd)));

            //frete SEDEX a cobrar 
            $freteSedexCobrar = explode("-", $this->x_calculaFrete('40045', $cepOrigem, $cepDestino, ($offer['Offer']['weight'] * $qtd)));

            $frete = str_replace(",", ".", $fretePac[0]);
            $valorTotal = $valor * $qtd;
            $this->Session->write('Carro.value_total', $valorTotal + $frete);

            $this->set(compact('valor', 'qtd'));
            $this->set(compact('fretePac', 'freteSedex', 'freteSedexCobrar', 'frete'));
            $this->render('Elements/ajax_checkout_quantidade');
        }

        /*
         * Acionado via ajax
         * Adiciona endereço amarrado ao usuario 
         * e atualiza tabela de endereço dinâmicamente
         */
        if ($page == 'addAddress') {

            $add['AditionalAddressesUser']['user_id'] = $this->Session->read('userData.User.id');
            $add['AditionalAddressesUser']['label'] = $this->request->data['label'];
            $add['AditionalAddressesUser']['address'] = $this->request->data['address'];
            $add['AditionalAddressesUser']['number'] = $this->request->data['number'];
            $add['AditionalAddressesUser']['district'] = $this->request->data['district'];
            $add['AditionalAddressesUser']['city'] = $this->request->data['city'];
            $add['AditionalAddressesUser']['state'] = $this->request->data['uf'];
            $add['AditionalAddressesUser']['complement'] = $this->request->data['complement'];
            $add['AditionalAddressesUser']['zip_code'] = $this->request->data['cep'];

            $address = $this->Utility->urlRequestToSaveData('users', $add);

            //Captura endereços do usuario
            $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.user_id' => $this->Session->read('userData.User.id'))));
            $enderecos = $this->Utility->urlRequestToGetData('users', 'all', $params);

            $this->set(compact('enderecos'));
            $this->layout = '';
            $this->render('Elements/ajax_checkout_endereco');
        }

        /*
         * Aciona via ajax
         * Atualiza endereço 
         * atualiza tabela de endereços dinâmicamente 
         */
        if ($page == 'updateAddress') {

            $add['AditionalAddressesUser']['user_id'] = $this->Session->read('userData.User.id');
            $add['AditionalAddressesUser']['id'] = $this->request->data['id'];
            $add['AditionalAddressesUser']['label'] = $this->request->data['label'];
            $add['AditionalAddressesUser']['address'] = $this->request->data['address'];
            $add['AditionalAddressesUser']['number'] = $this->request->data['number'];
            $add['AditionalAddressesUser']['district'] = $this->request->data['district'];
            $add['AditionalAddressesUser']['city'] = $this->request->data['city'];
            $add['AditionalAddressesUser']['state'] = $this->request->data['uf'];
            $add['AditionalAddressesUser']['complement'] = $this->request->data['complement'];
            $add['AditionalAddressesUser']['zip_code'] = $this->request->data['cep'];

            $address = $this->Utility->urlRequestToSaveData('users', $add);

            //Captura endereços do usuario
            $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.user_id' => $this->Session->read('userData.User.id'))));
            $enderecos = $this->Utility->urlRequestToGetData('users', 'all', $params);

            $this->set(compact('enderecos'));
            $this->render('Elements/ajax_checkout_endereco');
        }

        /*
         * Acionado via Ajax
         * Captura o endereço selecionado para edição 
         * e carrega dados um uma "div pop up" para a edição
         */
        if ($page == 'editAddress') {

            $id = $this->request->data['id'];
            $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.id' => $id)));
            $editEnd = $this->Utility->urlRequestToGetData('users', 'first', $params);

            $this->set(compact('editEnd', 'id'));
            $this->render('Elements/ajax_checkout_edita_endereco');
        }

        /*
         * Compra via boleto
         */
        if ($page == 'geraTokenCompra') {

            $idEnd = $this->request->data['endId'];
            $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.id' => $idEnd)));
            $endereco = $this->Utility->urlRequestToGetData('users', 'first', $params);
            $offer = $this->Session->read('Carro.Offer');
            $frete = str_replace(",", ".", $this->request->data['frete']);
            $qtd = $this->request->data['quantidade'];
            $valorSemFrete = $this->request->data['total'];
            $valorTotal = $valorSemFrete + $frete;
            $metricas = $this->Session->read('Carro.Opcoes.metricas');
            $diasEntrega = $this->request->data['diasEntrega'];

            //transformando metricas em texto
            $chaves = array_keys($metricas);
            $textMetricas = '';
            for ($i = 0; $i <= (count($metricas) - 1); $i++) {
                $textMetricas = $textMetricas . $chaves[$i] . ':' . $metricas[$chaves[$i]] . ', ';
            }
            /*
             * Salva a requisiçao em nossa base, e gera já o número do pedido
             * número do pedido usado como identificação unica idUnique
             */
//            $sql = "insert into checkouts(user_id, company_id, payment_method_id, offer_id, payment_state_id, unit_value, total_value, amount, shipping_value, shipping_type, delivery_time, metrics, address, city, zip_code, state, district, complement, number, date, transaction_moip_code, installment) "
//                    . "values (" . $this->Session->read('userData.User.id') . "," . $offer['Company']['id'] . ", 73, " . $offer['Offer']['id'] . ", 3, " . $offer['Offer']['value'] . ", " . $valorTotal . ", " . $qtd . "," . $frete . ", 'CORREIOS', " . $diasEntrega . ", "
//                    . "'" . $textMetricas . "', '" . $endereco['AditionalAddressesUser']['address'] . "', '" . $endereco['AditionalAddressesUser']['city'] . "', '" . $endereco['AditionalAddressesUser']['zip_code'] . "', '" . $endereco['AditionalAddressesUser']['state'] . "', '" . $endereco['AditionalAddressesUser']['district'] . "', '" . $endereco['AditionalAddressesUser']['complement'] . "'," . $endereco['AditionalAddressesUser']['number'] . " , '" . date('Y-m-d') . "', '', 0);";
//            $params2 = array('User' => array('query' => $sql));
//            $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params2);

            $arrayParams222 = array('Checkout' =>
                array(
                    'Checkout.user_id' => $this->Session->read('userData.User.id'),
                    'Checkout.company_id' => $offer['Company']['id'],
                    'Checkout.payment_method_id' => 73,
                    'Checkout.offer_id' => $offer['Offer']['id'],
                    'Checkout.payment_state_id' => 3,
                    'Checkout.unit_value' => $offer['Offer']['value'],
                    'Checkout.total_value' => $valorTotal,
                    'Checkout.amount' => $qtd,
                    'Checkout.shipping_value' => $frete,
                    'Checkout.shipping_type' => 'CORREIOS',
                    'Checkout.delivery_time' => $diasEntrega,
                    'Checkout.metrics' => $textMetricas,
                    'Checkout.address' => $endereco['AditionalAddressesUser']['address'],
                    'Checkout.city' => $endereco['AditionalAddressesUser']['city'],
                    'Checkout.zip_code' => $endereco['AditionalAddressesUser']['zip_code'],
                    'Checkout.state' => $endereco['AditionalAddressesUser']['state'],
                    'Checkout.district' => $endereco['AditionalAddressesUser']['district'],
                    'Checkout.number' => $endereco['AditionalAddressesUser']['number'],
                    'Checkout.complement' => $endereco['AditionalAddressesUser']['complement'],
                    'Checkout.date' => date('Y-m/d'),
                    'Checkout.transaction_moip_code' => 0,
                    'Checkout.installment' => 0
                )
            );
            $pagamento = $this->Utility->urlRequestToSaveData('payments', $arrayParams222);


            //caprtura checkout que acabamos de inserir
            $arrayParamsCheck = array(
                'Checkout' =>
                array(
                    'conditions' => array(
                        'Checkout.user_id' => $this->Session->read('userData.User.id')
                    ))
            );
            $checks = $this->Utility->urlRequestToGetData('payments', 'all', $arrayParamsCheck);
            //captura a compra em questão
            $compra = $checks[count($checks) - 1];


            //chamada do moip 
            $arrayParams = array('Payments' => array(
                    'email_empresa' => 'matheus.odilon@accential.com.br',
                    'porcentagem' => '2.00',
                    'parcelamento' => 'ACTIVE',
                    'parcelamento_juros' => 'ACTIVE',
                    'key' => '11PB4FPN68M1FE8MAPWUDIMEHFIGM8P6DMSBNXZZ',
                    'token' => 'JK75V6UGKYYUZR2ICVHJSSLD687UEJ9H',
                    'idUnique' => $compra['Checkout']['id'],
                    'reason' => $offer['Offer']['title'],
                    'value' => $valorTotal,
                    'setPlayer' => array(
                        'name' => $this->Session->read('userData.User.name'),
                        'email' => $this->Session->read('userData.User.email'),
                        'payerId' => $this->Session->read('userData.User.id'),
                        'billingAddress' => array(
                            'address' => $endereco['AditionalAddressesUser']['address'],
                            'number' => $endereco['AditionalAddressesUser']['number'],
                            'complement' => $endereco['AditionalAddressesUser']['complement'],
                            'city' => $endereco['AditionalAddressesUser']['city'],
                            'neighborhood' => $endereco['AditionalAddressesUser']['district'],
                            'state' => $endereco['AditionalAddressesUser']['state'],
                            'country' => 'BRA',
                            'zipCode' => $endereco['AditionalAddressesUser']['zip_code'],
                            'phone' => '(11)3929-2201'
                        )
                    )
                )
            );

            $dados = $this->Utility->urlRequestToCheckout('payments', $arrayParams);

            /**
             * 
             * Atualiza o registro da compra em nosso banco de dados
             * insere o transaction_moip_code
             * 
             */
            $updateQuery = "update checkouts set transaction_moip_code='" . $dados['ID'] . "' where id = " . $compra['Checkout']['id'] . ";";
            $updateParams = array('User' => array('query' => $updateQuery));
            $updateCompra = $this->Utility->urlRequestToGetData('users', 'query', $updateParams);

            /*
             * Nesse ponto nós guardamos as informaçãos geradas pelo MoIP (ID e Token)
             * Essas informações serão usuadas futuramente para regerear boleto ou
             * para futuras consultas 
             */
            $extraSql = "insert into checkouts_extra_infos(checkout_id, moip_id, moip_token) values(" . $compra['Checkout']['id'] . ", '" . $dados['ID'] . "', '" . $dados['token'] . "');";
            $paramsExtras = array('User' => array('query' => $extraSql));
            $extraInfos = $this->Utility->urlRequestToGetData('users', 'query', $paramsExtras);

            //Estatisticas - Guarda compras realizadas por boleto
            $statisticsQuery = "select * from offers_statistics where offer_id=" . $offer['Offer']['id'] . ";";
            $statisticsParams = array(
                'User' => array(
                    'query' => $statisticsQuery
                )
            );
            $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);

            $statisticsQuery2 = "update offers_statistics set purchased_billet = purchased_billet+1 where id = " . $statistics[0]['offers_statistics']['id'] . ";";
            $statisticsParams2 = array(
                'User' => array(
                    'query' => $statisticsQuery2
                )
            );
            $statistics2 = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams2);
            //

            $this->set(compact('arrayParams', 'endereco', 'offer', 'dados', 'pagamento', 'checks'));
            $this->render('Elements/ajax_check_pagamento_boleto');
        }

        /*
         * Compra via cartão
         * Ver melhor descrição em 
         * ajax_check_pagamento_cartao.phtml
         */
        if ($page == 'geraTokenCompraPorCartao') {

            $idEnd = $this->request->data['endId'];
            $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.id' => $idEnd)));
            $endereco = $this->Utility->urlRequestToGetData('users', 'first', $params);
            $offer = $this->Session->read('Carro.Offer');
            $frete = str_replace(",", ".", $this->request->data['frete']);
            $qtd = $this->request->data['quantidade'];
            $valorSemFrete = $this->request->data['total'];
            $valorTotal = $valorSemFrete + $frete;
            $metricas = $this->Session->read('Carro.Opcoes.metricas');

            //transformando metricas em texto
            $chaves = array_keys($metricas);
            $textMetricas = '';
            for ($i = 0; $i <= (count($metricas) - 1); $i++) {
                $textMetricas = $textMetricas . $chaves[$i] . ':' . $metricas[$chaves[$i]] . ', ';
            }
            /*
             * Salva a requisiçao em nossa base, e gera já o número do pedido
             * número do pedido usado como identificação unica idUnique
             */
            $sql = "insert into checkouts(user_id, company_id, payment_method_id, offer_id, payment_state_id, unit_value, total_value, amount, shipping_value, shipping_type, delivery_time, metrics, address, city, zip_code, state, district, complement, number, date, transaction_moip_code, installment) "
                    . "values (" . $this->Session->read('userData.User.id') . "," . $offer['Company']['id'] . ", 3, " . $offer['Offer']['id'] . ", 2, " . $offer['Offer']['value'] . ", " . $valorTotal . ", " . $qtd . "," . $frete . ", 'CORREIOS', 1, "
                    . "'" . $textMetricas . "', '" . $endereco['AditionalAddressesUser']['address'] . "', '" . $endereco['AditionalAddressesUser']['city'] . "', '" . $endereco['AditionalAddressesUser']['zip_code'] . "', '" . $endereco['AditionalAddressesUser']['state'] . "', '" . $endereco['AditionalAddressesUser']['district'] . "', '" . $endereco['AditionalAddressesUser']['complement'] . "'," . $endereco['AditionalAddressesUser']['number'] . " , '" . date('Y-m-d') . "', '', 0);";
            $params2 = array('User' => array('query' => $sql));
            $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params2);


            //caprtura checkout que acabamos de inserir
            $arrayParamsCheck = array(
                'Checkout' =>
                array(
                    'conditions' => array(
                        'Checkout.user_id' => $this->Session->read('userData.User.id')
                    ))
            );
            $checks = $this->Utility->urlRequestToGetData('payments', 'all', $arrayParamsCheck);
            //captura a compra em questão
            $compra = $checks[count($checks) - 1];


            //chamada do moip 
            $arrayParams = array('Payments' => array(
                    'email_empresa' => 'matheus.odilon@accential.com.br',
                    'porcentagem' => '2.00',
                    'parcelamento' => 'ACTIVE',
                    'parcelamento_juros' => 'ACTIVE',
                    'key' => '11PB4FPN68M1FE8MAPWUDIMEHFIGM8P6DMSBNXZZ',
                    'token' => 'JK75V6UGKYYUZR2ICVHJSSLD687UEJ9H',
                    'idUnique' => $compra['Checkout']['id'],
                    'reason' => $offer['Offer']['title'],
                    'value' => $valorTotal,
                    'setPlayer' => array(
                        'name' => $this->Session->read('userData.User.name'),
                        'email' => $this->Session->read('userData.User.email'),
                        'payerId' => $this->Session->read('userData.User.id'),
                        'billingAddress' => array(
                            'address' => $endereco['AditionalAddressesUser']['address'],
                            'number' => $endereco['AditionalAddressesUser']['number'],
                            'complement' => $endereco['AditionalAddressesUser']['complement'],
                            'city' => $endereco['AditionalAddressesUser']['city'],
                            'neighborhood' => $endereco['AditionalAddressesUser']['district'],
                            'state' => $endereco['AditionalAddressesUser']['state'],
                            'country' => 'BRA',
                            'zipCode' => $endereco['AditionalAddressesUser']['zip_code'],
                            'phone' => '(11)3929-2201'
                        )
                    )
                )
            );

            $dados = $this->Utility->urlRequestToCheckout('payments', $arrayParams);

            /**
             * 
             * Atualiza o registro da compra em nosso banco de dados
             * insere o transaction_moip_code
             * 
             */
            $updateQuery = "update checkouts set transaction_moip_code='" . $dados['ID'] . "' where id = " . $compra['Checkout']['id'] . ";";
            $updateParams = array('User' => array('query' => $updateQuery));
            $updateCompra = $this->Utility->urlRequestToGetData('users', 'query', $updateParams);

            //Estatisticas - Guarda compras realizadas por cartão
            $statisticsQuery = "select * from offers_statistics where offer_id=" . $offer['Offer']['id'] . ";";
            $statisticsParams = array(
                'User' => array(
                    'query' => $statisticsQuery
                )
            );
            $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);

            $statisticsQuery2 = "update offers_statistics set purchased_card = purchased_card+1 where id = " . $statistics[0]['offers_statistics']['id'] . ";";
            $statisticsParams2 = array(
                'User' => array(
                    'query' => $statisticsQuery2
                )
            );
            $statistics2 = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams2);
            //

            $this->set(compact('arrayParams', 'endereco', 'offer', 'dados', 'pagamento', 'checks'));
            $this->render('Elements/ajax_check_pagamento_cartao');
        }

        /*
         * Mostra Pop up de Resumo do Pedido
         */
        if ($page == 'resumeCheck') {

            //captura checkout que acabamos de inserir
            $arrayParamsCheck = array(
                'Checkout' =>
                array(
                    'conditions' => array(
                        'Checkout.user_id' => $this->Session->read('userData.User.id')
                    )),
                'PaymentMethod'
            );
            $checks = $this->Utility->urlRequestToGetData('payments', 'all', $arrayParamsCheck);
            //captura a compra em questão
            $compra = $checks[count($checks) - 2];


            $this->set(compact('compra', 'checks'));
            $this->render('Elements/ajax_check_resumo_pedido');
        }


        $user = $this->Session->read('userData.User');

        //Captura endereços do usuario
        $params = array('AditionalAddressesUser' => array('conditions' => array('AditionalAddressesUser.user_id' => $this->Session->read('userData.User.id'))));
        $enderecos = $this->Utility->urlRequestToGetData('users', 'all', $params);

        if (!empty($user['address'])) {
            $add['AditionalAddressesUser']['label'] = 'Endereço de cadastro';
            $add['AditionalAddressesUser']['address'] = $user['address'];
            $add['AditionalAddressesUser']['number'] = $user['number'];
            $add['AditionalAddressesUser']['district'] = $user['district'];
            $add['AditionalAddressesUser']['city'] = $user['city'];
            $add['AditionalAddressesUser']['state'] = $user['state'];
            $add['AditionalAddressesUser']['complement'] = $user['complement'];
            $add['AditionalAddressesUser']['zip_code'] = $user['zip_code'];
            $enderecos[count($enderecos) + 1] = $add;
        }

        $offer = $this->Session->read('Carro.Offer');
        $cepOrigem = str_replace('-', '', $this->Session->read('Carro.Offer.Company.zip_code'));
        $cepDestino = str_replace('-', '', $enderecos['0']['AditionalAddressesUser']['zip_code']);

        //frete PAC
        $fretePac = explode("-", $this->x_calculaFrete('41106', $cepOrigem, $cepDestino, $offer['Offer']['weight']));

        //frete SEDEX 
        $freteSedex = explode("-", $this->x_calculaFrete('40010', $cepOrigem, $cepDestino, $offer['Offer']['weight']));

        //frete SEDEX a cobrar 
        $freteSedexCobrar = explode("-", $this->x_calculaFrete('40045', $cepOrigem, $cepDestino, $offer['Offer']['weight']));

        $add['AditionalAddressesUser']['id'] = 2;
        $add['AditionalAddressesUser']['label'] = 'Casa 1';
        $this->Utility->urlRequestToGetData('users', 'query', $add);

        $frete = str_replace(",", ".", $fretePac[0]);

        //chamada do moip 
        $arrayParams = array('Payments' => array(
                'email_empresa' => 'matheus.odilon@accential.com.br',
                'porcentagem' => '2.00',
                'parcelamento' => 'ACTIVE',
                'parcelamento_juros' => 'ACTIVE',
                'key' => '11PB4FPN68M1FE8MAPWUDIMEHFIGM8P6DMSBNXZZ',
                'token' => 'JK75V6UGKYYUZR2ICVHJSSLD687UEJ9H',
                'idUnique' => 198,
                'reason' => 'CAMISETA',
                'value' => 100.00,
                'setPlayer' => array(
                    'name' => 'Gandhi',
                    'email' => 'gandhi@tst.com',
                    'payerId' => 298,
                    'billingAddress' => array(
                        'address' => 'endereco',
                        'number' => 290,
                        'complement' => '',
                        'city' => 'São Paulo',
                        'neighborhood' => 'Esse bairro',
                        'state' => 'SP',
                        'country' => 'BRA',
                        'zipCode' => '08540500',
                        'phone' => '(11)3929-2201'
                    )
                )
            )
        );


        //pr($_SESSION['Carrinho']);exit;											
        //$dados = $this->Utility->urlRequestToCheckout('payments', $arrayParams);
        /////////

        $this->set(compact('fretePac', 'freteSedex', 'freteSedexCobrar', 'frete', 'cepOrigem', 'cepDestino', 'addUserOffer'));
        $this->set(compact('pageName', 'tstFrete', 'dados'));
        $this->set(compact('enderecos'));
        $this->set(compact('user'));
    }

    /**
     * Ofertas das empresas assinadas
     */
    public function minhasOfertas() {
        $this->layout = 'users';
        $data = date('Y-m-d');

        /**
         * Captura id das empresas assinadas pelo usuario
         * para realizar pesquisa de ofertas por empresas assinadas
         */
        $arrayParams = array(
            'CompaniesUser' =>
            array(
                'fields' => array('CompaniesUser.id'),
                'conditions' => array(
                    'CompaniesUser.user_id' => 288,
                    'CompaniesUser.status' => 'ACTIVE'
                ))
        );

        $empresasAssinadas = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

        $personalOffers = array();
        foreach ($empresasAssinadas as $empresa) {

            $params = array(
                'Offer' =>
                array(
                    'conditions' =>
                    array(
                        'Offer.company_id' => $empresa['CompaniesUser']['id'],
                        'Offer.status' => 'ACTIVE',
                        'Offer.begins_at <= ' => $data,
                        'Offer.ends_at >= ' => $data
                    )
                )
            );
        }
    }

    /*
     * Generico
     */

    private function x_calculaFrete($servico, $cepOrigem, $cepDestino, $peso) {
        //$total = $this->Session->read('Carrinho.Opcoes.quantidade') * $this->Session->read('Carrinho.Oferta.value');
        //$totalPeso = $this->Session->read('Carrinho.Opcoes.quantidade') * $this->Session->read('Carrinho.Oferta.weight');
        // $this->Session->write('Carrinho.Oferta.value_total', $total);
        //$this->Session->write('Carrinho.Oferta.weight_total', $totalPeso);
        //pegando CEP de origem(empresa) e fazendo calculo de frete
        // $params = array('Company' => array('fields' => array('Company.zip_code'), 'conditions' => array('id' => $this->Session->read('Carrinho.Oferta.company_id'))));
        //$cep = $this->Utility->urlRequestToGetData('companies', 'first', $params);
        //$cep = $cep['Company']['zip_code'];
        $frete = $this->Utility->calcFrete($servico, $cepOrigem, $cepDestino, $peso);
        if ($frete == false) {
            return false;
        } else {
            return $frete;
        }
    }

    /**
     * Busca ofertas por id da categoria
     * @param type $idCategoria
     * @return type offers list
     */
    public function searchOfferByCategory($idCategoria) {

        $query2 = "select * from offers_extra_infos where category_id = {$idCategoria};";
        $filterParams2 = array(
            'User' => array(
                'query' => $query2
            )
        );
        $offers = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);

        $data = date("Y-m-d");
        $ofertas = null;

        foreach ($offers as $offer) {
            $params = array('Offer' => array('conditions' => array('Offer.id' => $offer['offers_extra_infos']['offer_id'], 'Offer.status' => 'ACTIVE', 'Offer.begins_at <= ' => $data, 'Offer.ends_at >= ' => $data)));
            $ofertas[] = $this->Utility->urlRequestToGetData('companies', 'first', $params);
        }

        return $ofertas;
    }

    /**
     * Busca ofertas de acordo com perfil que usuario formou no formulario
     * 
     */
    public function searchOfferByProfile($perfil) {

        $data = date('Y-m-d');

        $params = array('OffersFilter' =>
            array(
                'conditions' =>
                array(
                    'OffersFilter.religion LIKE' => "%{$perfil['Profile']['religion']}%",
                    'OffersFilter.gender LIKE' => "%{$perfil['Profile']['gender']}%",
                    'OffersFilter.political LIKE' => "%{$perfil['Profile']['political']}%",
                    'OffersFilter.location LIKE' => "%{$perfil['Profile']['location']}%",
                    'OffersFilter.relationship_status LIKE' => "%{$perfil['Profile']['relationship_status']}%",
                    'Offer.status' => 'ACTIVE',
                    'Offer.begins_at <= ' => $data,
                    'Offer.ends_at >= ' => $data,
                )
            ),
            'Offer'
        );

        $ofertas_perfil = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        return $ofertas_perfil;
    }

    public function myOffers() {
        $this->layout = 'users';
        $pageName = "Vitrine";

        $gender = $this->request->data['gender'];
        $relationshipStatus = $this->request->data['status'];
        $religion = $this->request->data['religion'];
        $political = $this->request->data['political'];
        $age = $this->request->data['age'];
        $location = $this->request->data['locat'];


        $perfil = null;
        $perfil['Profile']['gender'] = "{$gender}";
        $perfil['Profile']['political'] = "{$political}";
        $perfil['Profile']['location'] = "{$location}";
        $perfil['Profile']['religion'] = "{$religion}";
        $perfil['Profile']['relationship_status'] = "{$relationshipStatus}";

        $offersByProfile = $this->searchOfferByProfile($perfil);
        $offers = null;
        $i = 0;
        foreach ($offersByProfile as $of) {
            $offers[$i]['Offer'] = $of['Offer'];
            $i++;
        }

        $offers2 = null;
        foreach ($offers as $offer) {
            $query2 = "select * from offers_extra_infos where offer_id = {$offer['Offer']['id']};";
            $filterParams2 = array(
                'User' => array(
                    'query' => $query2
                )
            );
            $offer['extra_infos'] = $this->Utility->urlRequestToGetData('users', 'query', $filterParams2);
            $offers2[] = $offer;
        }

        $limite = 8;
        $this->set(compact('limite', 'offers2', 'perfil'));
        $this->render('Elements/ajax_vitrine');
    }

    /**
     *  Função que busca todos as "novidades" para o usuário
     *  Pegamos o id diretamente do usuario salvo na sessao
     * dado o fato dessa função ser executada APENAS E EXCLUSIVAMENTE 
     * APÓS o login
     * @return type
     */
    public function usersNews() {

        //iniciando a variavel 
        $offerNotification = '';

        /**
         * Ofertas criadas especialmente para ESSE usuário
         */
        $paramsOffersToMe = array(
            'OffersUser' =>
            array(
                'conditions' => array(
                    'OffersUser.user_id' => $this->Session->read('userData.User.id')
                )),
            'Offer'
        );
        $offersToMe = $this->Utility->urlRequestToGetData('offers', 'all', $paramsOffersToMe);

        /*
         * Convites de assinaturas para usuario
         */
        $paramsInvit = array(
            'CompaniesInvitationsUser' =>
            array(
                'conditions' => array(
                    'CompaniesInvitationsUser.user_id' => $this->Session->read('userData.User.id'),
                    'CompaniesInvitationsUser.status' => 'WAIT'
                )),
            'Company'
        );
        $invitesToMe = $this->Utility->urlRequestToGetData('companies', 'all', $paramsInvit);

//        $offerNotification['OffersToMe'] = $offersToMe;
//        $offerNotification['InvitesToMe'] = $invitesToMe;



        $offerNotification['OffersToMe'] = $offersToMe;
        $offerNotification['InvitesToMe'] = $invitesToMe;

        $total = count($offersToMe) + count($invitesToMe);


        $this->Session->write('userData.Notification.total', $total);
        $this->Session->write('userData.Notification.offersToMe', $offersToMe);
        $this->Session->write('userData.Notification.invitesToMe', $invitesToMe);
        $this->Session->write('userData.Notification', $offerNotification);

        return $offerNotification;
    }

    public function notification($page) {

        if ($page == 'assina') {

            $assina['CompaniesInvitationsUser']['id'] = $this->request->data['id'];
            $assina['CompaniesInvitationsUser']['status'] = 'ACTIVE';

            $assinando = $this->Utility->urlRequestToSaveData('users', $assina);
        } else
        if ($page == 'nao-assina') {

            $naoAssina['CompaniesInvitationsUser']['id'] = $this->request->data['id'];
            $naoAssina['CompaniesInvitationsUser']['status'] = 'INACTIVE';

            $assinando = $this->Utility->urlRequestToSaveData('users', $naoAssina);
        }

        //atualiza os dados na sessão do usuario
        $news = $this->usersNews();
    }

    /**
     * Salva as estatísticas do sistema
     * @param type $page
     */
    public function saveStatistics($page) {

        if ($page == 'offerDetailClick') {
            
        }

        if ($page == 'offerCheckoutClick') {
            
        }
    }

    /**
     * Notifications de novidades para a empresa
     */
    public function compNews() {

        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    'Checkout.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'Checkout.total_value > ' => '0',
                    'Checkout.payment_state_id' => 4
                ),
                'order' => array(
                    'Checkout.id' => 'DESC'
                ),
                'limit' => 5
            ),
            'PaymentState',
            'Offer',
            'User',
            'OffersUser'
        );
        $checkouts = $this->Utility->urlRequestToGetData('payments', 'all', $params);
    }

    public function meusDados($page) {
        $this->layout = 'users';
        $pageName = "Meus Dados";

        if ($page == 'ajax-cep') {
            if (!empty($this->request->data ['cep'])) {
                $cURL = curl_init("http://cep.correiocontrol.com.br/{$this->request->data['cep']}.json");
                curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                $resultado = curl_exec($cURL);
                curl_close($cURL);
                echo $resultado;
                exit();
            }
        }

        if ($page == 'save-notification') {
            $periodicity = $this->request->data ['periodicity'];
            $id = $this->Session->read('userData.User.id');

            $sqlSelect = "select * from users_preferences where user_id = {$id};";
            $paramsSelect = array('User' => array('query' => $sqlSelect));
            $select = $this->Utility->urlRequestToGetData('users', 'query', $paramsSelect);

            if (!empty($select)) {
                $sqlUpdate = "update users_preferences set notifications_periodicity = '{$periodicity}' where user_id = {$id};";
                $paramsUpdate = array('User' => array('query' => $sqlUpdate));
                $this->Utility->urlRequestToGetData('users', 'query', $paramsUpdate);
            } else {
                $sqlNotif = "insert into users_preferences(user_id, notifications_periodicity) values({$id}, '{$periodicity}');";
                $paramsNotif = array('User' => array('query' => $sqlNotif));
                $this->Utility->urlRequestToGetData('users', 'query', $paramsNotif);
            }
        }

        /*
         * Salva os dados principais do usuario
         * nome, email, sexo, etc
         */
        if ($page == 'save-principal') {
            $params['User']['id'] = $this->Session->read('userData.User.id');
            $params['User']['birthday'] = date('Y-m-d', strtotime($this->request->data['User']['birthday']));
            $params['User']['name'] = $this->request->data['name'];
            $params['User']['email'] = $this->request->data['email'];
            $params['User']['gender'] = $this->request->data['gender'];
            $params['User']['address'] = $this->request->data['address'];
            $params['User']['city'] = $this->request->data['city'];
            $params['User']['zip_code'] = $this->request->data['cep'];
            $params['User']['state'] = $this->request->data['uf'];
            $params['User']['district'] = $this->request->data['district'];
            $params['User']['number'] = $this->request->data['number'];
            $params['User']['complement'] = $this->request->data['complement'];

            $editar = $this->Utility->urlRequestToSaveData('users', $params);
            $this->Session->write('userData', $editar['data']);
        }

        /**
         * Executado pelo ajax
         * Salva novo endereço adicional do usuario
         */
        if ($page == 'save-aditional') {
            $add['AditionalAddressesUser']['user_id'] = $this->Session->read('userData.User.id');
            $add['AditionalAddressesUser']['label'] = $this->request->data['label'];
            $add['AditionalAddressesUser']['address'] = $this->request->data['address'];
            $add['AditionalAddressesUser']['number'] = $this->request->data['number'];
            $add['AditionalAddressesUser']['district'] = $this->request->data['district'];
            $add['AditionalAddressesUser']['city'] = $this->request->data['city'];
            $add['AditionalAddressesUser']['state'] = $this->request->data['uf'];
            $add['AditionalAddressesUser']['complement'] = $this->request->data['complement'];
            $add['AditionalAddressesUser']['zip_code'] = $this->request->data['cep'];

            $newAddress = $this->Utility->urlRequestToSaveData('users', $add);
        }

        /**
         * Executado pelo ajax
         * Salva ou altera dados adicionais do usuario
         * 
         */
        if ($page == 'save-facebook-infos') {
            $data = date('Y-m-d');
            //recebendo fbk profile da sessao para validarmos mudança ou criação
            $fbk = $this->Session->read('userData.Fbk');
            $nome = explode(" ", $this->Session->read('userData.User.name'));
            //caso ja exita um fbk profile do usuario nós somente faremos alteração de dados
            if ($fbk) {
                $addFP['FacebookProfile']['id'] = $fbk['FacebookProfile']['id'];
            }

            $addFP['FacebookProfile']['facebook_id'] = '000000000000000';
            $addFP['FacebookProfile']['user_id'] = $this->Session->read('userData.User.id');
            $addFP['FacebookProfile']['name'] = $this->Session->read('userData.User.name');
            $addFP['FacebookProfile']['first_name'] = '' . $nome[0];
            $addFP['FacebookProfile']['last_name'] = '' . $nome[1];
            $addFP['FacebookProfile']['email'] = $this->Session->read('userData.User.email');
            $addFP['FacebookProfile']['gender'] = $this->Session->read('userData.User.gender');
            $addFP['FacebookProfile']['birthday'] = $this->Session->read('userData.User.birthday');
            $addFP['FacebookProfile']['updated_time'] = $data;
            $addFP['FacebookProfile']['profile_link'] = $this->request->data['facebook'];
            $addFP['FacebookProfile']['religion'] = $this->request->data['religion'];
            $addFP['FacebookProfile']['political'] = $this->request->data['political'];
            $addFP['FacebookProfile']['relationship_status'] = $this->request->data['relationship'];
            $addFP['FacebookProfile']['location'] = '';

            $saveFaceProfile = $this->Utility->urlRequestToSaveData('users', $addFP);
        }

        /*
         * Executado via jax
         * Altera dados de endereço adicional do cliente
         */
        if ($page == 'update-aditional-address') {
            $param['AditionalAddressesUser']['id'] = $this->request->data['id'];
            $param['AditionalAddressesUser']['address'] = $this->request->data['endereco'];
            $param['AditionalAddressesUser']['number'] = $this->request->data['numero'];
            $param['AditionalAddressesUser']['complement'] = $this->request->data['complemento'];
            $param['AditionalAddressesUser']['district'] = $this->request->data['bairro'];
            $param['AditionalAddressesUser']['city'] = $this->request->data['cidade'];
            $param['AditionalAddressesUser']['state'] = $this->request->data['estado'];
            $param['AditionalAddressesUser']['zip_code'] = $this->request->data['cep'];

            $saveFaceProfile = $this->Utility->urlRequestToSaveData('users', $param);
        }

        /*
         * Validação senha para a alteração da mesma
         */
        if ($page == 'valida-senha') {

            $senha = $this->request->data['senha'];
            $senhaEnconded = md5($senha);

            $retorno = 're';
            if ($senhaEnconded == $this->Session->read('userData.User.password')) {
                $retorno = "{'validaSenha':'OK'}";
                echo $retorno;
                exit();
            } else {
                $retorno = "{'validaSenha':'ERROR'}";
                echo $retorno;
                exit();
            }
        }

        /**
         * Salva nova senha após ser validada
         */
        if ($page == 'save-new-pass') {
            $senha = $this->request->data['senha'];

            $params['User']['id'] = $this->Session->read('userData.User.id');
            $params['User']['password'] = $senha;

            $editar = $this->Utility->urlRequestToSaveData('users', $params);
            $this->Session->write('userData', $editar['data']);
        }

        /*
         * Salva preferencias do usuario
         * Background - 
         */
        if ($page == 'save-preferences') {

            //Só edita se já existe
            if (!empty($this->Session->read('userPreferences.id'))) {
                $upload = $this->Utility->x_uploadFile('users', $this->request->data['Photo']['file']);

                $sql = "update users_preferences set background='{$upload}' where id = {$this->Session->read('userPreferences.id')}";

                //$sql = "insert into users_preferences(id, user_id, background) values({$this->Session->read('userPreferences.id')},{$this->Session->read('userData.User.id')},'{$upload}');";
                $params = array('User' => array('query' => $sql));
                $save = $this->Utility->urlRequestToGetData('users', 'query', $params);
            } else {
                //cria caso não exista ainda
                $upload = $this->Utility->x_uploadFile('users', $this->request->data['Photo']['file']);

                $sql = "insert into users_preferences(user_id, background) values({$this->Session->read('userData.User.id')},'{$upload}');";
                $params = array('User' => array('query' => $sql));
                $save = $this->Utility->urlRequestToGetData('users', 'query', $params);
            }

            //capturando preferencias do usuario
            $preferenceQuery = "select * from users_preferences where user_id={$this->Session->read('userData.User.id')};";
            $preferenceParam = array(
                'User' => array(
                    'query' => $preferenceQuery
                )
            );
            $preferences = $this->Utility->urlRequestToGetData('users', 'query', $preferenceParam);
            $preferencias = $preferences[0];
            $this->Session->write("userPreferences", $preferencias['users_preferences']);

            $this->redirect(array('controller' => 'users', 'action' => 'meusDados', 'plugin' => 'users'));
        }

        /*
         * O file deve ser captura via form por isso é executado separadamente
         * Capturamos file e fazemos o upload e salvamos no perfil do usuarios
         */
        if ($page == 'save-user-photo') {
            
        }

        /**
         * Salva as infos do usuario referente as suas redes sociais
         */
        if ($page == 'save-social-networks') {

            //CRIAR REGISTRO CASO USUARIO AINDA NÃO TENHA USADO ESSA OPÇÃO
            if (empty($this->Session->read('userSocial.id'))) {
                //face
                $fbkLink = $this->request->data['fbkLink'];
                $fbkComment = $this->request->data['fbkComment'];
                $fbkWishes = $this->request->data['fbkWishes'];
                $fbkCheck = $this->request->data['fbkCheck'];
                //twitter
                $twtLink = $this->request->data['twtLink'];
                $twtCheck = $this->request->data['twtCheck'];
                $twtComment = $this->request->data['twtComment'];
                $twtWishes = $this->request->data['twtWishes'];
                //googlePlus
                $gplusLink = $this->request->data['gLink'];
                $gplusCheck = $this->request->data['gCheck'];
                $gplusComment = $this->request->data['gComment'];
                $gplusWishe = $this->request->data['gWishe'];

                $gPlus = 'INACTIVE';
                $face = 'INACTIVE';
                $twitter = 'INACTIVE';

                /**
                 * Caso algum 'Link' venha  vazio, então entendemos que o usuario não habilitou a mesma
                 * Caso contrário ativamos a rede
                 */
                if (!empty($fbkLink)) {
                    $face = 'ACTIVE';
                }
                if (!empty($twtLink)) {
                    $twitter = 'ACTIVE';
                }
                if (!empty($gplusLink)) {
                    $gPlus = 'ACTIVE';
                }

                $sql = "insert into users_social_networks(user_id, facebook, fbk_link, fbk_checkouts, fbk_comments, fbk_wishes,"
                        . "twitter, twitter_link, twitter_checkouts, twitter_comments, twitter_wishes,"
                        . "google_plus, gplus_link, gplus_checkouts, gplus_comments, gplus_wishes) "
                        . "values({$this->Session->read('userData.User.id')}, '{$face}', '{$fbkLink}', '{$fbkCheck}', '{$fbkComment}', '{$fbkWishes}',"
                        . "'{$twitter}', '{$twtLink}', '{$twtCheck}', '{$twtComment}', '{$twtWishes}',"
                        . "'{$gPlus}','{$gplusLink}','{$gplusCheck}','{$gplusComment}','{$gplusWishe}');";

                $params = array('User' => array('query' => $sql));
                $insertSocial = $this->Utility->urlRequestToGetData('users', 'query', $params);
                $networks = $insertSocial[0];
                $this->Session->write("userSocial", $networks['users_social_networks']);
            } else {
                //Caso já exista registro para esse usuario simplismente alteramos
                $fbkLink = $this->request->data['fbkLink'];
                $fbkComment = $this->request->data['fbkComment'];
                $fbkWishes = $this->request->data['fbkWishes'];
                $fbkCheck = $this->request->data['fbkCheck'];
                $twtLink = $this->request->data['twtLink'];
                $twtCheck = $this->request->data['twtCheck'];
                $twtComment = $this->request->data['twtComment'];
                $twtWishes = $this->request->data['twtWishes'];
                $face = 'INACTIVE';
                $twitter = 'INACTIVE';
                if (!empty($fbkLink)) {
                    $face = 'ACTIVE';
                }
                if (!empty($twtLink)) {
                    $twitter = 'ACTIVE';
                }

                $sql = "update users_social_networks set facebook='{$face}', fbk_link = '{$fbkLink}', fbk_checkouts='{$fbkCheck}', fbk_comments='{$fbkComment}', fbk_wishes='{$fbkWishes}',"
                        . "twitter='{$twitter}', twitter_link='{$twtLink}', twitter_checkouts='{$twtCheck}', twitter_comments='{$twtComment}', twitter_wishes='{$twtWishes}' where id = {$this->Session->read('userSocial.id')};";

                $params = array('User' => array('query' => $sql));
                $updateSocial = $this->Utility->urlRequestToGetData('users', 'query', $params);

                $networks = $updateSocial[0];
                $this->Session->write("userSocial", $networks['users_social_networks']);
            }
        }

        /*
         * Executado pelo ajax
         * Remove endereço
         */
        if ($page == 'delete-aditional') {
            $id = $this->request->data['id'];
            $query = "delete from aditional_addresses_users where id = {$id};";

            $params = array('User' => array('query' => $query));
            $removeAddress = $this->Utility->urlRequestToGetData('users', 'query', $params);
        }

        //pesquisando endereços adicionais do usuario
        $params = array(
            'AditionalAddressesUser' => array(
                'conditions' => array(
                    'AditionalAddressesUser.user_id' => $this->Session->read('userData.User.id')
                )
            )
        );
        $endsAdicionais = $this->Utility->urlRequestToGetData('users', 'all', $params);



        //pesquisando facebook profile do usuario
        $paramsFbkProfile = array(
            'FacebookProfile' => array(
                'conditions' => array(
                    'FacebookProfile.user_id' => $this->Session->read('userData.User.id')
                )
            )
        );

        $facebookProfile = $this->Utility->urlRequestToGetData('users', 'first', $paramsFbkProfile);
        $this->Session->write('userData.Fbk', $facebookProfile);

        //pesquisa infos de social network do usuario
        $query = "select * from users_social_networks where user_id={$this->Session->read('userData.User.id')};";
        $paramsSocial = array(
            'User' => array(
                'query' => $query
            )
        );
        $social = $this->Utility->urlRequestToGetData('users', 'query', $paramsSocial);
        $networks = $social[0];
        $this->Session->write("userSocial", $networks['users_social_networks']);

        // CAPTURANDO NOTIFICAÇÕES E PERIODICIDADES
        $sqlPeriod = "select * from users_preferences where user_id = {$this->Session->read('userData.User.id')};";
        $paramsPeriod = array('User' => array('query' => $sqlPeriod));
        $period = $this->Utility->urlRequestToGetData('users', 'query', $paramsPeriod);

        //pesquisa users using para saber se usuário já baixou o app em seu smartphone
        $queryUsing = "select * from users_using where user_id = {$this->Session->read('userData.User.id')}";
        $paramsUsing = array(
            'User' => array(
                'query' => $queryUsing
            )
        );
        $usingResult = $this->Utility->urlRequestToGetData('users', 'query', $paramsUsing);
        $using = $usingResult[0];

        $this->set(compact('endsAdicionais', 'facebookProfile', 'pageName', 'using', 'period'));
    }

    public function rss() {
        $this->layout = 'users';
        $data = date('Y-m-d');
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    //'public' => 'ACTIVE',
                    'Offer.status' => 'ACTIVE',
                    'Offer.begins_at <= ' => $data,
                    'Offer.ends_at >= ' => $data
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
                'limit' => 10
            )
        );
        $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);
        $this->set(compact('offers'));
    }

    public function companiesProfile($page, $comp) {
        $this->layout = 'users';
        $id = $_POST['compId'];

        if ($page == 'sign') {

            $arrayParams = array(
                'CompaniesUser' =>
                array(
                    'conditions' => array(
                        'CompaniesUser.user_id' => $this->Session->read('userData.User.id'),
                        'CompaniesUser.company_id' => $comp
                    )),
            );
            $assinado = $this->Utility->urlRequestToGetData('users', 'first', $arrayParams);

            //Se o usuario já tiver assinado a empresa 
            //nós somente mudamos o status
            if ($assinado) {

                $arrayParams = array(
                    'CompaniesUser' => array(
                        'id' => $assinado['CompaniesUser']['id'],
                        'status' => 'ACTIVE'
                    )
                );

                $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
            } else {

                $arrayParams = array(
                    'CompaniesUser' => array(
                        'user_id' => $this->Session->read('userData.User.id'),
                        'company_id' => $comp,
                        'status' => 'ACTIVE',
                        'date_register' => date('Y-m-d')
                    )
                );

                $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
            }

            $this->redirect(array('controller' => 'users', 'action' => 'assinaturas', 'plugin' => 'users'));
        }
        $pageName = 'Perfil da Empresa';
        //comentarios
        $query = "select * from offers_comments inner join users on offers_comments.user_id = users.id inner join offers on offers_comments.offer_id = offers.id where offers.company_id = " . $id . ";";

        $filterParams = array(
            'User' => array(
                'query' => $query
            )
        );
        $comments = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);

        //offers
        $data = date("Y-m-d");
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    'public' => 'ACTIVE',
                    'Offer.status' => 'ACTIVE',
                    'Offer.begins_at <= ' => $data,
                    'Offer.ends_at >= ' => $data,
                    'Offer.company_id' => $id
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
                'limit' => $limite
            )
        );
        $offers = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        //company 
        $paramsComp = array(
            'Company' => array(
                'conditions' => array(
                    'Company.id' => $id
                )
            )
        );
        $company = $this->Utility->urlRequestToGetData('companies', 'all', $paramsComp);

        //assinaturas
        $paramsSign = array(
            'CompaniesUser' => array(
                'conditions' => array(
                    'CompaniesUser.company_id' => $id,
                    'CompaniesUser.status' => "ACTIVE"
                )
            )
        );
        $signs = $this->Utility->urlRequestToGetData('companies', 'all', $paramsSign);

        $this->set(compact('offers', 'comments', 'compId', 'company', 'signs', 'pageName'));
    }

    public function naspReceiver() {

        $this->layout = 'users';

        $checkId = $_POST['id_transacao'];
        $checkStatus = $_POST['status_pagamento'];

        //atualizando o status da compra
        $query = "update checkouts set payment_state_id = " . $checkStatus . " where id= " . $checkId . ";";

        $filterParams = array(
            'User' => array(
                'query' => $query
            )
        );
        $comments = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);
        //status atualizado
        //caprturando dados da compra via ID
        $paramsSign = array(
            'Checkout' => array(
                'conditions' => array(
                    'Checkout.id' => $checkId
                )
            ),
            'User',
            'Offer',
            'PaymentMethod',
        );
        $checkout = $this->Utility->urlRequestToGetData('payments', 'all', $paramsSign);

        $paramsComp = array(
            'Company' => array(
                'conditions' => array(
                    'Company.id' => $checkout[0]['Checkout']['company_id']
                )
            )
        );
        $company = $this->Utility->urlRequestToGetData('companies', 'all', $paramsComp);
        // 8f80d277cef320fce81acce81c
        if ($checkout[0]['Checkout']['payment_state_id'] == 1) {
            $data['checkPaymentStatus'] = 'AUTORIZADO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 2) {
            $data['checkPaymentStatus'] = 'INICIADO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 3) {
            $data['checkPaymentStatus'] = 'BOLETO IMPRESSO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 4) {
            $data['checkPaymentStatus'] = 'CONCLUIDO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 5) {
            $data['checkPaymentStatus'] = 'CANCELADO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 6) {
            $data['checkPaymentStatus'] = 'EM ANÁLISE';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 7) {
            $data['checkPaymentStatus'] = 'ESTORNADO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 8) {
            $data['checkPaymentStatus'] = 'EM REVISÃO';
        } else if ($checkout[0]['Checkout']['payment_state_id'] == 9) {
            $data['checkPaymentStatus'] = 'REEMBOLSADO';
        }

        //capturando endereço de email
        $data['to'] = $checkout[0]['User']['email'];
        $data['email'] = $checkout[0]['User']['email'];

        $data['checkId'] = $checkId;
        $data['userName'] = $checkout[0]['User']['name'];
        $data['checkDate'] = date("d/m/Y", strtotime($checkout[0]['Checkout']['date']));
        $data['checkPaymentMethod'] = $checkout[0]['PaymentMethod']['type'] . ' ' . $checkout[0]['PaymentMethod']['name'];
        $data['checkInstallment'] = $checkout[0]['Checkout']['installment'];
        $data['checkAmount'] = $checkout[0]['Checkout']['amount'];

        //calculando valor das parcelas
        if ($checkout[0]['Checkout']['installment'] == 0) {
            $data['checkInstallmentValue'] = number_format($checkout[0]['Checkout']['total_value'], 2, ',', '.');
        } else {
            $data['checkInstallmentValue'] = $checkout[0]['Checkout']['total_value'] / $checkout[0]['Checkout']['installment'];
        }

        $data['shippingValue'] = number_format($checkout[0]['Checkout']['shipping_value'], 2, ',', '.');

        //desconto
        $porcentagemDeDesconto = $checkout[0]['Offer']['percentage_discount'] / 100;
        $quantidadeDescontada = $porcentagemDeDesconto * $checkout[0]['Offer']['value'];
        $valorComDesconto = $checkout[0]['Offer']['value'] - $quantidadeDescontada;
        $data['discountValue'] = number_format($valorComDesconto, 2, ',', '.');


        $checkout[0]['Checkout'][''];
        $data['checkTotalValue'] = number_format($checkout[0]['Checkout']['total_value'], 2, ',', '.');
        $data['offerTitle'] = $checkout[0]['Offer']['title'];
        $data['offerValue'] = number_format($checkout[0]['Offer']['value'], 2, ',', '.');
        $data['addressLabel'] = ''; // label
        $data['address'] = $checkout[0]['Checkout']['address']; // logradouro 
        $data['addressNum'] = $checkout[0]['Checkout']['number']; // numero
        $data['addressComple'] = $checkout[0]['Checkout']['complement']; //complemento
        $data['addressDristrict'] = $checkout[0]['Checkout']['district']; // bairro
        $data['addressCity'] = $checkout[0]['Checkout']['city'];
        $data['addressState'] = $checkout[0]['Checkout']['state'];
        $data['addressZipCode'] = $checkout[0]['Checkout']['zip_code'];
        $data['deliverytime'] = $checkout[0]['Checkout']['delivery_time']; // tempo para entrega
        $data['deliveryvalue'] = $checkout[0]['Checkout']['shipping_value']; // valor do frete

        $data['compFancy'] = $company[0]['Company']['fancy_name']; // facyname company
        $data['compAddress'] = $company[0]['Company']['address']; //logradouro empresa
        $data['compNumber'] = $company[0]['Company']['number']; // numero empresa
        $data['compZipcode'] = $company[0]['Company']['zip_code'];
        $data['compCity'] = $company[0]['Company']['city'];
        $data['compState'] = $company[0]['Company']['state'];
        $data['compDistrict'] = $company[0]['Company']['district'];
        $data['compTel'] = $company[0]['Company']['phone']; // telefone empresa
        $data['compEmail'] = $company[0]['Company']['email']; // email empresa
        //$return = $this->Utility->paymentStatusChange($data);
        //echo $checkId . '-' . $checkStatus . '- <br/>' . print_r($return);

        $return = $this->Utility->postEmail('users', 'mudancaStatus', $data);
        print_r($return);


        $this->render('Elements/testeup');
    }

    public function signCompany() {
        $this->layout = 'users';
        $id = $_POST['compId'];

        $arrayParams = array(
            'CompaniesUser' =>
            array(
                'conditions' => array(
                    'CompaniesUser.user_id' => $this->Session->read('userData.User.id'),
                    'CompaniesUser.company_id' => $id
                )),
        );
        $assinado = $this->Utility->urlRequestToGetData('users', 'first', $arrayParams);

        //Se o usuario já tiver assinado a empresa 
        //nós somente mudamos o status
        if ($assinado) {

            $arrayParams = array(
                'CompaniesUser' => array(
                    'id' => $assinado['CompaniesUser']['id'],
                    'status' => 'ACTIVE'
                )
            );

            $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
        } else {

            $arrayParams = array(
                'CompaniesUser' => array(
                    'user_id' => $this->Session->read('userData.User.id'),
                    'company_id' => $id,
                    'status' => 'ACTIVE',
                    'date_register' => date('Y-m-d')
                )
            );

            $updateStatus = $this->Utility->urlRequestToSaveData('companies', $arrayParams);
        }

        $this->redirect(array('controller' => 'users', 'action' => 'assinaturas', 'plugin' => 'users'));
    }

    public $components = array('Ses', 'Session');

    public function changeLogin($type, $params) {
        $this->layout = '';

        // PASSO 1
        // ESSE DICIONARIO VAI VARIAR DE EMAIL PARA EMAIL
        // ENVIA OS DADOS QUE O EMAIL A SER ENVIADO DEVE CONTER
        //esses parametros serao obrigatorios em todos os arrays de dados de email
        $data['user_id'] = "{$this->Session->read('userData.User.id')}";
        $data['email_type'] = 'companyInactivity';
        $data['api'] = 'companies';

        $data['userName'] = 'Matheus Odilon';
        $data['checkId'] = 'X123X';
        $data['checkPaymentMethod'] = 'Boleto Bradesco';
        $data['checkInstallment'] = '1';
        $data['checkTotalValue'] = '129,99';
        $data['email'] = "matheus.odilon@accential.com.br";

//        // PASSO 2
//        // CAPTURANDO DADOS DA PERIODICIDADE SELECIONADA PELO USUÁRIO PARA O ENVIO DOS EMAIS E NOTIFICACOES
//        // ISSO DEFINE SE JÁ ENVIAREMOS OU AGENDAREMOS 
//        $sqlPeriod = "select * from users_preferences where user_id = {$this->Session->read('userData.User.id')};";
//        $paramsPeriod = array('User' => array('query' => $sqlPeriod));
//        $period = $this->Utility->urlRequestToGetData('users', 'query', $paramsPeriod);
//
//        // VERIFICA QUAL É A PERIODICIDADE
//        // CASO SEJA UNITÁRIO JÁ ENVIAMOS O EMAIS
//        // CASO SEJA QUALQUER OUTRO, SALVAMOS OS DADOS NECESSARIOS PARA O ENVIO DO EMAIL NA DATA SELECIONADA PELO USUARIO
//        if ($period) {
//            if ($period[0]['users_preferences']['notifications_periodicity'] == 'UNITARY') {
//
//                // PASSO 5
//                // ENVIANDO EMAIL
//                $return = $this->Utility->postEmail('companies', 'companyInactivity', $data);
//
//                $body = json_encode($data);
//                $dt = date('Y/m/d');
//                $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
//                        . "values({$this->Session->read('userData.User.id')},"
//                        . "000,"
//                        . "'" . $data['email_type'] . "',"
//                        . "'UNITARY',"
//                        . "'{$dt}',"
//                        . "'" . $data['api'] . "',"
//                        . "'{$body}');";
//                $paramsLog = array('User' => array('query' => $sqlLog));
//                $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);
//            } else {
//
//                // TRANSFORMA O ARRAY EM UMA STRING PARA SER SALVA NO BANCO DE DADOS E SER USADO QUANDO NECESSARIO
//                $body = json_encode($data);
//
//                // PASSO 3
//                // CAPTURA AS INFORMAÇÕES DO EMAIL A SER ENVIADO PARA PODER SER SALVO NO BANCO DE DADOS E USADO QUANDO NECESSARIO
//                // A TABELA `MAIL_QUEUES` CONTÉM OS REGISTROS DOS EMAILS A SEREM ENVIADOS E DOS QUE JÁ FORAM AGENDADOS E ENVIADOS
//                // SALVA E ESPERA A DATA SELECIONADA PELO USUÁRIO PARA SER ENVIADO
//                $sqlSave = "insert into mail_queues(user_id, company_id, email_type, periodicity, status, email_body_infos, api) "
//                        . "values({$this->Session->read('userData.User.id')}, "
//                        . "000,"
//                        . "'companyInactivity',"
//                        . "'{$period[0]['users_preferences']['notifications_periodicity']}',"
//                        . "'WAITING',"
//                        . " '{$body}', "
//                        . "'companies');";
//                $paramsSave = array('User' => array('query' => $sqlSave));
//                $save = $this->Utility->urlRequestToGetData('users', 'query', $paramsSave);
//            }
//        }
//        $sql = "select * from mail_queues where id = 3;";
//        $params = array('User' => array('query' => $sql));
//        $return = $this->Utility->urlRequestToGetData('users', 'query', $params);
//
//        $data = json_decode($return[0]['mail_queues']['email_body_infos']);
//        $emailReturn = $this->Utility->postEmail("{$return[0]['mail_queues']['api']}", "{$return[0]['mail_queues']['email_type']}", $data);
//        print_r($return);

        //$return = $this->Utility->postEmail('companies', 'companyInactivity', $data);
       // print_r($return);
       // echo "<br/>";
       // print_r($data);
       // 
        
        $param['notifText'] = "Esse é o corpo da notificação";
        
        $retorno = $this->Utility->urlRequestToSendNotification($param);
        print_r($retorno);
        $this->render('Elements/testeup');
    }

    public function testeUpload() {

//        $this->layout = '';
//        $data['userName'] = 'Matheus Odilon';
//        $data['userEmail'] = 'matheusodilon0@gmail.com';
//        $data['pwd'] = '123456';
        $this->Utility->urlRequestToSendAndroidEmail('users', $data);

//        $upload = $this->Utility->adv_uploadFileComp('offers', $this->request->data['Offer']['photo']);
//        print_r($upload);
//        $this->render('testeftp');
    }

    /**
     * Lógica que contera a rotina de envio de emails automaticos
     * FLUXO DA FUNÇÃO:
     * 
     * PASSO 1 - VERIFICA AS DATAS 
     * PASSO 2 - VERIFICA SE É ULTIMO DIA DO MÊS 
     *       PASSO 2.1 - BUSCA TODOS OS EMAIL MENSAIS
     *       PASSO 2.2 - ENVIA OS EMAILS
     *       PASSO 2.3 - SALVA LOG PARA CADA ENVIO
     *       PASSO 2.4 - ALTERA STATUS DO EMAIL PARA 'SENT_OU' /ENVIADO
     * PASSO 3 - VERIFICA SE É DOMINGO (representado por 0)
     *       PASSO 3.1 - SEGUE FLUXO DE ENVIO
     *       ...
     * PASSO 4 - BUSCA TODOS OS EMAILS DIÁRIOS
     *       PASSO 4.1 SEGUE FLUXO DE ENVIO
     *       ...
     */
    public function automaticEmail() {
        $this->layout = '';

        $data = date('Y/m/d');
        $diaSemana = date('w', strtotime($data));

        $mes = '07';      // Mês desejado, pode ser por ser obtido por POST, GET, etc.
        $hoje = date("d");
        $ano = date("Y"); // Ano atual
        $ultimo_dia = date("t", mktime(0, 0, 0, $mes, '01', $ano)); // Mágica, plim!

        /*
         * LÓGICA SEMANAL
         * CASO HOJE SEJA DOMINGO 
         * EXECUTAMOS O ENVIO DOS EMAILS SEMANAIS
         * o DOMINGO é o dia ZERO do calendário 
         */
        if ($diaSemana == 0) {
            echo "hoje é domingo";
            //´LÓGICA SEMANAL
            $sqlSemanal = "select * from mail_queues where periodicity = 'WEEKLY' and status = 'WAITING';";
            $paramsSemanal = array('User' => array('query' => $sqlSemanal));
            $semanais = $this->Utility->urlRequestToGetData('users', 'query', $paramsSemanal);

            //LÓGICA DE ENVIO DOS EMAILS DIÁRIOS 
            foreach ($semanais as $semanal) {

                $emailParam = json_decode($semanal['mail_queues']['email_body_infos']);
                $return = $this->Utility->postEmailAutomatic('companies', 'companyInactivity', $emailParam);
                //print_r($emailParam);

                echo '<br/>' . $emailParam->email;
                echo"<br/><br/><br/>";

                $body = $semanal['mail_queues']['email_body_infos'];
                //$paramsEmail = json_decode($diario['mail_queues']['email_body_infos']);
                $dt = date('Y/m/d');
                $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                        . "values({$this->Session->read('userData.User.id')},"
                        . "000,"
                        . "'" . $emailParam->email_type . "',"
                        . "'WEEKLY',"
                        . "'{$dt}',"
                        . "'" . $emailParam->api . "',"
                        . "'{$body}');";
                $paramsLog = array('User' => array('query' => $sqlLog));
                $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);

                $updateEmail = "update mail_queues set status = 'SENT_OUT' where id = {$semanal['mail_queues']['id']};";
                $paramsUp = array('User' => array('query' => $updateEmail));
                $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);
            }
        }

        //CASO HOJE SEJA O ÚLTIMO DIA DO MÊS
        //EXECUTAMOS O ENVIO DOS EMAILS MENSAIS 
        if ($ultimo_dia == $hoje) {
            echo "hoje é o último dia do mês";

            //LÓGICA MENSAL
            $sqlMensal = "select * from mail_queues where periodicity = 'MONTHLY' and status = 'WAITING';";
            $paramsMensal = array('User' => array('query' => $sqlMensal));
            $mensais = $this->Utility->urlRequestToGetData('users', 'query', $paramsMensal);

            //LÓGICA DE ENVIO DOS EMAILS MENSAL
            foreach ($mensais as $mensal) {

                $emailParam = json_decode($mensal['mail_queues']['email_body_infos']);
                $return = $this->Utility->postEmailAutomatic('companies', 'companyInactivity', $emailParam);
                //print_r($emailParam);

                $body = $mensal['mail_queues']['email_body_infos'];
                $paramsEmail = json_decode($diario['mail_queues']['email_body_infos']);
                $dt = date('Y/m/d');
                $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                        . "values({$this->Session->read('userData.User.id')},"
                        . "000,"
                        . "'" . $emailParam->email_type . "',"
                        . "'MONTHLY',"
                        . "'{$dt}',"
                        . "'" . $emailParam->api . "',"
                        . "'{$body}');";
                $paramsLog = array('User' => array('query' => $sqlLog));
                $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);

                $updateEmail = "update mail_queues set status = 'SENT_OUT' where id = {$mensal['mail_queues']['id']};";
                $paramsUp = array('User' => array('query' => $updateEmail));
                $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);
            }
        }

        //´LÓGICA DIÁRIA
        //EXECUTADA TODOS OS DIAS
        $sqlDiario = "select * from mail_queues where periodicity = 'DAILY' and status = 'WAITING';";
        $paramsDiario = array('User' => array('query' => $sqlDiario));
        $diarios = $this->Utility->urlRequestToGetData('users', 'query', $paramsDiario);

        //LÓGICA DE ENVIO DOS EMAILS DIÁRIOS 
        foreach ($diarios as $diario) {

            $emailParam = json_decode($diario['mail_queues']['email_body_infos']);
            $return = $this->Utility->postEmailAutomatic('companies', 'companyInactivity', $emailParam);
            print_r($emailParam);

            $body = $diario['mail_queues']['email_body_infos'];
            $paramsEmail = json_decode($diario['mail_queues']['email_body_infos']);
            $dt = date('Y/m/d');
            $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                    . "values({$this->Session->read('userData.User.id')},"
                    . "000,"
                    . "'" . $emailParam->email_type . "',"
                    . "'UNITARY',"
                    . "'{$dt}',"
                    . "'" . $emailParam->api . "',"
                    . "'{$body}');";
            $paramsLog = array('User' => array('query' => $sqlLog));
            $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);

            $updateEmail = "update mail_queues set status = 'SENT_OUT' where id = {$diario['mail_queues']['id']};";
            $paramsUp = array('User' => array('query' => $updateEmail));
            $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);
        }

        /**
          echo date('Y/m/d');
          echo "<br/>Dia da semana: " . $diaSemana;
          echo "<br/> Último dia do mês é: " . $ultimo_dia;
          echo "<br/> Hoje é: " . $hoje;
          echo "<br/><br/><br/><br/>";
         * */
        $this->render('Elements/testeup');
    }

    
    /**
     * 
     *   * Lógica que contera a rotina de envio de notificações automaticas
     *     FLUXO DA FUNÇÃO:
     * 
     * PASSO 1 - VERIFICA AS DATAS 
     * PASSO 2 - VERIFICA SE É ULTIMO DIA DO MÊS 
     *       PASSO 2.1 - BUSCA TODAS NOTIFICAÇÕES MENSAIS
     *       PASSO 2.2 - ENVIA OS NOTIFICAÇÕES
     *       PASSO 2.3 - SALVA LOG PARA CADA ENVIO
     *       PASSO 2.4 - ALTERA STATUS DAS NOTIFICAÇÕES PARA 'SENT_OU' /ENVIADO
     * PASSO 3 - VERIFICA SE É DOMINGO (representado por 0)
     *       PASSO 3.1 - SEGUE FLUXO DE ENVIO
     *       ...
     * PASSO 4 - BUSCA TODOS AS NOTIFICAÇÕES DIÁRIOS
     *       PASSO 4.1 SEGUE FLUXO DE ENVIO
     */
    public function automaticNotification(){
        $this->layout = '';

        $data = date('Y/m/d');
        $diaSemana = date('w', strtotime($data));

        $mes = '07';      // Mês desejado, pode ser por ser obtido por POST, GET, etc.
        $hoje = date("d");
        $ano = date("Y"); // Ano atual
        $ultimo_dia = date("t", mktime(0, 0, 0, $mes, '01', $ano)); // Mágica, plim!

        /*
         * LÓGICA SEMANAL
         * CASO HOJE SEJA DOMINGO 
         * EXECUTAMOS O ENVIO DOS EMAILS SEMANAIS
         * o DOMINGO é o dia ZERO do calendário 
         */
        if ($diaSemana == 0) {
            echo "hoje é domingo";
            //´LÓGICA SEMANAL
            $sqlSemanal = "select * from notifications_queues where periodicity = 'WEEKLY' and status = 'WAITING';";
            $paramsSemanal = array('User' => array('query' => $sqlSemanal));
            $semanais = $this->Utility->urlRequestToGetData('users', 'query', $paramsSemanal);

            //LÓGICA DE ENVIO DOS EMAILS DIÁRIOS 
            foreach ($semanais as $semanal) {

                $emailParam = json_decode($semanal['mail_queues']['email_body_infos']);
                $return = $this->Utility->postEmailAutomatic('companies', 'companyInactivity', $emailParam);
                //print_r($emailParam);

                echo '<br/>' . $emailParam->email;
                echo"<br/><br/><br/>";

                $body = $semanal['mail_queues']['email_body_infos'];
                //$paramsEmail = json_decode($diario['mail_queues']['email_body_infos']);
                $dt = date('Y/m/d');
                $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                        . "values({$this->Session->read('userData.User.id')},"
                        . "000,"
                        . "'" . $emailParam->email_type . "',"
                        . "'WEEKLY',"
                        . "'{$dt}',"
                        . "'" . $emailParam->api . "',"
                        . "'{$body}');";
                $paramsLog = array('User' => array('query' => $sqlLog));
                $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);

                $updateEmail = "update mail_queues set status = 'SENT_OUT' where id = {$semanal['mail_queues']['id']};";
                $paramsUp = array('User' => array('query' => $updateEmail));
                $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);
            }
        }

        //CASO HOJE SEJA O ÚLTIMO DIA DO MÊS
        //EXECUTAMOS O ENVIO DOS EMAILS MENSAIS 
        if ($ultimo_dia == $hoje) {
            echo "hoje é o último dia do mês";

            //LÓGICA MENSAL
            $sqlMensal = "select * from mail_queues where periodicity = 'MONTHLY' and status = 'WAITING';";
            $paramsMensal = array('User' => array('query' => $sqlMensal));
            $mensais = $this->Utility->urlRequestToGetData('users', 'query', $paramsMensal);

            //LÓGICA DE ENVIO DOS EMAILS MENSAL
            foreach ($mensais as $mensal) {

                $emailParam = json_decode($mensal['mail_queues']['email_body_infos']);
                $return = $this->Utility->postEmailAutomatic('companies', 'companyInactivity', $emailParam);
                //print_r($emailParam);

                $body = $mensal['mail_queues']['email_body_infos'];
                $paramsEmail = json_decode($diario['mail_queues']['email_body_infos']);
                $dt = date('Y/m/d');
                $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                        . "values({$this->Session->read('userData.User.id')},"
                        . "000,"
                        . "'" . $emailParam->email_type . "',"
                        . "'MONTHLY',"
                        . "'{$dt}',"
                        . "'" . $emailParam->api . "',"
                        . "'{$body}');";
                $paramsLog = array('User' => array('query' => $sqlLog));
                $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);

                $updateEmail = "update mail_queues set status = 'SENT_OUT' where id = {$mensal['mail_queues']['id']};";
                $paramsUp = array('User' => array('query' => $updateEmail));
                $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);
            }
        }

        //´LÓGICA DIÁRIA
        //EXECUTADA TODOS OS DIAS
        $sqlDiario = "select * from mail_queues where periodicity = 'DAILY' and status = 'WAITING';";
        $paramsDiario = array('User' => array('query' => $sqlDiario));
        $diarios = $this->Utility->urlRequestToGetData('users', 'query', $paramsDiario);

        //LÓGICA DE ENVIO DOS EMAILS DIÁRIOS 
        foreach ($diarios as $diario) {

            $emailParam = json_decode($diario['mail_queues']['email_body_infos']);
            $return = $this->Utility->postEmailAutomatic('companies', 'companyInactivity', $emailParam);
            print_r($emailParam);

            $body = $diario['mail_queues']['email_body_infos'];
            $paramsEmail = json_decode($diario['mail_queues']['email_body_infos']);
            $dt = date('Y/m/d');
            $sqlLog = "insert into mail_log(user_id, company_id, email_type, periodicity, shipping_date, api, email_body_infos)"
                    . "values({$this->Session->read('userData.User.id')},"
                    . "000,"
                    . "'" . $emailParam->email_type . "',"
                    . "'UNITARY',"
                    . "'{$dt}',"
                    . "'" . $emailParam->api . "',"
                    . "'{$body}');";
            $paramsLog = array('User' => array('query' => $sqlLog));
            $saveLog = $this->Utility->urlRequestToGetData('users', 'query', $paramsLog);

            $updateEmail = "update mail_queues set status = 'SENT_OUT' where id = {$diario['mail_queues']['id']};";
            $paramsUp = array('User' => array('query' => $updateEmail));
            $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);
        }

        /**
          echo date('Y/m/d');
          echo "<br/>Dia da semana: " . $diaSemana;
          echo "<br/> Último dia do mês é: " . $ultimo_dia;
          echo "<br/> Hoje é: " . $hoje;
          echo "<br/><br/><br/><br/>";
         * */
        $this->render('Elements/testeup');
    }
}

?>