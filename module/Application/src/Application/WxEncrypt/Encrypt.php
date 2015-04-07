<?php
namespace Application\WxEncrypt;

include_once (BASE_PATH ."/inc/WxEncrypt/wxBizMsgCrypt.php");

class Encrypt
{
	protected $q;
	protected $pc;
	protected $timeStamp;
	protected $nonce;
	protected $msg_sign;
	
	public function __construct($sm, $q)
	{
		/*
		*
		* @params $sl Object serviceLocator
		* @params $q Array 
		*
		*/
		$config = $sm->get('Config');
		$wx = $config['env']['wx'];
		$token = $wx['token'];
		$encodingAesKey = $wx['encryptKey'];
		$appId = $wx['appId'];
		$this->pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appId);
				
		if(isset($q['signature'])){
			$this->msg_sign = $q['signature'];
		}
		
		if(isset($q['msg_signature'])){
			$this->msg_sign = $q['msg_signature'];
		}
		$this->timeStamp = $q['timestamp'];
		$this->nonce = $q['nonce'];
	}
	
	public function Decrypt($format)
	{
		/*
		 *
		* @params $format String 
		*
		*/
		$msg = '';
		$errCode = $this->pc->decryptMsg($this->msg_sign, $this->timeStamp, $this->nonce, $format, $msg);
		if ($errCode == 0) {
			return array(
				'status'=> true,
				'msg'	=> $msg,
			);
		} else {
			return array(
				'status' => false,
				'msg'	=> $errCode,
			);
		}
	}
	
	public function Encrypt($replyMsg)
	{
		/*
		 *
		 * @params $replyMsg String 
		 * 
		 */
		$encryptMsg = '';
		$errCode = $this->pc->encryptMsg($replyMsg, $this->timeStamp, $this->nonce, $encryptMsg);
		if ($errCode == 0) {
			return array(
				'status'=> true,
				'msg'	=> $encryptMsg,
			);
		} else {
			return array(
				'status' => false,
				'msg'	=> $errCode,
			);
		}
	}
}
