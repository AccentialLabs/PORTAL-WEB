<?php

error_reporting(0);
App::uses('CompaniesAppController', 'Companies.Controller');

/**
 * Companies Controller
 */
class CompaniesController extends CompaniesAppController {

    /**
     * Intervalo onde consideramos a oferta como nova.
     * Medido em dias
     *
     * @var int
     */
    protected $newOfferRange = 2;

    /**
     * Intervalo da última semana da oferta.
     * Medido em dias
     *
     * @var int
     */
    protected $lastWeekOfferRange = 7;
    protected $ageGroupRangeKeys = array(
        'empty' => "Não informado",
        '0_20' => "Menor de 20 anos",
        '20_29' => "De 20 a 29 anos",
        '30_39' => "De 30 a 39 anos",
        '40_49' => "De 40 a 49 anos",
        '50_59' => "De 50 a 59 anos",
        '60_120' => "Maior de 60 anos"
    );

    /**
     * Filtros de ofertas
     *
     * @var array
     */
    protected $offerFilters = array(
        'gender' => array(),
        'religion' => array(),
        'political' => array(),
        'location' => array(),
        'age_group' => array(),
        'relationship_status' => array()
    );
    // token e key para cadastro no moip
    protected $key = '11PB4FPN68M1FE8MAPWUDIMEHFIGM8P6DMSBNXZZ';
    protected $token = 'JK75V6UGKYYUZR2ICVHJSSLD687UEJ9H';

    public function beforeFilter() {
        parent::beforeFilter();

        $this->set('newOfferRange', $this->newOfferRange * 3600 * 24);
        $this->set('lastWeekOfferRange', $this->lastWeekOfferRange * 3600 * 24);
    }

