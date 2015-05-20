<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\WxEncrypt\Encrypt;

use Application\Document\Ticket;
use Application\Document\Message;
use WxDocument\Query;
use WxDocument\Article;
use WxDocument\User;
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
    	$matchData = '';

    	if($msgType == 'event') {
    		$Event = (string)$postObj->Event;
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
    				$userDoc = new User();
    				$userDoc->exchangeArray($userData);
    				$dm->persist($userDoc);
    				$dm->flush();
    				break;
    			case 'unsubscribe':
    				$openId = $postObj->FromUserName;
    				$cdm->createQueryBuilder('WxDocument\User')
    					->remove()
    					->field('id')->equals($openId)
    					->getQuery()
    					->execute();
    				return new ConsoleModel(array('result' => ''));
    				break;
    			case 'CLICK':
    				$EventKey = (string)$postObj->EventKey;
    				$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
				    				->field('keywords')->equals($EventKey)
				    				->getQuery()
				    				->getSingleResult();
    				$messageData['data']['pre'] = $postData['msg'];
    				$messageData['content'] = $EventKey;
    				$messageData['data']['query'] = $EventKey;
    				if(is_null($keywordsDoc)) {
    					$matchData = false;
    				}else {
    					$keywordsData = $keywordsDoc->getArrayCopy();
    					$matchData = $keywordsData;
    				}
    				break;
    			case 'SCAN':
    				$EventKey = (string)$postObj->EventKey;
    				$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
					    				->field('keywords')->equals($EventKey)
					    				->getQuery()
					    				->getSingleResult();
    				if(is_null($keywordsDoc)) {
    					$matchData = false;
    				}else {
    					$keywordsData = $keywordsDoc->getArrayCopy();
    					$matchData = $keywordsData;
    				}
    				break;
    		}
    	} else {
    		switch ($msgType) {
    			case 'text':
    				$content = (string)$postObj->Content;
    				$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
					    				->field('keywords')->equals($content)
					    				->getQuery()
					    				->getSingleResult();
    				
    				$messageData['data']['pre'] = $postData['msg'];
    				$messageData['content'] = $content;
    				$messageData['data']['query'] = $content;
    				if(is_null($keywordsDoc)) {
    					$settingDoc = $cdm->createQueryBuilder('WxDocument\Setting')->getQuery()->getSingleResult();
    					$settingData = $settingDoc->getArrayCopy();    					
    					if(isset($settingData['defaultReply'])) {
    						$defaultReply = $settingData['defaultReply'];
    						$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
					    						->field('keywords')->equals($defaultReply)
					    						->getQuery()
					    						->getSingleResult();
    						if(!is_null($keywordsDoc)) {
    							$keywordsData = $keywordsDoc->getArrayCopy();
    							$matchData = $keywordsData;
    						}
    					}
   					}else {
   						$keywordsData = $keywordsDoc->getArrayCopy();
   						$matchData = $keywordsData;
   					}
   					if($content == '客服') {
   						$matchData = array('type' => 'transfer_customer_service');
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
    				$articleCount = 0;
    				$newsDocs = $cdm->createQueryBuilder('WxDocument\Article')
    				->field('id')->in($matchData['newsId'])
    				->getQuery()->execute();
    				$articles = array();
    				foreach ($newsDocs as $newsDoc){
    					$articles[] = $newsDoc->getArrayCopy();
    					$articleCount = $articleCount + 1;
    				}
    				$returnData['ArticleCount'] = $articleCount;
    				$returnData['Articles'] = $articles;
    				break;
    			case 'transfer_customer_service':
    				break;
    		}
    		$returnData['MsgType'] = $matchData['type'];
    	}else {
    		$returnData = array(
    			'Content' => '欢迎关注本微信号',
    			'MsgType' => 'text'
    		);
    	}
    	
    	$result = $this->getResultXml($returnData);
    	$enResult = $wxEncrypt->Encrypt($result);
    	if($enResult['status']) {
    		$resultStr = $enResult['msg'];
    	} else {
    		$resultStr= 'success';
    	}
    	$messageData['data']['result'] = $result;
    	
    	$messageDoc = new Message();
    	$messageDoc->exchangeArray($messageData);
    	
    	$cdm->persist($messageDoc);
    	$cdm->flush();
    	
    	return new ConsoleModel(array('result' => $resultStr));
    }
}