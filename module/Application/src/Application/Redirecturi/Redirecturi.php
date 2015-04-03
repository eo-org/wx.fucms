<?php
namespace Application\Redirecturi;

class Redirecturi
{
	protected $sm;
	
	protected function curlPostResult($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		curl_close($ch);
			
		return $output;
	}
	
	public function __construct($sm)
	{
		/*
		 * @params $sm Object serviceLocator
		 */
		$this->sm = $sm;
	}
	
	public function get($websiteId)
	{
		/*
		 * @params $websiteId String ��վID
		*/
		$dm = $this->sm->get('DocumentManager');
		$config = $this->sm->get('Config');
		$wx = $config['env']['wx'];
		
		$tokenDoc = $dm->createQueryBuilder('Application\Document\Ticket')
		->field('label')->equals('token')
		->getQuery()
		->getSingleResult();
		$tokenFailed = true;
		if(!empty($tokenDoc)) {
		
			$modified = $tokenDoc->getModified()->format('y-m-d H:i:s');
			$cTimestamp = strtotime (date("y-m-d H:i:s"));
			$timestamp = strtotime ($modified);
			if($cTimestamp - $timestamp < 6000){
				$token = $tokenDoc->getValue();
				$tokenFailed = false;
			}
		}else {
			$tokenDoc = new Ticket();
			$tokenDoc->setLabel('token');
		}
		if($tokenFailed) {
			$ticketDoc = $dm->createQueryBuilder('Application\Document\Ticket')
			->field('label')->equals('ticket')
			->getQuery()
			->getSingleResult();
		
			$ticket = $ticketDoc->getValue();
			$getTokenUrl = $wx['path']['accessToken'];
			$post_data = array (
				"component_appid" => $wx['appId'],
				"component_appsecret" =>$wx['appSecret'],
				'component_verify_ticket' => $ticket,
			);
			$post_data = json_encode($post_data);
			$tokenResultStr = $this->curlPostResult($getTokenUrl, $post_data);
			$tokenResult = json_decode($tokenResultStr , true);
			$currentDateTime = new \DateTime();
			$tokenDoc->setMsg(array(
				'tokenResult' => $tokenResultStr,
			));
		
			$token = $tokenResult['component_access_token'];
			$tokenDoc->setValue($token);
			$tokenDoc->setModified($currentDateTime);
			$dm->persist($tokenDoc);
			$dm->flush();
		}
		$preAuthCodePostData = array(
			'component_appid' => $wx['appId'],
		);
		$preAuthCodePostDataStr = json_encode($preAuthCodePostData);
		$getPreAuthCodeUrl = $wx['path']['preAuthCode'].$token;
			
		$preAuthCodeResultStr = $this->curlPostResult($getPreAuthCodeUrl, $preAuthCodePostDataStr);
		$preAuthCodeResult = json_decode($preAuthCodeResultStr , true);
			
		$result = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$wx['appId'].'&pre_auth_code='.$preAuthCodeResult['pre_auth_code'].'&redirect_uri='.$wx['path']['redirectUri'].'/'.$websiteId;
		
		return $result;
	}
}