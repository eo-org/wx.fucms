<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="wx_query"
 * )
 */
class Query extends AbstractDocument
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
	 * @ODM\Field(type="int")
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
	protected $articles;
	
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
	
	/**
	 * @ODM\Field(type="date")
	 */
	protected $created;
	
	public function exchangeArray($data)
	{
		if(isset($data['label'])){
			$this->label = $data['label'];
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
		
		if(isset($data['articles'])){
			$this->articles = $data['articles'];
		}
		
		if(isset($data['mediaId'])){
			$this->mediaId = $data['mediaId'];
		}
		
		if(isset($data['title'])){
			$this->title = $data['title'];
		}
		
		if(isset($data['description'])){
			$this->description = $data['description'];
		}
	}
	public function getArrayCopy()
	{
		return array(
			'label' => $this->label,
			'match' => $this->match,
			'type' => $this->type,
			'content' => $this->content,
			'articles' => $this->articles,
			'mediaId' => $this->mediaId,
			'title' => $this->title,
			'description' => $this->description,
		);
	}
}