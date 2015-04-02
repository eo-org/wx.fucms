<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="wx_user"
 * )
 */
class WxUser extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
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
	 * @ODM\Field(type="date")
	 */
	protected $created;
	
	public function exchangeArray($data)
	{
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
	}
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'authorizerAppid'	=> $this->authorizerAppid,
			'authorizerAccessToken'	=> $this->authorizerAccessToken,
			'expiresIn'	=> $this->expiresIn,
			'authorizerRefreshToken'		=> $this->authorizerRefreshToken,
			'funcInfo' => $this->funcInfo,
		);
	}
}