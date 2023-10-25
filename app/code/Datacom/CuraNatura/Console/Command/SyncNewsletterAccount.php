<?php

namespace Datacom\CuraNatura\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncNewsletterAccount extends \Symfony\Component\Console\Command\Command
{
    protected $_conn;
    protected $_customerRepositoryInterface;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
	) {
        $this->_state = $state;
        $this->_conn = $resourceConnection;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;

		parent::__construct();
	}

	/**
	 * Configures the current command.
	 */
	protected function configure()
	{
		$this
			->setName('datacom:syncnewsletteraccount')
			->setDescription('Sincronizza gli account newsletter, esportandoli su Mailup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $conn = $this->_conn->getConnection();

        $rows = $conn->fetchAll('SELECT * FROM mg_newsletter_subscriber WHERE subscriber_status=1 AND subscriber_id NOT IN (SELECT subscriber_id FROM dtm_newsletter_sync)');

        foreach ($rows as $r) {
            try {
                $this->syncAccountData($r['subscriber_email'], intval($r['customer_id']));

                echo "E-MAIL: ".$r['subscriber_email']."\r\n";
                
                $query = 'INSERT INTO dtm_newsletter_sync (subscriber_id) VALUES ('.$r['subscriber_id'].')';

                $conn->query($query);
                //break;
            } catch (\Exception $ex) {
                //throw $ex;
            }
        }
    }

    protected function syncAccountData($email, $customerId) {
        //https://devdocs.magento.com/guides/v2.4/get-started/gs-curl.html
        //https://aureatelabs.com/magento-2/how-to-use-curl-in-magento-2/
        /*if (!empty($customerId)) {
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            echo $customer->getFirstname()."\r\n";
        } else {
            echo $email."\r\n";
        }*/
        $this->postMailUpRequest($email);
    }

    protected function postMailUpRequest($mail/*, $gruppi*/) {
        //list -> lista mail up in cui inserire il messaggio
        //group -> gruppo specifico dentro una lista di mailup (id numerici gruppi, eventualmente separati da virgola)
        //email -> email address
		$pars = array(
			'email' => $mail,
			'list' => 2,
			'group' => /*$gruppi*/146,
		);

		$curlSES=curl_init(); 

		curl_setopt($curlSES,CURLOPT_URL,"a0e4x.emailsp.net/frontend/subscribe.aspx");
		curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curlSES,CURLOPT_HEADER, false); 
		curl_setopt($curlSES, CURLOPT_POST, true);
		curl_setopt($curlSES, CURLOPT_POSTFIELDS,$pars);
		curl_setopt($curlSES, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curlSES, CURLOPT_TIMEOUT,30);

		$result=curl_exec($curlSES);

        curl_close($curlSES);

        //echo "RISPOSTA 1: ".$result."\r\n";
        
        $parsedResponse = explode('href', $result)[1];
        $parsedResponse = explode('"', $parsedResponse)[1];

        $curlSES=curl_init(); 

		curl_setopt($curlSES,CURLOPT_URL,"a0e4x.emailsp.net" . $parsedResponse);
		curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curlSES,CURLOPT_HEADER, false);
		curl_setopt($curlSES, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curlSES, CURLOPT_TIMEOUT,30);

		$result=curl_exec($curlSES);

        curl_close($curlSES);

        //echo "RISPOSTA 2: ".$result."\r\n";
	}
}