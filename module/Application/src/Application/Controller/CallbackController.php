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
    	if(isset($data['MsgType'])) {
    		switch ($data['MsgType'])
    		{
    			case 'text':
    				$resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], time(), $data['Content']);
    				break;
    			case 'news':
    				$articlesStr = '';
    				foreach ($data['Articles'] as $item){
    					if(isset($item['url'])) {
    						$item['url'] = $item['selfUrl'];
    					}
    					$itemStr = sprintf($newsItemTpl, $item['title'], $item['description'], $item['picUrl'], $item['url']);
    					$articlesStr = $articlesStr.$itemStr;
    				}
    				$resultStr = sprintf($newsTpl, $data['ToUserName'], $data['FromUserName'], time(), $data['ArticleCount'], $articlesStr);
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
    	
//     	$appId = $this->params()->fromRoute('appId');
    	
//     	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByAuthorizerAppid($appId);
//     	if($authDoc == null) {
//     		return new ConsoleModel(array('result' => "数据没有绑定"));
//     	}
//     	$websiteId = $authDoc->getWebsiteId();
//     	SiteInfo::setWebsiteId($websiteId);
    	
//     	$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    	
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');    	
    	$wxEncrypt = new Encrypt($sm, $q);
    	
    	$postData = $wxEncrypt->Decrypt($postData);
    	$postObj = simplexml_load_string($postData['msg'], 'SimpleXMLElement', LIBXML_NOCDATA);
    	$wxNumber = $postObj->ToUserName;
    	$msgContent = $postObj->Content;
    	$openId = $postObj->FromUserName;
    	$msgType = $postObj->MsgType;
    	
//     	$messageData = array(
//     		'appId' => $appId,
//     		'openId' => $openId,
//     		'type' => $msgType,
//     	);
    	$returnData = array(
    		'ToUserName' =>$openId,
    		'FromUserName' => $wxNumber,
    	);    	
    	
    	if($openId == 'ocjKfuG0RpHa_PJUMOEB1L9LOkzU'){
    		$returnData['MsgType'] = 'text';
    		$returnData['Content'] = 'from_callback';
    		$result = $this->getResultXml($returnData);
    		$enResult = $wxEncrypt->Encrypt($result);
    		if($enResult['status']) {
    			$resultStr = $enResult['msg'];
    		} else {
    			$resultStr= 'success';
    		}
    		$messageData['content'] = '自己测试发送消息';
    		$messageData['data'] = array();
    		$messageData['data']['res'] = $postObj;
    		$messageDoc = new Message();
    		$messageDoc->exchangeArray($messageData);
    		$dm->persist($messageDoc);
    		$dm->flush();
    		return new ConsoleModel(array('result' => $resultStr));
    	}

    	if($msgType == 'event') {
    		$Event = $postObj->Event;
    		//全网发布事件信息反馈
    		if($wxNumber == 'gh_3c884a361561'){
    			$returnData['MsgType'] = 'text';
    			$Event = (string)$Event;
    			$content = (string)$postObj->Content;
    			$returnData['Content'] = $Event.'from_callback';
    			$result = $this->getResultXml($returnData);
    			$enResult = $wxEncrypt->Encrypt($result);
    			if($enResult['status']) {
    				$resultStr = $enResult['msg'];
    			} else {
    				$resultStr= 'success';
    			}
    			return new ConsoleModel(array('result' => $resultStr));
    		}
    		//全网发布反馈结束
    		if($Event == 'subscribe') {
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
    		}
    	} else {
    		switch ($msgType) {
    			case 'text':
    				$matchData = '';
    				$content = (string)$postObj->Content;
    				if($content == 'TESTCOMPONENT_MSG_TYPE_TEXT'){
    					$matchData = array(
    						'type' => 'text',
    						'content' => 'TESTCOMPONENT_MSG_TYPE_TEXT_callback'
    					);
    					$messageData['content'] = 'TESTCOMPONENT_MSG_TYPE_TEXT';
    					$messageData['type'] = 'text';
    					$messageDoc = new Message();
    					$messageDoc->exchangeArray($messageData);
    					$dm->persist($messageDoc);
    					$dm->flush();
    				}else if($wxNumber == 'gh_3c884a361561'){
    					$content = strstr($str,':');
    					$content = substr($content, 1);
    					
    					/****/
    					$url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=9lwl3oJloAPWXhi2yFxd3NbpZCODJ4WLAvBxy4-XocIKOmb1t7-nxobN12NTYwycs8H4i4KAJbETmSiPFVSgOchMePoNHnmznORJq_Ktano';
    					
    					$postData = array('component_appid' => 'wx570bc396a51b8ff8','authorization_code' =>$content);
    					$postData = json_encode($postData);
    					$ch = curl_init();
    					curl_setopt($ch, CURLOPT_URL, $url);
    					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    					curl_setopt($ch, CURLOPT_POST, 1);
    					curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    					$output = curl_exec($ch);
    					curl_close($ch);
    					
    					/***/
    					$messageData['content'] = 'QUERY_AUTH_CODE';
    					$messageData['type'] = 'text';
    					$messageData['data'] = array('res'=>$postObj,'msg' => $output);
    					$messageDoc = new Message();
    					$messageDoc->exchangeArray($messageData);
    					$dm->persist($messageDoc);
    					$dm->flush();
    					return new ConsoleModel(array('result' => ''));
    				}else {
    					$keywordsDoc = $cdm->createQueryBuilder('Application\Document\Query')
    					->field('keywords')->equals($content)
    					->getQuery()
    					->getSingleResult();
    					
    					$messageData['data']['pre'] = $postData['msg'];
    					$messageData['content'] = $content;
    					$messageData['data']['query'] = $content;
    					if(!is_null($keywordsDoc)) {
    						$keywordsData = $keywordsDoc->getArrayCopy();
    						$matchData = $keywordsData;
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
    							$newsDocs = $cdm->createQueryBuilder('Application\Document\News')
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
    					}
    					$returnData['MsgType'] = $matchData['type'];
    				} else {
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
    		$result = $this->getResultXml($returnData);
    		$enResult = $wxEncrypt->Encrypt($result);
    		if($enResult['status']) {
    			$resultStr = $enResult['msg'];
    		} else {
    			$resultStr= 'success';
    		}
    		$messageData['data']['result'] = $result;
    	}
    	
//     	$messageDoc = new Message();
//     	$messageDoc->exchangeArray($messageData);
    	
//     	$cdm->persist($messageDoc);
//     	$cdm->flush();
    	
    	return new ConsoleModel(array('result' => $resultStr));
    }
    
    public function demoAction()
    {
    	$str = '0';
    	echo mb_detect_encoding($str);
    	die();
//     	$websiteId = '547e6e60ce2350a00d000029';
    	$websiteId = '547d70e3ce2350bc0d000029';
    	SiteInfo::setWebsiteId($websiteId);
    	$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    	$content = '我';
    	$keywordsDoc = $cdm->createQueryBuilder('Application\Document\Query')
					    	->field('keywords')->equals($content)
					    	->getQuery()->getSingleResult();
    	
    	
    	if(!is_null($keywordsDoc)){
    		print_r($content);
    	}else {
    		die('no');
    	}
//     	print_r($keywordsDoc->getArrayCopy());
    	die();
    	
    	return new jsonModel();
    }
}