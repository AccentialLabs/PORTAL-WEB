<?php

App::uses('AppController', 'Controller');

class PortalController extends AppController {

    public $layout = 'portal';

    /**
     * Exibe lista de ofertas publicas
     *
     * @return void
     */
    public function home() {

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
        $limit = 3;
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

        $this->set(compact('offers', 'limit', 'contador', 'update'));
        if (!empty($render))
            $this->render('ajax_ofertas');

        $this->redirect(array(
            'controller' => 'users',
            'action' => 'testa',
            'plugin' => 'users'
        ));
    }

}
