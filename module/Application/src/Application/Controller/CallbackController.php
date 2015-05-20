<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\WxEncrypt\Encrypt;

use Application\Document\Ticket;
use Application\Document\Message;
use WxDocument\Query;
use WxDocument\Article;
use Application\Document\Auth;

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
    			$ticketDoc->setValue($ticket);    			
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
    	} else if($infotype == 'unauthorized') {
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
    	$newsItemTpl = '<item>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<PicUrl><![CDATA[%s]]></PicUrl>
						<Url><![CDATA[%s]]></Url>
					</item>';
    	$newsTpl = '<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>%s</ArticleCount>
					<Articles>%s</Articles>
					</xml>';
    	$serviceTpl = '<xml>
					     <ToUserName><![CDATA[%s]]></ToUserName>
					     <FromUserName><![CDATA[%s]]></FromUserName>
					     <CreateTime>%s</CreateTime>
					     <MsgType><![CDATA[transfer_customer_service]]></MsgType>
					 </xml>';
    	if(isset($data['MsgType'])) {
    		switch ($data['MsgType'])
    		{
    			case 'text':
    				$resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], time(), $data['Content']);
    				break;
    			case 'news':
    				$articlesStr = '';
    				foreach ($data['Articles'] as $item){
    					$itemStr = sprintf($newsItemTpl, $item['title'], $item['description'], $item['coverUrl'], $item['url']);
    					$articlesStr = $articlesStr.$itemStr;
    				}
    				$resultStr = sprintf($newsTpl, $data['ToUserName'], $data['FromUserName'], time(), $data['ArticleCount'], $articlesStr);
    				break;
    			case 'transfer_customer_service':
    				$resultStr = sprintf($serviceTpl, $data['ToUserName'], $data['FromUserName'], time());
    				break;
    		}
    	}
    	return $resultStr;
    }
    
    public function msgAction()
    {
    	$resultStr = 'success';
    	
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');    	
    	$appId = $this->params()->fromRoute('appId');
    	
    	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByAuthorizerAppid($appId);
    	if($authDoc == null) {
    		return new ConsoleModel(array('result' => "数据没有绑定"));
    	}
    	$websiteId = $authDoc->getWebsiteId();
    	SiteInfo::setWebsiteId($websiteId);
    	
    	$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    	
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');    	
    	$wxEncrypt = new Encrypt($sm, $q);
    	
    	$postData = $wxEncrypt->Decrypt($postData);
    	$postObj = simplexml_load_string($postData['msg'], 'SimpleXMLElement', LIBXML_NOCDATA);
    	
//     	$wxNumber = $postObj->ToUserName;
//     	$msgContent = $postObj->Content;
//     	$openId = $postObj->FromUserName;
    	$msgType = $postObj->MsgType;
    	$messageData = array(
    		'openId' => $openId,
    	);
    	$replyResult = array('status' => false);
    	$messageReply = $sm->get('Application\Service\MessageReply');
    	if($msgType == 'event') {
    		$Event = (string)$postObj->Event;
    		$messageData['type'] = $Event;
    		switch ($Event) {
    			case 'subscribe':    				
    				$openId = $postObj->FromUserName;
    				$pa = $this->getServiceLocator()->get('Application\Service\PublicityAuth');
    				$authorizerAccessToken = $pa->getAuthorizerAccessToken($websiteId);
    				$getUserInfoUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$authorizerAccessToken.'&openid='.$openId.'&lang=zh_CN';
    				 
    				$ch = curl_init();
    				curl_setopt($ch, CURLOPT_URL, $getUserInfoUrl);
    				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    				curl_setopt($ch, CURLOPT_HEADER, 0);
    				$output = curl_exec($ch);
    				curl_close($ch);
    				$userData = json_decode($output, true);    				
    				break;
    			case 'CLICK':
    				$EventKey = (string)$postObj->EventKey;
    				$replyResult = $messageReply->getKeywordReply($postObj, $EventKey);
    				$messageData['content'] = $EventKey;
    				break;
    			case 'SCAN':
    				$EventKey = (string)$postObj->EventKey;
    				$replyResult = $messageReply->getKeywordReply($postObj, $EventKey);
    				$messageData['content'] = $EventKey;
    				break;
    		}
    	} else {
    		$messageData['type'] = $msgType;
    		switch ($msgType) {    			
    			case 'text':    				
    				$content = (string)$postObj->Content;
    				$replyResult = $messageReply->getKeywordReply($postObj, $content);
    				break;
    		}
    	}    	
    	if($replyResult['status']) {
    		$result = $this->getResultXml($replyResult['data']);
    		$enResult = $wxEncrypt->Encrypt($result);
    		if($enResult['status']) {
    			$resultStr = $enResult['msg'];
    		}
    	}
    	$messageDoc = new Message();
    	$messageDoc->exchangeArray($messageData);    	
    	$cdm->persist($messageDoc);
    	$cdm->flush();
    	
    	return new ConsoleModel(array('result' => $resultStr));
    }
}