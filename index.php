<?php
class chat
{
	public function valid(){
  		$signature = $_GET["signature"];
        	$timestamp = $_GET["timestamp"];
        	$nonce = $_GET["nonce"];	
		$token = 'lingpai';//你的令牌
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			echo $_GET['echostr'];
			return true;
		}else{
			return false;
		}
	}

	public function logmess(){
		file_put_contents('log.xml',$GLOBALS['HTTP_RAW_POST_DATA']);
	}

	public function reponse(){
		//xml字符串转换为xml对象
		if(empty($GLOBALS['HTTP_RAW_POST_DATA'])){
			echo '咋啥也不说';
			exit;
		}
		$xmlobj=simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'],'SimpleXMLElement',LIBXML_NOCDATA);
		if($xmlobj->MsgType=='text')$result=$this->receiveText($xmlobj);
		file_put_contents('receivelog.xml',$result);
		echo $result;//发给微信服务器
	}

	//返回消息
	public function receiveText($obj){
		$result='我帅吗';
		$con=$obj->Content;
		$content=$this->qingyunke($con);
		$result=$this->receive($obj,trim($content));
		return $result;
	}

	public function receive($obj,$content){
		$text="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content><MsgId>%s</MsgId></xml>";
		return sprintf($text,$obj->FromUserName,$obj->ToUserName,date('Y-m-d H:i:s',time()),$content,$obj->MsgId);
	}

	public function qingyunke($msg){
		$url ='http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$msg;
 		$ch = curl_init(); 
		$ch = curl_init($url) ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
 		$file_contents = curl_exec($ch) ;  
		$curl_errno = curl_errno($ch); 
		
		$a = json_decode($file_contents,true);	
		return $a['content'];
	}	
}

$wc=new chat();
if(!isset($_GET['echostr'])){
	$wc->logmess();
	$wc->reponse();
}else{
	$wc->valid();
}
