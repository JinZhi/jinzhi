<?php
/**
 * wechat php test
 */


//define your token
define("TOKEN", "jinzhi");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->valid();
$wechatObj->responseMsg();

$wechatObj->responseImgMsg();

class wechatCallbackapiTest
{

	public function valid()
	{
		$echoStr = $_GET["echostr"];

		//valid signature , option
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}

	public function responseImgMsg()
	{
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)){

			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$time = time();
			$textHaderTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<Content><![CDATA[]]></Content>
					<ArticleCount>%d</ArticleCount>
					<Articles>";
			$textContentTpl = "<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>";
			$textFooterTpl = "</Articles>
					<FuncFlag>1</FuncFlag>
					</xml>";
			if(!empty( $keyword ))
			{
				$msgType = "news";
				// 判断是否首次关注
// 				if ( $keyword == "Hello2BizUser" ) {
// 					$contentStr = "欢迎关注天气宝宝，请输入你要查询的城市!";
// 				}else {
// 					$contentStr =getWeather($keyword);
// 				}
// 				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
// 				echo $resultStr;
				$array = getWeather($keyword);
				$resultHaderStr = sprintf($textHaderTpl, $fromUsername, $toUsername, $time, count($array));
				foreach ($array as $key => $value) {
					$resultContentStr .= sprintf($textContentTpl, $value['title'], $value['des'], $value['pic'], $value['url']);
				}
				$resultFooterStr = sprintf($textFooterTpl);
				echo $resultStr = $resultHaderStr,$resultContentStr,$resultFooterStr;
			}else{
				echo "Input something...";
			}

		}else {
			echo "";
			exit;
		}
	}

	public function responseMsg()
	{
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)){

			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$time = time();
			$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";
			if(!empty( $keyword ))
			{
				$msgType = "text";
				$contentStr = $keyword;
				// 判断是否首次关注
				if ( $keyword == "Hello2BizUser" ) {
					$contentStr = "欢迎关注天气宝宝，请输入你要查询的城市!";
				}else {
					$contentStr =getWeather($keyword);
				}
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				echo $resultStr;
			}else{
				echo "Input something...";
			}

		}else {
			echo "";
			exit;
		}
	}

	private function checkSignature()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}


/**
 * getWeather
 * @param $keyword
 * @return unknown
 */
