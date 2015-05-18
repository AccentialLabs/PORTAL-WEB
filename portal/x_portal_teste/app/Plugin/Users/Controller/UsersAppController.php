<?php
App::uses ( 'AppController', 'Controller' );
class UsersAppController extends AppController {
	public $layout = 'user';
	
	/**
	 * * Intervalo onde consideramos a oferta como nova.
	 *
	 * Medido em dias *
	 * 
	 * @var int
	 *
	 */
	protected $newOfferRange = 2;
	
	/**
	 * * Intervalo onde consideramos a oferta como nova.
	 *
	 * Medido em dias
	 * * @var int
	 */
	protected $lastWeekOfferRange = 7;
	public function beforeFilter() {
		parent::beforeFilter ();
		
		$this->checkLogin ();
		
		// verifica se esta vendo ofertas de empresa especifica
		if ($this->request->data ['hdAllCompany']) {
			$this->Session->write ( 'Ofertas-Assinaturas', $this->request->data );
		}
		
		if ($this->Session->check ( 'userData' )) {
			$this->convites ();
			$this->wishlist ();
			$this->ofertas ();
		}
		
		$this->set ( 'newOfferRange', $this->newOfferRange * 3600 * 24 );
		$this->set ( 'lastWeekOfferRange', $this->lastWeekOfferRange * 3600 * 24 );
		$this->set ( 'pathUrl', "http://" . $_SERVER ['SERVER_NAME'] . "/portal/user/" );
		$this->set ( 'menu', $this->menu ( $this->request->params ['action'] ) );
		
		// verifica se usuario esta vendo lista de ofertas personalizada
		if ($this->Session->check ( 'Ofertas-Assinaturas' )) {
			if ($this->request->params ['action'] != "home") {
				$this->Session->delete ( 'Ofertas-Assinaturas' );
			}
		}
		
		// verifica se usuario esta vendo lista de ofertas de desejos
		if ($this->Session->check ( 'ofertasIds' )) {
			if ($this->request->params ['action'] != "home") {
				$this->Session->delete ( 'ofertasIds' );
			}
		}
		
		// verifica se carrinho foi abandonado e destroi sessao de compra
		if ($this->Session->check ( 'Carrinho' )) {
			if ($this->request->params ['action'] != 'detail_purchasing' && $this->request->params ['action'] != 'viewOffer') {
				$this->Session->delete ( 'Carrinho' );
				$this->Session->delete ( 'access' );
			}
		}
	}
	private function checkLogin() {
		// verifica se o usuario esta logado
		$session = $this->Session->read ( 'sessionLogado' );
		if (empty ( $session )) {
			$this->Session->write ( 'sessionLogado', false );
		}
		
		if ($this->params ['plugin'] == 'users') {
			if ($this->params ['controller'] == 'users' && $this->params ['action'] == 'home' && ! $session == true) {
				$this->redirect ( array (
						'controller' => 'users',
						'plugin' => 'users',
						'action' => 'login' 
				) );
			}
			if ($this->params ['controller'] == 'users' && $this->params ['action'] == 'signatures' && ! $session == true) {
				$this->redirect ( array (
						'controller' => 'users',
						'plugin' => 'users',
						'action' => 'login' 
				) );
			}
		}
		return null;
	}
	
