<?php

namespace Weixin\Document;

use Zend\InputFilter\Factory as FilterFactory, Zend\InputFilter\InputFilter;
use Core\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="admin"
 * )
 */
class User extends AbstractDocument
{
	/**
	 * @ODM\Id
	 */
	protected $id;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $loginName;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $password;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $roleId;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $name;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $tel;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $qq;
	
	/**
	 * @ODM\Field(type="string")
	 */
	protected $profile;
	protected $inputFilter;
	protected $dm;
	public function setDocumentManager($dm)
	{
		$this->dm = $dm;
	}
	public function getInputFilter()
	{
		if(! $this->inputFilter) {
			$inputFilter = new InputFilter();
			$inputFactory = new FilterFactory();
			
			$inputFilter->add($inputFactory->createInput(array(
				'name' => 'loginName',
				'requried' => true,
				'filters' => array(
					array(
						'name' => 'StringTrim'
					)
				),
				'validators' => array(
					array(
						'name' => 'NotEmpty'
					),
					array(
						'name' => '\Cms\Validator\DbExists',
						'options' => array(
							'dm' => $this->dm,
							'repository' => '\Cms\Document\Admin',
							'field' => 'loginName',
							'skip' => $this->id
						)
					)
				)
			)));
			$inputFilter->add($inputFactory->createInput(array(
				'name' => 'password',
				'requried' => true,
				'filters' => array(
					array(
						'name' => 'StringTrim'
					)
				),
				'validators' => array(
					array(
						'name' => 'NotEmpty'
					)
				)
			)));
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
	public function exchangeArray($data)
	{
		$this->loginName = $data['loginName'];
		$this->password = $data['password'];
		$this->roleId = $data['roleId'];
		$this->name = $data['name'];
		$this->tel = $data['tel'];
		$this->qq = $data['qq'];
		$this->profile = $data['profile'];
	}
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'loginName' => $this->loginName,
			'password' => $this->password,
			'roleId' => $this->roleId,
			'name' => $this->name,
			'tel' => $this->tel,
			'qq' => $this->qq,
			'profile' => $this->profile
		);
	}
	
	public function updatePassword($data)
	{
		if($this->getEncryptedPassword($data['oldPassword']) != $this->password) {
			return 'old password not match';
		}
		
		$this->password = $this->getEncryptedPassword($data['password']);
		return true;
	}
	
	public function getEncryptedPassword($password)
	{
		return $password;
	}
}