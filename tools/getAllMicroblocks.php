<?php
include "config.php";

FullBlockSpider();
while(1){
	LatestBlockSpider();
	sleep(2);
	echo ".";
}


function LatestBlockSpider(){	
	for($i=GetTopHeight()-5;$i<GetTopHeight()-1;$i++){
		$height=$i;
		//$url=DATA_SRC_SITE."v2/key-blocks/height/$height";
		$url="http://35.178.61.73:3013/v2/key-blocks/height/$height";
		$websrc=getwebsrc($url);
		$info=json_decode($websrc);		
		$prev_hash=$info->prev_hash;
		$prev_key_hash=$info->prev_key_hash;
		if($prev_hash!=$prev_key_hash){ProcessMicroBlock($prev_hash);}
	}	
}



function FullBlockSpider(){	
	for($i=1;$i<GetTopHeight()-1;$i++){
		$height=$i;
		//$url=DATA_SRC_SITE."v2/key-blocks/height/$height";
		$url="http://35.178.61.73:3013/v2/key-blocks/height/$height";
		$websrc=getwebsrc($url);
		$info=json_decode($websrc);		
		$prev_hash=$info->prev_hash;
		$prev_key_hash=$info->prev_key_hash;
		if($prev_hash!=$prev_key_hash){ProcessMicroBlock($prev_hash);}
	}	
}

function ProcessMicroBlock($microhash){
	$sql="SELECT hash FROM microblocks WHERE hash='$microhash'";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db1 = pg_connect($conn_string);
	$result_query1 = pg_query($db1, $sql);
	
	if (pg_num_rows($result_query1) == 0) {
		//$url=DATA_SRC_SITE."v2/micro-blocks/hash/$microhash/header";
		$url="http://35.178.61.73:3013/v2/micro-blocks/hash/$microhash/header";
		$websrc=getwebsrc($url);		
		$info=json_decode($websrc);
		$height=$info->height;
		$prev_hash=$info->prev_hash;
		$prev_key_hash=$info->prev_key_hash;
		$hash=$microhash;

		$sql_insert="INSERT INTO microblocks(hash,height,data) VALUES('$hash',$height,'$websrc')";
		$result_insert = pg_query($db1, $sql_insert);		
		echo "\n$microhash ...inserted\n";
		if($prev_hash!=$prev_key_hash){ProcessMicroBlock($prev_hash);}
		}
	}

function GetTopHeight()	{
	$url=DATA_SRC_SITE."v2/blocks/top";
	$websrc=getwebsrc($url);
	$info=json_decode($websrc);
	if(strpos($websrc,"key_block")==TRUE){		
		return $info->key_block->height;
	}
		
	if(strpos($websrc,"micro_block")==TRUE){
		return $info->micro_block->height;
		}
	
	return 0;
	}

function getwebsrc($url) {
	global $pid, $pageerror;
	$curl = curl_init ();
	$agent = "User-Agent: AE-testbot";
	
	curl_setopt ( $curl, CURLOPT_URL, $url );

	curl_setopt ( $curl, CURLOPT_USERAGENT, $agent );
	curl_setopt ( $curl, CURLOPT_ENCODING, 'gzip,deflate' );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); //×¥È¡301Ìø×ªºóÍøÖ·
	curl_setopt ( $curl, CURLOPT_AUTOREFERER, true );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $curl, CURLOPT_TIMEOUT, 60 );
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	
	$html = curl_exec ( $curl ); // execute the curl command
	$response_code = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
	if ($response_code != '200') { //Èç¹ûÎ´ÄÜ»ñÈ¡¸ÃÒ³Ãæ£¨·Ç200·µ»Ø£©£¬ÔòÖØÐÂ³¢ÊÔ»ñÈ¡
		echo 'Page error: ' . $response_code . $html;
		$pageerror = 1;
	
		//$pid=$pid+1;
	} else {
		//echo "\n" . $url . "  ==>GOT";
	
		//echo $response_code.'-';
	}
	curl_close ( $curl ); // close the connection
	

	return $html; // and finally, return $html
}
