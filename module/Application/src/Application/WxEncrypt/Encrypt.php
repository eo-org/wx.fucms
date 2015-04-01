<?php
namespace Application\WxEncrypt;

include_once "/html/WxEncrypt/wxBizMsgCrypt.php";

class Encrypt
{
	protected $token;
	protected $encodingAesKey;
	protected $appId;
	protected $pc;
	
	public function __construct($erviceLocator)
	{
		$config = $erviceLocator->get('Config');
		$wx = $config['env']['wx'];
		$token = $wx['token'];
		$encodingAesKey = $wx['encryptKey'];
		$appId = $wx['appId'];
		$this->pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appId);
	}
	
	public function Decrypt($q, $format)
	{
		$msg_sign = $q['msg_signature'];
		$timeStamp = $q['timetamp'];
		$nonce = $q['nonce'];
		$msg = '';
		$errCode = $this->pc->decryptMsg($msg_sign, $timeStamp, $nonce, $format, $msg);
		
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
	
	public function Encrypt()
	{
		
	}
}
// ������������Ϣ������ƽ̨
// $encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
// $token = "pamtest";
// $timeStamp = "1409304348";
// $nonce = "xxxxxx";
// $appId = "wxb11529c136998cb6";
// $text = "<xml><ToUserName><![CDATA[oia2Tj��������jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";


// $pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
// $encryptMsg = '';
// $errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
// if ($errCode == 0) {
// 	print("���ܺ�: " . $encryptMsg . "\n");
// } else {
// 	print($errCode . "\n");
// }

// $xml_tree = new DOMDocument();
// $xml_tree->loadXML($encryptMsg);
// $array_e = $xml_tree->getElementsByTagName('Encrypt');
// $array_s = $xml_tree->getElementsByTagName('MsgSignature');
// $encrypt = $array_e->item(0)->nodeValue;
// $msg_sign = $array_s->item(0)->nodeValue;

// $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
// $from_xml = sprintf($format, $encrypt);

// // �������յ����ں�ƽ̨���͵���Ϣ
// $msg = '';
// $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
// if ($errCode == 0) {
// 	print("���ܺ�: " . $msg . "\n");
// } else {
// 	print($errCode . "\n");
// }
