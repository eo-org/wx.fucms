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
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}
	
	public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function getKeywordReply($postObj, $keyword)
	{
		$keyword = (string)$keyword;
		$cdm = $this->sm->get('CmsDocumentManager');
		
		
		if($keyword == '10000'){
			$keywordsData = array(
				'type' => 'transfer_customer_service'
			);
		}else {
			$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
							->field('keywords')->equals($keyword)
							->getQuery()
							->getSingleResult();
			$keywordsData = '';
			if(is_null($keywordsDoc)) {
				$settingDoc = $cdm->createQueryBuilder('WxDocument\Setting')->getQuery()->getSingleResult();
				if(!is_null($settingDoc)) {
					$settingData = $settingDoc->getArrayCopy();
					if(isset($settingData['defaultReply'])) {
						$defaultReply = $settingData['defaultReply'];
						$keywordsDoc = $cdm->createQueryBuilder('WxDocument\Query')
						->field('keywords')->equals($defaultReply)
						->getQuery()
						->getSingleResult();
						if(!is_null($keywordsDoc)) {
							$keywordsData = $keywordsDoc->getArrayCopy();
						}
					}
				}
			}else {
				$keywordsData = $keywordsDoc->getArrayCopy();
			}
		}
		$wxNumber = $postObj->ToUserName;
		$openId = $postObj->FromUserName;
		if($keywordsData) {			
			$returnData = array(
				'ToUserName' =>$openId,
				'FromUserName' => $wxNumber,
				'MsgType' => $keywordsData['type'],
			);
			switch ($keywordsData['type']){
				case 'text':
					$returnData['Content'] = $keywordsData['content'];
					break;
				case 'news':
					$articleCount = 0;
					$newsDocs = $cdm->createQueryBuilder('WxDocument\Article')
									->field('id')->in($keywordsData['newsId'])
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
		}else {
			$result = array(
				'status' => true,
				'data' => array(
					'ToUserName' =>$openId,
					'FromUserName' => $wxNumber,
					'MsgType' => 'text',
					'Content' => '感谢您关注本微信号!'
				),
			);
		}		
		return $result;
	}
	
	public function getSubscribeReply($postObj)
	{
		$cdm = $this->sm->get('CmsDocumentManager');
		
	}
}