	/**
	 * *Protecao contra injection
	 * 
	 * @var string
	 *
	 */
	protected function securityForm($texto) {
		// Lista de palavras para procurar
		$check [1] = chr ( 34 ); // símbolo "
		$check [2] = chr ( 39 ); // símbolo '
		$check [3] = chr ( 92 ); // símbolo /
		$check [4] = chr ( 96 ); // símbolo `
		$check [5] = "drop table";
		$check [6] = "update";
		$check [7] = "alter table";
		$check [8] = "drop database";
		$check [9] = "drop";
		$check [10] = "select";
		$check [11] = "delete";
		$check [12] = "insert";
		$check [13] = "alter";
		$check [14] = "destroy";
		$check [15] = "table";
		$check [16] = "database";
		$check [17] = "union";
		$check [18] = "TABLE_NAME";
		$check [19] = "1=1";
		$check [20] = 'or 1';
		$check [21] = 'exec';
		$check [22] = 'INFORMATION_SCHEMA';
		$check [23] = 'like';
		$check [24] = 'COLUMNS';
		$check [25] = 'into';
		$check [26] = 'VALUES';
		
		$y = 1;
		$x = sizeof ( $check );
		
		while ( $y <= $x ) {
			$target = strpos ( $texto, $check [$y] );
			if ($target !== false) {
				$texto = str_replace ( $check [$y], "", $texto );
			}
			$y ++;
		}
		// Retorna a variável limpa sem perigos de SQL Injection
		return $texto;
	}
	private function menu($menu) {
		$tipo = 'nao-logado';
		if ($this->Session->read ( 'userData.User' ))
			$tipo = "logado";
			
			// verifica se usuario esta vendo ofertas de desejo
		if ($this->Session->read ( 'HomeOfertas' ) == 'desejos') {
			$mensagem = 'Ofertas de desejos';
			$desc = 'Aqui voc&ecirc; acessa ofertas recebidas do teu desejo realizado.';
			
			// verifica se usuario tem ofertas para colocar mensagem da home
		} else if ($this->Session->read ( 'HomeOfertas' ) == 'personalizado' && $this->Session->check ( 'Ofertas-Assinaturas' )) {
			// pegando nome de empresa
			$params = array (
					'Company' => array (
							'fields' => array (
									'Company.fancy_name' 
							),
							'conditions' => array (
									'id' => $this->Session->read ( 'Ofertas-Assinaturas.hdIdCOmpany' ) 
							) 
					) 
			);
			$company = $this->Utility->urlRequestToGetData ( 'companies', 'first', $params );
			
			$mensagem = 'Ofertas da empresa ' . $company ['Company'] ['fancy_name'];
			$desc = 'Aqui voc&ecirc; acessa ofertas personalizadas para seu perfil. Complete seu cadastro ou assine outras Empresas para receber outras ofertas';
		} else if ($this->Session->read ( 'HomeOfertas' ) == 'personalizado') {
			$mensagem = 'Ofertas personalizadas';
			$desc = 'Aqui voc&ecirc; acessa ofertas personalizadas para seu perfil. Complete seu cadastro ou assine outras Empresas para receber outras ofertas';
		} else {
			$mensagem = 'Ofertas publicas';
			$desc = 'Aqui voce encontra ofertas de acesso publico; ou seja, nao sao para um perfil especifico de usuarios. Para receber ofertas personalizadas, escolha e assine novas Empresas.';
		}
		
		switch ($menu) {
			case "signatures" :
				$menu = array (
						'nome' => 'Assinaturas',
						'icone' => 'icone-assinaturas.png',
						'desc' => 'Nesta tela voc&ecirc; escolhe as Empresas que gostaria de receber ofertas personalizadas',
						'tipo' => $tipo 
				);
				break;
			case "home" :
				$menu = array (
						'nome' => $mensagem,
						'icone' => 'icone-ofertas.png',
						'desc' => $desc,
						'tipo' => $tipo 
				);
				break;
			case "viewOffer" :
				$menu = array (
						'nome' => 'Detalhe da Oferta',
						'icone' => 'icone-ofertas.png',
						'desc' => 'detalhes do produto selecionado',
						'tipo' => $tipo 
				);
				break;
			case "detail_purchasing" :
				$menu = array (
						'nome' => 'Finalizando Compra',
						'icone' => 'icone-compras.png',
						'desc' => 'defina quantidade e endereco de entrega para produto',
						'tipo' => $tipo 
				);
				break;
			case "pagamento_mobile" :
				$menu = array (
						'nome' => 'Finalizando Compra',
						'icone' => 'icone-compras.png',
						'desc' => 'defina quantidade e endereco de entrega para produto',
						'tipo' => $tipo 
				);
				break;
			case "minhasCompras" :
				$menu = array (
						'nome' => 'Compras',
						'icone' => 'icone-compras.png',
						'desc' => 'gerenciando suas compras e comentarios de produtos',
						'tipo' => $tipo 
				);
				break;
			case "cad_perfil" :
				$menu = array (
						'nome' => 'Cadastro de Perfil',
						'icone' => 'icone-compras.png',
						'desc' => 'Faca um breve cadastro para aproveitar nossas ofertas',
						'tipo' => $tipo 
				);
				break;
			case "sucesso" :
				$menu = array (
						'nome' => 'Mensagem',
						'icone' => 'icone-ofertas.png',
						'desc' => '',
						'tipo' => $tipo 
				);
				break;
			case "pedidos_assinaturas" :
				$menu = array (
						'nome' => 'Pedidos de Assinaturas',
						'icone' => 'icone-convites.png',
						'desc' => 'alguma empresa gostaria que voce assinasse o seu feed',
						'tipo' => $tipo 
				);
				break;
			case "wishlist" :
				$menu = array (
						'nome' => 'Lista de desejos',
						'icone' => 'icone-desejo.png',
						'desc' => 'Voce deseja algum produto e nao encontrou? faca o teu pedido aqui',
						'tipo' => $tipo 
				);
				break;
			case "dados_cadastrais" :
				$menu = array (
						'nome' => 'Meus dados',
						'icone' => 'icone-dados-cadastrais.png',
						'desc' => 'Alteracao dos seus dados cadastrais',
						'tipo' => $tipo 
				);
				break;
			default :
				$menu = array (
						'nome' => 'HOME',
						'icone' => 'icone-home.png',
						'tipo' => $tipo 
				);
				break;
		}
		
		return $menu;
	}
	private function convites() {
		$user_id = $this->Session->read ( 'userData.User.id' );
		$params = array (
				'CompaniesInvitationsUser' => array (
						'conditions' => array (
								'CompaniesInvitationsUser.user_id' => $user_id,
								'preview' => 'INACTIVE' 
						) 
				) 
		);
		$contador = $this->Utility->urlRequestToGetData ( 'users', 'count', $params );
		$this->Session->write ( 'userData.User.convites', $contador );
		return null;
	}
	private function wishlist() {
		$user_id = $this->Session->read ( 'userData.User.id' );
		$params = array (
				'UsersWishlistCompany' => array (
						'conditions' => array (
								'UsersWishlistCompany.user_id' => $user_id,
								'preview' => 'INACTIVE' 
						) 
				) 
		);
		$contador = $this->Utility->urlRequestToGetData ( 'users', 'count', $params );
		if ($contador > 0) {
			$this->Session->write ( 'userData.User.wishslist', $contador );
		}
		return null;
	}
	
	// verifica se vai mostrar ofertas publicas (se usuario nao tem ofertas)
	private function ofertas() {
		$data = date ( 'Y/m/d' );
		$params = array (
				'OffersUser' => array (
						'conditions' => array (
								'user_id' => $this->Session->read ( 'userData.User.id' ),
								'Offer.status' => 'ACTIVE',
								'Offer.begins_at <= ' => $data,
								'Offer.ends_at >= ' => $data 
						) 
				),
				'Offer' 
		);
		$contador = $this->Utility->urlRequestToGetData ( 'offers', 'count', $params );
		
		if ($this->Session->check ( 'ofertasIds' )) {
			$this->Session->write ( 'HomeOfertas', 'desejos' );
		} else if (! $contador > 0 && ! $this->Session->check ( 'Ofertas-Assinaturas' )) {
			$this->Session->write ( 'HomeOfertas', 'publico' );
		} else {
			$this->Session->write ( 'HomeOfertas', 'personalizado' );
		}
	}
}
?>