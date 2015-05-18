<?php

	App::uses('AppController', 'Controller');

	class CompaniesAppController extends AppController {
		public $layout = 'company';
	
		public function beforeFilter() {
			parent::beforeFilter();
			
			$this->checkLogin();
			
			if(!empty($this->request->params['pass'][0]))
				$pass=$this->request->params['pass'][0];
			else 
				$pass=null;
				
			$this->set('menu', $this->menu($this->request->params['action'], $pass));
			
			//verifica se usuario esta logado e retorna paginas ja visualizadas para trabalho de primeiro acesso
			if($this->Session->check('CompanyLoggedIn')){
				$access = 	$this->verifyAccessJson();
				$this->set(compact('access'));	
			}							
		}
		
		
		private function checkLogin(){			
			//verifica se o usuario esta logado
			$session = $this->Session->read('sessionLogado');		
			if(empty($session)){	
				$this->Session->write('sessionLogado', false);	
			}		
					
			if($this->params['plugin']== 'companies'){							
				if($this->params['controller']=='companies' && $this->params['action']=='home' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}
				if($this->params['controller']=='companies' && $this->params['action']=='offers' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}
				if($this->params['controller']=='companies' && $this->params['action']=='addOffer' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}
				if($this->params['controller']=='companies' && $this->params['action']=='publico' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}
				if($this->params['controller']=='companies' && $this->params['action']=='signatures' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}
				if($this->params['controller']=='companies' && $this->params['action']=='listPurchase' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}	
				if($this->params['controller']=='companies' && $this->params['action']=='preferencias' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}	
				if($this->params['controller']=='companies' && $this->params['action']=='cadConvite' && !$session==true){				
					$this->redirect(array('controller' => 'companies', 'plugin'=>'companies', 'action' => 'login'));
				}				
			}
			return null;
		}
		
		
		private function menu($menu, $pass=null){									
					
			switch($menu){								
				case "home":
					$menu = array('nome'=>'Dashboard', 'icone'=>'icone-dashboard.png', 'desc'=>'Gerenciando suas ofertas e pagamentos', 'tipo'=>'home');									
				break;
				case "listPurchase":
					$menu = array('nome'=>'Compras', 'icone'=>'icone-compras.png', 'desc'=>'Gerenciando compras - CONCLUIDAS E PENDENTES', 'tipo'=>'home');
				break;
				case "signatures":
					$menu = array('nome'=>'Assinaturas', 'icone'=>'icone-assinaturas.png', 'desc'=>'Usuarios assinando tuas ofertas', 'tipo'=>'home'); 
				break;
				case "offers":
					$menu = array('nome'=>'Ofertas', 'icone'=>'icone-ofertas.png', 'desc'=>'Gerenciando ofertas cadastradas' , 'tipo'=>'ofertas');
				break;
				case "logoof":
					$menu = array('nome'=>'SAIR', 'icone'=>'icone-sair.png', 'desc'=>'SAINDO DA APLICACAO');
				break;
				case "publico":
					$menu = array('nome'=>'Ofertas', 'icone'=>'icone-publico-alvo.png', 'desc'=>'Publico Alvo', 'tipo'=>'ofertas');
				break;
				case "cadConvite":
					$menu = array('nome'=>'Encontre novos publicos', 'icone'=>'icone-convites.png', 'desc'=>'Use esta tela para descobrir novos publicos DENTRO da base de dados do TRUEONE', 'tipo'=>'home');
				break;
				case "wishlist":
					$menu = array('nome'=>'Lista de desejos para sua empresa', 'icone'=>'icone-desejo.png', 'desc'=>'Aqui voce recebe os pedidos de ofertas de usuarios assiantes da sua empresa', 'tipo'=>'home');
				break;
				case "addOffer":
					switch($pass){
						case 'offerFilters':
							$menu = array('nome'=>'Publico Alvo', 'icone'=>'icone-publico-alvo.png', 'desc'=>'Selecione o PUBLICO ALVO da sua oferta', 'tipo'=>'cadastro');
						break;
						case 'detalhes':
							$menu = array('nome'=>'Cadastro de Ofertas', 'icone'=>'icone-dados-ofertas.png', 'desc'=>'Cadastre as informacoes e as fotos da sua nova oferta', 'tipo'=>'cadastro');
						break;
						case 'resume':
							$menu = array('nome'=>'Pre visualizacao da sua oferta', 'icone'=>'icone-ofertas.png', 'desc'=>'Veja como vai ficar a tua oferta no portal TRUEONE', 'tipo'=>'cadastro');
						break;						
					}
					
				break;
				case "preCadastro":
					switch($pass){
						case 'faq':
							$menu = array('nome'=>'FAQ', 'icone'=>'icone-faq.png', 'desc'=>'Tire suas duvidas e entre em contato', 'tipo'=>'pre-cadastro');
						break;						
						default:
							$menu = array('nome'=>'Pre Cadastro de empresa', 'icone'=>'icone-tabela.png', 'desc'=>'Faca parte do TRUEONE - crie o perfil da sua empresa preenchendo o formulario abaixo', 'tipo'=>'pre-cadastro');
						break;						
					}
					
				break;	
				case "preferencias":
					switch($pass){
						case 'dados-bancarios':
							$menu = array('nome'=>'Dados bancarios', 'icone'=>'icone-dados-bancarios.png', 'desc'=>'Editando seus dados bancarios', 'tipo'=>'preferencias');
						break;
						case 'frete':
							$menu = array('nome'=>'Opcoes de Frete', 'icone'=>'icone-frete.png', 'desc'=>'Informe qual o tipo de frete utilizados nas suas ofertas', 'tipo'=>'preferencias');
						break;
						case 'alterar-senha':
							$menu = array('nome'=>'Alterar Senha', 'icone'=>'icone-tabela.png', 'desc'=>'Informe sua senha antiga e uma nova senha para acesso no trueone', 'tipo'=>'preferencias');
						break;
						default :
							$menu = array('nome'=>'Dados cadastrais', 'icone'=>'icone-dados-cadastrais.png', 'desc'=>'Editando seus dados cadastrais', 'tipo'=>'preferencias');
						break;						
					}
					
				break;						
				default:
					$menu = array('nome'=>'Dashboard', 'icone'=>'icone-dashboard.png', 'desc'=>'Gerenciando suas ofertas e pagamentos', 'tipo'=>'home');
				break;
			}
			
			return $menu;
		}
		
		//verifica primeiro acesso de cada pagina
		private function verifyAccessJson(){
					
			$action    = $this->request->params['action'];			
			$filename  = "js/empresas/json_access/".$this->Session->read('CompanyLoggedIn.Company.id').".json";			
			
			//verifica se arquivo json com acessos ja existe, se arquivo nao existe ele é criado
			if(file_exists($filename)) {				
				$json_access = file_get_contents($filename);
				$array_access = json_decode($json_access, true);
								
				if(!in_array($action, $array_access['Paginas'])) {									
					$qtd = count($this->Session->read('Paginas'));									
					$this->Session->write('Paginas.'.$qtd, $action);
					$array = array('Paginas'=>($this->Session->read('Paginas')));					
					$json = json_encode($array);
					file_put_contents($filename, $json);					
					if($action!="home")return true; else return false;																				
				}else{					
					return false;
				}							
			}else{				
				$array = array('Paginas'=>array());
				$json = json_encode($array);			
				file_put_contents($filename, $json);	
				$this->Session->write('Paginas');
				return true;
			}													
		}			
	}