    public function home() {

        // ultimas compras
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

        // ultimos pedidos
        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    'Checkout.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'Checkout.total_value > ' => '0',
                    'Checkout.payment_state_id <> ' => 4,
                    'NOT' => array(
                        'Checkout.payment_state_id' => array(
                            '999'
                        )
                    )
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
        $pedidos = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        // assinaturas
        $params = array(
            'CompaniesUser' => array(
                'conditions' => array(
                    'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'User.status' => 'ACTIVE'
                ),
                'order' => array(
                    'CompaniesUser.id' => 'DESC'
                ),
                'limit' => 5
            ),
            'User'
        );
        $assinaturasFor = $this->Utility->urlRequestToGetData('companies', 'all', $params);
        if (is_array($assinaturasFor)) {
            foreach ($assinaturasFor as $assinatura) {
                $assinatura ['User'] ['idade'] = $this->Utility->calcIdade(date('d/m/Y', strtotime($assinatura ['User'] ['birthday'])));
                $assinaturas [] = $assinatura;
            }
        } else {
            $assinaturas = '';
        }

        // ofertas
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    'Offer.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'status <>' => 'DELETE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
                'limit' => 5
            )
        );
        $ofertas = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        $this->set(compact('checkouts', 'assinaturas', 'ofertas', 'pedidos'));
    }

    /**
     * Exibe lista de ofertas cadastradas
     *
     * @return void
     */
    public function offers() {
        $data = date('Y-m-d');
        $limit = 10;
        $update = true;

        // total de ofertas
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    'company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'status ' => '<> DELETE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                )
            )
        );
        $contador = $this->Utility->urlRequestToGetData('offers', 'count', $params);

        $arrayBusca = array(
            'company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
            'status <>' => 'DELETE'
        );
        // verifica se esta sendo feita uma busca
        if (!empty($this->request->data ['busca'])) {
            if ($this->request->data ['calendario-inicio'] != 'Data Inicial' && $this->request->data ['calendario-fim'] != 'Data Final') {
                $dataInicio = $this->Utility->formataData($this->request->data ['calendario-inicio']);
                $dataFinal = $this->Utility->formataData($this->request->data ['calendario-fim']);
                $arrayData = array(
                    'Offer.begins_at >= ' => $dataInicio,
                    'Offer.ends_at <= ' => $dataFinal
                );
                $arrayBusca = array_merge($arrayData, $arrayBusca);
            }
            if ($this->request->data ['titulo'] != 'Titulo') {
                $arrayProduto = array(
                    'Offer.title LIKE' => "%{$this->request->data['titulo']}%"
                );
                $arrayBusca = array_merge($arrayProduto, $arrayBusca);
            }
        }

        // verifica se esta fazendo uma requisicao ajax
        if (!empty($this->request->data ['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST ['limit'] + 3;
            if ($limit > $contador) {
                $limit = $contador;
                $update = false;
            }
        }

        $params = array(
            'Offer' => array(
                'conditions' => $arrayBusca,
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
                'limit' => $limit
            )
        );
        $company = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        $this->set(compact('company', 'limit', 'contador', 'update'));
        if (!empty($render))
            $this->render('Elements/ajax_ofertas');
    }

    /**
     * Adiciona nova oferta
     *
     * @return void
     */
    public function addOffer($page = null, $id = null, $action = null) {

        // exclui sessao se � nova oferta
        if ($id == 'nova-oferta') {
            $this->Session->delete('addOffer');
            $id = null;
        }

        if ($this->Session->read('CompanyLoggedIn.Company.login_moip') == '') {

            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'cadastroMoip',
                'plugin' => 'companies'
            ));
        }

        /* Requisição Ajax - Busca usuários através dos filtros selecionados */
        if ($this->request->is('ajax')) {
            if (!empty($this->request->data ['metricas'])) {
                $render = true;
                $this->layout = '';
                $id = $this->request->data ['id'];

                $query = "SELECT id, category_id, name, value FROM categories_metrics WHERE category_id = {$id}";
                $params = array(
                    'User' => array(
                        'query' => $query
                    )
                );
                $metricas = $this->Utility->urlRequestToGetData('users', 'query', $params);
                $this->set(compact('metricas'));

                // gravando metricas em sessao
            } else if (!empty($this->request->data ['salvar_metricas'])) {

                $atributo = $this->request->data ['atributo'];
                $valor = $this->request->data ['valor'];
                if ($this->Session->check('Metricas.' . $atributo)) {
                    $quantidade = count($this->Session->read('Metricas.' . $atributo));
                    $this->Session->write('Metricas.' . $atributo . '.' . $quantidade, $valor);
                } else {
                    $this->Session->write('Metricas.' . $atributo . '.0', $valor);
                }
                exit();

                // excluindo metricas de sessao
            } else if (!empty($this->request->data ['excluir_metricas'])) {

                $atributo = $this->request->data ['atributo'];
                $valor = $this->request->data ['valor'];
                if (array_search($valor, $_SESSION ['Metricas'] [$atributo]) !== false) {
                    unset($_SESSION ['Metricas'] [$atributo] [array_search($valor, $_SESSION ['Metricas'] [$atributo])]);
                }
                exit();

                // finalizando trabalho de metricas
            } else if (!empty($this->request->data ['finalizar_metricas'])) {
                $renderM = true;
                $this->layout = '';

                $this->Session->write('addOffer.Offer.metrics', json_encode($this->Session->read('Metricas')));
                unset($_SESSION ['Metricas']);
            } else {
                $this->autoRender = false;

                /* Deletando imagem via json */
                if (isset($this->request->data ['indexOfferImage'])) {
                    $indice = $this->request->data ['indexOfferImage'];
                    if ($indice == "featured")
                        $this->Session->delete("addOffer.Offer.photo");
                    else
                        $this->Session->delete("addOffer.OffersPhoto.$indice");
                } else {
                    /* Setando filtros de oferta */
                    $this->setOfferFilters();
                }
            }
        }

        /* Coleta dados dos Forms */
        if ($this->request->is('post')) {

            if (!empty($this->request->data)) {

                $oldSession = $this->Session->check('addOffer') ? $this->Session->read('addOffer') : array();

                if (!empty($this->request->data ['OffersPhoto'] ['photo'] [0])) {
                    if ($this->request->data ['OffersPhoto'] ['photo'] [0] ['error'] == 4) {
                        $this->request->data ['OffersPhoto'] ['photo'] [0] = $this->request->data ['Offer'] ['photo'];
                    }
                }

                // upload de imagem
                if ($page == 'offerFilters' && !empty($this->request->data ['Offer'] ['photo'])) {
                    $upload = $this->Utility->uploadFile('offers', $this->request->data ['Offer'] ['photo']);
                    if (!empty($upload)) {
                        $this->request->data ['Offer'] ['photo'] = $upload;
                    }
                }

                if ($page == 'offerFilters' && !empty($this->request->data ['OffersPhoto'] ['photo'])) {

                    // tratando imagens
                    $x = null; // �ndice da imagem selecionada
                    $y = 0;
                    for ($i = 0; 5 > $i; $i ++) {
                        if (isset($this->request->data ['OffersPhoto'] ['photo'] [$i])) {
                            if ($this->request->data ['OffersPhoto'] ['photo'] [$i] ['error'] != 0) {
                                unset($this->request->data ['OffersPhoto'] ['photo'] [$i]);
                            } else {
                                $x [$y] = $i;
                                $y ++;
                            }
                        }
                    }

                    $upload = $this->Utility->uploadFile('offers', $this->request->data ['OffersPhoto'] ['photo'], true);

                    if (!empty($upload)) {
                        $j = 0;
                        foreach ($upload as $photo) {
                            $this->request->data ['OffersPhoto'] [$x [$j]] ['photo'] = $photo;
                            $j ++;
                        }
                        unset($this->request->data ['OffersPhoto'] ['photo']);
                    }
                }
                // pr($this->request->data);exit;
                $newSession = array_replace_recursive($oldSession, $this->request->data);
                $this->Session->write('addOffer', $newSession);
            }
        }

        $this->request->data = $this->Session->read('addOffer');
        switch ($page) {
            case 'detalhes' :
                $query = "SELECT id, name FROM categories";
                $categoriasParams = array(
                    'User' => array(
                        'query' => $query
                    )
                );
                $categorias = $this->Utility->urlRequestToGetData('users', 'query', $categoriasParams);

                $this->set(compact('categorias'));
                $this->render('offerDetails');
                if (!empty($render))
                    $this->render('Elements/ajax_metricas');
                if (!empty($renderM))
                    $this->render('Elements/ajax_metricas_resultado');
                break;

            case 'offerFilters' :
                if ($this->Session->read('addOffer.Offer.public') == 'ACTIVE')
                    $this->redirect(array(
                        'action' => 'addOffer',
                        'plugin' => 'companies',
                        'resume'
                    ));
                $this->Session->write('addOffer.offerDetails', 1);
                if (!$this->Session->check('addOffer.Offer'))
                    $this->redirect(array(
                        'action' => 'addOffer',
                        'plugin' => 'companies',
                        'detalhes'
                    ));

                $offerFiltersData = $this->loadOfferFilters();
                $this->set(compact('offerFiltersData'));
                $this->render('offerFilters');
                break;

            case 'resume' :
                $this->Session->write('addOffer.offerSchedule', 1);
                if (!empty($id)) {
                    $this->Session->delete('addOffer');

                    $params = array(
                        'Offer' => array(
                            'conditions' => array(
                                'Offer.id' => $id
                            )
                        ),
                        'OffersFilter',
                        'OffersUser',
                        'OffersPhoto'
                    );
                    $offersUpdate = $this->Utility->urlRequestToGetData('offers', 'first', $params);

                    // trabalhando usuarios
                    foreach ($offersUpdate ['OffersUser'] as $user) {
                        $profiles [] ['User'] ['id'] = $user ['user_id'];
                    }

                    // revivendo sessoes rsrs
                    $offersUpdate ['OffersFilter'] [0] ['gender'] == false ? $this->Session->write('addOffer.filters.gender', array()) : $this->Session->write('addOffer.filters.gender', explode(',', $offersUpdate ['OffersFilter'] [0] ['gender']));
                    $offersUpdate ['OffersFilter'] [0] ['location'] == false ? $this->Session->write('addOffer.filters.location', array()) : $this->Session->write('addOffer.filters.location', explode(',', $offersUpdate ['OffersFilter'] [0] ['location']));
                    $offersUpdate ['OffersFilter'] [0] ['age_group'] == false ? $this->Session->write('addOffer.filters.age_group', array()) : $this->Session->write('addOffer.filters.age_group', explode(',', $offersUpdate ['OffersFilter'] [0] ['age_group']));
                    $offersUpdate ['OffersFilter'] [0] ['political'] == false ? $this->Session->write('addOffer.filters.political', array()) : $this->Session->write('addOffer.filters.political', explode(',', $offersUpdate ['OffersFilter'] [0] ['political']));
                    $offersUpdate ['OffersFilter'] [0] ['relationship_status'] == false ? $this->Session->write('addOffer.filters.relationship_status', array()) : $this->Session->write('addOffer.filters.relationship_status', explode(',', $offersUpdate ['OffersFilter'] [0] ['relationship_status']));
                    $offersUpdate ['OffersFilter'] [0] ['religion'] == false ? $this->Session->write('addOffer.filters.religion', array()) : $this->Session->write('addOffer.filters.religion', explode(',', $offersUpdate ['OffersFilter'] [0] ['religion']));
                    $this->Session->write('addOffer.Offer', $offersUpdate ['Offer']);
                    $this->Session->write('addOffer.OffersPhoto', $offersUpdate ['OffersPhoto']);
                    $this->Session->write('addOffer.begins_at', date('d/m/Y', strtotime($offersUpdate ['Offer'] ['begins_at'])));
                    $this->Session->write('addOffer.ends_at', date('d/m/Y', strtotime($offersUpdate ['Offer'] ['ends_at'])));
                    $this->Session->write('addOffer.users', $profiles);
                    $this->Session->write('addOffer.countUserOffer', count($profiles));
                    $this->Session->write('addOffer.update', true);
                    $this->Session->write('addOffer.Offer.value', number_format($offersUpdate ['Offer'] ['value'], 2, ',', '.'));
                    if ($action == 'editar')
                        $this->redirect(array(
                            'action' => 'addOffer',
                            'plugin' => 'companies',
                            'detalhes'
                        ));
                } else {
                    if (!$this->Session->check('addOffer.Offer'))
                        $this->redirect(array(
                            'action' => 'addOffer',
                            'plugin' => 'companies',
                            'offerFilters'
                        ));
                    if (!$this->Session->check('addOffer.filters'))
                        $this->Session->write('addOffer.Offer.public', 'ACTIVE');

                    // pegando usuarios de oferta cadastrada
                    $filters = $this->Session->read('addOffer.filters');
                    if (!empty($filters ['gender'] [0]))
                        $conditions ['User.gender'] = $filters ['gender'];
                    if (!empty($filters ['location'] [0]))
                        $conditions ['FacebookProfile.location'] = $filters ['location'];
                    if (!empty($filters ['political'] [0]))
                        $conditions ['FacebookProfile.political'] = $filters ['political'];
                    if (!empty($filters ['relationship_status'] [0]))
                        $conditions ['FacebookProfile.relationship_status'] = $filters ['relationship_status'];
                    if (!empty($filters ['religion'] [0]))
                        $conditions ['FacebookProfile.religion'] = $filters ['religion'];

                    // buscando usuários
                    $companyId = $this->Session->read('CompanyLoggedIn.Company.id');
                    $params = array(
                        'CompaniesUser' => array(
                            'fields' => array(
                                'CompaniesUser.user_id'
                            ),
                            'conditions' => array(
                                'CompaniesUser.company_id' => $companyId,
                                'CompaniesUser.status' => 'ACTIVE'
                            )
                        )
                    );
                    $userIds = $this->Utility->urlRequestToGetData('companies', 'list', $params);
                    $conditions ['User.id'] = $userIds;
                    $params = array(
                        'User' => array(
                            'fields' => array(
                                'User.id'
                            ),
                            'conditions' => $conditions
                        ),
                        'FacebookProfile' => array()
                    );
                    $users = $this->Utility->urlRequestToGetData('users', 'all', $params);

                    $this->Session->write('addOffer.users', $users);
                    $this->Session->write('addOffer.countUserOffer', count($this->Session->read('addOffer.users')));
                }

                break;

            case 'reset' :
                $this->Session->delete('addOffer.filters');
                $this->redirect(array(
                    'action' => 'addOffer',
                    'plugin' => 'companies',
                    'offerFilters'
                ));
                break;
            case 'cancel' :
                $this->Session->delete('addOffer');
                $this->redirect(array(
                    'action' => 'offers'
                ));
                break;
            case 'delete' :
                $this->deleteOffer();
                $this->redirect(array(
                    'action' => 'offers'
                ));
            case 'publish' :
                $this->publishOffer();
                $this->Session->delete('addOffer');
                $this->redirect(array(
                    'action' => 'offers'
                ));
                break;
            default :
                $this->redirect(array(
                    'action' => 'publico',
                    'plugin' => 'companies'
                ));
                break;
        }
        // default: render add_offer view
    }

    public function publico() {
        $company_id = $this->Session->read('CompanyLoggedIn.Company.id');
        $paramsTotal = array(
            'CompaniesUser' => array(
                'fields' => array(
                    'user_id'
                ),
                'conditions' => array(
                    'CompaniesUser.company_id' => $company_id,
                    'CompaniesUser.status' => 'ACTIVE'
                )
            )
        );
        $publicoTotal = $this->Utility->urlRequestToGetData('companies', 'count', $paramsTotal);
        $usuarios = $this->Utility->urlRequestToGetData('companies', 'all', $paramsTotal);

        // jogando usuarios de empresa em array para conditions
        foreach ($usuarios as $usuario) {
            $usersConditions [] = $usuario ['CompaniesUser'] ['user_id'];
        }

        /*
         * trabalhando genero - masculino e feminino trabalhando genero - masculino e feminino
         */
        $Paramsgenero = array(
            'CompaniesUser' => array(
                'fields' => array(
                    "COUNT(User.id) AS count",
                    "User.gender AS filter"
                ),
                'group' => array(
                    "User.gender"
                ),
                'conditions' => array(
                    'CompaniesUser.company_id' => $company_id,
                    'CompaniesUser.status' => 'ACTIVE'
                ),
                'order' => array(
                    'COUNT(User.id)' => 'DESC'
                )
            ),
            'User' => array()
        );
        $filterGender = $this->Utility->urlRequestToGetData('users', 'all', $Paramsgenero);
        foreach ($filterGender as $genderF) {
            // calculando porcentagem
            $porcentagem = (($genderF [0] ['count'] / $publicoTotal) * 100);
            $genderF ['porcentagem'] = $porcentagem;
            $genders [] = $genderF;
        }

        /*
         * trabalhando politica trabalhando politica
         */
        $Paramspolitical = array(
            'FacebookProfile' => array(
                'fields' => array(
                    "COUNT(FacebookProfile.id) AS count",
                    "FacebookProfile.political AS filter"
                ),
                'group' => array(
                    "FacebookProfile.political"
                ),
                'conditions' => array(
                    'FacebookProfile.user_id' => $usersConditions
                ),
                'order' => array(
                    'COUNT(FacebookProfile.id)' => 'DESC'
                )
            )
        );

        $filterPolitical = $this->Utility->urlRequestToGetData('users', 'all', $Paramspolitical);
        $contador = 0;
        $politicalNaoInformado ['porcentagem'] = 0;
        foreach ($filterPolitical as $politicalF) {
            // contando perfil de usuarios que nao estao no facebook para finalizar porcentagem
            $contador = $politicalF [0] ['count'] + $contador;

            // calculando porcentagem
            $porcentagem = (($politicalF [0] ['count'] / $publicoTotal) * 100);
            $politicalF ['porcentagem'] = $porcentagem;

            // verifica se � status nao informado
            if ($politicalF ['FacebookProfile'] ['filter'] == '') {
                $politicalNaoInformado ['porcentagem'] = $porcentagem;
                unset($politicalF);
            } else {
                $politicals [] = $politicalF;
            }
        }
        // verifica se existe usuario na tabela user que nao esta no facebook
        if ($contador < $publicoTotal) {
            $contadorFaltou = $publicoTotal - $contador;
            $porcentagem = (($contadorFaltou / $publicoTotal) * 100) + $politicalNaoInformado ['porcentagem'];
        }

        $politica_usuarios ['array'] = $politicals;
        $politica_usuarios ['NaoInformado'] = $porcentagem;
        // pr($politica_usuarios);exit;

        /*
         * trabalhando religiao trabalhando religiao
         */
        $Paramsreligion = array(
            'FacebookProfile' => array(
                'fields' => array(
                    "COUNT(FacebookProfile.id) AS count",
                    "FacebookProfile.religion AS filter"
                ),
                'group' => array(
                    "FacebookProfile.religion"
                ),
                'conditions' => array(
                    'FacebookProfile.user_id' => $usersConditions
                ),
                'order' => array(
                    'COUNT(FacebookProfile.id)' => 'DESC'
                )
            )
        );

        $filterReligion = $this->Utility->urlRequestToGetData('users', 'all', $Paramsreligion);
        $contador = 0;
        $religionNaoInformado ['porcentagem'] = 0;
        foreach ($filterReligion as $religionF) {
            // contando perfil de usuarios que nao estao no facebook para finalizar porcentagem
            $contador = $religionF [0] ['count'] + $contador;

            // calculando porcentagem
            $porcentagem = (($religionF [0] ['count'] / $publicoTotal) * 100);
            $religionF ['porcentagem'] = $porcentagem;

            // verifica se � status nao informado
            if ($religionF ['FacebookProfile'] ['filter'] == '') {
                $religionNaoInformado ['porcentagem'] = $porcentagem;
                unset($religionF);
            } else {
                $religions [] = $religionF;
            }
        }
        // verifica se existe usuario na tabela user que nao esta no facebook
        if ($contador < $publicoTotal) {
            $contadorFaltou = $publicoTotal - $contador;
            $porcentagem = (($contadorFaltou / $publicoTotal) * 100) + $religionNaoInformado ['porcentagem'];
        }

        $religiao_usuarios ['array'] = $religions;
        $religiao_usuarios ['NaoInformado'] = $porcentagem;
        // pr($religiao_usuarios);exit;

        /*
         * trabalhando localidades trabalhando localidades
         */
        $Paramslocal = array(
            'FacebookProfile' => array(
                'fields' => array(
                    "COUNT(FacebookProfile.id) AS count",
                    "FacebookProfile.location AS filter"
                ),
                'group' => array(
                    "FacebookProfile.location"
                ),
                'conditions' => array(
                    'FacebookProfile.user_id' => $usersConditions
                ),
                'order' => array(
                    'COUNT(FacebookProfile.id)' => 'DESC'
                )
            )
        );

        $filterLocal = $this->Utility->urlRequestToGetData('users', 'all', $Paramslocal);

        $contador = 0;
        $localNaoInformado ['porcentagem'] = 0;
        foreach ($filterLocal as $localF) {
            // contando perfil de usuarios que nao estao no facebook para finalizar porcentagem
            $contador = $localF [0] ['count'] + $contador;

            // calculando porcentagem
            $porcentagem = (($localF [0] ['count'] / $publicoTotal) * 100);
            $localF ['porcentagem'] = $porcentagem;

            // verifica se � status nao informado
            if ($localF ['FacebookProfile'] ['filter'] == '') {
                $localNaoInformado ['porcentagem'] = $porcentagem;
                unset($localF);
            } else {
                $locals [] = $localF;
            }
        }
        // verifica se existe usuario na tabela user que nao esta no facebook
        if ($contador < $publicoTotal) {
            $contadorFaltou = $publicoTotal - $contador;
            $porcentagem = (($contadorFaltou / $publicoTotal) * 100) + $localNaoInformado ['porcentagem'];
        }

        $local_usuarios ['array'] = $locals;
        $local_usuarios ['NaoInformado'] = $porcentagem;

        /*
         * trabalhando status de relacionamento trabalhando status de relacionamento
         */
        $Paramsrelations = array(
            'FacebookProfile' => array(
                'fields' => array(
                    "COUNT(FacebookProfile.id) AS count",
                    "FacebookProfile.relationship_status AS filter"
                ),
                'group' => array(
                    "FacebookProfile.relationship_status"
                ),
                'conditions' => array(
                    'FacebookProfile.user_id' => $usersConditions
                ),
                'order' => array(
                    'COUNT(FacebookProfile.id)' => 'DESC'
                )
            )
        );

        $filterRelation = $this->Utility->urlRequestToGetData('users', 'all', $Paramsrelations);
        $contador = 0;
        $relationNaoInformado ['porcentagem'] = 0;
        foreach ($filterRelation as $relationF) {
            // contando perfil de usuarios que nao estao no facebook para finalizar porcentagem
            $contador = $relationF [0] ['count'] + $contador;

            // calculando porcentagem
            $porcentagem = (($relationF [0] ['count'] / $publicoTotal) * 100);
            $relationF ['porcentagem'] = $porcentagem;

            // verifica se � status nao informado
            if ($relationF ['FacebookProfile'] ['filter'] == '') {
                $relationNaoInformado ['porcentagem'] = $porcentagem;
                unset($relationF);
            } else {
                $relations [] = $relationF;
            }
        }
        // verifica se existe usuario na tabela user que nao esta no facebook
        if ($contador < $publicoTotal) {
            $contadorFaltou = $publicoTotal - $contador;
            $porcentagem = (($contadorFaltou / $publicoTotal) * 100) + $relationNaoInformado ['porcentagem'];
        }

        $relacionamento_usuarios ['array'] = $relations;
        $relacionamento_usuarios ['NaoInformado'] = $porcentagem;

        /*
         * trabalhando faixa etaria trabalhando faixa etaria
         */
        $query = "SELECT
					    SUM(IF(age < 20,1,0)) AS '0-19',
					    SUM(IF(age BETWEEN 20 AND 29,1,0)) AS '20-29',
					    SUM(IF(age BETWEEN 30 AND 39,1,0)) AS '30-39',
					    SUM(IF(age BETWEEN 40 AND 49,1,0)) AS '40-49',
					    SUM(IF(age BETWEEN 50 AND 59,1,0)) AS '50-59',
					    SUM(IF(age BETWEEN 60 AND 120,1, 0)) AS '60-120',
					    SUM(IF(age >= 121, 1, 0)) AS 'empty'
						FROM (SELECT YEAR(CURDATE())-YEAR(birthday) AS age FROM users as a, 
														companies_users as b where a.id=b.user_id and b.status='ACTIVE' and b.company_id = {$this->Session->read('CompanyLoggedIn.Company.id')}) AS derived";

        $filterParams = array(
            'User' => array(
                'query' => $query
            )
        );
        $filter = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);
        $total = 0;

        $porcentagem = (($filter [0] [0] ['0-19'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['0-19'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['0_20']
            );
        }

        $porcentagem = (($filter [0] [0] ['20-29'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['20-29'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['20_29']
            );
        }

        $porcentagem = (($filter [0] [0] ['30-39'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['30-39'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['30_39']
            );
        }

        $porcentagem = (($filter [0] [0] ['40-49'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['40-49'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['40_49']
            );
        }

        $porcentagem = (($filter [0] [0] ['50-59'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['50-59'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['50_59']
            );
        }

        $porcentagem = (($filter [0] [0] ['60-120'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['60-120'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['60_120']
            );
        }

        $porcentagem = (($filter [0] [0] ['empty'] / $publicoTotal) * 100);
        if ($porcentagem > 0) {
            $total = $porcentagem + $total;
            $filter [0] [0] ['empty'] = array(
                'porcentagem' => $porcentagem,
                'descricao' => $this->ageGroupRangeKeys ['empty']
            );
        }
        $filter = $filter [0] [0];
        $this->set(compact('publicoTotal', 'genders', 'filter', 'politica_usuarios', 'religiao_usuarios', 'relacionamento_usuarios', 'local_usuarios'));
    }

    public function signatures() {
        $update = true;
        $limit = 5;
        $params = array(
            'CompaniesUser' => array(
                'fields' => array(
                    'User.photo',
                    'User.name'
                ),
                'conditions' => array(
                    'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'User.status' => 'ACTIVE',
                    'CompaniesUser.status' => 'ACTIVE',
                    'YEAR(CompaniesUser.date_register)' => date('Y')
                ),
                'order' => array(
                    'CompaniesUser.id' => 'DESC'
                ),
                'limit' => $limit
            ),
            'User'
        );
        $contador = $this->Utility->urlRequestToGetData('companies', 'count', $params);

        // verifica se esta fazendo uma requisicao ajax
        if (!empty($this->request->data ['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST ['limit'] + 5;
            if ($limit > $contador) {
                $limit = $contador;
                $update = false;
            }
        }

        if (!$contador)
            $update = false;
        // assinaturas do ano atual
        $params = array(
            'CompaniesUser' => array(
                'fields' => array(
                    'User.photo',
                    'User.name'
                ),
                'conditions' => array(
                    'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'User.status' => 'ACTIVE',
                    'CompaniesUser.status' => 'ACTIVE',
                    'YEAR(CompaniesUser.date_register)' => date('Y')
                ),
                'order' => array(
                    'CompaniesUser.id' => 'DESC'
                ),
                'limit' => $limit
            ),
            'User'
        );
        $assinaturasAnoAtual = $this->Utility->urlRequestToGetData('companies', 'all', $params);

        // assinaturas de outros anos desde que empresa entrou no sistema
        $anoRegistro = date('Y', strtotime($this->Session->read('CompanyLoggedIn.Company.date_register')));
        for ($i = date('Y') - 1; $i >= $anoRegistro; $i --) {

            $params = array(
                'CompaniesUser' => array(
                    'fields' => array(
                        'User.photo',
                        'User.name'
                    ),
                    'conditions' => array(
                        'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                        'User.status' => 'ACTIVE',
                        'CompaniesUser.status' => 'ACTIVE',
                        'YEAR(CompaniesUser.date_register)' => $i
                    ),
                    'order' => array(
                        'CompaniesUser.id' => 'DESC'
                    ),
                    'limit' => 5
                ),
                'User'
            );
            $assinaturasArray = $this->Utility->urlRequestToGetData('companies', 'all', $params);
            $assinaturas [] [$i] = $assinaturasArray;
        }

        // assinaturas canceladas
        $params = array(
            'CompaniesUser' => array(
                'fields' => array(
                    'User.photo',
                    'User.name'
                ),
                'conditions' => array(
                    'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'User.status' => 'ACTIVE',
                    'CompaniesUser.status' => 'INACTIVE'
                ),
                'order' => array(
                    'CompaniesUser.id' => 'DESC'
                ),
                'limit' => 5
            ),
            'User'
        );
        $assinaturasCanceladas = $this->Utility->urlRequestToGetData('companies', 'all', $params);

        $this->set(compact('assinaturasAnoAtual', 'assinaturas', 'assinaturasCanceladas', 'limit', 'update'));
        if (!empty($render))
            $this->render('Elements/ajax_assinaturas');
    }

    /**
     * Efetua login da empresa para utilizar o app
     *
     * @return void
     */
    public function login() {
        $this->layout = 'company_login';

        if ($this->request->is('post')) {
            $email = trim($this->request->data ['Company'] ['email']);
            $pass = md5(trim($this->request->data ['Company'] ['password']));
            $conditions = array(
                'Company' => array(
                    'conditions' => array(
                        'Company.responsible_email' => $email,
                        'Company.password' => $pass,
                        'Company.status' => 'ACTIVE'
                    )
                ),
                'CompanyPreference' => array()
            );
            // buscando company na API
            $company = $this->Utility->urlRequestToGetData('companies', 'first', $conditions);

            if ((!empty($company ['status']) && $company ['status'] === 'GET_ERROR') || empty($company)) {
                $this->Session->setFlash('Dados inválidos. Tente novamente.');
            } else {
                // cria sessao com dados da empresa
                $this->Session->write('sessionLogado', true);
                $this->Session->write('CompanyLoggedIn', $company);
                $this->redirect(array(
                    'action' => 'home'
                ));
            }
        }
    }

    public function logoof() {
        $this->Session->destroy();
        $this->redirect(array(
            'controller' => 'companies',
            'plugin' => 'companies',
            'action' => 'home'
        ));
    }

    public function xlogoof() {
        session_destroy();
        $this->redirect(array('controller' => 'companies', 'plugin' => 'companies', 'action' => 'layoutPopUp'));
    }

    /**
     * Salva dados da oferta e filtros relacionados.
     *
     * @return string
     */
    private function publishOffer() {
        if ($this->Session->check('addOffer')) {

            // salvando oferta
            $params ['Offer'] = $this->Session->read('addOffer.Offer');

            if ($params ['Offer'] ['parcels'] == 1) {
                $params ['Offer'] ['parcels'] = 'ACTIVE';

                if ($params ['Offer'] ['parcels_off_impost'] == 1)
                    $params ['Offer'] ['parcels_off_impost'] = 'ACTIVE';
                else
                    $params ['Offer'] ['parcels_off_impost'] = 'INACTIVE';
            } else {
                $params ['Offer'] ['parcels'] = 'INACTIVE';
                $params ['Offer'] ['parcels_off_impost'] = 'INACTIVE';
            }

            $params ['Offer'] ['company_id'] = $this->Session->read('CompanyLoggedIn.Company.id');
            $params ['Offer'] ['status'] = 'ACTIVE';

            $params ['Offer'] ['begins_at'] = $this->Utility->formataData($params ['Offer'] ['begins_at']);
            $params ['Offer'] ['ends_at'] = $this->Utility->formataData($params ['Offer'] ['ends_at']);

            // trabalhando especificacao e descricao para insert no banco
            $params ['Offer'] ['description'] = strip_tags($params ['Offer'] ['description'], '<a><strong><p><br><b><li><ul><img>');
            $params ['Offer'] ['specification'] = strip_tags($params ['Offer'] ['specification'], '<a><strong><p><br><b><li><ul><img>');

            // $params['Offer']['description'] = substr($params['Offer']['description'], 0, 1000);
            // $params['Offer']['specification'] = substr ($params['Offer']['specification'], 0 ,1000);

            $ofertaPublica = $params ['Offer'] ['public'];
            $params ['Offer'] ['value'] = str_replace(',', '.', str_replace('.', '', $params ['Offer'] ['value']));
            $addOffer = $this->Utility->urlRequestToSaveData('offers', $params);
            if ($addOffer ['status'] == 'SAVE_OK') {
                if ($this->Session->read('addOffer.update') == true) {
                    // deletando publico alvo e filtros selecionados
                    $offerId = $this->Session->read('addOffer.Offer.id');

                    $query = "DELETE FROM offers_filters where offer_id = {$offerId}";
                    $params = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $delOfferFilter = $this->Utility->urlRequestToGetData('users', 'query', $params);

                    $query = "DELETE FROM offers_users where offer_id = {$offerId}";
                    $params = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $delOfferUser = $this->Utility->urlRequestToGetData('users', 'query', $params);

                    $query = "DELETE FROM offers_photos where offer_id = {$offerId}";
                    $params = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $delOfferUser = $this->Utility->urlRequestToGetData('users', 'query', $params);
                } else {
                    // pegando id de oferta cadastrada
                    $params = array(
                        'Offer' => array(
                            'order' => array(
                                'Offer.id' => 'DESC'
                            )
                        )
                    );
                    $offer = $this->Utility->urlRequestToGetData('offers', 'first', $params);
                    $offerId = $offer ['Offer'] ['id'];
                }

                // $offerId = 42;
                // pegando valores de filtros selecionados
                $gender = implode(',', $this->Session->read('addOffer.filters.gender'));
                $location = implode(',', $this->Session->read('addOffer.filters.location'));
                $age_group = implode(',', $this->Session->read('addOffer.filters.age_group'));
                $political = implode(',', $this->Session->read('addOffer.filters.political'));
                $relationship_status = implode(',', $this->Session->read('addOffer.filters.relationship_status'));
                $religion = implode(',', $this->Session->read('addOffer.filters.religion'));

                // fazendo insert de filtros
                $params = array(
                    'OffersFilter' => array(
                        'offer_id' => $offerId,
                        'gender' => $gender,
                        'location' => $location,
                        'age_group' => $age_group,
                        'political' => $political,
                        'religion' => $religion,
                        'relationship_status' => $relationship_status
                    )
                );
                $addOfferFilters = $this->Utility->urlRequestToSaveData('offers', $params);

                // fazendo insert de fotos
                $photos = $this->Session->read('addOffer.OffersPhoto');
                foreach ($photos as $photo) {
                    if (!empty($photo)) {
                        $params = array(
                            'OffersPhoto' => array(
                                'offer_id' => $offerId,
                                'photo' => $photo ['photo']
                            )
                        );
                        $addPhoto = $this->Utility->urlRequestToSaveData('offers', $params);
                    }
                }

                if ($ofertaPublica == 'INACTIVE') {
                    // fazendo insert de usuarios capturados
                    $users = $this->Session->read('addOffer.users');
                    foreach ($users as $user) {
                        $data = date('Y/m/d');
                        $query = "INSERT INTO offers_users values(NULL, '{$offerId}', '{$user['User']['id']}', '{$data}', 'facebook - portal')";
                        $params = array(
                            'User' => array(
                                'query' => $query
                            )
                        );

                        $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    }
                }

                // verifica se oferta esta sendo cadastrada para usuario que fez desejo
                if ($this->Session->check('CadOfferUser.id_linha_wishlist')) {
                    $data = date('Y/m/d');

                    // faz update de oferta para usuario na linha do wishlist
                    $params = array(
                        'UsersWishlistCompany' => array(
                            'id' => $this->Session->read('CadOfferUser.id_linha_wishlist'),
                            'status' => 'ACTIVE',
                            'offer_id' => $offerId
                        )
                    );
                    $desejosOfferUpdate = $this->Utility->urlRequestToSaveData('companies', $params);

                    if ($desejosOfferUpdate ['status'] == 'SAVE_OK') {

                        // verifica se usuario ja tem esta oferta
                        $params = array(
                            'OffersUser' => array(
                                'conditions' => array(
                                    'offer_id' => $desejosOfferUpdate ['data'] ['UsersWishlistCompany'] ['offer_id'],
                                    'user_id' => $desejosOfferUpdate ['data'] ['User'] ['id']
                                )
                            )
                        );
                        $offer_user = $this->Utility->urlRequestToGetData('users', 'first', $params);

                        if (!is_array($offer_user)) {
                            // salvando oferta para usuario
                            $data = date('Y/m/d');
                            $query = "INSERT INTO offers_users values(NULL, '{$desejosOfferUpdate['data']['UsersWishlistCompany']['offer_id']}', '{$desejosOfferUpdate['data']['User']['id']}', '{$data}', 'facebook - portal')";
                            $params = array(
                                'User' => array(
                                    'query' => $query
                                )
                            );
                            $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                        }
                    }
                }
            } else {
                echo "<script>alert('email enviado com sucesso');</script>";
                $this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'addOffer',
                    'plugin' => 'companies',
                    'detalhes'
                ));
            }
        }
    }

    private function deleteOffer() {
        if ($this->Session->read('addOffer.update') == true) {
            $offerId = $this->Session->read('addOffer.Offer.id');

            $query = "UPDATE offers set status='DELETE' where id = {$offerId}";
            $params = array(
                'User' => array(
                    'query' => $query
                )
            );
            $delOfferUser = $this->Utility->urlRequestToGetData('users', 'query', $params);

            $this->Session->delete('addOffer');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Ajax requests
     *
     * @return string
     */
    private function setOfferFilters() {
        // gravando filtros na sessão
        if (!empty($_POST ['filters'])) {
            $_SESSION ['addOffer'] ['filters'] = json_decode($_POST ['filters'], true);
            $status = array(
                'status' => '',
                'filters' => $_SESSION ['addOffer'] ['filters']
            );
            if (!$this->Session->check('addOffer.filters')) {
                $status ['status'] = 'FILTERS_NOT_OK';
            }
            echo json_encode($status);
            exit();
        } else {
            $filters = $_SESSION ['addOffer'] ['filters'];
            echo json_encode($filters);
            exit();
        }
    }

    /**
     * lista de pedidos
     * moderacao de comentarios
     *
     * @return string
     */
    public function listPurchase() {
        $companyId = $this->Session->read('CompanyLoggedIn.Company.id');
        $limit = 10;
        $valor_total = 0;
        $update = true;

        // array com dados obrigatorios para busca
        $arrayBusca = array(
            'Checkout.company_id' => $companyId,
            'Checkout.total_value > ' => 0,
            'NOT' => array(
                'Checkout.payment_state_id' => array(
                    '14'
                )
            )
        );

        // verifica se esta sendo feita uma busca
        if (!empty($this->request->data ['busca'])) {
            if ($this->request->data ['calendario-inicio'] != 'Data Inicial' && $this->request->data ['calendario-fim'] != 'Data Final') {
                $dataInicio = $this->Utility->formataData($this->request->data ['calendario-inicio']);
                $dataFinal = $this->Utility->formataData($this->request->data ['calendario-fim']);
                $arrayData = array(
                    'Checkout.date >= ' => $dataInicio,
                    'Checkout.date <= ' => $dataFinal
                );
                $arrayBusca = array_merge($arrayData, $arrayBusca);
            }
            if ($this->request->data ['produto'] != 'Produto') {
                $arrayProduto = array(
                    'Offer.title LIKE' => "%{$this->request->data['produto']}%"
                );
                $arrayBusca = array_merge($arrayProduto, $arrayBusca);
            }
            if ($this->request->data ['comprador'] != 'Comprador') {
                $arrayComprador = array(
                    'User.name LIKE' => "%{$this->request->data['comprador']}%"
                );
                $arrayBusca = array_merge($arrayComprador, $arrayBusca);
            }
        }

        $paramsCount = array(
            'Checkout' => array(
                'fields' => array(
                    'SUM(Checkout.total_value) as valor_total'
                ),
                'conditions' => $arrayBusca
            ),
            'Offer' => array(),
            'User' => array(),
            'PaymentState' => array()
        );

        $contador = $this->Utility->urlRequestToGetData('payments', 'count', $paramsCount);
        $valor_total = $this->Utility->urlRequestToGetData('payments', 'first', $paramsCount);
        if (is_array($valor_total))
            $valor_total = $valor_total [0] ['valor_total'];

        // verifica se esta fazendo uma requisicao ajax para mais pedidos
        if (!empty($this->request->data ['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST ['limit'] + 5;
            if ($limit > $contador) {
                $limit = $contador;

                // nao chama mais atualizacao
                $update = false;
            }
        }

        // verifica se esta fazendo uma requisicao ajax para atualizar publicacao de comentarios
        if (!empty($this->request->data ['id_comentario'])) {

            $arrayParams = array(
                'OffersComment' => array(
                    'id' => $this->request->data ['id_comentario'],
                    'status' => $this->request->data ['status']
                )
            );
            $updateStatus = $this->Utility->urlRequestToSaveData('offers', $arrayParams);
            exit();
        }

        $params = array(
            'Checkout' => array(
                'conditions' => $arrayBusca,
                'limit' => $limit,
                'order' => array(
                    'Checkout.id' => 'DESC'
                )
            ),
            'Offer' => array(),
            'User' => array(),
            'PaymentState' => array()
        );

        $checkouts = $this->Utility->urlRequestToGetData('payments', 'all', $params);
        if (is_array($checkouts)) {
            foreach ($checkouts as $checkout) {

                // verificando se pedido ja foi comentado por comprador
                $idOffer = $checkout ['Checkout'] ['offer_id'];
                $idUser = $checkout ['Checkout'] ['user_id'];
                $params = array(
                    'OffersComment' => array(
                        'conditions' => array(
                            'OffersComment.offer_id' => $idOffer,
                            'OffersComment.user_id' => $idUser
                        )
                    )
                );
                $comentario = $this->Utility->urlRequestToGetData('offers', 'first', $params);
                if (!$comentario == true) {
                    $checkout ['Checkout'] ['comment'] = false;
                } else {
                    $checkout ['Checkout'] ['comment'] = $comentario ['OffersComment'];
                }

                $checks [] = $checkout;
            }
        } else {
            $checks = "NENHUM REGISTRO ENCONTRADO";
        }

        $this->set(compact('checks', 'limit', 'valor_total', 'contador', 'update'));
        if (!empty($render))
            $this->render('Elements/ajax_compras');
    }

    public function preferencias($page = null) {
        switch ($page) {
            case 'dados-bancarios' :
                if ($this->request->is('post')) {

                    $params = $this->request->data;
                    $params ['CompanyPreference'] ['id'] = $this->Session->read('CompanyLoggedIn.CompanyPreference.id');

                    // verificando se agencia tem digito
                    if (!empty($params ['CompanyPreference'] ['agency-digito'])) {
                        $params ['CompanyPreference'] ['agency'] = $params ['CompanyPreference'] ['agency'] . '-' . $params ['CompanyPreference'] ['agency-digito'];
                    }
                    // verificando se conta tem digito
                    if (!empty($params ['CompanyPreference'] ['account-digito'])) {
                        $params ['CompanyPreference'] ['account'] = $params ['CompanyPreference'] ['account'] . '-' . $params ['CompanyPreference'] ['account-digito'];
                    }
                    // verificando se banco foi alterado
                    if (empty($params ['CompanyPreference'] ['bank'])) {
                        $params ['CompanyPreference'] ['bank'] = $this->Session->read('CompanyLoggedIn.CompanyPreference.bank');
                    }
                    unset($params ['CompanyPreference'] ['account-digito']);
                    unset($params ['CompanyPreference'] ['agency-digito']);

                    $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);
                    $this->Session->write('CompanyLoggedIn.CompanyPreference', $params ['CompanyPreference']);
                    $mensagem = "Dados bancarios editados com sucesso";
                }
                $this->set(compact('mensagem'));
                $this->render('dados-bancarios');
                break;
            case 'frete' :
                if ($this->request->is('post')) {
                    $params = $this->request->data;
                    $params ['CompanyPreference'] ['id'] = $this->Session->read('CompanyLoggedIn.CompanyPreference.id');

                    $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);
                    $this->Session->write('CompanyLoggedIn.CompanyPreference.shipping_value', $params ['CompanyPreference'] ['shipping_value']);
                    $this->Session->write('CompanyLoggedIn.CompanyPreference.delivery_time', $params ['CompanyPreference'] ['delivery_time']);
                    $this->Session->write('CompanyLoggedIn.CompanyPreference.use_correios_api', $params ['CompanyPreference'] ['use_correios_api']);

                    $mensagem = "Dados de Frete editados com sucesso";
                }
                $this->set(compact('mensagem'));
                $this->render('frete');
                break;
            case 'alterar-senha' :
                $mensagem = null;
                if ($this->request->is('post')) {

                    $senha = $this->request ['data'] ['senha_nova'];
                    $params = array(
                        'Company' => array(
                            'id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                            'password' => $senha
                        )
                    );

                    $updateSenha = $this->Utility->urlRequestToSaveData('companies', $params);
                    $this->Session->write('CompanyLoggedIn.Company.password', $updateSenha ['data'] ['Company'] ['password']);

                    if ($updateSenha ['status'] == 'SAVE_OK') {
                        $mensagem = "SENHA ALTERADA COM SUCESSO";
                    } else {
                        $mensagem = "Ocorreu um erro! Tente novamente";
                    }
                }
                $this->set(compact('mensagem'));
                $this->render('alterar_senha');
                break;
            default :
                if ($this->request->is('ajax')) {
                    if (!empty($this->request->data ['cep'])) {
                        $cURL = curl_init("http://cep.correiocontrol.com.br/{$this->request->data['cep']}.json");
                        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                        $resultado = curl_exec($cURL);
                        curl_close($cURL);
                        echo $resultado;
                        exit();
                    }
                }
                if ($this->request->is('post')) {

                    $params = array(
                        'Company' => array(
                            'conditions' => array(
                                'Company.responsible_cpf' => $this->request->data ['Company'] ['responsible_cpf'],
                                'Company.id <> ' => $this->Session->read('CompanyLoggedIn.Company.id')
                            )
                        )
                    );
                    $verificacaoCpf = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                    $params = array(
                        'Company' => array(
                            'conditions' => array(
                                'Company.responsible_email' => $this->request->data ['Company'] ['responsible_email'],
                                'Company.id <> ' => $this->Session->read('CompanyLoggedIn.Company.id')
                            )
                        )
                    );
                    $verificacaoEmail = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                    $params = array(
                        'Company' => array(
                            'conditions' => array(
                                'Company.cnpj' => $this->request->data ['Company'] ['cnpj'],
                                'Company.id <> ' => $this->Session->read('CompanyLoggedIn.Company.id')
                            )
                        )
                    );
                    $verificacaoCnpj = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                    if ($verificacaoCnpj == true) {
                        $mensagem = "CNPJ JA CADASTRADO NA NOSSA BASE DE DADOS";
                    } else if ($verificacaoEmail == true) {
                        $mensagem = "EMAIL JA CADASTRADO NA NOSSA BASE DE DADOS";
                    } else if ($verificacaoCpf == true) {
                        $mensagem = "CPF JA CADASTRADO NA NOSSA BASE DE DADOS";
                    } else {
                        // fazendo insert de empresa
                        $params = $this->request->data;
                        $params ['Company'] ['id'] = $this->Session->read('CompanyLoggedIn.Company.id');
                        $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);
                        $this->Session->write('CompanyLoggedIn.Company', $cadastro ['data'] ['Company']);

                        $mensagem = 'DADOS EDITADOS COM SUCESSO';
                    }
                    $this->set(compact('mensagem'));
                }
                $this->render('dados-cadastrais');
                break;
        }
    }

    /**
     * trabalhando desejos de usuarios para empresas
     *
     * @return string
     */
    public function wishlist($action = null, $id = null) {
        $company_id = $this->Session->read('CompanyLoggedIn.Company.id');

        // verifica se vai cadastrar uma oferta especifica para usuario
        if ($action == 'offer_user') {
            $this->Session->write('CadOfferUser.id_linha_wishlist', $id);
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'addOffer',
                'plugin' => 'companies',
                'detalhes'
            ));
        }

        // verifica post e se vai inserir uma oferta especifica p/ usuario
        if ($this->request->is('post')) {

            if ($this->request->data ['excluir_wishlist'] == true) {
                $render = true;
                $this->layout = '';

                // faz update de wishlist para excluir desejo
                $params = array(
                    'UsersWishlistCompany' => array(
                        'id' => $this->request->data ['id_linha'],
                        'status' => 'INACTIVE'
                    )
                );

                $desejosDel = $this->Utility->urlRequestToSaveData('companies', $params);

                if (!$desejosDel ['status'] == 'SAVE_OK') {
                    $mensagem = "Ocorreu um erro. Tente novamente";
                }
            } else if ($this->request->data ['id_oferta'] > 0) {

                // faz update de oferta para usuario na linha do wishlist
                $params = array(
                    'UsersWishlistCompany' => array(
                        'id' => $this->request->data ['id_linha'],
                        'status' => 'ACTIVE',
                        'offer_id' => $this->request->data ['id_oferta']
                    )
                );

                $desejosOfferUpdate = $this->Utility->urlRequestToSaveData('companies', $params);

                if ($desejosOfferUpdate ['status'] == 'SAVE_OK') {

                    // verifica se usuario ja tem esta oferta
                    $params = array(
                        'OffersUser' => array(
                            'conditions' => array(
                                'offer_id' => $desejosOfferUpdate ['data'] ['UsersWishlistCompany'] ['offer_id'],
                                'user_id' => $desejosOfferUpdate ['data'] ['User'] ['id']
                            )
                        )
                    );
                    $offer_user = $this->Utility->urlRequestToGetData('users', 'first', $params);

                    if (!is_array($offer_user)) {
                        // salvando oferta para usuario
                        $data = date('Y/m/d');
                        $query = "INSERT INTO offers_users values(NULL, '{$desejosOfferUpdate['data']['UsersWishlistCompany']['offer_id']}', '{$desejosOfferUpdate['data']['User']['id']}', '{$data}', 'facebook - portal')";
                        $params = array(
                            'User' => array(
                                'query' => $query
                            )
                        );
                        $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    }
                } else {
                    $mensagem = "Ocorreu um erro. Tente novamente";
                }
            } else {
                $mensagem = "Voce nao selecinou nenhuma oferta para este desejo. Tente novamente";
            }
        }

        $limit = 6;
        $update = true;

        // listando desejos
        $params = array(
            'UsersWishlistCompany' => array(
                'conditions' => array(
                    'UsersWishlistCompany.company_id' => $company_id,
                    'UsersWishlistCompany.status' => 'WAIT'
                ),
                'order' => array(
                    'UsersWishlistCompany.id' => 'DESC'
                )
            )
        );
        $contador = $this->Utility->urlRequestToGetData('companies', 'count', $params);

        // verifica se esta fazendo uma requisicao ajax
        if (!empty($this->request->data ['limit'])) {
            $render = true;
            $this->layout = '';
            $limit = $_POST ['limit'] + 2;
            if ($limit >= $contador) {
                $limit = $contador;
                $update = false;
            }
        }

        // listando desejos
        $params = array(
            'UsersWishlistCompany' => array(
                'conditions' => array(
                    'UsersWishlistCompany.company_id' => $company_id,
                    'UsersWishlistCompany.status' => 'WAIT'
                ),
                'order' => array(
                    'UsersWishlistCompany.id' => 'DESC'
                ),
                'limit' => $limit
            ),
            'User',
            'UsersWishlist',
            'CompaniesCategory'
        );
        $desejos = $this->Utility->urlRequestToGetData('companies', 'all', $params);

        // pegando ofertas de empresa
        $params = array(
            'Offer' => array(
                'fields' => array(
                    'Offer.id',
                    'Offer.title'
                ),
                'conditions' => array(
                    'Offer.company_id' => $company_id,
                    'Offer.status' => 'ACTIVE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                )
            )
        );
        $ofertas = $this->Utility->urlRequestToGetData('offers', 'all', $params);

        $this->set(compact('ofertas', 'desejos', 'limit', 'contador', 'update', 'mensagem'));

        if (!empty($render))
            $this->render('Elements/ajax_desejos');
    }

    /**
     * convidando usuarios para assinar empresa logada
     *
     * @return string
     */
    public function cadConvite($page = null, $id = null) {
        $company_id = $this->Session->read('CompanyLoggedIn.Company.id');

        if ($this->request->is('ajax')) {
            $this->setOfferFilters();
        }

        if ($page == 'filtros') {
            $offerFiltersData = $this->loadFilters();
            $this->set(compact('offerFiltersData'));
            $this->render('filtros');
        } else if ($page == 'resume') {

            $data = date('Y/m/d');

            $filters = $this->Session->read('addOffer.filters');
            if (!empty($filters ['gender'] [0]))
                $conditions ['User.gender'] = $filters ['gender'];
            if (!empty($filters ['location'] [0]))
                $conditions ['FacebookProfile.location'] = $filters ['location'];
            if (!empty($filters ['political'] [0]))
                $conditions ['FacebookProfile.political'] = $filters ['political'];
            if (!empty($filters ['relationship_status'] [0]))
                $conditions ['FacebookProfile.relationship_status'] = $filters ['relationship_status'];
            if (!empty($filters ['religion'] [0]))
                $conditions ['FacebookProfile.religion'] = $filters ['religion'];
            // $conditions['User.id'] = $this->Session->read('addOffer.userIds');
            // pegando valores de filtros selecionados
            $gender = implode(',', $this->Session->read('addOffer.filters.gender'));
            $location = implode(',', $this->Session->read('addOffer.filters.location'));
            $age_group = implode(',', $this->Session->read('addOffer.filters.age_group'));
            $political = implode(',', $this->Session->read('addOffer.filters.political'));
            $relationship_status = implode(',', $this->Session->read('addOffer.filters.relationship_status'));
            $religion = implode(',', $this->Session->read('addOffer.filters.religion'));

            // fazendo insert de filtros
            $query = "INSERT INTO companies_invitations_filters values(NULL, '{$company_id}', '{$gender}', '{$religion}', '{$political}', '{$age_group}', '{$location}', '{$relationship_status}', '{$data}')";
            $params = array(
                'User' => array(
                    'query' => $query
                )
            );
            $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);

            // pegando ultimo ID de convite
            $params = array(
                'CompaniesInvitationsFilter' => array(
                    'fields' => array(
                        'id'
                    ),
                    'order' => array(
                        'id' => 'DESC'
                    )
                )
            );
            $convite = $this->Utility->urlRequestToGetData('companies', 'first', $params);
            $convite_id = $convite ['CompaniesInvitationsFilter'] ['id'];

            $params = array(
                'User' => array(
                    'fields' => array(
                        'User.id'
                    ),
                    'conditions' => $conditions
                ),
                'FacebookProfile' => array()
            );
            $users = $this->Utility->urlRequestToGetData('users', 'all', $params);

            // realizando convites
            foreach ($users as $user) {

                // verifica se usuario esta assinado na empresa
                $params = array(
                    'CompaniesUser' => array(
                        'conditions' => array(
                            'company_id' => $company_id,
                            'user_id' => $user ['User'] ['id']
                        )
                    )
                );
                $assinatura_usuario = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                if (!$assinatura_usuario) {

                    // verifica se ja foi enviado convite para esse usuario
                    $params = array(
                        'CompaniesInvitationsUser' => array(
                            'conditions' => array(
                                'CompaniesInvitationsUser.user_id' => $user ['User'] ['id'],
                                'CompaniesInvitationsFilter.company_id' => $company_id
                            )
                        ),
                        'CompaniesInvitationsFilter'
                    );
                    $convite_usuario = $this->Utility->urlRequestToGetData('users', 'count', $params);

                    if (!$convite_usuario) {
                        // criando convite para usuario que nao tem assinatura nem convite anterior de empresa logada
                        $query = "INSERT INTO companies_invitations_users values(NULL, '{$convite_id}', '{$user['User']['id']}', {$company_id}, 'WAIT', 'INACTIVE', '{$data}')";
                        $params = array(
                            'User' => array(
                                'query' => $query
                            )
                        );
                        $addConvite = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    }
                }
            }
        }

        // pegando convites ja realizados pela empresa
        $params = array(
            'CompaniesInvitationsFilter' => array(
                'conditions' => array(
                    'company_id' => $company_id
                )
            )
        );
        $convites = $this->Utility->urlRequestToGetData('companies', 'all', $params);
        $this->set(compact('convites'));
    }

    /**
     * Pre cadastro de empresa no sistema
     *
     * @return string
     */
    public function preCadastro($page = null, $moip = null) {
        $buscaDadosCadastro = '';
        if ($page == 'sucesso') {
            if ($this->Session->check('URL')) {

                if ($moip != null) {
                    $this->set('moipFalse', true);
                } else {
                    $this->set('moipFalse', false);
                    $this->set('url', $this->Session->read('URL'));
                }
            }
            $this->render('sucesso');
        } else if ($page == 'faq') {
            if ($this->request->is('post')) {
                echo "<script>alert('email enviado com sucesso');</script>";
            }
            $this->render('faq');
        } else if ($page == 'email') {
            $params = $this->Session->read('email');
            $teste = $this->Utility->urlRequestToLoginData('companies', 'first', $params);
            $this->Session->delete('email');
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'login',
                'plugin' => 'companies'
            ));
        } else {
            $mensagem = '';
            if ($this->request->is('ajax')) {
                if (!empty($this->request->data ['cep'])) {
                    $cURL = curl_init("http://cep.correiocontrol.com.br/{$this->request->data['cep']}.json");
                    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                    $resultado = curl_exec($cURL);
                    curl_close($cURL);
                    echo $resultado;
                    exit();
                }
            } else {
                if ($this->request->is('post')) {

                    // verificando se vai recuperar dados
                    if ($this->request->data ['verificar_dados'] == true) {

                        // recuperando dados para cadastro de empresa
                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.responsible_cpf' => $this->request->data ['cpf'],
                                    'Company.cnpj' => $this->request->data ['cnpj'],
                                    'Company.phone' => $this->request->data ['telefone1']
                                )
                            )
                        );
                        $buscaDadosCadastro = $this->Utility->urlRequestToGetData('companies', 'first', $params);
                        if (!$buscaDadosCadastro) {
                            $mensagem = "Dados Inv�lidos, realizar um novo cadastro";
                        }
                    } else {
                        if (!empty($this->request->data ['Company'] ['id'])) {
                            $query = "Delete from companies where id={$this->request->data['Company']['id']}";
                            $params = array(
                                'Company' => array(
                                    'query' => $query
                                )
                            );
                            $delCompanyNewCad = $this->Utility->urlRequestToGetData('companies', 'query', $params);
                        }

                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.responsible_cpf' => $this->request->data ['Company'] ['responsible_cpf']
                                )
                            )
                        );
                        $verificacaoCpf = $this->Utility->urlRequestToGetData('companies', 'count', $params);
                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.responsible_email' => $this->request->data ['Company'] ['responsible_email']
                                )
                            )
                        );
                        $verificacaoEmail = $this->Utility->urlRequestToGetData('companies', 'count', $params);
                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.cnpj' => $this->request->data ['Company'] ['cnpj']
                                )
                            )
                        );
                        $verificacaoCnpj = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                        if ($verificacaoCnpj == true) {
                            $mensagem = "CNPJ JA CADASTRADO NA NOSSA BASE DE DADOS";
                        } else if ($verificacaoEmail == true) {
                            $mensagem = "EMAIL JA CADASTRADO NA NOSSA BASE DE DADOS";
                        } else if ($verificacaoCpf == true) {
                            $mensagem = "CPF JA CADASTRADO NA NOSSA BASE DE DADOS";
                        } else {
                            $upload = $this->Utility->uploadFile('companies', $this->request->data ['Company'] ['logo']);

                            if (!empty($upload)) {
                                if ($upload == "UPLOAD_ERROR" && !empty($this->request->data ['logo'])) {
                                    $upload = $this->request->data ['logo'];
                                }
                                unset($this->request->data ['logo']);

                                // fazendo insert de empresa
                                $params = $this->request->data;
                                $params ['Company'] ['status'] = 'ACTIVE';
                                $params ['Company'] ['logo'] = $upload;
                                $params ['Company'] ['register'] = 'INACTIVE';
                                $params ['Company'] ['category_id'] = 1;
                                $params ['Company'] ['sub_category_id'] = 5;
                                $params ['Company'] ['date_register'] = date('Y/m/d');
                                $params ['CompanyPreference'] ['use_correios_api'] = 1;

                                $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);

                                if (!$cadastro) {
                                    $mensagem = "Ocorreu um erro no teu cadastro";
                                } else {

                                    if (!empty($this->request->data ['Company'] ['id'])) {
                                        // nao faz chamada para MOIP
                                        $params = array(
                                            'Company' => array(
                                                'conditions' => array(
                                                    'Company.id' => $cadastro ['data'] ['Company'] ['id']
                                                ),
                                                'fields' => array(
                                                    'Company.Email',
                                                    'Company.responsible_email'
                                                )
                                            )
                                        );
                                        $teste = $this->Utility->urlRequestToLoginData('companies', 'first', $params);
                                        $this->Session->write('email', $params);

                                        $this->redirect(array(
                                            'controller' => 'companies',
                                            'action' => 'preCadastro',
                                            'plugin' => 'companies',
                                            'sucesso',
                                            'moipFalse'
                                        ));

                                        // chamar funcao de email de boas vindas
                                    } else {
                                        $params = array(
                                            'Company' => array(
                                                'conditions' => array(
                                                    'Company.id' => $cadastro ['data'] ['Company'] ['id']
                                                ),
                                                'fields' => array(
                                                    'Company.Email',
                                                    'Company.responsible_email'
                                                )
                                            )
                                        );
                                        $teste = $this->Utility->urlRequestToLoginData('companies', 'first', $params);
                                        $this->Session->write('email', $params);

                                        // trabalhando cadastro integrado do MOIP
                                        $sua_key = $this->key;
                                        $seu_token = $this->token;
                                        $auth = $seu_token . ':' . $sua_key;
                                        /*
                                         * $sua_key = 'SKMQ5HKQFTFRIFQBJEOROIGM70I6QVIN9KA5YIWB'; $seu_token = 'WOA4NBQ2AUMHJQ2NJIA6Q6X4ECXHFJUR'; $auth = $seu_token.':'.$sua_key;
                                         */

                                        $xml = "<PreCadastramento> 
													<prospect> 
														<idProprio>{$cadastro['data']['Company']['id']}</idProprio> 
														<nome>{$cadastro['data']['Company']['responsible_name']}</nome> 
														<sobrenome></sobrenome> 
														<email>{$cadastro['data']['Company']['responsible_email']}</email> 
														<dataNascimento></dataNascimento> 
														<rg></rg> 
														<cpf>{$cadastro['data']['Company']['responsible_cpf']}</cpf> 
														<cep>{$cadastro['data']['Company']['zip_code']}</cep> 
														<rua>{$cadastro['data']['Company']['address']}</rua> 
														<numero>{$cadastro['data']['Company']['number']}</numero> 
														<complemento></complemento> 
														<bairro>{$cadastro['data']['Company']['district']}</bairro> 
														<cidade>{$cadastro['data']['Company']['city']}</cidade> 
														<estado>{$cadastro['data']['Company']['state']}</estado> 
														<telefoneFixo>{$cadastro['data']['Company']['responsible_phone']}</telefoneFixo> 
														<razaoSocial>{$cadastro['data']['Company']['corporate_name']}</razaoSocial> 
														<nomeFantasia>{$cadastro['data']['Company']['fancy_name']}</nomeFantasia> 
														<cnpj>{$cadastro['data']['Company']['cnpj']}</cnpj> 
														<cepEmpresa>{$cadastro['data']['Company']['zip_code']}</cepEmpresa> 
														<ruaEmpresa>{$cadastro['data']['Company']['address']}</ruaEmpresa> 
														<numeroEmpresa>{$cadastro['data']['Company']['number']}</numeroEmpresa> 
														<complementoEmpresa></complementoEmpresa> 
														<bairroEmpresa>{$cadastro['data']['Company']['district']}</bairroEmpresa> 
														<cidadeEmpresa>{$cadastro['data']['Company']['city']}</cidadeEmpresa> 
														<estadoEmpresa>{$cadastro['data']['Company']['state']}</estadoEmpresa> 
														<telefoneFixoEmpresa>{$cadastro['data']['Company']['phone']}</telefoneFixoEmpresa> 
														<tipoConta>1</tipoConta>
													</prospect> 
											</PreCadastramento>		
										";

                                        // pr($xml);exit;
                                        // O HTTP Basic Auth � utilizado para autentica��o
                                        $header [] = "Authorization: Basic " . base64_encode($auth);

                                        // URL do SandBox - Nosso ambiente de testes
                                        // $url = "https://desenvolvedor.moip.com.br/sandbox/ws/alpha/PreCadastramento";
                                        $url = "https://www.moip.com.br/ws/alpha/PreCadastramento";

                                        $curl = curl_init();
                                        curl_setopt($curl, CURLOPT_URL, $url);

                                        // header que diz que queremos autenticar utilizando o HTTP Basic Auth
                                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

                                        // informa nossas credenciais
                                        curl_setopt($curl, CURLOPT_USERPWD, $auth);
                                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
                                        curl_setopt($curl, CURLOPT_POST, true);

                                        // Informa nosso XML de instru��o
                                        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);

                                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                                        // efetua a requisi��o e coloca a resposta do servidor do MoIP em $ret
                                        $ret = curl_exec($curl);
                                        $err = curl_error($curl);
                                        curl_close($curl);
                                        $xml = simplexml_load_string($ret);

                                        if ($xml->RespostaPreCadastramento->Status == 'Sucesso') {
                                            $url = "https://www.moip.com.br/cadastro/trueone/{$xml->RespostaPreCadastramento->idRedirecionamento}";

                                            $this->Session->write('URL', $url);
                                            $this->redirect(array(
                                                'controller' => 'companies',
                                                'action' => 'preCadastro',
                                                'plugin' => 'companies',
                                                'sucesso'
                                            ));
                                        } else {
                                            if (substr($xml->RespostaPreCadastramento->Erro, 0, 6) == 'E-mail') {
                                                $mensagem = "Cadastro efetuado com sucesso. Use sua conta do MOIP";
                                            } else {
                                                $mensagem = "Nao foi possivel cadastrar a tua empresa no MOIP - Entre em contato com o administrador do site";
                                            }
                                        }
                                    }
                                }
                            } else {
                                $mensagem = "Nao foi possivel efetuar o cadastro. Erro no envio de Imagem";
                            }
                        }
                    }
                    // aqui
                    $this->set(compact('mensagem', 'buscaDadosCadastro'));
                }
            }
        }
    }

    public function cadastroMoip() {
        /*
         * $all_api_call = simplexml_load_file($url); $all_api = array(); $all_api = $all_api_call->result; $list_all_api_name = array(); $i = 0; foreach ($all_api->children() as $funcky_function) { $string_tmp = (string )$funcky_function->function; $list_all_api_name[$i++] = $putain; }
         */
        $url = null;
        $mensagem = null;
        $erro = false;

        $arrayCompany ['data'] = $this->Session->read('CompanyLoggedIn');
        $xml = $this->cadMoipXml($arrayCompany, 'offers');

        if ($xml ['RespostaPreCadastramento'] ['Status'] == 'Sucesso') {
            $url = "https://www.moip.com.br/cadastro/trueone/{$xml->RespostaPreCadastramento->idRedirecionamento}";
        } else if ($xml ['RespostaPreCadastramento'] ['Login'] != '') {
            $login = $xml ['RespostaPreCadastramento'] ['Login'];
            $params = array(
                'Company' => array(
                    'id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'login_moip' => $login
                )
            );
            $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);

            if ($cadastro ['status'] == 'SAVE_OK') {
                $this->Session->write('CompanyLoggedIn.Company.login_moip', $cadastro ['data'] ['Company'] ['login_moip']);
                $this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'addOffer',
                    'plugin' => 'companies',
                    'detalhes'
                ));
            } else {
                $mensagem = "OCORREU UM ERRO NO TEU CADASTRO";
                $erro = true;
            }
        } else {
            $mensagem = $xml ['RespostaPreCadastramento'] ['Erro'];
            $erro = true;
        }

        $this->set(compact('url', 'mensagem', 'erro'));
    }

    /**
     * convites para usuarios via facebook
     *
     * @return string
     */
    public function convidarAmigos() {
        $this->render = false;
        $message = "Quer convidar teus amigos para o TRUEONE?";
        $requests_url = "http://www.facebook.com/dialog/apprequests?app_id=" . $this->appId . "&redirect_uri=http://facebook.com&message=" . $message;

        if (empty($_REQUEST ["request_ids"])) {
            echo ("<script> top.location.href='" . $requests_url . "'</script>");
        } else {
            echo "Request Ids: ";
            print_r($_REQUEST ["request_ids"]);
        }
    }

    public function new_password() {
        $this->layout = 'company_login';
        if ($this->request->data) {
            $arrayParams = array(
                'Company' => array(
                    'fields' => array(
                        'Company.id',
                        'Company.fancy_name',
                        'Company.responsible_email'
                    ),
                    'conditions' => array(
                        'Company.responsible_email' => $this->request->data ['Company'] ['responsible_email']
                    )
                )
            );

            $nova_senha = $this->Utility->urlRequestPasswordRecovery('companies', 'first', $arrayParams);

            if (!$nova_senha) {
                $mensagem = 'Email informado nao foi encontrado no trueone';
            } else {
                $mensagem = 'Senha alterada com sucesso, verifique sua caixa de entrada';
            }
            $this->set(compact('mensagem'));
        }
    }

    /**
     * Carrega os dados da base para montar os filtros de ofertas
     *
     * @return string
     */
    private function loadOfferFilters() {

        // buscando usuários
        $companyId = $this->Session->read('CompanyLoggedIn.Company.id');
        $params = array(
            'CompaniesUser' => array(
                'fields' => array(
                    'CompaniesUser.user_id'
                ),
                'conditions' => array(
                    'CompaniesUser.company_id' => $companyId,
                    'CompaniesUser.status' => 'ACTIVE'
                )
            )
        );
        $userIds = $this->Utility->urlRequestToGetData('companies', 'list', $params);

        if (!empty($userIds) && empty($userIds ['status'])) {
            $_SESSION ['addOffer'] ['userIds'] = $userIds;

            // conta público alvo
            $this->offerFilters ['target'] = count($userIds);

            // busca os filtros
            foreach ($this->offerFilters as $key => $value) {
                if ($key == 'gender') {
                    $Paramsgenero = array(
                        'CompaniesUser' => array(
                            'fields' => array(
                                "COUNT(User.id) AS count",
                                "User.gender AS filter"
                            ),
                            'group' => array(
                                "User.gender"
                            ),
                            'conditions' => array(
                                'CompaniesUser.company_id' => $companyId,
                                'CompaniesUser.status' => 'ACTIVE'
                            ),
                            'order' => array(
                                'COUNT(User.id)' => 'DESC'
                            )
                        ),
                        'User' => array()
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'all', $Paramsgenero);
                } else if ($key == 'age_group') {
                    // busca faixa etária
                    $query = "SELECT
					    SUM(IF(age < 20,1,0)) AS '{$this->ageGroupRangeKeys['0_20']}',
					    SUM(IF(age BETWEEN 20 AND 29,1,0)) AS '{$this->ageGroupRangeKeys['20_29']}',
					    SUM(IF(age BETWEEN 30 AND 39,1,0)) AS '{$this->ageGroupRangeKeys['30_39']}',
					    SUM(IF(age BETWEEN 40 AND 49,1,0)) AS '{$this->ageGroupRangeKeys['40_49']}',
					    SUM(IF(age BETWEEN 50 AND 59,1,0)) AS '{$this->ageGroupRangeKeys['50_59']}',
					    SUM(IF(age BETWEEN 60 AND 120,1, 0)) AS '{$this->ageGroupRangeKeys['60_120']}',
					    SUM(IF(age >= 121, 1, 0)) AS '{$this->ageGroupRangeKeys['empty']}'
						FROM (SELECT YEAR(CURDATE())-YEAR(birthday) AS age FROM users as a, 
														companies_users as b where a.id=b.user_id and b.status='ACTIVE' and b.company_id = {$this->Session->read('CompanyLoggedIn.Company.id')}) AS derived";

                    $filterParams = array(
                        'FacebookProfile' => array(
                            'query' => $query
                        )
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);
                } else {
                    $filterParams = array(
                        'FacebookProfile' => array(
                            'fields' => array(
                                "COUNT(FacebookProfile.{$key}) AS count",
                                "FacebookProfile.{$key} AS filter"
                            ),
                            'conditions' => array(
                                'FacebookProfile.user_id' => $userIds
                            ),
                            'group' => array(
                                "FacebookProfile.{$key}"
                            ),
                            'order' => array(
                                "FacebookProfile.{$key}" => 'ASC'
                            )
                        )
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'all', $filterParams);
                }
                if (!empty($filter) && empty($filter ['status'])) {
                    $this->offerFilters [$key] = $this->formatOfferFilters($filter);
                }
            }
            return $this->offerFilters;
        } else {
            $this->Session->setFlash('Houve um problema para carregar os filtros. Tente novamente.');
        }
    }

    /**
     * Carrega os dados da base para montar os filtros de ofertas
     *
     * @return string
     */
    private function loadFilters() {

        // buscando usuários
        $companyId = $this->Session->read('CompanyLoggedIn.Company.id');
        $params = array(
            'CompaniesUser' => array(
                'fields' => array(
                    'CompaniesUser.user_id'
                ),
                'conditions' => array(
                    'CompaniesUser.status' => 'ACTIVE'
                )
            )
        );
        $userIds = $this->Utility->urlRequestToGetData('companies', 'list', $params);
        if (!empty($userIds) && empty($userIds ['status'])) {
            $_SESSION ['addOffer'] ['userIds'] = $userIds;

            // conta público alvo
            $this->offerFilters ['target'] = count($userIds);

            // busca os filtros
            foreach ($this->offerFilters as $key => $value) {
                if ($key == 'gender') {
                    $Paramsgenero = array(
                        'CompaniesUser' => array(
                            'fields' => array(
                                "COUNT(User.id) AS count",
                                "User.gender AS filter"
                            ),
                            'group' => array(
                                "User.gender"
                            ),
                            'order' => array(
                                'COUNT(User.id)' => 'DESC'
                            )
                        ),
                        'User' => array()
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'all', $Paramsgenero);
                } else if ($key == 'age_group') {
                    // busca faixa etária
                    $query = "SELECT
					    SUM(IF(age < 20,1,0)) AS '{$this->ageGroupRangeKeys['0_20']}',
					    SUM(IF(age BETWEEN 20 AND 29,1,0)) AS '{$this->ageGroupRangeKeys['20_29']}',
					    SUM(IF(age BETWEEN 30 AND 39,1,0)) AS '{$this->ageGroupRangeKeys['30_39']}',
					    SUM(IF(age BETWEEN 40 AND 49,1,0)) AS '{$this->ageGroupRangeKeys['40_49']}',
					    SUM(IF(age BETWEEN 50 AND 59,1,0)) AS '{$this->ageGroupRangeKeys['50_59']}',
					    SUM(IF(age >=60, 1, 0)) AS '{$this->ageGroupRangeKeys['60_120']}',
					    SUM(IF(age IS NULL, 1, 0)) AS '{$this->ageGroupRangeKeys['empty']}'
						FROM (SELECT YEAR(CURDATE())-YEAR(birthday) AS age FROM facebook_profiles) AS derived";

                    $filterParams = array(
                        'FacebookProfile' => array(
                            'query' => $query
                        )
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'query', $filterParams);
                } else {
                    $filterParams = array(
                        'FacebookProfile' => array(
                            'fields' => array(
                                "COUNT(FacebookProfile.{$key}) AS count",
                                "FacebookProfile.{$key} AS filter"
                            ),
                            'conditions' => array(
                                'FacebookProfile.user_id' => $userIds
                            ),
                            'group' => array(
                                "FacebookProfile.{$key}"
                            ),
                            'order' => array(
                                "FacebookProfile.{$key}" => 'ASC'
                            )
                        )
                    );
                    $filter = $this->Utility->urlRequestToGetData('users', 'all', $filterParams);
                }
                if (!empty($filter) && empty($filter ['status'])) {
                    $this->offerFilters [$key] = $this->formatOfferFilters($filter);
                }
            }
            return $this->offerFilters;
        } else {
            $this->Session->setFlash('Houve um problema para carregar os filtros. Tente novamente.');
        }
    }

    /**
     * Formata os filtros de maneira mais legível para se trabalhar na view
     *
     * @return array
     */
    private function formatOfferFilters($filter) {
        $rangeKey = array_flip($this->ageGroupRangeKeys);
        $formattedFilter = array();
        $i = 0;
        foreach ($filter as $value) {

            if (array_key_exists('FacebookProfile', $value)) {
                if ($value ['FacebookProfile'] ['filter'] == '') {
                    // retirando registros em branco
                    unset($value);
                } else {
                    $formattedFilter [$i] ['count'] = $value [0] ['count'];
                    $formattedFilter [$i] ['name'] = $value ['FacebookProfile'] ['filter'];
                    $formattedFilter [$i] ['percent'] = $this->filterPercentageCalculation($value [0] ['count']);
                }
            } else if (array_key_exists('User', $value)) {
                if ($value ['User'] ['filter'] == '') {
                    // retirando registros em branco
                    unset($value);
                } else {
                    $formattedFilter [$i] ['count'] = $value [0] ['count'];
                    $formattedFilter [$i] ['name'] = $value ['User'] ['filter'];
                    $formattedFilter [$i] ['percent'] = $this->filterPercentageCalculation($value [0] ['count']);
                }
            } else {
                // faixa etária
                $j = 0;
                foreach ($value [0] as $label => $count) {
                    $range = $rangeKey [$label];
                    $numbers = explode('_', $range);
                    if ($numbers [0] == '0') {
                        $formattedFilter [$j] ['range'] = date('Y') . '_' . (date('Y') - $numbers [1] + 1);
                    } elseif ($numbers [0] == 'empty') {
                        $formattedFilter [$j] ['range'] = $numbers [0];
                    } else {
                        $formattedFilter [$j] ['range'] = (date('Y') - $numbers [0]) . '_' . (date('Y') - $numbers [1]);
                    }

                    $formattedFilter [$j] ['label'] = $label;
                    $formattedFilter [$j] ['count'] = $count;
                    $formattedFilter [$j] ['percent'] = $this->filterPercentageCalculation($count);
                    $j ++;
                }
            }
            $i ++;
        }
        return $formattedFilter;
    }

    private function cadMoipXml(array $cadastro, $type) {
        // trabalhando cadastro integrado do MOIP
        $sua_key = $this->key;
        $seu_token = $this->token;
        $auth = $seu_token . ':' . $sua_key;
        /*
         * $sua_key = 'SKMQ5HKQFTFRIFQBJEOROIGM70I6QVIN9KA5YIWB'; $seu_token = 'WOA4NBQ2AUMHJQ2NJIA6Q6X4ECXHFJUR'; $auth = $seu_token.':'.$sua_key;
         */

        $xml = "<PreCadastramento> 
					<prospect> 
						<idProprio>{$cadastro['data']['Company']['id']}</idProprio> 
						<nome>{$cadastro['data']['Company']['responsible_name']}</nome> 
						<sobrenome></sobrenome> 
						<email>{$cadastro['data']['Company']['responsible_email']}</email> 
						<dataNascimento></dataNascimento> 
						<rg></rg> 
						<cpf>{$cadastro['data']['Company']['responsible_cpf']}</cpf> 
						<cep>{$cadastro['data']['Company']['zip_code']}</cep> 
						<rua>{$cadastro['data']['Company']['address']}</rua> 
						<numero>{$cadastro['data']['Company']['number']}</numero> 
						<complemento></complemento> 
						<bairro>{$cadastro['data']['Company']['district']}</bairro> 
						<cidade>{$cadastro['data']['Company']['city']}</cidade> 
						<estado>{$cadastro['data']['Company']['state']}</estado> 
						<telefoneFixo>{$cadastro['data']['Company']['responsible_phone']}</telefoneFixo> 
						<razaoSocial>{$cadastro['data']['Company']['corporate_name']}</razaoSocial> 
						<nomeFantasia>{$cadastro['data']['Company']['fancy_name']}</nomeFantasia> 
						<cnpj>{$cadastro['data']['Company']['cnpj']}</cnpj> 
						<cepEmpresa>{$cadastro['data']['Company']['zip_code']}</cepEmpresa> 
						<ruaEmpresa>{$cadastro['data']['Company']['address']}</ruaEmpresa> 
						<numeroEmpresa>{$cadastro['data']['Company']['number']}</numeroEmpresa> 
						<complementoEmpresa></complementoEmpresa> 
						<bairroEmpresa>{$cadastro['data']['Company']['district']}</bairroEmpresa> 
						<cidadeEmpresa>{$cadastro['data']['Company']['city']}</cidadeEmpresa> 
						<estadoEmpresa>{$cadastro['data']['Company']['state']}</estadoEmpresa> 
						<telefoneFixoEmpresa>{$cadastro['data']['Company']['phone']}</telefoneFixoEmpresa> 
						<tipoConta>1</tipoConta>
					</prospect> 
			</PreCadastramento>		
		";

        // pr($xml);exit;
        // O HTTP Basic Auth � utilizado para autentica��o
        $header [] = "Authorization: Basic " . base64_encode($auth);

        // URL do SandBox - Nosso ambiente de testes
        // $url = "https://desenvolvedor.moip.com.br/sandbox/ws/alpha/PreCadastramento";
        $url = "https://www.moip.com.br/ws/alpha/PreCadastramento";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        // header que diz que queremos autenticar utilizando o HTTP Basic Auth
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        // informa nossas credenciais
        curl_setopt($curl, CURLOPT_USERPWD, $auth);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
        curl_setopt($curl, CURLOPT_POST, true);

        // Informa nosso XML de instru��o
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // efetua a requisi��o e coloca a resposta do servidor do MoIP em $ret
        $ret = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $xml = simplexml_load_string($ret);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);
        return $array;
    }

    /**
     * Método auxiliar para cálculo de porcentagem dos filtros
     * em relação ao total da base selecionada.
     *
     * @return string
     */
    private function filterPercentageCalculation($value) {
        $total = $this->offerFilters ['target'];
        (double) $percent = ($value / $total) * 100;
        return round($percent, 0);
    }

    // TESTE VIEW PRE CADASTRO COMPANY
    public function precadastro_teste($page = null) {

        if ($page == 'sucesso') {
            //CRIAR 'SUCESSO'
        } else if ($page == 'incluir') {
            $errors = '';
            //$this->set('url', $this->Session->read('URL'));
            // recebe imagem upada
            //$arquivo = $this->request->data ['Company'] ['logo'];
            //$image_result = $this->Utility->x_validaImagem($arquivo);
            // imagem est� OK
            //$upload = $this->Utility->x_uploadFile('companies', $this->request->data ['Company'] ['logo']);
            $upload = $this->Utility->adv_uploadFileComp('companies', $this->request->data ['Company'] ['logo']);

            // dados da empresa
            $corporateName = $this->request->data ['Company'] ['corporate_name'];
            $fancyName = $this->request->data ['Company'] ['fancy_name'];
            $cnpj = $this->request->data ['Company'] ['cnpj'];
            $phone = $this->request->data ['Company'] ['phone'];
            $phone2 = $this->request->data ['Company'] ['phone_2'];
            $compEmail = $this->request->data ['Company'] ['email'];
            $site = $this->request->data ['Company'] ['site'];
            // $logo = $arquivo;
            // categoria
            // responsavel pela conta
            $responsible_name = $this->request->data ['Company'] ['responsible_name'];
            $responsible_cpf = $this->request->data ['Company'] ['responsible_cpf'];
            $responsible_email = $this->request->data ['Company'] ['responsible_email'];
            $responsible_phone = $this->request->data ['Company'] ['responsible_phone'];
            $responsible_phone_2 = $this->request->data ['Company'] ['responsible_phone_2'];
            $responsible_cell_phone = $this->request->data ['Company'] ['responsible_cell_phone'];
            // endereco
            $address = $this->request->data ['Company'] ['address'];
            $complement = $this->request->data ['Company'] ['complement'];
            $city = $this->request->data ['Company'] ['city'];
            $state = $this->request->data ['Company'] ['state'];
            $number = $this->request->data ['Company'] ['number'];
            $zip_code = $this->request->data ['Company'] ['zip_code'];
            $district = $this->request->data ['Company'] ['district'];
            $categoria = $this->request->data ['catego'];
            $subcategoria = $this->request->data ['subCatego'];
            $password = $this->generateRandomPassword();

            // DADOS DA EMPRESA
            $params1 ['Company'] ['corporate_name'] = $corporateName;
            $params1 ['Company'] ['fancy_name'] = $fancyName;
            $params1 ['Company'] ['cnpj'] = $cnpj;
            $params1 ['Company'] ['phone'] = $phone;
            $params1 ['Company'] ['phone_2'] = $phone2;
            $params1 ['Company'] ['site_url'] = $site;
            $params1 ['Company'] ['email'] = $compEmail;
            $params1 ['Company'] ['logo'] = $upload;
            // RESPONSAVEL PELA CONTA
            $params1 ['Company'] ['responsible_name'] = $responsible_name;
            $params1 ['Company'] ['responsible_email'] = $responsible_email;
            $params1 ['Company'] ['responsible_cpf'] = $responsible_cpf;
            $params1 ['Company'] ['responsible_phone'] = $responsible_phone;
            $params1 ['Company'] ['responsible_phone_2'] = $responsible_phone_2;
            $params1 ['Company'] ['responsible_cell_phone'] = $responsible_cell_phone;
            // ENDERECO
            $params1 ['Company'] ['address'] = $address;
            $params1 ['Company'] ['complement'] = $complement;
            $params1 ['Company'] ['city'] = $city;
            $params1 ['Company'] ['state'] = $state;
            $params1 ['Company'] ['district'] = $district;
            $params1 ['Company'] ['number'] = $number;
            $params1 ['Company'] ['zip_code'] = $zip_code;

            // $params1 ['Company'] ['password'] = $password;
            $params1 ['Company'] ['category_id'] = str_replace(",", "", $categoria);
            $params1 ['Company'] ['sub_category_id'] = 5;
            $params1 ['Company'] ['status'] = 'ACTIVE';
            $params1 ['Company'] ['date_register'] = date('Y/m/d');

            // INCLUI
            $cadastro = $this->Utility->urlRequestToSaveData('companies', $params1);

            $arrayCompany = array(
                'Company' => array(
                    'conditions' => array(
                        'Company.email' => $compEmail
                    )
                )
            );

            $comp = $this->Utility->urlRequestToGetData('companies', 'all', $arrayCompany);

            $compId = $comp [0] ['Company'] ['id'];
            // INCLUI CATEGORIA E SUBCATEGORIA NA TABELA AUXILIAR
            $cat = $categoria;
            $sub = $subcategoria;
            $arrayCategorias = explode(",", $cat);
            $arraySubCategorias = explode(",", $sub);

            // CARREGA SUBCATEGORIAS
            $arrayParamsSub = array(
                'CompaniesSubCategory' => array()
            );
            $subCategorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParamsSub);

            // C�DIGO PRA INCLUIR CATEGORIA E SUB CATEGORIA NA TABELA AUXILIAR
            foreach ($arrayCategorias as $arrayCat) {
                for ($i = 0, $size = count($arraySubCategorias); $i < $size; ++$i) {
                    foreach ($subCategorias as $subss) {
                        if ($subss ['CompaniesSubCategory'] ['id'] == $arraySubCategorias [$i]) {
                            if ($subss ['CompaniesSubCategory'] ['category_id'] == $arrayCat) {
                                array_push($sabores, $arrayCat);
                                array_push($sabores, $arraySubCategorias [$i]);

                                $query = "INSERT INTO companies_categories_sub_categories(category_id, sub_category_id, company_id) VALUES(" . $arrayCat . "," . $arraySubCategorias [$i] . "," . $compId . ");";
                                $params = array(
                                    'Company' => array(
                                        'query' => $query
                                    )
                                );
                                $categoriesSubCategories = $this->Utility->urlRequestToGetData('companies', 'query', $params);
                            }
                        }
                    }
                }
            }

            $arrayParams = array(
                "Company" => array(
                    'id' => $compId,
                    'email' => $comp [0] ['Company'] ['email'],
                    'responsible_email' => $comp [0] ['Company'] ['responsible_email']
                )
            );

            $updating = $this->Utility->x_urlRequestToLoginData('companies', 'first', $arrayParams);
            $type = "first";
            $teste = $this->x_cadMoipXml($comp, $type);

            $url = 'https://www.moip.com.br/cadastro/trueone/' . $teste ['RespostaPreCadastramento'] ['idRedirecionamento'];

            // $this->Session->write ( 'CompanyLoggedIn', $comp );
            $_SESSION ['CompanyLoggedIn'] = ($comp);
            $_SESSION ['url_moip'] = ($url);
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutDash',
                'plugin' => 'companies'
            ));
        } else {

            if ($this->request->is('ajax')) {
                if (!empty($this->request->data ['cep'])) {
                    $cURL = curl_init("http://cep.correiocontrol.com.br/{$this->request->data['cep']}.json");
                    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                    $resultado = curl_exec($cURL);
                    curl_close($cURL);
                    echo $resultado;
                    exit();
                }
            }

            // CARREGA CATEGORIA
            $arrayParams = array(
                'CompaniesCategory' => array(),
            );
            $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);

            // CARREGA SUBCATEGORIAS
            $arrayParamsSub = array(
                'CompaniesSubCategory' => array()
            );
            $subCategorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParamsSub);

            // salva registro usando diretamente uma query
            $query = "INSERT INTO companies_categories_sub_categories(category_id, sub_category_id, company_id) VALUES(1,1,117);";
            $params = array(
                'Company' => array(
                    'query' => $query
                )
            );
            // $categoriesSubCategories = $this->Utility->urlRequestToGetData ( 'companies', 'query', $params );
            // fim do salva registro com query

            $mensagem = '';

            $this->set(compact('mensagem'));
            $this->set(compact('subCategorias'));
            $this->set(compact('categorias', 'mensagem'));
            $this->render('x_pre_cadastro', 'x_comp_pre_cad_layout');
        }
    }

    // TESTE LAYOUT DA POP UP LOGIN COMPANY
    public function layoutPopUp() {

        if (!empty($_SESSION ['CompanyLoggedIn'])) {
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutDash',
                'plugin' => 'companies'
            ));
        }


        if ($this->request->is('post')) {
            //recupa dados do formulario
            $email = trim($this->request->data ['Company'] ['email']);
            $pass = md5(trim($this->request->data ['Company'] ['password']));

            $conditions = array(
                'Company' => array(
                    'conditions' => array(
                        'Company.responsible_email' => $email,
                        'Company.password' => $pass,
                        'Company.status' => 'ACTIVE'
                    )
                ),
                'CompanyPreference' => array()
            );
            // buscando company na API
            $company = $this->Utility->urlRequestToGetData('companies', 'first', $conditions);

            //verifica se é um usuario secundário
            if ((!empty($company ['status']) && $company ['status'] === 'GET_ERROR') || empty($company)) {

                $usuario = $this->secondaryUserLogin($email, $pass);
                if ($usuario['login_status'] === 'LOGIN_OK') {
                    $this->Session->write('sessionLogado', true);
                    $this->Session->write('CompanyLoggedIn', $usuario['company']);
                    $this->Session->write('userLoggedType', $usuario[0]['secondary_users']['secondary_type_id']);
                    $this->redirect(array(
                        'controller' => 'companies',
                        'action' => 'layoutDash',
                        'plugin' => 'companies'
                    ));
                }
            }

            if ((!empty($company ['status']) && $company ['status'] === 'GET_ERROR') || empty($company)) {

                $resultLogin = $this->secondaryUserLogin($email, md5($this->request->data['Company']['password']));

                if ($resultLogin['login_status'] != 'LOGIN_ERRO') {

                    $this->Session->write('sessionLogado', true);
                    $this->Session->write('secondUserLogado', true);
                    $this->Session->write('CompanyLoggedIn', $resultLogin['company']);
                    $this->Session->write('SecondaryUserLoggedIn', $resultLogin);

                    //redirecionando
                    $this->redirect(array(
                        'controller' => 'companies',
                        'action' => 'layoutDash',
                        'plugin' => 'companies'
                    ));
                }

                $this->set(compact('resultLogin'));
                $this->layout = 'liso';
                $this->render('popup_login');
            } else {
                // cria sessao com dados da empresa
                $this->Session->write('sessionLogado', true);
                $this->Session->write('CompanyLoggedIn', $company);
                $this->Session->write('userLoggedType', 1);
                $this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'layoutDash',
                    'plugin' => 'companies'
                ));
            }
        } else {
            $this->layout = 'liso';
            $this->render('popup_login');
        }
    }

    // TESTE LAYOUT DA DASHBOARD COMPANY
    public function layoutDash($page) {

        $pageName = 'dashboard';
        if (empty($_SESSION ['CompanyLoggedIn'])) {
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutPopUp',
                'plugin' => 'companies'
            ));
        }

        //capturando aniversáriantes
        $minhaData = date('m-d');
        $bithSql = "select * from users where birthday LIKE '%$minhaData';";
        $birthParams = array('User' => array('query' => $bithSql));
        $birthdays = $this->Utility->urlRequestToGetData('users', 'query', $birthParams);

        //envia email de aniversario para usuario selecionado
        if ($page == 'sendBirthdayEmail') {
            $userEmail = $this->request->data['userEmail'];

            $param['userEmail'] = $userEmail;
            $return = $this->Utility->postEmail('users', 'birthday', $param);
        }

        /*
         * verifica se é a requisição feita via ajax
         * para a mudança de status da compra
         * 
         */
        if ($this->request->is('ajax')) {
            $checkId = $this->request->data['checkId'];

            //mudança de status para 4 - valida o envio do produto para usuario
            //usuario receberá o status 'CONCLUIDO' em sua lista
            $query = "UPDATE checkouts set payment_state_id = 4 WHERE id = {$checkId};";

            $params = array('User' => array('query' => $query));
            $checkout = $this->Utility->urlRequestToGetData('users', 'query', $params);
        }

        $this->Session->write('addOffer.Offer', '');
        $params = array(
            'Offer' => array(
                'id' => 178,
                'status' => 'INACTIVE'
            )
        );

        $testeUpdate = $this->Utility->urlRequestToSaveData('offers', $params);

        $company = $_SESSION ['CompanyLoggedIn'];
        // $id = $this->Session->read ( 'CompanyLoggedIn.0.Company.id' );
        // compras finalizadas
        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    //'Checkout.company_id' => 116,
                    'Checkout.company_id' => $company ['Company'] ['id'],
                    'Checkout.total_value > ' => '0',
                    'Checkout.payment_state_id' => 4
                ),
                'order' => array(
                    'Checkout.id' => 'DESC'
                )
            ),
            'PaymentState',
            'Offer',
            'User',
            'OffersUser'
        );
        $checkouts = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        // compras pendentes
        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    //'Checkout.company_id' => 116,
                    'Checkout.company_id' => $company['Company'] ['id'],
                    'Checkout.total_value > ' => '0',
                    'Checkout.payment_state_id <> ' => 4,
                    'NOT' => array(
                        'Checkout.payment_state_id' => array(
                            '999'
                        )
                    )
                ),
                'order' => array(
                    'Checkout.id' => 'DESC'
                )
            ),
            'PaymentState',
            'Offer',
            'User',
            'OffersUser'
        );
        $pedidos = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        // ultimas assinaturas
        $params = array(
            'CompaniesUser' => array(
                'conditions' => array(
                    //'CompaniesUser.company_id' =>116,
                    'CompaniesUser.company_id' => $company['Company'] ['id'],
                    'CompaniesUser.status' => 'ACTIVE',
                    'User.status' => 'ACTIVE'
                ),
                'order' => array(
                    'CompaniesUser.id' => 'DESC'
                )
            ),
            'User'
        );
        $assinaturasFor = $this->Utility->urlRequestToGetData('companies', 'all', $params);
        if (is_array($assinaturasFor)) {
            foreach ($assinaturasFor as $assinatura) {
                $assinatura ['User'] ['idade'] = $this->Utility->calcIdade(date('d/m/Y', strtotime($assinatura ['User'] ['birthday'])));
                $assinaturas [] = $assinatura;
            }
        } else {
            $assinaturas = '';
        }

        // ultimas ofertas cadastradas
        $params = array(
            'Offer' => array(
                'conditions' => array(
                    'Offer.company_id' => $company['Company'] ['id'],
                    'status <>' => 'DELETE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                )
            )
        );
        $ofertas = $this->Utility->urlRequestToGetData('offers', 'all', $params);


        // lista das entregas que precisam ser feitas hoje, amanha, etc
        $paramsDelivery = array(
            'Checkout' => array(
                'conditions' => array(
                    'Checkout.company_id' => $company['Company'] ['id'],
                    'Checkout.payment_state_id' => 1
                )
            ),
            'User',
            'Offer'
        );
        $deliveriesToDo = $this->Utility->urlRequestToGetData('payments', 'all', $paramsDelivery);

        // entregas para hoje e/ou amanha
        $deliveriesToday = array();
        foreach ($deliveriesToDo as $delivery) {
            $dataDaEntrega = date('d/m/y', strtotime("+1 days", strtotime($delivery['Checkout']['date'])));
            $dataHoje = date('d/m/y');
            $dataAmanha = date('d/m/y', strtotime("+1"));

            // hoje
            if ($dataDaEntrega == $dataHoje) {
                $deliveriesToday['Today'] = $delivery;
            } else
            //amanha
            if ($dataDaEntrega == $dataAmanha) {
                $deliveriesToday['Tomorrow'] = $delivery;
            }
        }

        //Capturando dados da preferencia da empresa - caso exista
        $sqlAparencia = "select * from companies_preferences where companies_id = {$this->Session->read('CompanyLoggedIn.Company.id')};";
        $paramsSel = array(
            'User' => array(
                'query' => $sqlAparencia
            )
        );
        $aparencia = $this->Utility->urlRequestToGetData('users', 'query', $paramsSel);
        $this->Session->write("compPreferences", $aparencia[0]['companies_preferences']);

        //Capturando dados de social network da empresa - caso exista
        $sqlSocial = "select * from companies_social_networks where company_id = {$this->Session->read('CompanyLoggedIn.Company.id')};";
        $paramsSocial = array(
            'User' => array(
                'query' => $sqlSocial
            )
        );
        $social = $this->Utility->urlRequestToGetData('users', 'query', $paramsSocial);
        $this->Session->write("compSocial", $social[0]);

        /*
         * Valida lista,
         * se não estiver vazia salva váriavel e mostra popup
         */
        if (!empty($deliveriesToday)) {
            $this->Session->write('Deliveries.today', true);
        } else {
            $this->Session->write('Deliveries.today', false);
        }

        $url_moip = $_SESSION ['url_moip'];

        $this->set(compact('usuarios'));
        $this->set(compact('company', 'pageName'));
        $this->set(compact('url_moip', 'birthdays'));
        $this->set(compact('checkouts', 'assinaturas', 'ofertas', 'pedidos', 'deliveriesToDo', 'deliveriesToday'));
        $this->layout = 'x_dash';
        $this->render('x_dash_home');
    }

    // LAYOUT DA CADASTRO DE OFERTA COMPANY
    public function layoutCadOferta($page = null) {

        $pageName = "offers";
        if (empty($_SESSION ['CompanyLoggedIn'])) {
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutPopUp',
                'plugin' => 'companies'
            ));
        }

        //fotos extras 
        $paramsAssinantes = array(
            'CompaniesUser' => array(
                'conditions' => array(
                    'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'CompaniesUser.status' => 'ACTIVE'
                )
            )
        );
        $compUsers = $this->Utility->urlRequestToGetData('companies', 'all', $paramsAssinantes);


        // valida se empresa já possui um usuário no moip 
        //valida comp moip
        /*
          if ($this->Session->read('CompanyLoggedIn.Company.login_moip') == '') {

          $type = "first";
          $comp = $_SESSION ['CompanyLoggedIn'];
          $teste = $this->x_cadMoipXml($comp, $type);
          $url = 'https://www.moip.com.br/cadastro/trueone/' . $teste ['RespostaPreCadastramento'] ['idRedirecionamento'];

          $_SESSION ['url_moip'] = ($url);
          $this->redirect(array(
          'controller' => 'companies',
          'action' => 'layoutDash',
          'plugin' => 'companies'
          ));
          } */

        $this->layout = 'x_dash';

        $query = "select * from offers_attributes order by name;";
        $params3 = array(
            'User' => array(
                'query' => $query
            )
        );
        $atributos = $this->Utility->urlRequestToGetData('users', 'query', $params3);

        $query = "select * from offers_domains;";
        $params3 = array(
            'User' => array(
                'query' => $query
            )
        );
        $dominios = $this->Utility->urlRequestToGetData('users', 'query', $params3);

        if ($page == 'requisicao') {
            if ($this->request->is('ajax')) {
                if (!empty($this->request->data['coluna']) and ! empty($this->request->data['linha'])) {
                    $render = true;
                    $this->layout = '';
                    $coluna = $this->request->data['coluna'];
                    $linha = $this->request->data['linha'];

                    $query = "select * from offers_domains where offer_attribute_id = $coluna;";
                    $paramsColuna = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $colunas = $this->Utility->urlRequestToGetData('users', 'query', $paramsColuna);

                    $query = "select * from offers_domains where offer_attribute_id = $linha;";
                    $paramsLinha = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $linhas = $this->Utility->urlRequestToGetData('users', 'query', $paramsLinha);
                }
                //$this->set(compact('sub_categorias'));
                $eixoLinha = $this->request->data['linha'];
                $categoria = $this->request->data['coluna'];
                $this->set(compact('colunas'));
                $this->set(compact('linhas'));
                $this->set(compact('categoria', 'eixoLinha'));

                if (!empty($render))
                    $this->render('Elements/ajax_teste');
            }
        }else {

            if ($page == 'incluir') {

                //salvando a recorrencia na sessao, pois salvaremos somente no proximo form
                $recurrence = $this->request->data['Offer']['recorrencia'];
                $this->Session->write('Offer.recurrence', $recurrence);

                $formType = $this->request->data['Offer']['editOrSelec'];

                if ($formType == 'insert') {

                    $offersExtraPhotos = null;
                    $contad = 0;
                    //verificando e upando imagens extras da imagem
                    if (!empty($this->request->data['Offer']['photos_extra_zero'])) {
                        //$extra0 = $this->Utility->x_uploadFile('offers', $this->request->data['Offer']['photos_extra_zero']);
                        $extra0 = $this->Utility->adv_uploadFile('offers', $this->request->data['Offer']['photos_extra_zero']);
                        $offersExtraPhotos[$contad] = $extra0;
                        $contad++;
                    }
                    if (!empty($this->request->data['Offer']['photos_extra_one'])) {
                        //$extra1 = $this->Utility->x_uploadFile('offers', $this->request->data['Offer']['photos_extra_one']);
                        $extra1 = $this->Utility->adv_uploadFile('offers', $this->request->data['Offer']['photos_extra_one']);
                        $offersExtraPhotos[$contad] = $extra1;
                        $contad++;
                    }
                    if (!empty($this->request->data['Offer']['photos_extra_two'])) {
                        //$extra2 = $this->Utility->x_uploadFile('offers', $this->request->data['Offer']['photos_extra_two']);
                        $extra2 = $this->Utility->adv_uploadFile('offers', $this->request->data['Offer']['photos_extra_two']);
                        $offersExtraPhotos[$contad] = $extra2;
                        $contad++;
                    }
                    if (!empty($this->request->data['Offer']['photos_extra_three'])) {
                        //$extra3 = $this->Utility->x_uploadFile('offers', $this->request->data['Offer']['photos_extra_three']);
                        $extra3 = $this->Utility->adv_uploadFile('offers', $this->request->data['Offer']['photos_extra_three']);
                        $offersExtraPhotos[$contad] = $extra3;
                        $contad++;
                    }
                    if (!empty($this->request->data['Offer']['photos_extra_four'])) {
                        //$extra4 = $this->Utility->x_uploadFile('offers', $this->request->data['Offer']['photos_extra_four']);
                        $extra4 = $this->Utility->adv_uploadFile('offers', $this->request->data['Offer']['photos_extra_four']);
                        $offersExtraPhotos[$contad] = $extra4;
                        $contad++;
                    }

                    //salvando fotos na sessao
                    $this->Session->write('offerExtrasPhotos', $offersExtraPhotos);

                    //$upload = $this->Utility->x_uploadFile('offers', $this->request->data ['Offer'] ['photo']);
                    $upload = $this->Utility->adv_uploadFile('offers', $this->request->data ['Offer'] ['photo']);

                    $param['Offer']['company_id'] = $company = $_SESSION ['CompanyLoggedIn'];

                    $param['Offer']['title'] = $this->request->data['Offer']['title'];
                    $param['Offer']['description'] = $this->request->data['Offer']['description'];
                    $param['Offer']['specification'] = $this->request->data['Offer']['specification'];
                    $param['Offer']['resume'] = $this->request->data['Offer']['resume'];
                    $param['Offer']['public'] = $this->request->data['Offer']['public'];
                    $param['Offer']['value'] = $this->request->data['Offer']['value'];
                    $param['Offer']['percentage_discount'] = substr($this->request->data['Offer']['percentage_discount'], 0, 2);
                    $param['Offer']['weight'] = $this->request->data['Offer']['weight'];
                    $param['Offer']['begins_at'] = $this->formataData2($this->request->data['Offer']['begins_at']);
                    $param['Offer']['ends_at'] = $this->formataData2($this->request->data['Offer']['ends_at']);
                    $param['Offer']['parcels'] = $this->request->data['Offer']['parcels'];
                    $param['Offer']['parcels_off_impost'] = $this->request->data['Offer']['parcels_off_impost'];
                    $param['Offer']['discounted_value'] = $this->request->data['Offer']['discounted_value'];
                    $param['Offer']['photo'] = $upload;
                    $param['Offer']['editOrSelec'] = $this->request->data['Offer']['editOrSelec'];

                    $query = "select * from offers_domains;";
                    $paramsColuna = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $dominios = $this->Utility->urlRequestToGetData('users', 'query', $paramsColuna);
                    $totalDominios = count($dominios);
                    //Logica que percore toda a tabela dinamica 'Y & X', pegando index e valores 
                    //Faz a amarração de Linha por coluna guardaDominio[Id de X(linha)][Id de Y(coluna)] = conteudo;
                    //O Preço é buscado e salvo da mesma maneira, 
                    //para acessa-lo basta adicionar a chave 'preco' (guardaDominio['preco'][Id de X][Id de Y])
                    $eixoY = $this->request->data['selectboxY'];
                    for ($f = 0; $f < $totalDominios; $f++) {

                        for ($i = 0; $i < $totalDominios; $i++) {
                            if ($dominios[$i]['offers_domains']['offer_attribute_id'] == $eixoY and ! empty($this->request->data['cont-' . $dominios[$f]['offers_domains']['id'] . '-' . $dominios[$i]['offers_domains']['id']])) {

                                $guardaDominio[$dominios[$f]['offers_domains']['id']][$dominios[$i]['offers_domains']['id']] = $this->request->data['cont-' . $dominios[$f]['offers_domains']['id'] . '-' . $dominios[$i]['offers_domains']['id']];
                                $preco = $str = str_replace('.', '', $this->request->data['preco-column-' . $dominios[$i]['offers_domains']['id']]);
                                $guardaDominio['preco'][$dominios[$f]['offers_domains']['id']][$dominios[$i]['offers_domains']['id']] = str_replace(',', '.', $preco);
                            }
                        }
                    }

                    $param['Offer']['domains'] = $guardaDominio;
                    $param['Offer']['qtd_domains'] = count($dominios);
                    //Busca público caso oferta seja direcionada 
                    if ($param['Offer']['public'] == 'INACTIVE') {

                        $filters = array(
                            'OffersFilter' => array(
                                'gender' => $this->request->data['Offer']['filters']['gender'],
                                'location' => $this->request->data['Offer']['filters']['location'],
                                'age_group' => $this->request->data['Offer']['filters']['age_group'],
                                'political' => $this->request->data['Offer']['filters']['political'],
                                'religion' => $this->request->data['Offer']['filters']['religion'],
                                'relationship_status' => $this->request->data['Offer']['filters']['relationship_status']
                            )
                        );
                        $param['Offer']['public_filters'] = $filters;
                    }

                    $param['Offer']['photos_extras'][0] = $this->request->data['Offer']['photos_extra_zero'];
                    $param['Offer']['photos_extras'][1] = $this->request->data['Offer']['photos_extra_one'];
                    $param['Offer']['photos_extras'][2] = $this->request->data['Offer']['photos_extra_two'];
                    $param['Offer']['photos_extras'][3] = $this->request->data['Offer']['photos_extra_three'];
                    $param['Offer']['photos_extras'][4] = $this->request->data['Offer']['photos_extra_four'];

                    $param['Offer']['extra_infos'] = $this->request->data['Offer']['extra_infos'];
                    $this->Session->write('viewOffer', $param);
                    $this->Session->write('OfferDomains', $dominios);

                    if ($this->request->data['Offer']['editOrSelec'] == 'insert') {

                        $this->redirect(array(
                            'controller' => 'companies',
                            'action' => 'visulizaOferta',
                            'plugin' => 'companies',
                        ));
                    } else if ($this->request->data['Offer']['editOrSelect'] == 'edit') {
                        $this->redirect(array(
                            'controller' => 'companies',
                            'action' => 'visulizaOferta',
                            'plugin' => 'companies',
                            'edit'
                        ));
                    }
                } else if ($formType == 'edit') {

                    $offerUp = $this->Session->read('addOffer.Offer');
                    $offerId = $_SESSION['upOfferId'];


                    if (!empty($this->request->data['Offer'])) {
                        //$upload = $this->Utility->x_uploadFile('offers', $this->request->data ['Offer'] ['photo']);
                        $upload = $this->Utility->adv_uploadFile('offers', $this->request->data ['Offer'] ['photo']);

                        $param['Offer']['photo'] = $upload;
                    } else {
                        $param['Offer']['photo'] = $offerUp['photo'];
                    }

                    //capturando valowq
                    $param['Offer']['company_id'] = $company = $_SESSION ['CompanyLoggedIn'];
                    $param['Offer']['id'] = $offerId;
                    $param['Offer']['title'] = $this->request->data['Offer']['title'];
                    $param['Offer']['description'] = $this->request->data['Offer']['description'];
                    $param['Offer']['specification'] = $this->request->data['Offer']['specification'];
                    $param['Offer']['resume'] = $this->request->data['Offer']['resume'];
                    $param['Offer']['public'] = $this->request->data['Offer']['public'];
                    $param['Offer']['value'] = $this->request->data['Offer']['value'];
                    $param['Offer']['percentage_discount'] = substr($this->request->data['Offer']['percentage_discount'], 0, 2);
                    $param['Offer']['weight'] = $this->request->data['Offer']['weight'];
                    $param['Offer']['begins_at'] = $this->request->data['Offer']['begins_at'];
                    $param['Offer']['ends_at'] = $this->request->data['Offer']['ends_at'];
                    if (!empty($this->request->data['Offer']['parcels'])) {
                        $param['Offer']['parcels'] = $this->request->data['Offer']['parcels'];
                    } else {
                        $param['Offer']['parcels'] = $offerUp['parcels'];
                    }

                    if (!empty($this->request->data['Offer']['parcels_off_impost'])) {
                        $param['Offer']['parcels_off_impost'] = $this->request->data['Offer']['parcels_off_impost'];
                    } else {
                        $param['Offer']['parcels_off_impost'] = $offerUp['parcels_off_impost'];
                    }

                    $param['Offer']['discounted_value'] = $this->request->data['Offer']['discounted_value'];


                    $query = "select * from offers_domains;";
                    $paramsColuna = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $dominios = $this->Utility->urlRequestToGetData('users', 'query', $paramsColuna);
                    $totalDominios = count($dominios);
                    //Logica que percore toda a tabela dinamica 'Y & X', pegando index e valores 
                    //Faz a amarração de Linha por coluna guardaDominio[Id de X(linha)][Id de Y(coluna)] = conteudo;
                    //O Preço é buscado e salvo da mesma maneira, 
                    //para acessa-lo basta adicionar a chave 'preco' (guardaDominio['preco'][Id de X][Id de Y])
                    $eixoY = $this->request->data['selectboxY'];
                    for ($f = 0; $f < $totalDominios; $f++) {

                        for ($i = 0; $i < $totalDominios; $i++) {
                            if ($dominios[$i]['offers_domains']['offer_attribute_id'] == $eixoY and ! empty($this->request->data['cont-' . $dominios[$f]['offers_domains']['id'] . '-' . $dominios[$i]['offers_domains']['id']])) {

                                $guardaDominio[$dominios[$f]['offers_domains']['id']][$dominios[$i]['offers_domains']['id']] = $this->request->data['cont-' . $dominios[$f]['offers_domains']['id'] . '-' . $dominios[$i]['offers_domains']['id']];
                                $preco = $str = str_replace('.', '', $this->request->data['preco-column-' . $dominios[$i]['offers_domains']['id']]);
                                $guardaDominio['preco'][$dominios[$f]['offers_domains']['id']][$dominios[$i]['offers_domains']['id']] = str_replace(',', '.', $preco);
                            }
                        }
                    }

                    $param['Offer']['domains'] = $guardaDominio;
                    $param['Offer']['qtd_domains'] = count($dominios);
                    $param['Offer']['editOrSelec'] = $this->request->data['Offer']['editOrSelec'];
                    //Busca público caso oferta seja direcionada 
                    if ($param['Offer']['public'] == 'INACTIVE') {

                        $filters = array(
                            'OffersFilter' => array(
                                'gender' => $this->request->data['Offer']['filters']['gender'],
                                'location' => $this->request->data['Offer']['filters']['location'],
                                'age_group' => $this->request->data['Offer']['filters']['age_group'],
                                'political' => $this->request->data['Offer']['filters']['political'],
                                'religion' => $this->request->data['Offer']['filters']['religion'],
                                'relationship_status' => $this->request->data['Offer']['filters']['relationship_status']
                            )
                        );
                        $param['Offer']['public_filters'] = $filters;
                    }

                    //pegando todas as fotos 

                    $this->Session->write('viewOffer', $param);
                    $this->Session->write('OfferDomains', $dominios);
                    $this->Session->write('offerUp', $offerUp);
                    $this->Session->write('addOffer.Offer', '');
                    $this->redirect(array(
                        'controller' => 'companies',
                        'action' => 'visulizaOferta',
                        'plugin' => 'companies',
                    ));
                }
                //traz a oferta a ser atualizada para a tela de cadastro
            } else if ($page == 'edita') {

                $offerId = $this->request->data['offer-edit'];

                $paramToUp = array(
                    'Offer' => array(
                        'conditions' => array(
                            'Offer.id' => $offerId
                        )
                    ),
                    'OffersPhoto'
                );

                $offerToUp = $this->Utility->urlRequestToGetData('offers', 'all', $paramToUp);
                $this->Session->write('addOffer.Offer', $offerToUp[0]['Offer']);
                $arrayParams = array(
                    'CompaniesCategory' => array()
                );
                $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);

                //extra infos offers
                $sqlExtraInfo = "select * from offers_extra_infos inner join companies_categories on companies_categories.id = offers_extra_infos.category_id where offer_id = {$offerId};";
                $paramsExtra = array(
                    'User' => array(
                        'query' => $sqlExtraInfo
                    )
                );
                $extraInfoss = $this->Utility->urlRequestToGetData('users', 'query', $paramsExtra);

                //fotos extras 
                $params = array(
                    'OffersPhoto' => array(
                        'conditions' => array(
                            'OffersPhoto.offer_id' => $offerId
                        )
                    )
                );
                $offerPhotos = $this->Utility->urlRequestToGetData('offers', 'all', $params);

                //buscando dados para préformar a tabela dinamica
                $sqlDTColuna = "select * from offers_metrics inner join offers_domains on offers_metrics.offer_metrics_y_id = offers_domains.id inner join offers_attributes on offers_attributes.id = offers_domains.offer_attribute_id where offers_metrics.offer_id = {$offerId};";
                $paramsDTColuna = array(
                    'User' => array(
                        'query' => $sqlDTColuna
                    )
                );
                $editDTColunas = $this->Utility->urlRequestToGetData('users', 'query', $paramsDTColuna);

                //buscando dados para préformar a tabela dinamica
                $sqlDTLinhas = "select * from offers_metrics inner join offers_domains on offers_metrics.offer_metrics_y_id = offers_domains.id inner join offers_attributes on offers_attributes.id = offers_domains.offer_attribute_id where offers_metrics.offer_id = {$offerId};";
                $paramsDTLinha = array(
                    'User' => array(
                        'query' => $sqlDTLinhas
                    )
                );
                $editDTLinhas = $this->Utility->urlRequestToGetData('users', 'query', $paramsDTLinha);

                $_SESSION['editDTLinhas'] = $editDTLinhas;
                $_SESSION['editDTColunas'] = $editDTColunas;
                $_SESSION['extraInfosss'] = $extraInfoss[0];
                $_SESSION['offersPhoto'] = $offerPhotos;
                $_SESSION['upOfferId'] = $offerId;
                $offerFiltersData = $this->loadOfferFilters();
                $editOrInsert = 'edit';
                $this->set(compact('editOrInsert', 'extraInfoss'));
                $this->set(compact('offerToUp'));
                $this->set(compact('offerFiltersData'));
                $this->set(compact('atributos', 'dominios'));
                $this->set(compact('categorias'));
                $this->set(compact('pagina'));

                //$this->render('x_cadastro_ofertas');

                $this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'layoutCadOferta',
                    'plugin' => 'companies',
                ));
            } else if ($page == 'publico') {
                $arrayParams = array(
                    'CompaniesCategory' => array()
                );
                $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);


                $offerFiltersData = $this->loadOfferFilters();
                $pagina = 'publico';
                $editOrInsert = 'insert';
                $this->set(compact('editOrInsert'));
                $this->set(compact('offerFiltersData'));
                $this->set(compact('atributos', 'dominios'));
                $this->set(compact('categorias'));
                $this->set(compact('pagina', 'compUsers'));
                $this->render('x_cadastro_ofertas');
            } else {
                $arrayParams = array(
                    'CompaniesCategory' => array()
                );
                $categorias = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);

                $offerFiltersData = $this->loadOfferFilters();
                $pagina = 'incluir';
                $editOrInsert = 'insert';
                $this->set(compact('editOrInsert'));
                $this->set(compact('offerFiltersData'));
                $this->set(compact('atributos', 'dominios'));
                $this->set(compact('categorias'));
                $this->set(compact('pagina', 'pageName', 'compUsers'));
                $this->render('x_cadastro_ofertas');
            }
        }
    }

    public function visulizaOferta($page = null) {
        $company_id = $this->Session->read('CompanyLoggedIn.Company.id');
        if ($page == 'incluir') {


            $offer = $_SESSION['viewOffer'];
            $domains = $_SESSION['OfferDomains'];

            $teste = $offer;

            $this->Session->write('shareOfferId', $teste['Offer']['id']);

            if ($offer['Offer']['editOrSelec'] == 'insert') {

                //PASSO 1 - INCLUI INFOS BÁSICAS DA OFERTA
                $param['Offer']['company_id'] = $company_id;
                $param['Offer']['title'] = $teste['Offer']['title'];
                $param['Offer']['resume'] = $teste['Offer']['resume'];
                $param['Offer']['public'] = $teste['Offer']['public'];
                $param['Offer']['value'] = $teste['Offer']['value'];
                $param['Offer']['percentage_discount'] = $teste['Offer']['percentage_discount'];
                $param['Offer']['weight'] = $teste['Offer']['weight'];
                $param['Offer']['begins_at'] = $teste['Offer']['begins_at'];
                $param['Offer']['ends_at'] = $teste['Offer']['ends_at'];
                if ($teste['Offer']['parcels'] == 'on') {
                    $param['Offer']['parcels'] = 'ACTIVE';
                } else {
                    $param['Offer']['parcels'] = 'INACTIVE';
                }
                $param['Offer']['photo'] = $teste['Offer']['photo'];
                $param['Offer']['parcels_off_impost'] = $teste['Offer']['parcels_off_impost'];
                $desc = $teste['Offer']['description'];
                $param['Offer']['description'] = $desc;
                $param['Offer']['specification'] = $teste['Offer']['specification'];
                $orientation = $this->request->data['Offer']['orientation'];
                $addOfferFilters = $this->Utility->urlRequestToSaveData('offers', $param);
                $ocorr = $this->Session->read('Offer.recurrence');

                //inserindo infos adicionais da oferta
                $query = "insert into offers_extra_infos(offer_type, delivery_mode, delivery_deadline, delivery_value, category_id, offer_id, offer_orientation)"
                        . " values('" . $offer['Offer']['extra_infos']['offer_type'] . "','" . $offer['Offer']['extra_infos']['delivery_mode'] . "', " .
                        $offer['Offer']['extra_infos']['delivery_dealine'] . "," . $offer['Offer']['extra_infos']['delivery_value'] . "," . $offer['Offer']['extra_infos']['category_id'] .
                        "," . $addOfferFilters['data']['Offer']['id'] . ",'" . $orientation . "');";

                $paramExtras = array(
                    'User' => array(
                        'query' => $query
                    )
                );

                $infosExtras = $this->Utility->urlRequestToGetData('users', 'query', $paramExtras);

                //SALVANDO A RECORRENCIA
                $query = "UPDATE offers_extra_infos SET recurrence = " . $ocorr . " WHERE offer_id = " . $addOfferFilters['data']['Offer']['id'] . ";";

                $paramsCurr = array(
                    'User' => array(
                        'query' => $query
                    )
                );
                $infosExtras = $this->Utility->urlRequestToGetData('users', 'query', $paramsCurr);


                //PASSO 2 - INCLUIR FILTROS DA OFERTA
                //so incluir filtro caso oferta seja publica
                if ($teste['Offer']['public'] == 'INACTIVE') {
                    $filtros = $teste['Offer']['public_filters'];
                    $filtros['OffersFilter']['offer_id'] = $addOfferFilters['data']['Offer']['id'];
                    $addFilters = $this->Utility->urlRequestToSaveData('offers', $filtros);
                }
                //PASSO 3 - INCLUIR METRICAS DA OFERTA (Ex: tamanho X cor)
                $dominios = $teste['Offer']['domains'];

                $total = count($domains) - 1;
                $qtdTotal = $domains[$total]['offers_domains']['id'];

                //Varre registros do array retornado pela página
                for ($i = 0; $i <= $qtdTotal; $i++) {

                    for ($f = 0; $f <= $qtdTotal; $f++) {
                        if (!empty($dominios[$i][$f]) and ! $dominios[$i][$f] == '') {

                            $paramsQ = array(
                                'User' => array(
                                    'query' => "INSERT INTO offers_metrics(offer_id, offer_metrics_x_id, offer_metrics_y_id, amount, value) VALUES(" . $addOfferFilters['data']['Offer']['id'] . "," . $i . "," . $f . "," . $dominios[$i][$f] . "," . $dominios['preco'][$i][$f] . ");"
                                )
                            );

                            $abc = $this->Utility->urlRequestToGetData('users', 'query', $paramsQ);
                        }
                    }
                }

//                //insere imagens extra da oferta 
//                 foreach ($teste['Offer']['photos_extras'] as $extraPhoto) {
//                    if (!empty($extraPhoto)) {
//                        $upload = $this->Utility->x_uploadFile('offers', $extraPhoto);
//                        
//                        $params = array(
//                            'OffersPhoto' => array(
//                                'offer_id' => $addOfferFilters['data']['Offer']['id'],
//                                'photo' => $upload
//                            )
//                        );
//                        $addPhoto = $this->Utility->urlRequestToSaveData('offers', $params);
//                    }
//                }
                //grava oferta caso seja direcionada a usuario
                if (!empty($_SESSION['direcOfferCompWishId']) and ! empty($_SESSION['direcOfferUserId'])) {

                    //atualiza status do desejo
                    $params = array(
                        'UsersWishlistCompany' => array(
                            'id' => $_SESSION['direcOfferCompWishId'],
                            'status' => 'ACTIVE',
                            'offer_id' => $addOfferFilters['data']['Offer']['id']
                        )
                    );
                    $desejosOfferUpdate = $this->Utility->urlRequestToSaveData('companies', $params);

                    //cria vinculo entre oferta e usuario
                    $data = date('Y/m/d');
                    $query = "INSERT INTO offers_users values(NULL, '{$desejosOfferUpdate['data']['UsersWishlistCompany']['offer_id']}', '{$desejosOfferUpdate['data']['User']['id']}', '{$data}', 'facebook - portal')";
                    $params = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                }

                if (!empty($_SESSION['direcOfferUserId'])) {
                    $data = date('Y/m/d');
                    $userid = $_SESSION['direcOfferUserId'];
                    $query = "INSERT INTO offers_users values(NULL, '{$addOfferFilters['data']['Offer']['id']}', '{$userid}', '{$data}', 'facebook - portal')";
                    $params = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                }

                //Cria registro na tabela de Estatísticas da oferta
                //offers_statistics
                $queryStatistics = "insert into offers_statistics(offer_id, details_click, checkouts_click, purchased_billet, purchased_card)
                                      values(" . $addOfferFilters['data']['Offer']['id'] . ", 0, 0, 0, 0);";

                $paramStatistics = array(
                    'User' => array(
                        'query' => $queryStatistics
                    )
                );
                $statistics = $this->Utility->urlRequestToGetData('users', 'query', $paramStatistics);

                $this->Session->write('direcOfferCompWishId', '');
                $this->Session->write('direcOfferUserId', '');

                //ADICIONANDO IMAGENS EXTRAS DA OFERTA 
                $extraPhotos = $this->Session->read('offerExtrasPhotos');
                if (!empty($extraPhotos)) {
                    foreach ($extraPhotos as $photo) {
                        $sql = "insert into offers_photos(offer_id, photo) values({$addOfferFilters['data']['Offer']['id']}, '{$photo}');";

                        $paramsExtras = array('User' => array('query' => $sql));
                        $insertExtra = $this->Utility->urlRequestToGetData('users', 'query', $paramsExtras);
                    }
                }
                $this->Session->write('offerExtrasPhotos', null);

                $this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'layoutDash',
                    'plugin' => 'companies',
                ));
            } else if ($teste['Offer']['editOrSelec'] == 'edit') {

                $offerOrig = $_SESSION['offerUp'];
                $offer = $_SESSION['viewOffer'];
                $domains = $_SESSION['OfferDomains'];

                $teste = $offer;

                //PASSO 1 - INCLUI INFOS BÁSICAS DA OFERTA
                $param['Offer']['company_id'] = $company_id;
                $param['Offer']['id'] = $offerOrig['id'];
                $param['Offer']['title'] = $teste['Offer']['title'];
                $param['Offer']['resume'] = $teste['Offer']['resume'];
                $param['Offer']['public'] = $teste['Offer']['public'];
                $param['Offer']['value'] = $teste['Offer']['value'];
                $param['Offer']['percentage_discount'] = $teste['Offer']['percentage_discount'];
                $param['Offer']['weight'] = $teste['Offer']['weight'];
                $param['Offer']['begins_at'] = $teste['Offer']['begins_at'];
                $param['Offer']['ends_at'] = $teste['Offer']['ends_at'];
                if ($teste['Offer']['parcels'] == 'ACTIVE' or $teste['Offer']['parcels'] == 'INACTIVE') {
                    $param['Offer']['parcels'] = $teste['Offer']['parcels'];
                } else {
                    if ($teste['Offer']['parcels'] == 'on') {
                        $param['Offer']['parcels'] = 'ACTIVE';
                    } else {
                        $param['Offer']['parcels'] = 'INACTIVE';
                    }
                }
                $param['Offer']['photo'] = $teste['Offer']['photo'];
                $param['Offer']['parcels_off_impost'] = $teste['Offer']['parcels_off_impost'];
                $desc = $teste['Offer']['description'];
                $param['Offer']['description'] = $desc;
                $param['Offer']['specification'] = $teste['Offer']['specification'];

                //SALVA OS DADOS PRINCIPAIS DA OFERTA
                $addOfferFilters = $this->Utility->urlRequestToSaveData('offers', $param);

                //SALVA OS FILTROS CASO HAJA MUDANÇA
                if ($teste['Offer']['public'] == 'INACTIVE') {
                    $filtros = $teste['Offer']['public_filters'];
                    if (!empty($filtros)) {

                        $query = "DELETE FROM offers_filters where offer_id = {$offerOrig['id']}";
                        $params = array(
                            'User' => array(
                                'query' => $query
                            )
                        );
                        $delOfferFilter = $this->Utility->urlRequestToGetData('users', 'query', $params);

                        $filtros['OffersFilter']['offer_id'] = $offerOrig['id'];
                        $addFilters = $this->Utility->urlRequestToSaveData('offers', $filtros);
                    }
                }

                //salvando as metricas da oferta
                $dominios = $teste['Offer']['domains'];

                $total = count($domains) - 1;
                $qtdTotal = $domains[$total]['offers_domains']['id'];
                if (!empty($dominios)) {

                    $query = "DELETE FROM offers_metrics where offer_id = {$offerOrig['id']}";
                    $params = array(
                        'User' => array(
                            'query' => $query
                        )
                    );
                    $delOfferFilter = $this->Utility->urlRequestToGetData('users', 'query', $params);

                    for ($i = 0; $i <= $qtdTotal; $i++) {

                        for ($f = 0; $f <= $qtdTotal; $f++) {
                            if (!empty($dominios[$i][$f]) and ! $dominios[$i][$f] == '') {

                                $paramsQ = array(
                                    'User' => array(
                                        'query' => "INSERT INTO offers_metrics(offer_id, offer_metrics_x_id, offer_metrics_y_id, amount, value) VALUES(" . $addOfferFilters['data']['Offer']['id'] . "," . $i . "," . $f . "," . $dominios[$i][$f] . "," . $dominios['preco'][$i][$f] . ");"
                                    )
                                );

                                $abc = $this->Utility->urlRequestToGetData('users', 'query', $paramsQ);
                            }
                        }
                    }
                }$this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'layoutDash',
                    'plugin' => 'companies',
                ));
            }
        } else if ($page == 'edit') {

            $offer = $_SESSION['viewOffer'];
            $domains = $_SESSION['OfferDomains'];
            $offerId = $this->request->data['offer-edit'];


            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutDash',
                'plugin' => 'companies',
            ));
        } else {

            $offer = $_SESSION['viewOffer'];
            $upload = $_SESSION['imageUp'];

            $arrayCompany = array(
                'Offer' => array(
                    'conditions' => array(
                        'Offer.id' => 181
                    )
                ),
                'Company'
            );

            $myOffer = $this->Utility->urlRequestToGetData('offers', 'all', $arrayCompany);
            $this->set(compact('query'));
            $this->set(compact('upload'));
            $this->set(compact('myOffer'));
            $this->set(compact('offer'));
            $this->layout = 'x_dash';
            $this->render('x_cad_ofer_visualiza');
        }
    }

    // LAYOUT WISHLIST
    public function layoutWishlist() {

        $pageName = "wishlist";
        if (empty($_SESSION ['CompanyLoggedIn'])) {
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutPopUp',
                'plugin' => 'companies'
            ));
        }

        $company_id = $this->Session->read('CompanyLoggedIn.Company.id');
        if ($this->request->is('post')) {

            if ($this->request->data ['excluir_wishlist'] == true) {
                $render = true;
                $this->layout = '';

                // faz update de wishlist para excluir desejo
                $params = array(
                    'UsersWishlistCompany' => array(
                        'id' => $this->request->data ['id_linha'],
                        'status' => 'INACTIVE'
                    )
                );

                $desejosDel = $this->Utility->urlRequestToSaveData('companies', $params);

                if (!$desejosDel ['status'] == 'SAVE_OK') {
                    $mensagem = "Ocorreu um erro. Tente novamente";
                }
            } else if ($this->request->data ['id_oferta'] > 0) {

                // faz update de oferta para usuario na linha do wishlist
                $params = array(
                    'UsersWishlistCompany' => array(
                        'id' => $this->request->data ['id_linha'],
                        'status' => 'ACTIVE',
                        'offer_id' => $this->request->data ['id_oferta']
                    )
                );

                $desejosOfferUpdate = $this->Utility->urlRequestToSaveData('companies', $params);

                if ($desejosOfferUpdate ['status'] == 'SAVE_OK') {

                    // verifica se usuario ja tem esta oferta
                    $params = array(
                        'OffersUser' => array(
                            'conditions' => array(
                                'offer_id' => $desejosOfferUpdate ['data'] ['UsersWishlistCompany'] ['offer_id'],
                                'user_id' => $desejosOfferUpdate ['data'] ['User'] ['id']
                            )
                        )
                    );
                    $offer_user = $this->Utility->urlRequestToGetData('users', 'first', $params);

                    if (!is_array($offer_user)) {
                        // salvando oferta para usuario
                        $data = date('Y/m/d');
                        $query = "INSERT INTO offers_users values(NULL, '{$desejosOfferUpdate['data']['UsersWishlistCompany']['offer_id']}', '{$desejosOfferUpdate['data']['User']['id']}', '{$data}', 'facebook - portal')";
                        $params = array(
                            'User' => array(
                                'query' => $query
                            )
                        );
                        $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    }
                } else {
                    $mensagem = "Ocorreu um erro. Tente novamente";
                }
            } else {
                //cadastrar oferta para usuario
                $userWishCompId = $this->request->data ['id_linha'];
                $userWishId = $this->request->data['id_user'];
                $this->Session->write('direcOfferCompWishId', $userWishCompId);
                $this->Session->write('direcOfferUserId', $userWishId);

                $this->redirect(array(
                    'controller' => 'companies',
                    'action' => 'layoutCadOferta',
                    'plugin' => 'companies',
                ));
            }
        }


        // listando desejos
        $params = array(
            'UsersWishlistCompany' => array(
                'conditions' => array(
                    'UsersWishlistCompany.company_id' => $company_id,
                    'UsersWishlistCompany.status' => 'WAIT'
                ),
                'order' => array(
                    'UsersWishlistCompany.id' => 'DESC'
                ),
            ),
            'User',
            'UsersWishlist',
            'CompaniesCategory'
        );
        $desejos = $this->Utility->urlRequestToGetData('companies', 'all', $params);

        // pegando ofertas de empresa
        $params = array(
            'Offer' => array(
                'fields' => array(
                    'Offer.id',
                    'Offer.title'
                ),
                'conditions' => array(
                    'Offer.company_id' => $company_id,
                    'Offer.status' => 'ACTIVE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                )
            )
        );
        $ofertas = $this->Utility->urlRequestToGetData('offers', 'all', $params);


        $this->set(compact('desejos', 'ofertas', 'company_id', 'pageName'));
        $this->layout = 'x_dash';
        $this->render('x_wishlist');
    }

    // LAYOUT CONFIGURACOES
    public function layoutConfiguracoes($page) {

        $pageName = 'preferencias';
        //exclui usuario secundario
        if ($page == 'deleteSecondaryUser') {
            $secondUserId = $this->request->data['secondaryUserId'];

            $sql = "delete from secondary_users where id = {$secondUserId};";
            $params = array('User' => array('query' => $sql));
            $delete = $this->Utility->urlRequestToGetData('users', 'query', $params);
        }

        if ($page == 'save-preferences') {

            $emptyBackground = $this->request->data['data']['empty-background'];

            //caso não seja fundo branco, subimos a imagem selecionada pelo usuário e salvamos o link como background
            if ($emptyBackground == false) {
                if (empty($this->Session->read('compPreferences.id'))) {
                    $upload = $this->Utility->adv_uploadFileComp('companies', $this->request->data['Photo']['file']);
                    //$sql = "update users_preferences set background='{$upload}' where id = {$this->Session->read('userPreferences.id')}";

                    $sql = "insert into companies_preferences(companies_id, background) values({$this->Session->read('CompanyLoggedIn.Company.id')},'{$upload}');";
                    $params = array('User' => array('query' => $sql));
                    $save = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    $this->redirect(array('controller' => 'companies', 'action' => 'layoutConfiguracoes', 'plugin' => 'companies'));
                } else {
                    $upload = $this->Utility->adv_uploadFileComp('companies', $this->request->data['Photo']['file']);
                    $sql = "update companies_preferences set background='{$upload}' where id = {$this->Session->read('compPreferences.id')}";

                    //$sql = "insert into companies_preferences(companies_id, background) values({$this->Session->read('CompanyLoggedIn.Company.id')},'{$upload}');";
                    $params = array('User' => array('query' => $sql));
                    $save = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    $this->redirect(array('controller' => 'companies', 'action' => 'layoutConfiguracoes', 'plugin' => 'companies'));
                }
            } else {
                if (empty($this->Session->read('compPreferences.id'))) {

                    $sql = "insert into companies_preferences(companies_id, background) values({$this->Session->read('CompanyLoggedIn.Company.id')},'#ffffff');";
                    $params = array('User' => array('query' => $sql));
                    $save = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    $this->redirect(array('controller' => 'companies', 'action' => 'layoutConfiguracoes', 'plugin' => 'companies'));
                } else {

                    $sql = "update companies_preferences set background='#ffffff' where id = {$this->Session->read('compPreferences.id')}";
                    //$sql = "insert into companies_preferences(companies_id, background) values({$this->Session->read('CompanyLoggedIn.Company.id')},'{$upload}');";
                    $params = array('User' => array('query' => $sql));
                    $save = $this->Utility->urlRequestToGetData('users', 'query', $params);
                    $this->redirect(array('controller' => 'companies', 'action' => 'layoutConfiguracoes', 'plugin' => 'companies'));
                }
            }
        }

        /*
         * @author Matheus Odilon
         * O processo de cadastro da empresa já é um processo demorado,
         * por esse motivo criamos o registro de companies_social_networks aqui
         * caso a empresa ainda não possua
         * Isso não altera em nada as outras funções do sistema.
         */
        if ($page == 'save-social-network') {

            //Cria registro companies_social_networks caso ainda não exista
            if (empty($this->Session->read('compSocial.companies_social_networks.id'))) {
                $fbkLink = $this->request->data['fbkLink'];
                $twtLink = $this->request->data['twtLink'];
                $gplusLink = $this->request->data['gplusLink'];
                $gplusOffers = $this->request->data['gplusOffers'];
                $twtOffers = $this->request->data['twtOffers'];
                $fbkOffers = $this->request->data['fbkOffers'];

                $face = 'INACTIVE';
                $twt = 'INACTIVE';
                $gplus = 'INACTIVE';

                if (!empty($fbkLink)) {
                    $face = 'ACTIVE';
                }
                if (!empty($twtLink)) {
                    $twt = 'ACTIVE';
                }
                if (!empty($gplusLink)) {
                    $gplus = 'ACTIVE';
                }

                /*
                 * Query para criação do registro companies_social_networks caso empresa ainda não possua
                 */
                $sqlSocial = "insert into companies_social_networks(company_id, facebook, fbk_link, fbk_new_offers, twitter, twitter_link, twitter_new_offers, google_plus, gplus_link, gplus_new_offers) "
                        . "values({$this->Session->read('CompanyLoggedIn.Company.id')}, "
                        . "'{$face}', '{$fbkLink}', '{$fbkOffers}',"
                        . "'{$twt}', '{$twtLink}', '{$twtOffers}', "
                        . "'{$gplus}', '{$gplusLink}', '{$gplusOffers}');";
                $paramsSql = array('User' => array('query' => $sqlSocial));
                $insertSocial = $this->Utility->urlRequestToGetData('users', 'query', $paramsSql);

                /*
                 * Recupera infos
                 */
                $sqlSelect = "select * from companies_social_networks where company_id = {$this->Session->read('CompanyLoggedIn.Company.id')};";
                $paramsSelect = array('User' => array('query' => $sqlSelect));
                $selectSocial = $this->Utility->urlRequestToGetData('users', 'query', $paramsSelect);
                $networks = $selectSocial[0];
                $this->Session->write("compSocial", $networks);
            } else {

                $fbkLink = $this->request->data['fbkLink'];
                $twtLink = $this->request->data['twtLink'];
                $gplusLink = $this->request->data['gplusLink'];
                $gplusOffers = $this->request->data['gplusOffers'];
                $twtOffers = $this->request->data['twtOffers'];
                $fbkOffers = $this->request->data['fbkOffers'];

                $face = 'INACTIVE';
                $twt = 'INACTIVE';
                $gplus = 'INACTIVE';

                if (!empty($fbkLink)) {
                    $face = 'ACTIVE';
                }
                if (!empty($twtLink)) {
                    $twt = 'ACTIVE';
                }
                if (!empty($gplusLink)) {
                    $gplus = 'ACTIVE';
                }

                $sqlUpdate = "update companies_social_networks set facebook='{$face}', fbk_link='{$fbkLink}', fbk_new_offers='{$fbkOffers}',"
                        . " twitter='{$twt}', twitter_link='{$twtLink}', twitter_new_offers='{$twtOffers}', "
                        . "google_plus='{$gplus}', gplus_link='{$gplusLink}', gplus_new_offers='{$gplusOffers}' "
                        . "where id = {$this->Session->read('compSocial.companies_social_networks.id')};";
                $paramsUp = array('User' => array('query' => $sqlUpdate));
                $update = $this->Utility->urlRequestToGetData('users', 'query', $paramsUp);

                /*
                 * Recupera infos
                 */
                $sqlSelect = "select * from companies_social_networks where company_id = {$this->Session->read('CompanyLoggedIn.Company.id')};";
                $paramsSelect = array('User' => array('query' => $sqlSelect));
                $selectSocial = $this->Utility->urlRequestToGetData('users', 'query', $paramsSelect);
                $networks = $selectSocial[0];
                $this->Session->write("compSocial", $networks);
            }
        }

        if ($page == 'password') {
            $senha = $this->request ['data'] ['senha_nova'];
            $nome = $this->request ['data']['responsible_name'];
            $email = $this->request ['data']['responsible_email'];

            $params = array(
                'Company' => array(
                    'id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                    'responsible_name' => $nome,
                    'responsible_email' => $email,
                    'password' => $senha
                )
            );

            $updateSenha = $this->Utility->urlRequestToSaveData('companies', $params);
            $mensagem = 'Senha alterada com Sucesso!';
            $this->set(compact('mensagem'));
            $this->set(compact('updateSenha'));
            $this->Session->write('CompanyLoggedIn.Company.password', $updateSenha ['data'] ['Company'] ['password']);
            // $this->redirect(array('controller' => 'users', 'action' => 'home', 'plugin' => 'users'));

            $this->redirect(array('controller' => 'companies', 'action' => 'xlogoof', 'plugin' => 'companies'));
        }

        //SALVANDO FRETE
        if ($page == 'frete') {
            $params = $this->request->data;

            $params ['CompanyPreference'] ['id'] = $this->Session->read('CompanyLoggedIn.CompanyPreference.id');
            $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);
            $this->layout = 'x_dash';
            $this->render('x_configuracoes');
        }

        //EDITA COMPANY
        if ($page == 'updateCompany') {
            $params = $this->request->data;

            $upload = $this->Utility->adv_uploadFileComp('companies', $this->request->data ['Company'] ['logo']);

            $params ['Company'] ['logo'] = $upload;
            $params ['Company'] ['id'] = $this->Session->read('CompanyLoggedIn.Company.id');

            $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);

            $arrayParams = array(
                'Company' => array(
                    'conditions' => array(
                        'Company.id' => $this->Session->read('CompanyLoggedIn.Company.id')
                    )
                )
            );

            $comp = $this->Utility->urlRequestToGetData('companies', 'all', $arrayParams);

            $this->Session->write('CompanyLoggedIn.Company', $comp [0]['Company']);
            $company = $comp[0];
            $this->set(compact('comp'));
            $this->set(compact('company'));
            $this->layout = 'x_dash';
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutConfiguracoes',
                'plugin' => 'companies'
            ));
        } else {

            //TIPOS DE USUARIOS PARA USUARIOS SECUNDARIOS
            $query = "select * from secondary_users_types;";
            $params = array(
                'User' => array(
                    'query' => $query
                )
            );
            $tiposUsuarios = $this->Utility->urlRequestToGetData('users', 'query', $params);

            //Recupera company para alteração de dados
            $company = $this->Session->read('CompanyLoggedIn');

            $sqlAparencia = "select * from companies_preferences where companies_id = {$this->Session->read('CompanyLoggedIn.Company.id')};";
            $paramsSel = array(
                'User' => array(
                    'query' => $sqlAparencia
                )
            );
            $aparencia = $this->Utility->urlRequestToGetData('users', 'query', $paramsSel);
            $this->Session->write("compPreferences", $aparencia[0]['companies_preferences']);


            $secondUserSQL = "select * from secondary_users inner join secondary_users_types on secondary_users.secondary_type_id = secondary_users_types.id  where company_id  = {$this->Session->read('CompanyLoggedIn.Company.id')};";
            $secondUserParam = array(
                'User' => array(
                    'query' => $secondUserSQL
                )
            );
            $secondUsers = $this->Utility->urlRequestToGetData('users', 'query', $secondUserParam);

            //OPÇÕES DO FRETE/BANCO/CPF/USE_CORREIOS
            $prefComp = "select * from company_preferences where company_id  = {$this->Session->read('CompanyLoggedIn.Company.id')};";
            $prefsComp = array(
                'User' => array(
                    'query' => $prefComp
                )
            );
            $companyPreference = $this->Utility->urlRequestToGetData('users', 'query', $prefsComp);


            $this->set(compact('company', 'aparencia', 'secondUsers', 'companyPreference', 'pageName'));
            $this->set(compact('tiposUsuarios'));
            $this->layout = 'x_dash';
            $this->render('x_configuracoes');
        }
    }

    // LAYOUT VENDAS
    public function layoutVendas() {


        $pageName = "vendas";
        if (empty($_SESSION ['CompanyLoggedIn'])) {
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutPopUp',
                'plugin' => 'companies'
            ));
        }

        $company = $_SESSION ['CompanyLoggedIn'];

        // todas as compras
        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    //'Checkout.company_id' => 116,
                    'Checkout.company_id' => $company['Company'] ['id'],
                //'Checkout.total_value > ' => '0',
                ),
                'order' => array(
                    'Checkout.id' => 'DESC'
                ),
            ),
            'PaymentState',
            'Offer',
            'User',
            'OffersUser'
        );
        $todasCompras = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        $todasWithComment = '';
        foreach ($todasCompras as $compra) {
            $arrayParams = array(
                'OffersComment' =>
                array(
                    'conditions' => array(
                        'OffersComment.offer_id' => $compra['Offer']['id'],
                        'OffersComment.user_id' => $compra['User']['id']
                    )),
            );
            $comentario = $this->Utility->urlRequestToGetData('offers', 'first', $arrayParams);
            $compra['OffersComment'] = $comentario['OffersComment'];
            $todasWithComment[] = $compra;
        }

        // compras finalizadas
        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    //'Checkout.company_id' => 116,
                    'Checkout.company_id' => $company['Company'] ['id'],
                    // 'Checkout.total_value > ' => '0',
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
        $finalizadas = $this->Utility->urlRequestToGetData('payments', 'all', $params);

        $finalizadasWithComment = '';
        foreach ($finalizadas as $compra) {
            $arrayParams = array(
                'OffersComment' =>
                array(
                    'conditions' => array(
                        'OffersComment.offer_id' => $compra['Offer']['id'],
                        'OffersComment.user_id' => $compra['User']['id']
                    )),
            );
            $comentario = $this->Utility->urlRequestToGetData('offers', 'first', $arrayParams);
            $compra['OffersComment'] = $comentario['OffersComment'];
            $finalizadasWithComment[] = $compra;
        }
        // compras pendentes
        $params = array(
            'Checkout' => array(
                'conditions' => array(
                    //'Checkout.company_id' => 116,
                    'Checkout.company_id' => $company['Company'] ['id'],
                    //'Checkout.total_value > ' => '0',
                    'Checkout.payment_state_id <> ' => 4,
                    'NOT' => array(
                        'Checkout.payment_state_id' => array(
                            '999'
                        )
                    )
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
        $pendentes = $this->Utility->urlRequestToGetData('payments', 'all', $params);
        $pendentesWithComment = '';
        foreach ($pendentes as $compra) {
            $arrayParams = array(
                'OffersComment' =>
                array(
                    'conditions' => array(
                        'OffersComment.offer_id' => $compra['Offer']['id'],
                        'OffersComment.user_id' => $compra['User']['id']
                    )),
            );
            $comentario = $this->Utility->urlRequestToGetData('offers', 'first', $arrayParams);
            $compra['OffersComment'] = $comentario['OffersComment'];
            $pendentesWithComment[] = $compra;
        }

        $this->set(compact('todasCompras'));
        $this->set(compact('pendentes'));
        $this->set(compact('finalizadas', 'pageName'));
        $this->set(compact('todasWithComment', 'finalizadasWithComment', 'pendentesWithComment'));
        $this->layout = 'x_dash';
        $this->render('x_vendas');
    }

    // LAYOUT OFFERS
    public function layoutOffers($page) {

        unset($_SESSION ['editDTLinhas']);
        unset($_SESSION ['editDTColunas']);
        unset($_SESSION ['offersPhoto']);
        unset($_SESSION ['extraInfosss']);

        $pageName = "offers";
        if (empty($_SESSION ['CompanyLoggedIn'])) {
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutPopUp',
                'plugin' => 'companies'
            ));
        }

        /*
         * Ativa ou desativa oferta
         */
        if ($page == 'offerStatus') {
            $offerId = $this->request->data['offerId'];
            $status = $this->request->data['status'];

            if ($status == 'ATIVO') {

                $Q = "update offers set status = 'INACTIVE' where id = " . $offerId . ";";
                $qparams = array(
                    'User' => array(
                        'query' => $Q
                    )
                );
                $up = $this->Utility->urlRequestToGetData('users', 'query', $qparams);
            }
            if ($status == 'INATIVO') {

                $Q = "update offers set status = 'ACTIVE' where id =" . $offerId . ";";
                $qparams = array(
                    'User' => array(
                        'query' => $Q
                    )
                );
                $up = $this->Utility->urlRequestToGetData('users', 'query', $qparams);
            }
        }


        $company = $_SESSION ['CompanyLoggedIn'];

        //LISTA TODAS
        $arrayParams = array(
            'Offer' => array(
                'conditions' => array(
                    'Offer.company_id' => $company['Company'] ['id']
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
            )
        );

        $ofertas = $this->Utility->urlRequestToGetData('offers', 'all', $arrayParams);

        $offersWithStatistics = '';
        foreach ($ofertas as $oferta) {
            $statisticsQuery = "select details_click, checkouts_click, purchased_billet, purchased_card, sum(evaluation) evaluation, count(evaluation) votantes
 from offers_statistics inner join offers_comments on offers_statistics.offer_id = offers_comments.offer_id where offers_statistics.offer_id =" . $oferta['Offer']['id'] . ";";

            $statisticsParams = array(
                'User' => array(
                    'query' => $statisticsQuery
                )
            );
            $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);
            $oferta['Statistics'] = $statistics[0];
            $offersWithStatistics[] = $oferta;
        }

        //LISTA ATIVAS
        $paramsAtivas = array(
            'Offer' => array(
                'conditions' => array(
                    'Offer.company_id' => $company['Company'] ['id'],
                    'Offer.status' => 'ACTIVE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
            )
        );
        $ofertasAtiva = $this->Utility->urlRequestToGetData('offers', 'all', $paramsAtivas);

        $ativas = '';
        foreach ($ofertasAtiva as $oferta) {
            $statisticsQuery = "select details_click, checkouts_click, purchased_billet, purchased_card, sum(evaluation) evaluation, count(evaluation) votantes
 from offers_statistics inner join offers_comments on offers_statistics.offer_id = offers_comments.offer_id where offers_statistics.offer_id =" . $oferta['Offer']['id'] . ";";
            $statisticsParams = array(
                'User' => array(
                    'query' => $statisticsQuery
                )
            );
            $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);
            $oferta['Statistics'] = $statistics[0];
            $ativas[] = $oferta;
        }

        //LISTA INATIVAS
        $paramsInativas = array(
            'Offer' => array(
                'conditions' => array(
                    'Offer.company_id' => $company['Company'] ['id'],
                    'Offer.status' => 'INACTIVE'
                ),
                'order' => array(
                    'Offer.id' => 'DESC'
                ),
            )
        );
        $ofertasInativa = $this->Utility->urlRequestToGetData('offers', 'all', $paramsInativas);

        $inativas = '';
        foreach ($ofertasInativa as $oferta) {
            $statisticsQuery = "select details_click, checkouts_click, purchased_billet, purchased_card, sum(evaluation) evaluation, count(evaluation) votantes
 from offers_statistics inner join offers_comments on offers_statistics.offer_id = offers_comments.offer_id where offers_statistics.offer_id =" . $oferta['Offer']['id'] . ";";
            $statisticsParams = array(
                'User' => array(
                    'query' => $statisticsQuery
                )
            );
            $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);
            $oferta['Statistics'] = $statistics[0];
            $inativas[] = $oferta;
        }

        $statisticsQuery = "select details_click, checkouts_click, purchased_billet, purchased_card, sum(evaluation) evaluation, count(evaluation) votantes
 from offers_statistics inner join offers_comments on offers_statistics.offer_id = offers_comments.offer_id where offers_statistics.offer_id =" . $oferta['Offer']['id'] . ";";
        $statisticsParams = array(
            'User' => array(
                'query' => $statisticsQuery
            )
        );
        $statistics = $this->Utility->urlRequestToGetData('users', 'query', $statisticsParams);

        $this->set(compact('ofertasInativa'));
        $this->set(compact('ofertasAtiva', 'pageName'));
        $this->set(compact('ofertas', 'offersWithStatistics', 'ativas', 'inativas', 'statistics'));
        $this->layout = 'x_dash';
        $this->render('x_offers');
    }

    // LAYOUT ASSINATURAS
    public function layoutAssinaturas($page) {

        $pageName = "assinaturas";
        if ($page == 'redirect') {

            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutCadOferta',
                'plugin' => 'companies',
            ));
        } else if ($page == 'offerPersonalizada') {

            //  Salva o id do usuario na sessao
            //  quando a oferta for criada amarraremos ela ao id salvo
            $userId = $this->request->data['userId'];
            $this->Session->write('direcOfferUserId', $userId);
        } else {

            //ASSINATURAS DO ANO LETIVO ATIVAS
            $params = array(
                'CompaniesUser' => array(
                    'conditions' => array(
                        'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                        'User.status' => 'ACTIVE',
                        'CompaniesUser.status' => 'ACTIVE',
                    //'YEAR(CompaniesUser.date_register)' => date('Y')
                    ),
                    'order' => array(
                        'CompaniesUser.id' => 'DESC'
                    ),
                ),
                'User'
            );
            $assinaturasAnoAtual = $this->Utility->urlRequestToGetData('companies', 'all', $params);

            //ASSINATURAS CANCELADAS
            $params = array(
                'CompaniesUser' => array(
                    'conditions' => array(
                        'CompaniesUser.company_id' => $this->Session->read('CompanyLoggedIn.Company.id'),
                        'User.status' => 'ACTIVE',
                        'CompaniesUser.status' => 'INACTIVE'
                    ),
                    'order' => array(
                        'CompaniesUser.id' => 'DESC'
                    ),
                ),
                'User'
            );
            $assinaturasCanceladas = $this->Utility->urlRequestToGetData('companies', 'all', $params);

            if (is_array($assinaturasAnoAtual)) {
                foreach ($assinaturasAnoAtual as $assinatura) {
                    $assinatura ['User'] ['idade'] = $this->Utility->calcIdade(date('d/m/Y', strtotime($assinatura ['User'] ['birthday'])));
                    $assinaturas [] = $assinatura;
                }
            } else {
                $assinaturas = '';
            }

            $this->set(compact('assinaturasCanceladas'));
            $this->set(compact('assinaturas', 'pageName'));
            $this->set(compact('assinaturasAnoAtual'));
            $this->layout = 'x_dash';
            $this->render('x_assinaturas');
        }
    }

    public function preCadTeste($page = null, $id = null) {

//        if($page == 'inclui'){
//        // dados da empresa
//        $corporateName = $this->request->data ['Company'] ['corporate_name'];
//        $fancyName = $this->request->data ['Company'] ['fancy_name'];
//        $cnpj = $this->request->data ['Company'] ['cnpj'];
//        $phone = $this->request->data ['Company'] ['phone'];
//        $phone2 = $this->request->data ['Company'] ['phone_2'];
//        $compEmail = $this->request->data ['Company'] ['email'];
//        $site = $this->request->data ['Company'] ['site'];
//        // categoria
//        // responsavel pela conta
//        $responsible_name = $this->request->data ['Company'] ['responsible_name'];
//        $responsible_cpf = $this->request->data ['Company'] ['responsible_cpf'];
//        $responsible_email = $this->request->data ['Company'] ['responsible_email'];
//        $responsible_phone = $this->request->data ['Company'] ['responsible_phone'];
//        $responsible_phone_2 = $this->request->data ['Company'] ['responsible_phone_2'];
//        $responsible_cell_phone = $this->request->data ['Company'] ['responsible_cell_phone'];
//        // endereco
//        $address = $this->request->data ['Company'] ['address'];
//        $complement = $this->request->data ['Company'] ['complement'];
//        $city = $this->request->data ['Company'] ['city'];
//        $state = $this->request->data ['Company'] ['state'];
//        $number = $this->request->data ['Company'] ['number'];
//        $zip_code = $this->request->data ['Company'] ['zip_code'];
//        $district = $this->request->data ['Company'] ['district'];
//        $logo = $this->request->data ['Company']['logo'];
//
//        // DADOS DA EMPRESA
//        $params3 ['Company'] ['corporate_name'] = $corporateName;
//        $params3 ['Company'] ['fancy_name'] = $fancyName;
//        $params3 ['Company'] ['cnpj'] = $cnpj;
//        $params3 ['Company'] ['phone'] = $phone;
//        $params3 ['Company'] ['phone_2'] = $phone2;
//        $params3 ['Company'] ['site_url'] = $site;
//        $params3 ['Company'] ['email'] = $compEmail;
//        $params3 ['Company'] ['logo'] = $logo;
//        // RESPONSAVEL PELA CONTA
//        $params3 ['Company'] ['responsible_name'] = $responsible_name;
//        $params3 ['Company'] ['responsible_email'] = $responsible_email;
//        $params3 ['Company'] ['responsible_cpf'] = $responsible_cpf;
//        $params3 ['Company'] ['responsible_phone'] = $responsible_phone;
//        $params3 ['Company'] ['responsible_phone_2'] = $responsible_phone_2;
//        $params3 ['Company'] ['responsible_cell_phone'] = $responsible_cell_phone;
//        // ENDERECO
//        $params3 ['Company'] ['address'] = $address;
//        $params3 ['Company'] ['complement'] = $complement;
//        $params3 ['Company'] ['city'] = $city;
//        $params3 ['Company'] ['state'] = $state;
//        $params3 ['Company'] ['district'] = $district;
//        $params3 ['Company'] ['number'] = $number;
//        $params3 ['Company'] ['zip_code'] = $zip_code;
//
//        $params3 ['Company'] ['category_id'] = 1;
//        $params3 ['Company'] ['sub_category_id'] = 5;
//        $params3 ['Company'] ['status'] = 'ACTIVE';
//        $params3 ['Company'] ['date_register'] = date('Y/m/d');
//
//         //$cadastro = $this->Utility->urlRequestToSaveData('companies', $params3);
//         
//         $upload = $this->Utility->x_uploadFile('companies', $logo);
//         
//         echo "<br />" . $corporateName;
//         echo "<br />" . $fancyName;
//         echo "<br />" . $cnpj;
//         echo "<br />" . $phone;
//         echo "<br />" . $site;
//         echo "<br />" . $compEmail;
//         echo "<br />" . $responsible_name;
//         echo "<br />" . $responsible_email;
//         echo "<br />" . $responsible_cpf;
//         echo "<br />" . $responsible_phone;
//         echo "<br />" . $zip_code;
//         echo "<br />" . $address;
//         echo "<br />" . $number;
//         echo "<br />" . $district;
//         echo "<br />" . $state;
//         echo "<br />" . $logo;
//         echo "<br />" . $upload;
//       
//        $this->layout = 'liso';
//        $this->render('popup_login');
//        }else if($page == 'sucesso'){
//            
//        $this->redirect(array(
//                    'controller' => 'companies',
//                    'action' => 'layoutVendas',
//                    'plugin' => 'companies'
//                ));
//        }

        if ($page == 'incluir') {


            $params = array(
                'OffersFilter' => array(
                    'offer_id' => 182,
                    'gender' => $this->request->data['Offer']['filters']['gender'],
                    'location' => $this->request->data['Offer']['filters']['location'],
                    'age_group' => $this->request->data['Offer']['filters']['age_group'],
                    'political' => $this->request->data['Offer']['filters']['political'],
                    'religion' => $this->request->data['Offer']['filters']['religion'],
                    'relationship_status' => $this->request->data['Offer']['filters']['relationship_status']
                )
            );
            //$addOfferFilters = $this->Utility->urlRequestToSaveData('offers', $params);


            $company = $_SESSION ['CompanyLoggedIn'];

            $upload = $this->Utility->uploadFile('offers', $this->request->data ['Offer'] ['photo']);

            $param['Offer']['company_id'] = $company['Company']['id'];
            $param['Offer']['title'] = $this->request->data['Offer']['title'];
            $param['Offer']['description'] = $this->request->data['Offer']['description'];
            $param['Offer']['specification'] = $this->request->data['Offer']['specification'];
            $param['Offer']['resume'] = $this->request->data['Offer']['resume'];
            $param['Offer']['public'] = $this->request->data['Offer']['public'];
            $param['Offer']['value'] = $this->request->data['Offer']['value'];
            $param['Offer']['percentage_discount'] = $this->request->data['Offer']['percentage_discount'];
            $param['Offer']['weight'] = $this->request->data['Offer']['weight'];
            $param['Offer']['begins_at'] = $this->formataData2($this->request->data['Offer']['begins_at']);
            $param['Offer']['ends_at'] = $this->formataData2($this->request->data['Offer']['ends_at']);
            $param['Offer']['parcels'] = $this->request->data['Offer']['parcels'];
            $param['Offer']['parcels_off_impost'] = $this->request->data['Offer']['parcels_off_impost'];
            $param['Offer']['photo'] = $upload;

            //$addOffer = $this->Utility->urlRequestToSaveData('offers', $param);
            $query = "select * from offers_domains;";
            $paramsColuna = array(
                'User' => array(
                    'query' => $query
                )
            );
            $dominios = $this->Utility->urlRequestToGetData('users', 'query', $paramsColuna);
            $totalDominios = count($dominios);



            //Logica que percore toda a tabela dinamica 'Y & X', pegando index e valores 
            //Faz a amarração de Linha por coluna guardaDominio[Id de X(linha)][Id de Y(coluna)] = conteudo;
            //O Preço é buscado e salvo da mesma maneira, 
            //para acessa-lo basta adicionar a chave 'preco' (guardaDominio['preco'][Id de X][Id de Y])
            $eixoY = $this->request->data['selectboxY'];
            for ($f = 0; $f < $totalDominios; $f++) {

                for ($i = 0; $i < $totalDominios; $i++) {
                    if ($dominios[$i]['offers_domains']['offer_attribute_id'] == $eixoY and ! empty($this->request->data['cont-' . $dominios[$f]['offers_domains']['id'] . '-' . $dominios[$i]['offers_domains']['id']])) {

                        $guardaDominio[$dominios[$f]['offers_domains']['id']][$dominios[$i]['offers_domains']['id']] = $this->request->data['cont-' . $dominios[$f]['offers_domains']['id'] . '-' . $dominios[$i]['offers_domains']['id']];
                        $preco = $str = str_replace('.', '', $this->request->data['preco-column-' . $dominios[$i]['offers_domains']['id']]);
                        $guardaDominio['preco'][$dominios[$f]['offers_domains']['id']][$dominios[$i]['offers_domains']['id']] = str_replace(',', '.', $preco);
                    }
                }
            }

            $photos2 = $this->request->data['Offer']['photos'];

            $totalGuarda = count($guardaDominio);
            $offerFiltersData = $this->loadOfferFilters();
            //$this-set(compact('addOffer'));
            $this->set(compact('offerFiltersData'));
            $this->set(compact('photos', 'photos2'));
            $this->set(compact('eixoY'));
            $this->set(compact('guardaDominio'));
            $this->set(compact('totalGuarda'));
            $this->set(compact('dominios'));
            $this->set(compact('param'));
            $this->layout = 'x_dash';
            $this->render('teste_template');
        } else if ($page == 'editar') {

            if (!empty($id)) {
                $params = array(
                    'Offer' => array(
                        'conditions' => array(
                            'Offer.id' => $id
                        )
                    ),
                    'OffersFilter',
                    'OffersUser',
                    'OffersPhoto'
                );
                $offersUpdate = $this->Utility->urlRequestToGetData('offers', 'first', $params);

                $this->set(compact('offersUpdate'));
                $this->layout = 'x_dash';
                $this->render('teste_template');
            }
        }
    }

    public function preCadastro2($page = null, $moip = null) {
        $buscaDadosCadastro = '';
        if ($page == 'sucesso') {
            if ($this->Session->check('URL')) {

                if ($moip != null) {
                    $this->set('moipFalse', true);
                } else {
                    $this->set('moipFalse', false);
                    $this->set('url', $this->Session->read('URL'));
                }
            }
            $this->render('sucesso');
        } else if ($page == 'faq') {
            if ($this->request->is('post')) {
                echo "<script>alert('email enviado com sucesso');</script>";
            }
            $this->render('faq');
        } else if ($page == 'email') {
            $params = $this->Session->read('email');
            $teste = $this->Utility->urlRequestToLoginData('companies', 'first', $params);
            $this->Session->delete('email');
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'login',
                'plugin' => 'companies'
            ));
        } else {
            $mensagem = '';
            if ($this->request->is('ajax')) {
                if (!empty($this->request->data ['cep'])) {
                    $cURL = curl_init("http://cep.correiocontrol.com.br/{$this->request->data['cep']}.json");
                    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                    $resultado = curl_exec($cURL);
                    curl_close($cURL);
                    echo $resultado;
                    exit();
                }
            } else {
                if ($this->request->is('post')) {

                    // verificando se vai recuperar dados
                    if ($this->request->data ['verificar_dados'] == true) {

                        // recuperando dados para cadastro de empresa
                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.responsible_cpf' => $this->request->data ['cpf'],
                                    'Company.cnpj' => $this->request->data ['cnpj'],
                                    'Company.phone' => $this->request->data ['telefone1']
                                )
                            )
                        );
                        $buscaDadosCadastro = $this->Utility->urlRequestToGetData('companies', 'first', $params);
                        if (!$buscaDadosCadastro) {
                            $mensagem = "Dados Inv�lidos, realizar um novo cadastro";
                        }
                    } else {
                        if (!empty($this->request->data ['Company'] ['id'])) {
                            $query = "Delete from companies where id={$this->request->data['Company']['id']}";
                            $params = array(
                                'Company' => array(
                                    'query' => $query
                                )
                            );
                            $delCompanyNewCad = $this->Utility->urlRequestToGetData('companies', 'query', $params);
                        }

                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.responsible_cpf' => $this->request->data ['Company'] ['responsible_cpf']
                                )
                            )
                        );
                        $verificacaoCpf = $this->Utility->urlRequestToGetData('companies', 'count', $params);
                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.responsible_email' => $this->request->data ['Company'] ['responsible_email']
                                )
                            )
                        );
                        $verificacaoEmail = $this->Utility->urlRequestToGetData('companies', 'count', $params);
                        $params = array(
                            'Company' => array(
                                'conditions' => array(
                                    'Company.cnpj' => $this->request->data ['Company'] ['cnpj']
                                )
                            )
                        );
                        $verificacaoCnpj = $this->Utility->urlRequestToGetData('companies', 'count', $params);

                        if ($verificacaoCnpj == true) {
                            $mensagem = "CNPJ JA CADASTRADO NA NOSSA BASE DE DADOS";
                        } else if ($verificacaoEmail == true) {
                            $mensagem = "EMAIL JA CADASTRADO NA NOSSA BASE DE DADOS";
                        } else if ($verificacaoCpf == true) {
                            $mensagem = "CPF JA CADASTRADO NA NOSSA BASE DE DADOS";
                        } else {
                            $upload = $this->Utility->uploadFile('companies', $this->request->data ['Company'] ['logo']);

                            if (!empty($upload)) {
                                if ($upload == "UPLOAD_ERROR" && !empty($this->request->data ['logo'])) {
                                    $upload = $this->request->data ['logo'];
                                }
                                unset($this->request->data ['logo']);

                                // fazendo insert de empresa
                                $params = $this->request->data;
                                $params ['Company'] ['status'] = 'ACTIVE';
                                $params ['Company'] ['logo'] = $upload;
                                $params ['Company'] ['register'] = 'INACTIVE';
                                $params ['Company'] ['category_id'] = 1;
                                $params ['Company'] ['sub_category_id'] = 5;
                                $params ['Company'] ['date_register'] = date('Y/m/d');
                                $params ['CompanyPreference'] ['use_correios_api'] = 1;

                                $cadastro = $this->Utility->urlRequestToSaveData('companies', $params);

                                if (!$cadastro) {
                                    $mensagem = "Ocorreu um erro no teu cadastro";
                                } else {

                                    if (!empty($this->request->data ['Company'] ['id'])) {
                                        // nao faz chamada para MOIP
                                        $params = array(
                                            'Company' => array(
                                                'conditions' => array(
                                                    'Company.id' => $cadastro ['data'] ['Company'] ['id']
                                                ),
                                                'fields' => array(
                                                    'Company.Email',
                                                    'Company.responsible_email'
                                                )
                                            )
                                        );
                                        $teste = $this->Utility->urlRequestToLoginData('companies', 'first', $params);
                                        $this->Session->write('email', $params);

                                        $this->redirect(array(
                                            'controller' => 'companies',
                                            'action' => 'preCadastro',
                                            'plugin' => 'companies',
                                            'sucesso',
                                            'moipFalse'
                                        ));

                                        // chamar funcao de email de boas vindas
                                    } else {
                                        $params = array(
                                            'Company' => array(
                                                'conditions' => array(
                                                    'Company.id' => $cadastro ['data'] ['Company'] ['id']
                                                ),
                                                'fields' => array(
                                                    'Company.Email',
                                                    'Company.responsible_email'
                                                )
                                            )
                                        );
                                        $teste = $this->Utility->urlRequestToLoginData('companies', 'first', $params);
                                        $this->Session->write('email', $params);

                                        // trabalhando cadastro integrado do MOIP
                                        $sua_key = $this->key;
                                        $seu_token = $this->token;
                                        $auth = $seu_token . ':' . $sua_key;
                                        /*
                                         * $sua_key = 'SKMQ5HKQFTFRIFQBJEOROIGM70I6QVIN9KA5YIWB'; $seu_token = 'WOA4NBQ2AUMHJQ2NJIA6Q6X4ECXHFJUR'; $auth = $seu_token.':'.$sua_key;
                                         */

                                        $xml = "<PreCadastramento> 
													<prospect> 
														<idProprio>{$cadastro['data']['Company']['id']}</idProprio> 
														<nome>{$cadastro['data']['Company']['responsible_name']}</nome> 
														<sobrenome></sobrenome> 
														<email>{$cadastro['data']['Company']['responsible_email']}</email> 
														<dataNascimento></dataNascimento> 
														<rg></rg> 
														<cpf>{$cadastro['data']['Company']['responsible_cpf']}</cpf> 
														<cep>{$cadastro['data']['Company']['zip_code']}</cep> 
														<rua>{$cadastro['data']['Company']['address']}</rua> 
														<numero>{$cadastro['data']['Company']['number']}</numero> 
														<complemento></complemento> 
														<bairro>{$cadastro['data']['Company']['district']}</bairro> 
														<cidade>{$cadastro['data']['Company']['city']}</cidade> 
														<estado>{$cadastro['data']['Company']['state']}</estado> 
														<telefoneFixo>{$cadastro['data']['Company']['responsible_phone']}</telefoneFixo> 
														<razaoSocial>{$cadastro['data']['Company']['corporate_name']}</razaoSocial> 
														<nomeFantasia>{$cadastro['data']['Company']['fancy_name']}</nomeFantasia> 
														<cnpj>{$cadastro['data']['Company']['cnpj']}</cnpj> 
														<cepEmpresa>{$cadastro['data']['Company']['zip_code']}</cepEmpresa> 
														<ruaEmpresa>{$cadastro['data']['Company']['address']}</ruaEmpresa> 
														<numeroEmpresa>{$cadastro['data']['Company']['number']}</numeroEmpresa> 
														<complementoEmpresa></complementoEmpresa> 
														<bairroEmpresa>{$cadastro['data']['Company']['district']}</bairroEmpresa> 
														<cidadeEmpresa>{$cadastro['data']['Company']['city']}</cidadeEmpresa> 
														<estadoEmpresa>{$cadastro['data']['Company']['state']}</estadoEmpresa> 
														<telefoneFixoEmpresa>{$cadastro['data']['Company']['phone']}</telefoneFixoEmpresa> 
														<tipoConta>1</tipoConta>
													</prospect> 
											</PreCadastramento>		
										";

                                        // pr($xml);exit;
                                        // O HTTP Basic Auth � utilizado para autentica��o
                                        $header [] = "Authorization: Basic " . base64_encode($auth);

                                        // URL do SandBox - Nosso ambiente de testes
                                        // $url = "https://desenvolvedor.moip.com.br/sandbox/ws/alpha/PreCadastramento";
                                        $url = "https://www.moip.com.br/ws/alpha/PreCadastramento";

                                        $curl = curl_init();
                                        curl_setopt($curl, CURLOPT_URL, $url);

                                        // header que diz que queremos autenticar utilizando o HTTP Basic Auth
                                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

                                        // informa nossas credenciais
                                        curl_setopt($curl, CURLOPT_USERPWD, $auth);
                                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
                                        curl_setopt($curl, CURLOPT_POST, true);

                                        // Informa nosso XML de instru��o
                                        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);

                                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                                        // efetua a requisi��o e coloca a resposta do servidor do MoIP em $ret
                                        $ret = curl_exec($curl);
                                        $err = curl_error($curl);
                                        curl_close($curl);
                                        $xml = simplexml_load_string($ret);

                                        if ($xml->RespostaPreCadastramento->Status == 'Sucesso') {
                                            $url = "https://www.moip.com.br/cadastro/trueone/{$xml->RespostaPreCadastramento->idRedirecionamento}";

                                            $this->Session->write('URL', $url);
                                            $this->redirect(array(
                                                'controller' => 'companies',
                                                'action' => 'preCadastro',
                                                'plugin' => 'companies',
                                                'sucesso'
                                            ));
                                        } else {
                                            if (substr($xml->RespostaPreCadastramento->Erro, 0, 6) == 'E-mail') {
                                                $mensagem = "Cadastro efetuado com sucesso. Use sua conta do MOIP";
                                            } else {
                                                $mensagem = "Nao foi possivel cadastrar a tua empresa no MOIP - Entre em contato com o administrador do site";
                                            }
                                        }
                                    }
                                }
                            } else {
                                $mensagem = "Nao foi possivel efetuar o cadastro. Erro no envio de Imagem";
                            }
                        }
                    }
                    // aqui
                    $this->set(compact('mensagem', 'buscaDadosCadastro'));
                }
            }
        }
    }

    public function capDados() {
        $config ["largura"] = 140;
        $config ["altura"] = 140;

        $arquivo = $this->request->data ['Company'] ['logo'];

        if (!eregi("^image/(pjpeg|jpeg|png|gif)$", $arquivo ["type"])) {
            echo "ERRO NO FORMATO";
            // exit ();
        } else {
            echo "formato correto";
        }
        echo "<br />";
        $tamanhos = getimagesize($arquivo ["tmp_name"]);

        if ($tamanhos [0] > $config ["largura"]) {
            echo "largura � maior do que recomendado";
        } else {

            echo "largura dentro do padrao";
        }
        echo "<br />";
        // Verificar altura
        if ($tamanhos [1] > $config ["altura"]) {
            echo "altura � maior do que recomendado";
        } else {
            echo "altura dentro do padr�o";
        }

        // $upload = $this->Utility->x_uploadFile ( 'companies', $this->request->data ['Company'] ['logo'] );
    }

    public function openPopUp() {
        $ter = 'tftt';
        $this->layout = 'x_comp_pre_cad_layout';
        $this->render('pop_teste');
    }

    private function x_cadMoipXml(array $cadastro, $type) {
        // trabalhando cadastro integrado do MOIP
        $sua_key = $this->key;
        $seu_token = $this->token;
        $auth = $seu_token . ':' . $sua_key;
        /*
         * $sua_key = 'SKMQ5HKQFTFRIFQBJEOROIGM70I6QVIN9KA5YIWB'; $seu_token = 'WOA4NBQ2AUMHJQ2NJIA6Q6X4ECXHFJUR'; $auth = $seu_token.':'.$sua_key;
         */

        $xml = "<PreCadastramento>
		<prospect>
		<idProprio>{$cadastro[0]['Company']['id']}</idProprio>
		<nome>{$cadastro[0]['Company']['responsible_name']}</nome>
						<sobrenome></sobrenome>
							<email>{$cadastro[0]['Company']['responsible_email']}</email>
						<dataNascimento></dataNascimento>
							<rg></rg>
							<cpf>{$cadastro[0]['Company']['responsible_cpf']}</cpf>
						<cep>{$cadastro[0]['Company']['zip_code']}</cep>
									<rua>{$cadastro[0]['Company']['address']}</rua>
											<numero>{$cadastro[0]['Company']['number']}</numero>
						<complemento>{$cadastro[0]['Company']['complement']}</complemento>
							<bairro>{$cadastro[0]['Company']['district']}</bairro>
						<cidade>{$cadastro[0]['Company']['city']}</cidade>
									<estado>{$cadastro[0]['Company']['state']}</estado>
									<telefoneFixo>{$cadastro[0]['Company']['responsible_phone']}</telefoneFixo>
											<razaoSocial>{$cadastro[0]['Company']['corporate_name']}</razaoSocial>
											<nomeFantasia>{$cadastro[0]['Company']['fancy_name']}</nomeFantasia>
											<cnpj>{$cadastro[0]['Company']['cnpj']}</cnpj>
													<cepEmpresa>{$cadastro[0]['Company']['zip_code']}</cepEmpresa>
													<ruaEmpresa>{$cadastro[0]['Company']['address']}</ruaEmpresa>
													<numeroEmpresa>{$cadastro[0]['Company']['number']}</numeroEmpresa>
													<complementoEmpresa></complementoEmpresa>
													<bairroEmpresa>{$cadastro[0]['Company']['district']}</bairroEmpresa>
													<cidadeEmpresa>{$cadastro[0]['Company']['city']}</cidadeEmpresa>
													<estadoEmpresa>{$cadastro[0]['Company']['state']}</estadoEmpresa>
													<telefoneFixoEmpresa>{$cadastro[0]['Company']['phone']}</telefoneFixoEmpresa>
													<tipoConta>1</tipoConta>
													</prospect>
													</PreCadastramento>
													";

        // pr($xml);exit;
        // O HTTP Basic Auth � utilizado para autentica��o
        $header [] = "Authorization: Basic " . base64_encode($auth);

        // URL do SandBox - Nosso ambiente de testes
        // $url = "https://desenvolvedor.moip.com.br/sandbox/ws/alpha/PreCadastramento";
        $url = "https://www.moip.com.br/ws/alpha/PreCadastramento";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        // header que diz que queremos autenticar utilizando o HTTP Basic Auth
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        // informa nossas credenciais
        curl_setopt($curl, CURLOPT_USERPWD, $auth);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
        curl_setopt($curl, CURLOPT_POST, true);

        // Informa nosso XML de instru��o
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // efetua a requisi��o e coloca a resposta do servidor do MoIP em $ret
        $ret = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $xml = simplexml_load_string($ret);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);
        return $array;
    }

    /**
     * Gera password baseado em MD5
     *
     * @return string
     */
    private function generateRandomPassword() {
        $alphabet = "23456789bcdefghijklmnopqrstuwxyzBCDEFGHIJKLMNOPQRSTUWXYZ";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i ++) {
            $n = rand(0, $alphaLength);
            $pass [] = $alphabet [$n];
        }
        return implode($pass);
    }

    public function formataData2($dataH) {
        $data = explode('/', $dataH);
        if (!empty($data[1])) {
            return $data[2] . '-' . $data[0] . '-' . $data[1];
        } else {
            return 'ERRO - CONVERT DATA';
        }
    }

    //CADASTRO DE USUARIOS SECUNDARIOS
    public function cadSecondaryUsers($page = null) {

        $company = $_SESSION ['CompanyLoggedIn'];

        //CRIA USUARIO
        if ($page == 'insert') {

            $password = $this->generateRandomPassword();
            $param['SecondaryUser']['name'] = $this->request->data['SecondaryUser']['name'];
            $param['SecondaryUser']['email'] = $this->request->data['SecondaryUser']['email'];
            $param['SecondaryUser']['type'] = $this->request->data['SecondaryUser']['type'];
            $param['SecondaryUser']['company_id'] = $company['Company']['id'];
            $param['SecondaryUser']['normalPass'] = $password;
            $param['SecondaryUser']['hashPass'] = md5($password);

            $query = "INSERT INTO secondary_users(name, email, password, company_id, secondary_type_id)"
                    . " VALUES('" . $param['SecondaryUser']['name'] . "','" . $param['SecondaryUser']['email'] . "','" . $param['SecondaryUser']['hashPass'] . "'," . 119 . "," . $param['SecondaryUser']['type'] . ");";
            $params = array(
                'User' => array(
                    'query' => $query
                )
            );
            $addUserOffer = $this->Utility->urlRequestToGetData('users', 'query', $params);

            //enviando email para usuário secundário
            $data['normalPass'] = $param['SecondaryUser']['normalPass'];
            $data['name'] = $param['SecondaryUser']['name'];
            $data['email'] = $param['SecondaryUser']['email'];

            $this->Utility->postEmail('companies', 'secondaryUser', $data);
            //

            $mensagem = 'Usuário cadastrado.';

            $this->set(compact('param'));
            $this->set(compact('mensagem'));
            //$this->set(compact('param'));
            $this->layout = 'X_dash';
            $this->redirect(array(
                'controller' => 'companies',
                'action' => 'layoutCadOferta',
                'plugin' => 'companies'
            ));
        } else {

            $query = "select * from secondary_users_types;";
            $params = array(
                'User' => array(
                    'query' => $query
                )
            );

            $param['email'] = 'mts@accential.com';
            $param['password'] = '9TEcJJg7';
            $select = $this->secondaryUserLogin($param);

            $this->set(compact('select'));
            $this->set(compact('envio'));
            $this->set(compact('tiposUsuarios'));
            $this->layout = 'X_dash';
            $this->render('secondary_users');
        }
    }

    //FAZER LOGIN DE USUARIO SECUNDARIO
    /*
     * Faz o login do usuario e traz infos da company 
     */
    public function secondaryUserLogin($email, $senha) {

        $query = "select * from secondary_users where email = '$email' and password = '$senha';";
        $params = array(
            'User' => array(
                'query' => $query
            )
        );
        $usuario = $this->Utility->urlRequestToGetData('users', 'query', $params);

        //Caso exista esse usuario
        if (!empty($usuario)) {
            $usuario['login_status'] = 'LOGIN_OK';

            $conditions = array(
                'Company' => array(
                    'conditions' => array(
                        'Company.id' => $usuario[0]['secondary_users']['company_id']
                    )
                ),
                'CompanyPreference' => array()
            );

            // buscando company na API
            $company = $this->Utility->urlRequestToGetData('companies', 'first', $conditions);
            $usuario['company'] = $company;

            //Caso não exista
        } else {
            $usuario['login_status'] = 'LOGIN_ERRO';
        }

        return $usuario;
    }

    public function loadHome() {
        $this->redirect(array(
            'controller' => 'companies',
            'action' => 'layoutPopUp',
            'plugin' => 'companies'
        ));
    }

    public function myPublic() {
        $this->redirect(array(
            'controller' => 'companies',
            'action' => 'layoutCadOferta',
            'plugin' => 'companies',
            'publico'
        ));
    }

    /**
     * 
     * Captura informaçãos novas para a empresa
     * o resultado dessa pesquisa alimenta as notificações
     * 
     * Desejos não respondidos - assinaturas - vendas
     */
    public function compNews() {
        $this->layout = 'X_dash';
        $this->render('x_cad_ofer_visualiza');
    }

    public function signCompany() {
        $compId = $_POST['compId'];
        $userId = $_POST['userId'];
    }

}
