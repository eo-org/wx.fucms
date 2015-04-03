<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="message"
 * )
 */
class Message extends AbstractDocument
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
	 * @ODM\Field(type="hash")
	 */
	protected $data;
	
	/**
	 * @ODM\Field(type="date")
	 */
	protected $created;
	
	public function exchangeArray($data)
	{
		if(isset($data['data'])){
			$this->data = $data['data'];
		}		
	}
	public function getArrayCopy()
	{
		return array(
			'data' => $this->data,
		);
	}
}