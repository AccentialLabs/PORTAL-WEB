<?php

/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
/* HOME */
Router::connect('/', array(
    'controller' => 'portal',
    'action' => 'home',
    'plugin' => ''
));

/* HOME 2 */
Router::connect('/newHom', array(
    'controller' => 'portal',
    'action' => 'newHome',
    'plugin' => ''
));

// teste envio automatico dos emails
Router::connect('/user/automaticEmail', array(
    'controller' => 'users',
    'action' => 'automaticEmail',
    'plugin' => 'users'
));

Router::connect('/user/teste/*', array(
    'controller' => 'users',
    'action' => 'teste',
    'plugin' => 'users'
));

//teste
Router::connect('/user/changeLogin', array(
    'controller' => 'users',
    'action' => 'changeLogin',
    'plugin' => 'users'
));

//teste2
Router::connect('/user/testeUpload', array(
    'controller' => 'users',
    'action' => 'testeUpload',
    'plugin' => 'users'
));

Router::connect('/user/ofertasParaMim', array(
    'controller' => 'users',
    'action' => 'myOffers',
    'plugin' => 'users'
));

Router::connect('/user/ofertas/*', array(
    'controller' => 'users',
    'action' => 'minhasOfertas',
    'plugin' => 'users'
));

//MEUS DADOS CADASTRAIS
Router::connect('/user/meus-dados/*', array(
    'controller' => 'users',
    'action' => 'meusDados',
    'plugin' => 'users'
));

//NOTIFICATIONS COM AJAX
Router::connect('/user/notification/*', array(
    'controller' => 'users',
    'action' => 'notification',
    'plugin' => 'users'
));

//LOGIN COM AJAX
Router::connect('/user/realizar-compra/*', array(
    'controller' => 'users',
    'action' => 'realizarCompra',
    'plugin' => 'users'
));

//LOGIN COM AJAX
Router::connect('/user/ajaxLogin/*', array(
    'controller' => 'users',
    'action' => 'ajaxLogin',
    'plugin' => 'users'
));

//ENVIA EMAIL
Router::connect('/user/enviaEmail/*', array(
    'controller' => 'users',
    'action' => 'enviaEmail',
    'plugin' => 'users'
));

//SECONDARY HOME
Router::connect('/user/vitrine/*', array(
    'controller' => 'users',
    'action' => 'testa',
    'plugin' => 'users'
));

//NEW WISH
Router::connect('/user/wish/*', array(
    'controller' => 'users',
    'action' => 'wish',
    'plugin' => 'users'
));

//NEW compras
Router::connect('/user/minhas-compras/*', array(
    'controller' => 'users',
    'action' => 'compras',
    'plugin' => 'users'
));

//NEW ASSINATURAS
Router::connect('/user/assinaturas/*', array(
    'controller' => 'users',
    'action' => 'assinaturas',
    'plugin' => 'users'
));

//OFFER
Router::connect('/user/offerDetail/*', array(
    'controller' => 'users',
    'action' => 'offersDetail',
    'plugin' => 'users'
));
/* USERS */
Router::connect('/user', array(
    'controller' => 'users',
    'action' => 'home',
    'plugin' => 'users'
));

//companies profile
Router::connect('/user/companiesProfile/*', array(
    'controller' => 'users',
    'action' => 'companiesProfile',
    'plugin' => 'users'
));


//NASP
Router::connect('/user/nasp', array(
    'controller' => 'users',
    'action' => 'naspReceiver',
    'plugin' => 'users'
));

Router::connect('/user/rss', array(
    'controller' => 'users',
    'action' => 'rss',
    'plugin' => 'users'
));

