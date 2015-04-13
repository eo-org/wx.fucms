<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="auth"
 * )
 */
class Auth extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $websiteId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $status = 'active';
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $authorizerAppid;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $authorizerAccessToken;
	
	/**
	 * @ODM\Field(type="int")
	 */
	protected $expiresIn;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $authorizerRefreshToken;
	
	/**
	 * @ODM\Field(type="date")
	 */
	protected $tokenModified;
	
	/**
	 * @ODM\Field(type="hash")
	 */
	protected $funcInfo;
	
	/**
	 * @ODM\Field(type="msg")
	 */
	protected $msg;
	
	/**
	 * @ODM\Field(type="date")
	 */
	protected $created;
	
	public function exchangeArray($data)
	{
		if(isset($data['websiteId'])){
			$this->websiteId = $data['websiteId'];
		}
		
		if(isset($data['authorizer_appid'])){
			$this->authorizerAppid = $data['authorizer_appid'];
		}
		
		if(isset($data['authorizer_access_token'])){
			$this->authorizerAccessToken = $data['authorizer_access_token'];
		}
		
		if(isset($data['expires_in'])){
			$this->expiresIn = $data['expires_in'];
		}
		
		if(isset($data['authorizer_refresh_token'])){
			$this->authorizerRefreshToken = $data['authorizer_refresh_token'];
		}
		
		if(isset($data['func_info'])){
			$this->funcInfo = $data['func_info'];
		}
		
		if(isset($data['tokenModified'])){
			$this->tokenModified = $data['tokenModified'];
		}
		
		if(isset($data['msg'])){
			$this->msg = $data['msg'];
		}
	}
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'websiteId' => $this->websiteId,
			'authorizerAppid'	=> $this->authorizerAppid,
			'authorizerAccessToken'	=> $this->authorizerAccessToken,
			'expiresIn'	=> $this->expiresIn,
			'authorizerRefreshToken'		=> $this->authorizerRefreshToken,
			'funcInfo' => $this->funcInfo,
		);
	}
	
	public function refreshTokenExpired()
	{
		$created = $this->created->getTimestamp();
		$now = time();
		return $now - $created > 2400000;
	}
	
	public function tokenExpired()
	{
		$created = $this->tokenModified->getTimestamp();
		$now = time();
		return $now - $created > 7000;
	}
}