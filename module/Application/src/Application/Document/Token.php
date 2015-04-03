<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="token"
 * )
 */
class Token extends AbstractDocument
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
	protected $redirecturi;
	
	/**
	 * @ODM\Field(type="date")
	 */
	protected $created;
	
	public function exchangeArray($data)
	{
		if(isset($data['websiteId'])){
			$this->websiteId = $data['websiteId'];
		}
		
		if(isset($data['redirecturi'])){
			$this->redirecturi = $data['redirecturi'];
		}		
	}
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'websiteId' => $this->websiteId,
			'redirecturi' => $this->redirecturi,
		);
	}
}