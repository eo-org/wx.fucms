<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\WxEncrypt\Encrypt;

use Application\Document\Ticket;
use Application\Document\Message;
use Application\Document\Query;

use Application\SiteInfo;

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
    		
    		if($ticketDoc) {    			    			    			
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
    			'time' => time(),
    		));
    		$currentDateTime = new \DateTime();
    		$ticketDoc->setModified($currentDateTime);
    		$dm->persist($ticketDoc);    		
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
    		}
    	}
    	$dm->flush();
    	return new ConsoleModel();
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
    				$resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], time(), $data['Content']);
    				break;
    		}
    	}
    	return $resultStr;
    }
    
    public function msgAction()
    {
    	
    	
    	
    	
    	$resultStr = 'success';
    	
//     	$demo = '<xml>
//                 <ToUserName><![CDATA[ocjKfuG0RpHa_PJUMOEB1L9LOkzU]]></ToUserName>
//                 <FromUserName><![CDATA[wx536a9272e58807e7]]></FromUserName>
//                 <CreateTime>1428374648</CreateTime>
//                 <MsgType><![CDATA[text]]></MsgType>
//                 <Content><![CDATA[text]]></Content>
//                 </xml>';
    	
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	$appId = $this->params()->fromRoute('appId');
    	
    	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByAuthorizerAppid($appId);
    	if($authDoc == null) {
    		return new ConsoleModel(array('result' => "数据没有绑定"));
    	}
    	$websiteId = $authDoc->getWebsiteId();
//     	$websiteId = '547d70e3ce2350bc0d000029';
    	SiteInfo::setWebsiteId($websiteId);
    	
    	$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    	
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');
    	$wxEncrypt = new Encrypt($sm, $q);
    	
    	
    	$messageDoc = new Message();
    	$postData = $wxEncrypt->Decrypt($postData);
    	$postObj = simplexml_load_string($postData['msg'], 'SimpleXMLElement', LIBXML_NOCDATA);
    	$wxNumber = $postObj->ToUserName;
    	$msgContent = $postObj->Content;
    	$openId = $postObj->FromUserName;
    	$msgType = $postObj->MsgType;
    	
    	$messageData = array(
    		'appId' => $appId,
    		'openId' => $openId,
    		'type' => $msgType,
    	);
    	$returnData = array(
    		'ToUserName' =>$openId,
    		'FromUserName' => $wxNumber,
    	);

    	if($msgType == 'event') {
    		
    	} else {
    		switch ($msgType) {
    			case 'text':
    				$content = $postObj->Content;
    				$messageData['content'] = $content;    				
    					$keywordsDoc = $cdm->createQueryBuilder('Application\Document\Query')
					    					->field('keywords')->equals($content)
					    					->getQuery()->getSingleResult();
    					if(!is_null($keywordsDoc)){
    						$messageData['data'] = array(
    							'key' => $content,
    							'websiteId' => $websiteId,
    							'sss' => $keywordsDoc->getId(),
    						);
    					}
    				
    				
    				$matchData = '';
    				if(!is_null($keywordsDoc)) {
    					$matchData['type'] = 'text';
    					$matchData['Content'] = $keywordsDoc->getType();
    				}else {
    					$matchData['type'] = 'text';
    					$matchData['Content'] = $websiteId;
    				}
    				if($matchData){
    					switch ($matchData['type']) {
    						case 'text':
    							$returnData['Content'] = $matchData['content'];
    							break;
    						case 'image':
    							$returnData['MediaId'] = $matchData['mediaId'];
    							break;
    						case 'voice':
    							$returnData['MediaId'] = $matchData['mediaId'];
    							break;
    						case 'video':
    							$returnData['MediaId'] = $matchData['mediaId'];
    							$returnData['Title'] = $matchData['title'];
    							$returnData['Description'] = $matchData['description'];
    							break;
    						case 'news':
    							$returnData['ArticleCount'] = count($matchData['articles']);
    							$returnData['Articles'] = $matchData['articles'];
    							break;
    					}
    					$returnData['MsgType'] = $matchData['type'];
    				}else {
    					$returnData['Content'] = '热烈欢迎您/:handclap/:handclap/:handclap鼓掌关注武汉长江联合官方微信账号，我们只提供领先的信息化解决方案，如果您对建站有任何的疑问，可随时咨询，我们将及时报以最专业的答复，您的十分满意是我们唯一的服务宗旨mo-得意~~';
    					$returnData['MsgType'] = 'text';
    				}
    				break;
    			case 'image':
    				$picUrl = $postObj->PicUrl;
    				$mediaId = $postObj->MediaId;
    				$messageData['picUrl'] = $picUrl;
    				$messageData['mediaId'] = $mediaId;
    				break;
    			case 'voice':
    				$mediaId = $postObj->MediaId;
    				$format = $postObj->Format;
    				$messageData['format'] = $format;
    				$messageData['mediaId'] = $mediaId;
    				break;
    			case 'video':
    				$mediaId = $postObj->MediaId;
    				$thumbMediaId = $postObj->ThumbMediaId;
    				$messageData['mediaId'] = $mediaId;
    				$messageData['thumbMediaId'] = $thumbMediaId;
    				break;
    			case 'shortvideo':
    				$mediaId = $postObj->MediaId;
    				$thumbMediaId = $postObj->ThumbMediaId;
    				$messageData['mediaId'] = $mediaId;
    				$messageData['thumbMediaId'] = $thumbMediaId;
    				break;
    			case 'location':
    				$locationX = $postObj->Location_X;
    				$locationY = $postObj->Location_Y;
    				$scale = $postObj->Scale;
    				$label = $postObj->Label;
    				$messageData['locationX'] = $locationX;
    				$messageData['locationY'] = $locationY;
    				$messageData['scale'] = $scale;
    				$messageData['label'] = $label;
    				break;
    			case 'link':
    				$title = $postObj->Title;
    				$description = $postObj->Description;
    				$url = $postObj->Url;
    				$messageData['title'] = $title;
    				$messageData['description'] = $description;
    				$messageData['url'] = $url;
    				break;
    		}
    	}
    	$result = $this->getResultXml($returnData);
    	$enResult = $wxEncrypt->Encrypt($result);
    	if($enResult['status']) {
    		$resultStr = $enResult['msg'];
    	} else {
    		$resultStr= 'success';
    	}
    	$messageDoc->exchangeArray($messageData);
    	$currentDateTime = new \DateTime();
    	$messageDoc->setCreated($currentDateTime);
    	
    	$cdm->persist($messageDoc);
    	$cdm->flush();
    	
    	return new ConsoleModel(array('result' => $resultStr));
    }
    
    public function demoAction()
    {
    	
    	$websiteId = '547e6e60ce2350a00d000029';
//     	$websiteId = '547d70e3ce2350bc0d000029';
    	SiteInfo::setWebsiteId($websiteId);
    	$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    	$keywordsDoc = $cdm->createQueryBuilder('Application\Document\Query')
					    	->field('keywords')->equals('我')
					    	->getQuery()->getSingleResult();
    	
    	
    	print_r($keywordsDoc->getArrayCopy());
    	die();
    	
    	return new jsonModel();
    }
}