<?php
require_once 'HTTP/Request2.php';
date_default_timezone_set('utc');
//ニコニコ動画URLを検索
$date = '/'.date('Y-m-d\TH',strtotime('-1 hour')).'/';

$since = 'since:'.date('Y-m-d',strtotime('-1 hour'));
$qward = '&q=nicovideo watch filter:links -live -news -dic -seiga '.$since;
$rss = 'http://search.twitter.com/search.atom?lang=ja&rpp=100'.$qward;
//$rss = rawurlencode($rss);
$pattern_id = '/(sm[0-9]+)|(nm[0-9]+)/';
$pattern_url = '/http:\/\/[0-9a-z_,.:;&=+*%$#!?@()~\'\/-]+/i';

$nico_id = array();
$page_count = 0;
$data_flag = false;
$break = false;
//echo $rss;
//echo $xml;
nicovideo_id($rss);

function nicovideo_id($rss){
		global $nico_id,$pattern_id,$pattern_url,$date,$data_flag,$break,$page_count;

		//$rss = file_get_contents($rss);
		//$xml = simplexml_load_string($rss);
		$xml = simplexml_load_file($rss);
		//print_r($xml);
		$page_count++;

		foreach($xml->entry as $entry){
			if(preg_match($date,$entry->updated)){
					$data_flag = true;
					preg_match_all($pattern_id,$entry->title,$match);
				if($match[0][0]){
						$nico_id[] = $match[0][0];
				} else {
						/*
						preg_match_all($pattern_url,$entry->title,$match_url);
						$h = get_headers($match_url[0][0],true);
						if(isset($h['Location'])){
								$url = $h['Location'];
								//print_r($url);
								if(is_array($url)){
										$url = end($url);
								}
								preg_match_all($pattern_id,$url,$match__url);
								if($match__url[0][0]){
										$nico_id[] = $match__url[0][0];
								}
						}
						 */
				}
			} else {
					if($data_flag){
							$break = true;
							break;
					}
			}
		}
		if(!$break){
				if($page_count < 15){
						foreach($xml->link as $link){
								if($link->attributes()->rel == 'next'){
										//$href = simplexml_load_file($link->attributes()->href);
										nicovideo_id($link->attributes()->href);
								}
						}
				}
		}
}
$video_ids = array_count_values($nico_id);
arsort($video_ids);
//print_r($video_ids);


//動画ID更新履歴を変数に格納
$id_old = array();
$fp = fopen("nicovideo_id.txt", "r");
while (!feof($fp)){
	$id_rireki = fgets($fp);
	$id_old[] = mb_ereg_replace("\n", "",$id_rireki);
}
fclose($fp);

//更新ID選別
foreach ($video_ids as $id => $num){
	$count = 0;
	if ($num >= 20){
		//履歴と被りがないか確認
		for ($i = 0;$i < count($id_old) -1;$i++){
			if ($id_old[$i] == $id){
				$count++;
			}
		}
	
		//被りがなかったら更新
		if ($count == 0){
	
			$api_url = 'http://ext.nicovideo.jp/api/getthumbinfo/'.$id;
			$xml = simplexml_load_file($api_url);
			$xml = $xml->thumb;
	
			//外部プレイヤー貼りつけokなら1、ngなら0
			if ($xml->embeddable == 1){
	
				//テンプレートphp呼び出し
				require_once 'template.php';
	
				//動画IDを送る
				template($id);
	
				//ファイルに動画ID書き込み
				$fp_write = fopen("nicovideo_id.txt", "a");
				fputs($fp_write, $id."\n");
				fclose($fp_write);
				break;
			}
		}
	}
 
}
