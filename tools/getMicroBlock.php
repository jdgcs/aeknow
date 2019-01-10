<?php
while(1){
	topImport();
	keyBlockSpider();
	selfSpider();
	sleep(2);
	echo ".";
}


function topImport(){
	$url="http://127.0.0.1:3013/v2/blocks/top";
	$websrc=getwebsrc($url);
	$info=json_decode($websrc);
	if(strpos($websrc,"micro_block")>0){
		$microblock=$info->micro_block->prev_hash;
		if(strpos("dd".$microblock,"mh_")>0){
			ProcessMicroBlock($microblock);	
		}
		
		}
	}



function selfSpider(){
	$sql="select prev_hash from microblock where prev_hash LIKE 'mh_%' AND height>".(GetTopHeight()-100)." order by time desc";
	//$sql="select prev_hash from microblock where prev_hash LIKE 'mh_%' order by time desc";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessMicroBlock($row[0]);	
	}	
}


function keyBlockSpider(){
	$sql="select prev_hash from miner where prev_hash LIKE 'mh_%' AND height>".(GetTopHeight()-100);
	//$sql="select prev_hash from miner where prev_hash LIKE 'mh_%'";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessMicroBlock($row[0]);	
	}	
}

function FullBlockSpider(){
	$sql="select prev_hash from miner order by hid desc";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		if(strpos("dd".$row[0],"mh_")>0){
			ProcessMicroBlock($row[0]);	
		}
	}	
}


function keyFullBlockSpider(){
	$sql="select prev_hash from miner where prev_hash LIKE 'mh_%'";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessMicroBlock($row[0]);	
	}	
}

function selfFullSpider(){
	$sql="select prev_hash from microblock where prev_hash LIKE 'mh_%'";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessMicroBlock($row[0]);	
	}	
}

function ProcessMicroBlock($microhash){
	$sql="SELECT * FROM microblock WHERE hash='$microhash'";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db1 = pg_connect($conn_string);
	$result_query1 = pg_query($db1, $sql);
	
	if (pg_num_rows($result_query1) == 0) {
		$url="http://127.0.0.1:3013/v2/micro-blocks/hash/$microhash/header";
		$websrc=getwebsrc($url);
		$websrc=getwebsrc($url);
		$info=json_decode($websrc);
			$hash=$info->hash;
			$height=$info->height;
			$pof_hash=$info->pof_hash;
			$prev_hash=$info->prev_hash;
			$prev_key_hash=$info->prev_key_hash;
			$signature=$info->signature;
			$state_hash=$info->state_hash;
			$time=$info->time;
			$txs_hash=$info->txs_hash;
			$version=$info->version;
		$sql_insert="INSERT INTO microblock(hash,height,pof_hash,prev_hash,prev_key_hash,signature,state_hash,time,txs_hash,version) VALUES('$hash',$height,'$pof_hash','$prev_hash','$prev_key_hash','$signature','$state_hash',$time,'$txs_hash',$version)";
		$result_insert = pg_query($db1, $sql_insert);		
		echo "\n$microhash ...inserted\n";
		if($prev_hash!=$prev_key_hash){ProcessMicroBlock($prev_hash);echo "prev:\n";}
		}
	}

function GetTopHeight()	{
	$url="http://127.0.0.1:3013/v2/blocks/top";
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
