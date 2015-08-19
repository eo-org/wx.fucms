<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="log"
 * )
 */
class Log extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $msg;
	
	public function setMsg($val)
	{
		$this->msg = $val;
	}
}