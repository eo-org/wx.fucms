<?php
namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="wx_message"
 * )
 */
class _Message
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
	protected $openId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $type;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $content;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $picUrl;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $mediaId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $format;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $thumbMediaId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $locationX;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $locationY;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $scale;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $label;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $title;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $description;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $url;
	
	/**
	 * @ODM\Field(type="hash")
	 */
	protected $data;
	
	public function getId()
	{
		return $this->id;
	}
	
	public function exchangeArray($data)
	{
		if(isset($data['appId'])){
			$this->appId = $data['appId'];
		}
		
		if(isset($data['openId'])){
			$this->openId = $data['openId'];
		}
		
		if(isset($data['type'])){
			$this->type = $data['type'];
		}
		
		if(isset($data['content'])){
			$this->content = $data['content'];
		}
		
		if(isset($data['picUrl'])){
			$this->picUrl = $data['picUrl'];
		}
		
		if(isset($data['mediaId'])){
			$this->mediaId = $data['mediaId'];
		}
		
		if(isset($data['format'])){
			$this->format = $data['format'];
		}
		
		if(isset($data['thumbMediaId'])){
			$this->thumbMediaId = $data['thumbMediaId'];
		}
		
		if(isset($data['locationX'])){
			$this->locationX = $data['locationX'];
		}
		
		if(isset($data['locationY'])){
			$this->locationY = $data['locationY'];
		}
		
		if(isset($data['scale'])){
			$this->scale = $data['scale'];
		}
		
		if(isset($data['label'])){
			$this->label = $data['label'];
		}
		
		if(isset($data['title'])){
			$this->title = $data['title'];
		}
		
		if(isset($data['description'])){
			$this->description = $data['description'];
		}
		
		if(isset($data['url'])){
			$this->url = $data['url'];
		}
		
		if(isset($data['data'])){			
			$this->data = $data['data'];
		}
	}
	public function getArrayCopy()
	{
		return array(
			'appId' => $this->appId,
			'openId' => $this->openId,
			'type' => $this->type,
			'content' => $this->content,
			'picUrl' => $this->picUrl,
			'mediaId' => $this->mediaId,
			'format' => $this->format,
			'thumbMediaId' => $this->thumbMediaId,
			'locationX' => $this->locationX,
			'locationY' => $this->locationY,
			'scale' => $this->scale,
			'label' => $this->label,
			'title' => $this->title,
			'description' => $this->description,
			'url' => $this->url,
		);
	}
	
	public function getOpenId()
	{
		return $this->openId;
	}
}