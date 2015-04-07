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
		* @params $sl Object serviceLocator,��ȡ������ƽ̨�����ò���
		* @params $q Array ����ƽ̨������Ϣ��URL�����������Ի�ȡʱ����Ȳ���������֤����
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
		 * ����ƽ̨������Ϣ����
		* @params $format String ����ƽ̨��������Ϣ��XML��ʽ���ַ���
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
		 * �ظ���Ϣ���ܴ��
		 * @params $replyMsg String �ظ���Ϣ��XML��ʽ���ַ���
		 * 
		 */
		$encryptMsg = '';
		$errCode = $pc->encryptMsg($replyMsg, $this->timeStamp, $this->nonce, $encryptMsg);
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
