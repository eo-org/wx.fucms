<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\Session\User;
use Application\Document\Admin as Admin;
use Application\WxEncrypt\Encrypt;

class CallbackController extends AbstractActionController
{
    public function indexAction()
    {
    	$q = $this->params()->fromQuery();
    	$format = file_get_contents('php://input');
    	$serviceLocator = $this->getServiceLocator();
    	$wxEncrypt = new Encrypt($serviceLocator, $q);
    	
//     	$format = '<xml>
// 		    <AppId><![CDATA[wx2ce4babba45b702d]]></AppId>
// 		    <Encrypt><![CDATA[K3cO8Z4PSJ91oORMInA2pNVcOrEqDshAKpGDxBQGN+GPmxjwSr78iuJxhqX7bGdmTEI9C2NBIGJJPtwHUD8XJo42Me9qrONmmr2gbOxxP5T4iihWoim7PRSljEl05XOTfYbDeJCSoPz2i8uNVDj9wB14LVvM2Qy2sREW2MmvuwccWB4+w8egmEL2LzWt6enhbLfLGHZw+qpQ1j0PJBioIjdZIUszZXKlelw0acnFv0pr+r/4SvtxnT7/AwOXNoMb/R9mSfJEL+P/v9BirID7WfIa0fGkVu7jWXBCxzlc6RFLjUAjlBffLGN0OveY2GO8GgWzTVs8qBIB7BuUNlPE/yVhPnRzzrlVsddawyb8TZhgPaFw7ZBcOwFUJodQoAuk9Wy8zBqGgItc2iQHPm08n2mEXQPgToh5XmCIeet1kr5MbmEVgPDppPSm1gljV6CiPpAZ73AjLcV2dS1oyW7IVA==]]></Encrypt>
// 		</xml>';

    	$postData = $wxEncrypt->Decrypt($format);
    	
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	$doc = $dm->createQueryBuilder('Application\Document\Admin')
    					->field('appSecret')->equals('0c79e1fa963cd80cc0be99b20a18faeb')
    					->getQuery()
    					->getSingleResult();
    	if($doc){
    		if($postData){
    			$xmlData = new \DOMDocument();
    			$xmlData->loadXML($postData['msg']);
    			$array_info_type = $xmlData->getElementsByTagName('InfoType');
    			$infotype = $array_info_type->item(0)->nodeValue;
    			if($infotype == 'component_verify_ticket'){
    				$array_ticket = $xmlData->getElementsByTagName('ComponentVerifyTicket');
    				$ticket = $array_ticket->item(0)->nodeValue;
    			}
    			$doc->setTicket($ticket);
    			$doc->setData(array(
    				'data' => $postData,
    			));
   			}else {
   				$doc->setData(array('q'=>$q));
   			}
    	}else {
    		$doc = new Admin();
    		$data = array(
    			'appId'=>'wx2ce4babba45b702d',
    			'appSecret' => '0c79e1fa963cd80cc0be99b20a18faeb',
    			'data' => $postData,
    		);
    		$doc->exchangeArray($data);
    	}
    	$currentDateTime = new \DateTime();
    	$doc->setModified($currentDateTime);
    	$dm->persist($doc);
    	$dm->flush();
    	return new ConsoleModel();
    }
    
    public function loginAction()
    {
    	
    }
}