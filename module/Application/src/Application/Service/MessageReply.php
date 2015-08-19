<?php
namespace Application\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use WxDocument\Query;
use WxDocument\Setting;
use WxDocument\Article;

class MessageReply implements ServiceLocatorAwareInterface
{
	protected $sm;
	
	protected $textTpl;
	
	protected $newsTpl;
	
	protected $newsItemTpl;
	
	protected $customerServiceTpl;
	
	public function __construct()
	{
		$this->textTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			</xml>";
		
		$this->newsTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[news]]></MsgType>
			<ArticleCount>%s</ArticleCount>
			<Articles>%s</Articles>
			</xml>";
		
		$this->newsItemTpl = "<item>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<PicUrl><![CDATA[%s]]></PicUrl>
			<Url><![CDATA[%s]]></Url>
			</item>";
		
		$customerServiceTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[transfer_customer_service]]></MsgType>
			</xml>";
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}
	
	public function getServiceLocator()
	{
		return $this->sm;
	}
	
	protected function _getTextXml($appId, $openId, $content)
	{
// 		$resultStr = '';
// 		switch ($type)
// 		{
// 			case 'text':
// 				$resultStr = 
// 				break;
// 			case 'news':
// 				$articlesStr = '';
// 				foreach ($data['articleList'] as $item){
// 					$itemStr = sprintf($this->newsItemTpl, $item['title'], $item['description'], $item['coverUrl'], $item['url']);
// 					$articlesStr .= $itemStr;
// 				}
// 				$resultStr = sprintf($this->newsTpl, $openId, $appId, time(), $data['articleCount'], $articlesStr);
// 				break;
// 			case 'transfer_customer_service':
// 				$resultStr = sprintf($this->customerServiceTpl, $openId, $appId, time());
// 				break;
// 		}
		return sprintf($this->textTpl, $openId, $appId, time(), $content);
	}
	
	protected function _getNewsXml($appId, $openId, $articleList)
	{
		$articlesStr = '';
		foreach ($articleList as $item){
			$itemStr = sprintf($this->newsItemTpl, $item['title'], $item['description'], $item['coverUrl'], $item['url']);
			$articlesStr .= $itemStr;
		}
		$resultStr = sprintf($this->newsTpl, $openId, $appId, time(), count($articleList), $articlesStr);
	}
	
	protected function _getCustomerServiceXml($appId, $openId)
	{
		return sprintf($this->customerServiceTpl, $openId, $appId, time());
	}
	
	public function getReply($appId, $openId, $keyword)
	{
		$keyword = (string)$keyword;
		$cdm = $this->sm->get('CmsDocumentManager');
		
		$msgType = 'text';
		$reply = $keyword;
		
		$xml = "";
		
		if($keyword == '10000') {
			//qr code set 10000 equals customer service
			//$msgType = 'transfer_customer_service';
			$xml = $this->_getCustomerServiceXml($appId, $openId);
		} else {
			$queryDoc = $cdm->createQueryBuilder('WxDocument\Query')
				->field('keywords')->equals($keyword)
				->getQuery()
				->getSingleResult();
			if(!is_null($queryDoc)) {
// 				$keywordsData = array(
// 					'type' => 'text',
// 					'data' => '感谢您关注本公众号',
// 				);
// 			} else {
				$msgType = $queryDoc->getType();
				$reply = $queryDoc->getContent();
				
				
// 				$keywordsData = $keywordsDoc->getArrayCopy();
			}
		}
		//$wxNumber = $postObj->ToUserName;
		//$openId = $postObj->FromUserName;
		// 		if($keywordsData) {
		
		
// 		$returnData = array(
// 			'ToUserName' => $openId,
// 			'FromUserName' => $wxNumber,
// 			'MsgType' => $keywordsData['type'],
// 		);
		switch ($msgType){
			case 'text':
				//$returnData['Content'] = $keywordsData['content'];
				
				
				//$returnData['articleCount'] = $articleCount;
				//$returnData['articleList'] = $articles;
				//$reply = 
				
				$xml = $this->_getTextXml($appId, $openId, $reply);
				
				break;
			case 'news':
				$articleCount = 0;
// 				if(isset($keywordsData['news'])) {
// 					$newsIdArr = array();
// 					foreach ($keywordsData['news'] as $newsItem) {
// 						$newsIdArr[] = $newsItem['id'];
// 					}
// 				}else {
// 					$newsIdArr = $keywordsData['newsId'];
// 				}
				
				$newsIdArr = $queryDoc->getNewsId();
				
				
				$newsDocs = $cdm->createQueryBuilder('WxDocument\Article')
					->field('id')->in($newsIdArr)
					->getQuery()
					->execute();
				$articleList = array();
				foreach ($newsDocs as $newsDoc){
					$articleList[] = $newsDoc->getArrayCopy();
					//$articleCount = $articleCount + 1;
				}
				//$returnData['articleCount'] = $articleCount;
				//$returnData['articleList'] = $articles;
				
				$sml = $this->_getNewsXml($appId, $openId, $articleList);
				
				break;
		}
// 		$result = array(
// 			'status' => true,
// 			'data' => $returnData
// 		);
		
		
		// 		}else {
		// 			$result = array(
		// 				'status' => true,
		// 				'data' => array(
		// 					'ToUserName' =>$openId,
		// 					'FromUserName' => $wxNumber,
		// 					'MsgType' => 'text',
		// 					'Content' => '感谢您关注本微信号!'
		// 				),
		// 			);
		// 		}
		return $xml;
	}
	
	public function getKeywordReply($postObj, $keyword)
	{
		$keyword = (string)$keyword;
		$cdm = $this->sm->get('CmsDocumentManager');
		
		
		if($keyword == '10000'){
			$keywordsData = array(
				'type' => 'transfer_customer_service'
			);
		} else {
			$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
				->field('keywords')->equals($keyword)
				->getQuery()
				->getSingleResult();
			$keywordsData = '';
			if(is_null($keywordsDoc)) {
// 				$settingDoc = $cdm->createQueryBuilder('WxDocument\Setting')->getQuery()->getSingleResult();
// 				if(!is_null($settingDoc)) {
// 					$settingData = $settingDoc->getArrayCopy();
// 					if(isset($settingData['defaultReply'])) {
// 						$defaultReply = $settingData['defaultReply'];
// 						$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
// 							->field('keywords')->equals($defaultReply)
// 							->getQuery()
// 							->getSingleResult();
// 						if(!is_null($keywordsDoc)) {
// 							$keywordsData = $keywordsDoc->getArrayCopy();
// 						}
// 					}
// 				}

				$keywordsData = array(
					'type' => 'text',
					'data' => '感谢您关注本公众号',
				);
			} else {
				$keywordsData = $keywordsDoc->getArrayCopy();
			}
		}
		$wxNumber = $postObj->ToUserName;
		$openId = $postObj->FromUserName;
// 		if($keywordsData) {

		
		$returnData = array(
			'ToUserName' => $openId,
			'FromUserName' => $wxNumber,
			'MsgType' => $keywordsData['type'],
		);
		switch ($keywordsData['type']){
			case 'text':
				$returnData['Content'] = $keywordsData['content'];
				break;
			case 'news':
				$articleCount = 0;
				if(isset($keywordsData['news'])) {
					$newsIdArr = array();
					foreach ($keywordsData['news'] as $newsItem) {
						$newsIdArr[] = $newsItem['id'];
					}
				}else {
					$newsIdArr = $keywordsData['newsId'];
				}
				$newsDocs = $cdm->createQueryBuilder('WxDocument\Article')
								->field('id')->in($newsIdArr)
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
		$result = array(
			'status' => true,
			'data' => $returnData
		);
		
		
// 		}else {
// 			$result = array(
// 				'status' => true,
// 				'data' => array(
// 					'ToUserName' =>$openId,
// 					'FromUserName' => $wxNumber,
// 					'MsgType' => 'text',
// 					'Content' => '感谢您关注本微信号!'
// 				),
// 			);
// 		}		
		return $result;
	}
	
	public function getSubscribeReply($postObj)
	{
		$cdm = $this->sm->get('CmsDocumentManager');
		
	}
}