<?php

namespace Application\Document;

use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="wx_picture_article"
 * )
 */
class News extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $title;
	
	/**
	 * @ODM\Field(type="int")
	 */
	protected $sort;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $picUrl;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $url;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $selfUrl;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $introtext;
	
	/**
	 * @ODM\Field(type="hash")
	 */
	protected $children;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $groupId;
	
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
		if(isset($data['title'])){
			$this->title = $data['title'];
		}
		
		if(isset($data['sort'])){
			$this->sort = $data['sort'];
		}
		
		if(isset($data['picUrl'])){
			$this->picUrl = $data['picUrl'];
		}
		
		if(isset($data['url'])){
			$this->url = $data['url'];
		}
		
		if(isset($data['selfUrl'])){
			$this->selfUrl = $data['selfUrl'];
		}
		
		if(isset($data['groupId'])){
			$this->groupId = $data['groupId'];
		}
		
		if(isset($data['description'])){
			$this->description = $data['description'];
		}
		
		if(isset($data['introtext'])){
			$this->introtext = $data['introtext'];
		}
		
		if(isset($data['children'])){
			$this->children = $data['children'];
		}
	}
	public function getArrayCopy()
	{
		return array(
			'id'	=> $this->id,
			'title' => $this->title,
			'sort' => $this->sort,
			'picUrl' => $this->picUrl,
			'url' => $this->url,
			'selfUrl' => $this->selfUrl,
			'groupId' => $this->groupId,
			'description' => $this->description,
			'introtext' => $this->introtext,
			'children' => $this->children,
		);
	}
}