function getWeather($keyword) {
	include 'weather_code.php';
	$code=$weather_code[$keyword];
	$url="http://m.weather.com.cn/data/".$code.".html";

	$file=file_get_contents($url);
	$obj=json_decode($file);
	$weatherinfo = $obj -> weatherinfo;
	$city = $weatherinfo -> city;
	$temp1=$weatherinfo->temp1;
	$temp2=$weatherinfo->temp2;
	$temp3=$weatherinfo->temp3;
	$img1=$weatherinfo->img1;
	$img2=$weatherinfo->img3;
	$img3=$weatherinfo->img5;
	$weather1=$weatherinfo->weather1;
	$weather2=$weatherinfo->weather2;
	$weather3=$weatherinfo->weather3;
	$wind1=$weatherinfo->wind1;
	$wind2=$weatherinfo->wind2;
	$wind3=$weatherinfo->wind3;

	$index=$weatherinfo->index;
	$index_d=$weatherinfo->index_d;
	$date_y=$weatherinfo->date_y;
	
	$array = array(
			array("title"=>$city,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/image.jpg"),
			array("title"=>$index_d,"des"=>"testdes"),
			array("title"=>$date_y." ".$temp1." ".$weather1." ".$wind1,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/images/".$img1.".png"),
			array("title"=>$temp2." ".$weather2." ".$wind2,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/images/".$img2.".png"),
			array("title"=>$temp3." ".$weather3." ".$wind3,"des"=>"testdes","pic"=>"http://api.itcreating.com/weather/images/".$img3.".png"),
			
	);
	return $array;
}

/**
 * 中国气象网的接口：http://m.weather.com.cn/data/城市代码.html，
 * 如http://m.weather.com.cn/data/101280101.html，下面是返回的数据，数据为json格式。
 *
 * {
 "weatherinfo": {
 "city": "广州",
 "city_en": "guangzhou",
 "date_y": "2013年2月27日",
 "date": "",
 "week": "星期三",
 "fchh": "11",
 "cityid": "101280101",
 <!-- 从今天开始到第六天的每天的天气情况，这里的温度是摄氏温度 -->
 "temp1": "25℃~19℃",
 "temp2": "24℃~19℃",
 "temp3": "25℃~13℃",
 "temp4": "22℃~11℃",
 "temp5": "16℃~9℃",
 "temp6": "20℃~11℃",
 <!-- 从今天开始到第六天的每天的天气情况，这里的温度是华氏温度 -->
 "tempF1": "77℉~66.2℉",
 "tempF2": "75.2℉~66.2℉",
 "tempF3": "77℉~55.4℉",
 "tempF4": "71.6℉~51.8℉",
 "tempF5": "60.8℉~48.2℉",
 "tempF6": "68℉~51.8℉",
 <!-- 天气描述 -->
 "weather1": "阴",
 "weather2": "阴",
 "weather3": "阴转小雨",
 "weather4": "小雨",
 "weather5": "阴转晴",
 "weather6": "晴",
 <!-- 天气描述图片序号 -->
 "img1": "2",
 "img2": "99",
 "img3": "2",
 "img4": "99",
 "img5": "2",
 "img6": "7",
 "img7": "7",
 "img8": "99",
 "img9": "2",
 "img10": "0",
 "img11": "0",
 "img12": "99",
 "img_single": "2",
 <!-- 图片名称 -->
 "img_title1": "阴",
 "img_title2": "阴",
 "img_title3": "阴",
 "img_title4": "阴",
 "img_title5": "阴",
 "img_title6": "小雨",
 "img_title7": "小雨",
 "img_title8": "小雨",
 "img_title9": "阴",
 "img_title10": "晴",
 "img_title11": "晴",
 "img_title12": "晴",
 "img_title_single": "阴",
 <!-- 风速描述 -->
 "wind1": "微风",
 "wind2": "微风",
 "wind3": "微风转北风4-5级",
 "wind4": "北风4-5级转3-4级",
 "wind5": "微风",
 "wind6": "微风",
 <!-- 风速级别描述 -->
 "fx1": "微风",
 "fx2": "微风",
 "fl1": "小于3级",
 "fl2": "小于3级",
 "fl3": "小于3级转4-5级",
 "fl4": "4-5级转3-4级",
 "fl5": "小于3级",
 "fl6": "小于3级",
 <!-- 今天穿衣指数 -->
 "index": "舒适",
 "index_d": "建议着长袖T恤、衬衫加单裤等服装。年老体弱者宜着针织长袖衬衫、马甲和长裤。",
 <!-- 48小时穿衣指数 -->
 "index48": "舒适",
 "index48_d": "建议着长袖T恤、衬衫加单裤等服装。年老体弱者宜着针织长袖衬衫、马甲和长裤。",
 <!-- 紫外线及48小时紫外线 -->
 "index_uv": "最弱",
 "index48_uv": "最弱",
 <!-- 洗车 -->
 "index_xc": "较适宜",
 <!-- 旅游 -->
 "index_tr": "适宜",
 <!-- 舒适指数 -->
 "index_co": "舒适",
 "st1": "26",
 "st2": "18",
 "st3": "25",
 "st4": "18",
 "st5": "26",
 "st6": "9",
 <!-- 晨练 -->
 "index_cl": "较适宜",
 <!-- 晾晒 -->
 "index_ls": "不太适宜",
 <!-- 过敏 -->
 "index_ag": "极不易发"
 }
 }
 */







?>






















