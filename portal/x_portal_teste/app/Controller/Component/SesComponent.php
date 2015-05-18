<?php

//require 'Vendor/aws.phar';
//App::import('Vendor', 'aws.phar');
//App::import('Vendor', 'aws');
//App::uses('aws.phar', 'Vendor');

use Aws\Ses\SesClient;

/**
 * Description of SesComponent
 *
 * @author user
 */
class SesComponent extends Component {

    private $client;

    public function sendSesEmail() {

//        $this->client = SesClient::factory(array(
//                    'key' => 'AKIAJN32XAXLBOLPESVA',
//                    'secret' => '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',
//                    'region' => 'us-east-1'
//        ));
//        //$ses = new AmazonSES();
//
//        $msg = array();
//        $msg['Source'] = "contato@adventa.com.br";
//        //ToAddresses must be an array
//        $msg['Destination']['ToAddresses'][] = "contato@adventa.com.br";
//
//        $msg['Message']['Subject']['Data'] = "Subject";
//        $msg['Message']['Subject']['Charset'] = "UTF-8";
//
//        $msg['Message']['Body']['Text']['Data'] = "Text data of email";
//        $msg['Message']['Body']['Text']['Charset'] = "UTF-8";
//
//        $msg['Message']['Body']['Html']['Data'] = "Body html data";
//        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";
//        try {
//            $result = $this->client->sendEmail($msg);
//            //save the MessageId which can be used to track the request
//            $msg_id = $result->get('MessageId');
//            return "MessageId: $msg_id";
//        } catch (Exception $e) {
//            //An error happened and the email did not get sent
//            return 'ERROR: ' . $e->getMessage();
//        }

        try {
            $ses = SesClient::factory(array(
                        'key' => 'AKIAJN32XAXLBOLPESVA',
                        'secret' => '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',
                        'region' => Region::US_EAST_1
            ));


            $ses->verifyEmailIdentity(array(
                'EmailAddress' => 'contato@adventa.com.br'
            ));
            return "ok";
        } catch (Exception $e) {
            return $e->getMessage();
        }
        echo "d";
    }

}

?>