Router::connect('/user/view-offer/*', array(
    'controller' => 'users',
    'action' => 'viewOffer',
    'plugin' => 'users'
));
Router::connect('/user/shipping-address/*', array(
    'controller' => 'users',
    'action' => 'shippingAddress',
    'plugin' => 'users'
));
Router::connect('/user/purchase-details/*', array(
    'controller' => 'users',
    'action' => 'purchaseDetails',
    'plugin' => 'users'
));
Router::connect('/user/checkout/*', array(
    'controller' => 'users',
    'action' => 'checkout',
    'plugin' => 'users'
));
Router::connect('/user/amigos/*', array(
    'controller' => 'users',
    'action' => 'convidarAmigos',
    'plugin' => 'users'
));
Router::connect('/user/cadastro/*', array(
    'controller' => 'users',
    'action' => 'cad_perfil',
    'plugin' => 'users'
));
Router::connect('/user/signatures/*', array(
    'controller' => 'users',
    'action' => 'signatures',
    'plugin' => 'users'
));
Router::connect('/user/public/*', array(
    'controller' => 'users',
    'action' => 'offerPublic',
    'plugin' => 'users'
));
Router::connect('/user/compras/*', array(
    'controller' => 'users',
    'action' => 'minhasCompras',
    'plugin' => 'users'
));
Router::connect('/user/detalhe-da-compra/*', array(
    'controller' => 'users',
    'action' => 'detail_purchasing',
    'plugin' => 'users'
));
Router::connect('/user/pagamento/*', array(
    'controller' => 'users',
    'action' => 'pagamento_mobile',
    'plugin' => 'users'
));
Router::connect('/user/login', array(
    'controller' => 'users',
    'action' => 'login',
    'plugin' => 'users'
));
Router::connect('/user/sair/*', array(
    'controller' => 'users',
    'action' => 'logoof',
    'plugin' => 'users'
));
Router::connect('/user/retorno/*', array(
    'controller' => 'users',
    'action' => 'sucesso',
    'plugin' => 'users'
));
Router::connect('/user/pedidos-assinaturas/*', array(
    'controller' => 'users',
    'action' => 'pedidos_assinaturas',
    'plugin' => 'users'
));
Router::connect('/user/wishlist/*', array(
    'controller' => 'users',
    'action' => 'wishlist',
    'plugin' => 'users'
));
Router::connect('/user/esqueci-minha-senha/*', array(
    'controller' => 'users',
    'action' => 'new_password',
    'plugin' => 'users'
));
Router::connect('/user/dados-cadastrais/*', array(
    'controller' => 'users',
    'action' => 'dados_cadastrais',
    'plugin' => 'users'
));

/* COMPANIES */
Router::connect('/company', array(
    'controller' => 'companies',
    'action' => 'loadHome',
    'plugin' => 'companies'
));
Router::connect('/company/ofertas', array(
    'controller' => 'companies',
    'action' => 'offers',
    'plugin' => 'companies'
));
Router::connect('/company/assinaturas', array(
    'controller' => 'companies',
    'action' => 'signatures',
    'plugin' => 'companies'
));
Router::connect('/company/oferta-cadastro/*', array(
    'controller' => 'companies',
    'action' => 'addOffer',
    'plugin' => 'companies'
));
Router::connect('/company/publico/*', array(
    'controller' => 'companies',
    'action' => 'publico',
    'plugin' => 'companies'
));
Router::connect('/company/login/*', array(
    'controller' => 'companies',
    'action' => 'login',
    'plugin' => 'companies'
));

// view teste
Router::connect('/company/precadastro-teste/*', array(
    'controller' => 'companies',
    'action' => 'precadastro_teste',
    'plugin' => 'companies'
));

// teste do layout da pop up
Router::connect('/company/layout-popup', array(
    'controller' => 'companies',
    'action' => 'layoutPopUp',
    'plugin' => 'companies'
));

// teste do layout da dash
Router::connect('/company/layout-dash/*', array(
    'controller' => 'companies',
    'action' => 'layoutDash',
    'plugin' => 'companies'
));

// layout cadastro de oferta
Router::connect('/company/layout-cad-ofertas/*', array(
    'controller' => 'companies',
    'action' => 'layoutCadOferta',
    'plugin' => 'companies'
));

