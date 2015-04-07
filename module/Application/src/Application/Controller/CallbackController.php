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
    
    protected function getResultXml($data)
    {
    	$resultStr = 'success';
    	$textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
    	if(isset($data['MsgType'])) {
    		switch ($data['MsgType'])
    		{
    			case 'text':
    				$resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], time(), $data['MsgType'], $data['Content']);
    				break;
    		}    		
    	}    	
    	return $resultStr;
    }
    
    public function msgAction()
    {
    	$data = array();
    	$result = 'success';
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	$appId = $this->params()->fromRoute('appId');
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');
    	$wxEncrypt = new Encrypt($sm, $q);
    	
    	$messageDoc = new Message();
    	$data['postData'] = $postData;
    	$postData = $wxEncrypt->Decrypt($postData);
    	$data['dePostData'] = $postData;
    	$xmlData = new \DOMDocument();
    	$xmlData->loadXML($postData['msg']);
    	
    	$wxNumber = $this->getXmlNode($xmlData, 'ToUserName');
    	$msgContent = $this->getXmlNode($xmlData, 'Content');//��Ϣ����
    	$openId = $this->getXmlNode($xmlData, 'FromUserName');//�û��빫�ںż�Ψһʶ����
    	$msgType = $this->getXmlNode($xmlData, 'MsgType');//��Ϣ����
    	
    	$messageData = array(
    		'appId' => $appId,
    		'openId' => $openId,
    		'type' => $msgType,
    	);
    	$returnData = array(
    		'ToUserName' =>$openId,
    		'FromUserName' => $wxNumber,
    	);
    	if($msgType != 'event'){
    		
    	}else {
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
    	}
    	
    	$returnData['Content'] = '���һ�ӭ��mo-����mo-����mo-���ƹ�ע�人�������Ϲٷ�΢���˺ţ�����ֻ�ṩ���ȵ���Ϣ�����������������Խ�վ���κε����ʣ�����ʱ��ѯ�����ǽ���ʱ������רҵ�Ĵ𸴣�����ʮ������������Ψһ�ķ�����ּmo-����~~';
    	$returnData['MsgType'] = 'text';
    	$result = $this->getResultXml($returnData);
    	$data['result'] = $result;    	 
    	$resultStr = $wxEncrypt->Encrypt($result);
    	$data['resultStr'] = $resultStr;
    	
    	$messageDoc->exchangeArray($messageData);
    	$currentDateTime = new \DateTime();
    	$messageDoc->setCreated($currentDateTime);
    	$messageDoc->setData($data);
    	
    	$dm->persist($messageDoc);
    	$dm->flush();
    	
    	return new ConsoleModel(array('result' => $resultStr));
    }
    
    
}