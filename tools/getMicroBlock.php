<?php
//FullBlockSpider();


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
	if(strpos($websrc,"micro_block")>0){
		$pattern='/(.*)"prev_hash":"(.*)","prev_key_hash(.*)/i';
		$microblock=preg_match($pattern,$websrc, $match);
		if(strpos("dd".$match[2],"mh_")>0){
			ProcessMicroBlock($match[2]);	
		}
		
		}
	}



function selfSpider(){
	//$sql="select prev_hash from microblock where prev_hash LIKE 'mh_%' AND height>".(GetTopHeight()-100)." order by time desc";
	$sql="select prev_hash from microblock where prev_hash LIKE 'mh_%' order by time desc";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessMicroBlock($row[0]);	
	}	
}


function keyBlockSpider(){
	$sql="select prev_hash from miner where prev_hash LIKE 'mh_%' AND height>".(GetTopHeight()-100);
	$sql="select prev_hash from miner where prev_hash LIKE 'mh_%'";
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
		$pattern='/{"hash":"(.*)","height":(.*),"pof_hash":"(.*)","prev_hash":"(.*)","prev_key_hash":"(.*)","signature":"(.*)","state_hash":"(.*)","time":(.*),"txs_hash":"(.*)","version":(.*)}/i';
		preg_match($pattern,$websrc, $match);
			$hash=$match[1];
			$height=$match[2];
			$pof_hash=$match[3];
			$prev_hash=$match[4];
			$prev_key_hash=$match[5];
			$signature=$match[6];
			$state_hash=$match[7];
			$time=$match[8];
			$txs_hash=$match[9];
			$version=$match[10];
		$sql_insert="INSERT INTO microblock(hash,height,pof_hash,prev_hash,prev_key_hash,signature,state_hash,time,txs_hash,version) VALUES('$hash',$height,'$pof_hash','$prev_hash','$prev_key_hash','$signature','$state_hash',$time,'$txs_hash',$version)";
		$result_insert = pg_query($db1, $sql_insert);		
		echo "\n$microhash ...inserted\n";
		if($prev_hash!=$prev_key_hash){ProcessMicroBlock($prev_hash);echo "prev:\n";}
		}
	}

function GetTopHeight()	{
	$url="http://127.0.0.1:3013/v2/blocks/top";
	$websrc=getwebsrc($url);
	if(strpos($websrc,"key_block")==TRUE){
		$pattern='/{\"key_block\":{"beneficiary\":\"(.*)\",\"hash\":\"(.*)\",\"height\":(.*),\"miner\":\"(.*)\",\"nonce\":(.*),\"pow\":(.*),\"prev_hash\":\"(.*)\",\"prev_key_hash\":\"(.*)\",\"state_hash\":\"(.*)\",\"target\":(.*),\"time\":(.*),\"version\":(.*)}}/i';
		preg_match($pattern,$websrc, $match);
		return $match[3];
	}
	
	if(strpos($websrc,"micro_block")==TRUE){
		$pattern='/(.*),"height":(.*),"pof_hash"(.*)/i';
		preg_match($pattern,$websrc, $match);
		return $match[2];
		}
	
	return 0;
	//print_r($match);
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
