<?php
//livedoor_api('たいとる','テキ<br/>スト','<br/>てすと');

function livedoor_api($title,$text,$text_append){
		require_once 'HTTP/Request2.php';

		$id = ""; /* livedoorID */
		$pass = ""; /* パスワード */

		$url = "http://livedoor.blogcms.jp/atom/blog/".$id.'/article';

		$created = date('Y-m-d\TH:i:s\Z');
		$nonce = pack('H*', sha1(md5(time())));
		$pass_digest = base64_encode(pack('H*', sha1($nonce.$created.$pass)));
		$wsse =
				'UsernameToken Username="'.$id.'", '.
				'PasswordDigest="'.$pass_digest.'", '.
				'Nonce="'.base64_encode($nonce).'", '.
				'Created="'.$created.'"';

		$text .= $text_append;
		//print_r($text);
		$text64= base64_encode($text);
		$rawdata =
				'<?xml version="1.0"?>'.
				'<entry xmlns="http://purl.org/atom/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">'.
				'<title type="text/html" mode="escaped">'.$title.'</title>'.
				'<content type="application/xhtml+xml" mode="base64">'.$text64.'</content>'.
				'</entry>';
		$headers = array(
		  'X-WSSE: ' . $wsse,
		  'Expect:'
  );
		//print_r($rawdata);

try{
		$req = new HTTP_Request2();
		$req->setUrl($url);
		$req->setMethod(HTTP_Request2::METHOD_POST);
		//$req->setHeader('Content-type: text/xml; charset=utf-8');
		$req->setHeader($headers);
		$req->setBody($rawdata);
		$response = $req->send();
		//echo $response->getBody();

} catch (HTTP_Request2_Exception $e) {
		die($e->getMessage());
} catch (Exception $e) {
		die($e->getMessage());
}

/*
	$rawdata =
	  '<?xml version="1.0"?>'.
	  '<entry xmlns="http://purl.org/atom/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">'.
	    '<title type="text/html" mode="escaped">'.$title.'</title>'.
	    '<content type="application/xhtml+xml" mode="base64">'.$text64.'</content>'.
	  '</entry>';

	$headers =array(
	  'X-WSSE: ' . $wsse,
	  'Expect:'
	);

	$ch = includcurl();
	print_r($ch);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$rawdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($ch);
	print_r($res);
	curl_close($ch);

	//出力結果確認用
	echo $res;
*/
}
