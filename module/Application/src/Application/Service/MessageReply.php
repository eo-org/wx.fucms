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
		
		$this->customerServiceTpl = "<xml>
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
	
	protected function _getTextXml($mpId, $openId, $content)
	{
		return sprintf($this->textTpl, $openId, $mpId, time(), $content);
	}
	
	protected function _getNewsXml($mpId, $openId, $articleList)
	{
		$articleListStr = '';
		foreach($articleList as $item) {
			$itemStr = sprintf($this->newsItemTpl, $item['title'], $item['description'], $item['coverUrl'], $item['url']);
			$articleListStr.= $itemStr;
		}
		return sprintf($this->newsTpl, $openId, $mpId, time(), count($articleList), $articleListStr);
	}
	
	protected function _getCustomerServiceXml($mpId, $openId)
	{
		return sprintf($this->customerServiceTpl, $openId, $mpId, time());
	}
	
	/**
	 * 
	 * @param String $mpId
	 * @param String $openId
	 * @param String $keyword
	 * @return string
	 * @example $mpId is the original id of each Media Platform, $openId is the user id who sends the message, $keyword is the message
	 */
	public function getReply($mpId, $openId, $keyword)
	{
		$keyword = (string)$keyword;
		$cdm = $this->sm->get('CmsDocumentManager');
		
		$replyType = 'text';
		$reply = '欢迎使用本服务号为您服务';
		
		$xml = "";
		
		if($keyword == '10000') {
			//qr code set 10000 equals customer service
			$xml = $this->_getCustomerServiceXml($mpId, $openId);
		} else {
			$queryDoc = $cdm->createQueryBuilder('WxDocument\Query')
				->field('keywords')->equals($keyword)
				->getQuery()
				->getSingleResult();
			if(!is_null($queryDoc)) {
				$replyType = $queryDoc->getType();
				$reply = $queryDoc->getContent();
			}
		}
		switch ($replyType){
			case 'text':
				$xml = $this->_getTextXml($mpId, $openId, $reply);
				break;
			case 'news':
				$articleCount = 0;
				$newsIdArr = $queryDoc->getNews();
				
				$idArr = array();
				foreach($newsIdArr as $newsItem) {
					$idArr[] = $newsItem['id'];
				}
				
				$newsDocs = $cdm->createQueryBuilder('WxDocument\Article')
					->field('id')->in($idArr)
					->getQuery()
					->execute();
				$articleList = array();
				foreach ($newsDocs as $newsDoc){
					$articleList[] = $newsDoc->getArrayCopy();
				}
				$xml = $this->_getNewsXml($mpId, $openId, $articleList);
				break;
		}
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
					$newsData = $newsDoc->getArrayCopy();
					if(!isset($newsData['url'])){
						$newsData['url'] = $newsData['selfUrl'];
					}
					$articles[] = $newsData;
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