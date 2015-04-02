<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="ticket"
 * )
 */
class Ticket extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $label;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $value;
	
	/**
	 * @ODM\Field(type="hash")
	 */
	protected $msg;
	
	/**
	 * @ODM\Field(type="date")
	 */
	protected $modified;
	
	public function exchangeArray($data)
	{
		if(isset($data['label'])){
			$this->label = $data['label'];
		}
		
		if(isset($data['value'])){
			$this->value = $data['value'];
		}
		
		if(isset($data['msg'])){
			$this->msg = $data['msg'];
		}
		
	}
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'label'	=> $this->label,
			'value'	=> $this->value,
			'msg'	=> $this->msg,
		);
	}
}