<?php
function template($id){
	$api_url = 'http://ext.nicovideo.jp/api/getthumbinfo/'.$id;
	$xml = simplexml_load_file($api_url);
	$xml = $xml->thumb;

	//必要な情報を入れ込む
	$title = $xml->title;
	$img = $xml->thumbnail_url;
	$time = $xml->length;

	//外部プレイヤー貼りつけokなら1、ngなら0
	if ($xml->embeddable == 1){

		/*-------------------
		本文整形
		---------------------*/

		/* 画像 */
		$text =
		  '<div class="video_img">'.
		  '<a href="http://www.nicovideo.jp/watch/'.$id.'">'.
		  '<img src="'.$img.'" '.
		  'width="170" height="140" border="0" '.
		  'alt="'.$title.'" />'.
		  '</a>'.
		  '<span class="video_time">'.$time.'</span>'.
		  '</div>';

		/* tweet数 */
		$text .=
		  '<a href="http://twitter.com/share" class="twitter-share-button" '.
		  'data-text="'.$title.' : smile中毒 ('.$time.') #nicovideo #'.$id.'" '.
		  'data-counturl="http://www.nicovideo.jp/watch/'.$id.'" data-count="vertical" data-lang="ja">Tweet</a>'.
		  '<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';

		/* スクリプト変数 */
		$text_append = "\n".
		'<script type="text/javascript">'."\n".
		'<!--'."\n".
		    'var nico = "'.$id.'";'."\n".
		    'var title = "'.$title.'";'."\n".
		'// -->'."\n".
		'</script>';

		//livedoor書き込み用ファイル呼び出し
		require_once 'autorenew.php';

		//タイトルと書込情報を送る
		livedoor_api($title,$text,$text_append);
	}

}