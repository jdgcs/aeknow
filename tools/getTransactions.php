<?php
include "config.php";

while(1){
	routineImport();
	sleep(2);
	echo ".";
	}

function routineImport(){
	$sql="SELECT hash FROM microblock WHERE height>".(GetTopHeight()-50)." order by time desc";
	//$sql="SELECT hash FROM microblock order by time desc";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessTransactions($row[0]);	
	}
}

function firstTimeImport(){
	$sql="SELECT hash FROM microblock order by time ";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessTransactions($row[0]);	
	}
}

function ProcessTransactions($hash){
	$url=DATA_SRC_SITE."v2/micro-blocks/hash/$hash/transactions";
	$ttl=0;
	$websrc=getwebsrc($url);
	$info=json_decode($websrc);
	$txcounter=count($info->transactions);
	
	for($m=0;$m<$txcounter;$m++){
		$type=$info->transactions[$m]->tx->type;
		if($type=='SpendTx'){
			$block_hash=$info->transactions[$m]->block_hash;
			$block_height=$info->transactions[$m]->block_height;
			$hash=$info->transactions[$m]->hash;
			$signatures=$info->transactions[$m]->signatures[0];
			$amount=$info->transactions[$m]->tx->amount;
			$fee=$info->transactions[$m]->tx->fee;
			$nonce=$info->transactions[$m]->tx->nonce;
			$payload=$info->transactions[$m]->tx->payload;
			$recipient_id=$info->transactions[$m]->tx->recipient_id;
			$sender_id=$info->transactions[$m]->tx->sender_id;
			if (property_exists($info->transactions[$m]->tx,"ttl")){
				$ttl=$info->transactions[$m]->tx->ttl;
			}		
			$version=$info->transactions[$m]->tx->version;
									
			$sql="SELECT hash from transactions WHERE hash='$hash'";
			$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
			$db1 = pg_connect($conn_string);
			$result_query1 = pg_query($db1, $sql);
			
			if (pg_num_rows($result_query1) == 0) {
				$sql_insert="INSERT INTO transactions(block_hash,block_height,hash,signatures,amount,fee,nonce,payload,recipient_id,sender_id,ttl,type,version) VALUES('$block_hash',$block_height,'$hash','$signatures',$amount,$fee,$nonce,'$payload','$recipient_id','$sender_id',$ttl,'$type',$version)";
				//echo "$sql_insert\n";sleep(2);
				$result_insert = pg_query($db1, $sql_insert);
				echo "tx $hash inerted.\n";
				}
		}
		
		
		if($type=='NameUpdateTx'){
			//print_r($info);
			}
		
		if($type=='NamePreclaimTx'){
			//print_r($info);
			}
		
		
		if($type=='NameClaimTx'){
			//print_r($info);
			}
			
			
		}
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
