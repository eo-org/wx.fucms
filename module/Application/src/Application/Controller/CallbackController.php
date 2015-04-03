<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\WxEncrypt\Encrypt;

use Application\Document\Ticket;
use Application\Document\Message;

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
    
    protected function getXmlNode($xmlData, $tagName)
    {
    	$tat_array = $xmlData->getElementsByTagName($tagName);
    	$value = $tat_array->item(0)->nodeValue;
    	 
    	return $value;
    }
    
    public function msgAction()
    {
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	$appId = $this->params()->fromRoute('appId');
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');
    	$wxEncrypt = new Encrypt($sm, $q);
    	
    	$messageDoc = new Message();
    	
    	$postData = $wxEncrypt->Decrypt($postData);
    	
    	$xmlData = new \DOMDocument();
    	$xmlData->loadXML($postData['msg']);
    	
    	
    	$msgContent = $this->getXmlNode($xmlData, 'Content');//消息内容
    	$openId = $this->getXmlNode($xmlData, 'FromUserName');//用户与公众号间唯一识别码
    	$msgType = $this->getXmlNode($xmlData, 'MsgType');//消息类型
    	$messageData = array(
    		'appId' => $appId,
    		'openId' => $openId,
    		'type' => $msgType,
    	);
    	switch ($msgType) {
    		case 'text':
    			$content = $this->getXmlNode($xmlData, 'Content');
    			$messageData['content'] = $content;
    			break;
    		case 'image':
    			$picUrl = $this->getXmlNode($xmlData, 'PicUrl');
    			$mediaId = $this->getXmlNode($xmlData, 'MediaId');
    			$messageData['picUrl'] = $picUrl;
    			$messageData['mediaId'] = $mediaId;
    			break;
    		case 'voice':
    			$mediaId = $this->getXmlNode($xmlData, 'MediaId');
    			$format = $this->getXmlNode($xmlData, 'Format');
    			$messageData['format'] = $format;
    			$messageData['mediaId'] = $mediaId;
    			break;
    		case 'video':
    			$mediaId = $this->getXmlNode($xmlData, 'MediaId');
    			$thumbMediaId = $this->getXmlNode($xmlData, 'ThumbMediaId');
    			$messageData['mediaId'] = $mediaId;
    			$messageData['thumbMediaId'] = $thumbMediaId;
    			break;
    		case 'shortvideo':
    			$mediaId = $this->getXmlNode($xmlData, 'MediaId');
    			$thumbMediaId = $this->getXmlNode($xmlData, 'ThumbMediaId');
    			$messageData['mediaId'] = $mediaId;
    			$messageData['thumbMediaId'] = $thumbMediaId;
    			break;
    		case 'location':
    			$locationX = $this->getXmlNode($xmlData, 'Location_X');
    			$locationY = $this->getXmlNode($xmlData, 'Location_Y');
    			$scale = $this->getXmlNode($xmlData, 'Scale');
    			$label = $this->getXmlNode($xmlData, 'Label');
    			$messageData['locationX'] = $locationX;
    			$messageData['locationY'] = $locationY;
    			$messageData['scale'] = $scale;
    			$messageData['label'] = $label;
    			break;
    		case 'link':
    			$title = $this->getXmlNode($xmlData, 'Title');
    			$description = $this->getXmlNode($xmlData, 'Description');
    			$url = $this->getXmlNode($xmlData, 'Url');
    			$messageData['title'] = $title;
    			$messageData['description'] = $description;
    			$messageData['url'] = $url;
    			break;    			
    	}
    	$messageDoc->exchangeArray($messageData);
    	$currentDateTime = new \DateTime();
    	$messageDoc->setCreated($currentDateTime);
    	
    	$dm->persist($messageDoc);
    	$dm->flush();
    	return new ConsoleModel();
    }
    
    
}