<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="wx_query"
 * )
 */
class Query
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="hash")
	 */
	protected $keywords;
	
	/**
	 * @ODM\Field(type="boolean")
	 */
	protected $match;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $type;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $content;
	
	/**
	 * @ODM\Field(type="hash")
	 */
	protected $newsId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $mediaId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $title;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $description;
	
	public function exchangeArray($data)
	{
		if(isset($data['keywords'])){
			$this->keywords = $data['keywords'];
		}
		
		if(isset($data['match'])){
			$this->match = $data['match'];
		}
		
		if(isset($data['type'])){
			$this->type = $data['type'];
		}
		
		if(isset($data['content'])){
			$this->content = $data['content'];
		}
		
		if(isset($data['newsId'])){
			$this->newsId = $data['newsId'];
		}
	}
	public function getArrayCopy()
	{
		return array(
			'keywords' => $this->keywords,
			'match' => $this->match,
			'type' => $this->type,
			'content' => $this->content,
			'newsId' => $this->newsId,
		);
	}
}