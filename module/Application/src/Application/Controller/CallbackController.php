<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\WxEncrypt\Encrypt;

use Application\Document\Ticket;
use Application\Document\Messgae;

class CallbackController extends AbstractActionController
{
    public function indexAction()
    {
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');
    	$serviceLocator = $this->getServiceLocator();
    	$wxEncrypt = new Encrypt($serviceLocator, $q);
    	
    	$xml_tree = new \DOMDocument();
    	$xml_tree->loadXML($postData);
    	$array_e = $xml_tree->getElementsByTagName('Encrypt');
    	$encrypt = $array_e->item(0)->nodeValue;
    	$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
    	$from_xml = sprintf($format, $encrypt);
    	$postData = $wxEncrypt->Decrypt($from_xml);
    	
    	$xmlData = new \DOMDocument();
    	$xmlData->loadXML($postData['msg']);
    	
    	$array_info_type = $xmlData->getElementsByTagName('InfoType');
    	$infotype = $array_info_type->item(0)->nodeValue;
    	if($infotype == 'component_verify_ticket') {
    		$array_ticket = $xmlData->getElementsByTagName('ComponentVerifyTicket');
    		$ticket = $array_ticket->item(0)->nodeValue;
    		$ticketDoc = $dm->createQueryBuilder('Application\Document\Ticket')
				    		->field('label')->equals('ticket')
				    		->getQuery()
				    		->getSingleResult();
    		
    		if(!empty($ticketDoc)) {
    			    			    			
    			$ticketDoc->setTicket($ticket);    			
    			
    		}else {
    			
    			$ticketDoc = new Ticket();
    			$data = array(
    				'label' => 'ticket',
    				'value' => $ticket,
    			);
    			$ticketDoc->exchangeArray($data);
    			
    		}
    		$ticketDoc->setMsg(array(
    			'postdata' => $postData,
    		));
    		$currentDateTime = new \DateTime();
    		$ticketDoc->setModified($currentDateTime);
    		$dm->persist($ticketDoc);
    		$dm->flush();
    	}else if($infotype == 'unauthorized'){
    		$array_appId = $xmlData->getElementsByTagName('AuthorizerAppid');
    		$appId = $array_appId->item(0)->nodeValue;
    		
    		$authDoc = $dm->createQueryBuilder('Application\Document\Ticket')
				    		->field('authorizerAppid')->equals($appId)
				    		->getQuery()
				    		->getSingleResult();
    		if($authDoc) {
    			$authDoc->setStatus('inactive');
    			$dm->persist($authDoc);
    			$dm->flush();
    		}
    	}
    	return new ConsoleModel();
    }
    
    public function msgAction()
    {
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	$appId = $this->params()->fromRoute('appId');
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');
    	$wxEncrypt = new Encrypt($sm, $q);
    	
    	$messageDoc = new Messgae();
    	
    	$postDataDe = $wxEncrypt->Decrypt($postData);
    	
    	$messageDoc->setData(array(
    		'data' => array(
    			'message' => $postData,
    			'messageDec' => $postDataDe,
    			'aaa' => 'aaaaaa',
    		),
    	));
    	$messageDoc->setAppId($appId);
    	
    	$dm->persist($messageDoc);
    	$dm->flush();
    	return new ConsoleModel();
    }
}