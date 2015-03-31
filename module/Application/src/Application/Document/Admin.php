<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="admin"
 * )
 */
class Admin extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $appId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $appSecret;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $ticket;
	
	public function exchangeArray($data)
	{
		if(isset($data['appId'])){
			$this->appId = $data['appId'];
		}
		
		if(isset($data['appSecret'])){
			$this->appSecret = $data['appSecret'];
		}
		
		if(isset($data['ticket'])){
			$this->ticket = $data['ticket'];
		}
		
	}
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'appId'	=> $this->appId,
			'appSecret'	=> $this->appSecret,
			'ticket'	=> $this->ticket,
		);
	}
}