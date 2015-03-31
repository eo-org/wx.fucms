<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	public function indexAction()
	{
		die('ok');
		$admin = $this->getServiceLocator()->get('Sp\User');
		$adminId = $admin->getUserData('id');
		
		$dm = $this->getServiceLocator()->get('DocumentManager');
		$adminPreferenceDoc = $dm->getRepository('Cms\Document\Admin\Preference')->findOneBy(array(
			'adminId' => $adminId,
			'key' => 'widget'
		));
		
		$widgets = array();
		if(!is_null($adminPreferenceDoc)) {
			$widgets = $adminPreferenceDoc->getConfigs();
		}
		
		$vm = new ViewModel();
		$vm->setTemplate('admin/index/index');
		foreach($widgets as $widgetClass) {
			$widgetClassName = '\\'.str_replace('_', '\\', $widgetClass);
			$widgetObj = new $widgetClassName();
			$vm->addChild($widgetObj->getViewModel(), $widgetClass);
		}

		$vm->setVariable('widgets', $widgets);
		$subsiteService = $this->getServiceLocator()->get('Admin\Service\SubsiteService');
		$subsiteId = $subsiteService->getId();
		$vm->setVariable('siteId', $subsiteId);
		return $vm;
		
// 		return array(
// 			'vm'	=> $vm,
// 			'siteId'	=> $subsiteId,
// 		);
	}
}