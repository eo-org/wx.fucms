<?php
namespace Application\EventListener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Analytics\Document\Action;

class AuthListener extends AbstractListenerAggregate
{
	
	public function attach(EventManagerInterface $events)
	{
		$this->listeners[] = $events->attach('authorized.post', array(
			$this,
			'getAction'
		), 10000);
		
		$this->listeners[] = $events->attach('unauthorized.post', array(
			$this,
			'deleteAction'
		), 1000);
	}
	
	public function getAction($e)
	{
		$eventParams = $e->getTarget();
		 /*
		 * @eventParams array sm,authorizerAccessToken
		 */
		
		$authorizerAccessToken = $eventParams['authorizerAccessToken'];
		
		/**获取公众号自动回复消息数据**/
		$autoReplyInfoUrl = 'https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info?access_token='.$authorizerAccessToken;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $autoReplyInfoUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		$autoReplyInfoResult = json_decode($output);
		
		/**获取公众号菜单数据**/
		$menuInfoUrl = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token='.$authorizerAccessToken;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $menuInfoUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		$menuInfoResult = json_decode($output);
		
		
		$sm = $eventParams['sm'];
		$dm = $sm->get('DocumentManager');
		$doc = new Action();
		$data = array(
			'resourceId'	=> $resourceDoc->getId(),
			'resourceLabel'	=> $resourceDoc->$getResourceLabel(),
			'resource'		=> $eventParams['resource'],
			'action'		=> 'create',
			'adminId'		=> $user->getUserData('id'),
		);
		$doc->exchangeArray($data);
		$dm->persist($doc);
		$dm->flush();
	}
	
	public function deleteAction($e)
	{
		$eventParams = $e->getTarget();
		$resourceDoc = $eventParams['doc'];
		if(isset($eventParams['labelKey'])){
			$getResourceLabel = 'get'.ucfirst($eventParams['labelKey']);
		}else {
			$getResourceLabel = 'getLabel';
		}
		$sm = $eventParams['sm'];
		$user = $sm->get('Sp\User');
		$dm = $sm->get('DocumentManager');
		$doc = new Action();
		$data = array(
			'resourceId'	=> $resourceDoc->getId(),
			'resourceLabel'	=> $resourceDoc->$getResourceLabel(),
			'resource'		=> $eventParams['resource'],
			'action'		=> 'delete',
			'adminId'		=> $user->getUserData('id'),
		);
		$doc->exchangeArray($data);
		$dm->persist($doc);
		$dm->flush();
	}
}