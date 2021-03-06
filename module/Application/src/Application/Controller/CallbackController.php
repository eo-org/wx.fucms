<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\WxEncrypt\Encrypt;

use Application\Document\Ticket;
use WxDocument\Message;
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
    	$resultStr = '';
    	
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	
    	$q = $this->params()->fromQuery();
    	$postData = file_get_contents('php://input');
    	$wxEncrypt = new Encrypt($sm, $q);
    	$postData = $wxEncrypt->Decrypt($postData);
    	$postObj = simplexml_load_string($postData['msg'], 'SimpleXMLElement', LIBXML_NOCDATA);
    	 
    	$mpId = $postObj->ToUserName;
    	$openId = $postObj->FromUserName;
    	$msgType = $postObj->MsgType;
    	$messageReply = $sm->get('Application\Service\MessageReply');
    	/***全网发布校验***/
    	if($mpId == 'gh_3c884a361561'){
    		$returnData = array(
    			'FromUserName' => $mpId,
    			'ToUserName' => $openId
    		);
    		if($msgType == 'event'){
    			$Event = $postObj->Event;
				$returnData['MsgType'] = 'text';
				$returnData['Content'] = (string)$Event.'from_callback';
			}else if($msgType == 'text') {
				$content = (string)$postObj->Content;
				if($content == 'TESTCOMPONENT_MSG_TYPE_TEXT'){
					$returnData['MsgType'] = 'text';
					$returnData['Content'] = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
				}else {
					$content = strstr($content,':');
					$content = substr($content, 1);
					$touserData = array(
							'touser' => 'ozy4qt1eDxSxzCr0aNT0mXCWfrDE',
							'msgtype' => 'text',
							'text' => array(
								'content' => $content.'_from_api',
							),
					);
					$pa = $sm->get('Application\Service\PublicityAuth');
					$accessToken = $pa->getComponentAccessToken();
					$url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$accessToken;
						
					$postData = array('component_appid' => 'wx2ce4babba45b702d','authorization_code' => $content);
					$postData = json_encode($postData);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
					$output = curl_exec($ch);
					curl_close($ch);
					
					$tokenResult = json_decode($output, true);
					$token = $tokenResult['authorization_info']['authorizer_access_token'];
					
					$touserData = json_encode($touserData);
					$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $touserData);
					$output = curl_exec($ch);
					curl_close($ch);
					
					$messageData['content'] = 'QUERY_AUTH_CODE';
					$messageData['type'] = 'text';
					$messageData['msg'] = array('res'=>$postObj,'msg' => $tokenResult, 'return' => $output, 'auth_code' => $content);
					$messageDoc = new Message();
					$messageDoc->exchangeArray($messageData);
					$dm->persist($messageDoc);
					$dm->flush();
					
					return new ConsoleModel(array('result' => ''));
				}
			}
			$result = $this->getResultXml($returnData);
			$enResult = $wxEncrypt->Encrypt($result);
			if($enResult['status']) {
				$resultStr = $enResult['msg'];
			} else {
				$resultStr= 'success';
			}
			return new ConsoleModel(array('result' => $resultStr));
    	}
    	/***全网发布校验结束**/
    	$appId = $this->params()->fromRoute('appId');
    	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByAuthorizerAppid($appId);
    	if($authDoc == null) {
    		return new ConsoleModel(array('result' => "数据没有绑定"));
    	}
    	$websiteId = $authDoc->getWebsiteId();
    	SiteInfo::setWebsiteId($websiteId);
    	$replyResult = array('status' => false);    	
    	if($msgType == 'event') {
    		$Event = (string)$postObj->Event;
    		switch ($Event) {
    			case 'subscribe':
    				$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    				$pa = $sm->get('Application\Service\PublicityAuth');
    				$authorizerAccessToken = $pa->getAuthorizerAccessToken($websiteId);
    				$getUserInfoUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$authorizerAccessToken.'&openid='.$openId.'&lang=zh_CN';
    				$ch = curl_init();
    				curl_setopt($ch, CURLOPT_URL, $getUserInfoUrl);
    				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    				curl_setopt($ch, CURLOPT_HEADER, 0);
    				$output = curl_exec($ch);
    				curl_close($ch);
    				$userData = json_decode($output, true);
    				//获取用户信息结束
    				
    				$settingDoc = $cdm->createQueryBuilder('WxDocument\Setting')->getQuery()->getSingleResult();
    				$settingData = $settingDoc->getArrayCopy();
    				
    				$userDoc = new User();
    				$userDoc->exchangeArray($userData);
    				$cdm->persist($userDoc);
    				$cdm->flush();
    				if($settingData['isAddFriendReplyOpen']) {
    					$xml = $messageReply->getReply($mpId, $openId, $settingData['addFriendAutoreplyInfo']);
    				}else {
    					return new ConsoleModel(array('result' => ''));
    				}    				
    				break;
    			case 'unsubscribe':
    				$cdm = $this->getServiceLocator()->get('CmsDocumentManager');
    				$openid = (string)$openId;
    				$cdm->createQueryBuilder('WxDocument\User')
						->remove()
						->field('openid')->equals($openid)
						->getQuery()
						->execute();
    				return new ConsoleModel(array('result' => 'success'));
    				break;
    			case 'CLICK':
    				$EventKey = (string)$postObj->EventKey;
    				$xml = $messageReply->getReply($mpId, $openId, $EventKey);
    				break;
    			case 'SCAN':
    				$EventKey = (string)$postObj->EventKey;
    				$xml = $messageReply->getReply($mpId, $openId, $EventKey);
    				break;
    		}
    	} else if($msgType == 'text') {
    		
    		$keyword = (string)$postObj->Content;
    		$xml = $messageReply->getReply($mpId, $openId, $keyword);
    	}
		$enResult = $wxEncrypt->Encrypt($xml);
		if($enResult['status']) {
			$resultStr = $enResult['msg'];
		}    	
    	return new ConsoleModel(array('result' => $resultStr));
    }
}