// layout wishlist
Router::connect('/company/layout-wishlist', array(
    'controller' => 'companies',
    'action' => 'layoutWishlist',
    'plugin' => 'companies'
));

// layout configuracoes
Router::connect('/company/layout-configs/*', array(
    'controller' => 'companies',
    'action' => 'layoutConfiguracoes',
    'plugin' => 'companies'
));


//layout vendas
Router::connect('/company/layout-vendas', array(
    'controller' => 'companies',
    'action' => 'layoutVendas',
    'plugin' => 'companies'
));

//layout ofertas
Router::connect('/company/layout-offers/*', array(
    'controller' => 'companies',
    'action' => 'layoutOffers',
    'plugin' => 'companies'
));

//layout assinaturas
Router::connect('/company/layout-assinaturas/*', array(
    'controller' => 'companies',
    'action' => 'layoutAssinaturas',
    'plugin' => 'companies'
));

//open pop up categorias
Router::connect('/company/open-pop', array(
    'controller' => 'companies',
    'action' => 'openPopUp',
    'plugin' => 'companies'
));

//PRECADASTRO TESTE
Router::connect('/company/precad/*', array(
    'controller' => 'companies',
    'action' => 'preCadTeste',
    'plugin' => 'companies'
));

//TESTE EIXO X E Y
Router::connect('/company/tes', array(
    'controller' => 'companies',
    'action' => 'teste',
    'plugin' => 'companies'
));

//Cadastro de usuários secundários
Router::connect('/company/secondUsers/*', array(
    'controller' => 'companies',
    'action' => 'cadSecondaryUsers',
    'plugin' => 'companies'
));

Router::connect('/company/logoof', array(
    'controller' => 'companies',
    'action' => 'logoof',
    'plugin' => 'companies'
));
Router::connect('/company/preferencias/*', array(
    'controller' => 'companies',
    'action' => 'preferencias',
    'plugin' => 'companies'
));
Router::connect('/company/compras', array(
    'controller' => 'companies',
    'action' => 'listPurchase',
    'plugin' => 'companies'
));
Router::connect('/company/pre-cadastro/*', array(
    'controller' => 'companies',
    'action' => 'preCadastro',
    'plugin' => 'companies'
));
Router::connect('/company/convites-usuarios/*', array(
    'controller' => 'companies',
    'action' => 'cadConvite',
    'plugin' => 'companies'
));
Router::connect('/company/esqueci-minha-senha/*', array(
    'controller' => 'companies',
    'action' => 'new_password',
    'plugin' => 'companies'
));
Router::connect('/company/desejos/*', array(
    'controller' => 'companies',
    'action' => 'wishlist',
    'plugin' => 'companies'
));
Router::connect('/company/convites-facebook/*', array(
    'controller' => 'companies',
    'action' => 'convidarAmigos',
    'plugin' => 'companies'
));
Router::connect('/company/cadastro-moip/*', array(
    'controller' => 'companies',
    'action' => 'cadastroMoip',
    'plugin' => 'companies'
));

Router::connect('/company/meu-publico/*', array(
    'controller' => 'companies',
    'action' => 'myPublic',
    'plugin' => 'companies'
));

//SAIR 
Router::connect('/company/sair', array(
    'controller' => 'companies',
    'action' => 'xlogoof',
    'plugin' => 'companies'
));

Router::connect('/company/cadOfferDisp', array(
    'controller' => 'companies',
    'action' => 'compNews',
    'plugin' => 'companies'
));

/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
Router::connect('/pages/*', array(
    'controller' => 'pages',
    'action' => 'display'
));

/**
 * Load all plugin routes.
 * See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
CakePlugin::routes();

/**
 * Load the CakePHP default routes.
 * Only remove this if you do not want to use
 * the built-in default routes.
 */
require CAKE . 'Config' . DS . 'routes.php';
