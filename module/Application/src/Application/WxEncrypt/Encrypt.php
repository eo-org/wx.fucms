<?php
namespace Application\WxEncrypt;

include_once "/html/WxEncrypt/wxBizMsgCrypt.php";

class Encrypt
{
	protected $q;
	protected $pc;
	protected $timeStamp;
	protected $nonce;
	protected $msg_sign;
	
	public function __construct($sl, $q)
	{
		/*
		*
		* @params $sl Object serviceLocator,获取第三方平台的配置参数
		* @params $q Array 公众平台发出消息的URL所带参数，以获取时间戳等参数进行验证解密
		*
		*/
		$config = $sl->get('Config');
		$wx = $config['env']['wx'];
		$token = $wx['token'];
		$encodingAesKey = $wx['encryptKey'];
		$appId = $wx['appId'];
		$this->pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appId);
		$this->msg_sign = $q['msg_signature'];
		$this->timeStamp = $q['timestamp'];
		$this->nonce = $q['nonce'];
	}
	
	public function Decrypt($format)
	{
		/*
		 * 公众平台发出消息解密
		* @params $format String 公众平台发出的消息，XML格式的字符串
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
		 * 回复消息加密打包
		 * @params $replyMsg String 回复消息，XML格式的字符串
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
