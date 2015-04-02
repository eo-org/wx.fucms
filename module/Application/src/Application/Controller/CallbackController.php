<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\Session\User;
use Application\Document\Admin as Admin;
use Application\Document\Ticket;
use Application\WxEncrypt\Encrypt;

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
    			
    		}
    		$ticketDoc->setMsg(array(
    			'postdata' => $postData,
    		));
    		$currentDateTime = new \DateTime();
    		$ticketDoc->setModified($currentDateTime);
    		$dm->persist($ticketDoc);
    		$dm->flush();
    	}else {
    		
    	}
    	
    	
    	
//     	if(is_null($ticketDoc)){
//     		if($postData){
    			
//     			$doc->setTicket($ticket);
//     			$doc->setData(array(
//     				'data' => $postData,
//     			));
//    			}else {
//    				$doc->setData(array('q'=>$q));
//    			}
//     	}else {
//     		$ticketDoc = new Ticket();
//     		$data = array(
//     			'label'=>'ticket',
//     			'appSecret' => '0c79e1fa963cd80cc0be99b20a18faeb',
//     			'data' => $postData,
//     		);
//     		$doc->exchangeArray($data);
//     	}
//     	$currentDateTime = new \DateTime();
//     	$doc->setModified($currentDateTime);
//     	$dm->persist($doc);
//     	$dm->flush();
    	return new ConsoleModel();
    }
    
    public function loginAction()
    {
    	
    }
}