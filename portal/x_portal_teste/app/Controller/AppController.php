<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

class AppController extends Controller {
	
	public $ext = '.phtml';
	public $components = array('Session','Utility');
	public $helpers = array('Html','Form','Session');
	
	
	/**
	 * ID do app fornecido pelo Facebook
	 * @var string
	 */
	protected $appId = '447466838647107';

	/**
	 * Token do app fornecido pelo Facebook
	 * @var string
	 */
	protected $appSecret = 'b432e357dd19491aa8d45acd7074b2f6';

	public function beforeFilter() {		
		//verifica erro
		if($this->modelClass=='CakeError'){
			//$this->redirect(array('controller' => 'portal', 'action' => 'home', 'plugin' => false));
                        $this->redirect(array('controller' => 'users', 'action' => 'testa', 'plugin' => 'users'));
		}		
		$pluginName = ucwords($this->request->params['plugin']);
		$this->set(compact('pluginName'));							
	}

}?>