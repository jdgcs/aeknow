<?php
//updateSpendTx();
//updateTx();
updateAENSExpired();
function updateAENSExpired(){
	$conn_string = "host=127.0.0.1 port=5432 dbname=ae user=postgres";
	$db1 = pg_connect($conn_string);
	
	$sql="SELECT nsid,aename FROM regaens WHERE expired is NULL";
	$result = pg_query($db1, $sql);
	while ($row = pg_fetch_row($result)) {
		$nsid=$row[0];
		$aename=$row[1];
		$url="http://127.0.0.1:3013/v2/names/$aename.test";
		$websrc=getwebsrc($url);
		
		if(strpos($websrc,"ttl")>0){
			$info=json_decode($websrc);
			$ttl=$info->ttl;			
			$sql_update="UPDATE regaens SET expired=$ttl WHERE nsid=$nsid";
			if(pg_query($db1, $sql_update)){
				echo "$aename...updated.\n";
				}		
		}
		
		}
	}


function updateTx(){
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db1 = pg_connect($conn_string);
	
	$sql="SELECT tid,tx,txtype FROM txs WHERE sender_id is NULL";
	$result = pg_query($db1, $sql);
	while ($row = pg_fetch_row($result)) {
		$tid=$row[0];
		$tx=json_decode($row[1]);
		$type=$row[2];
		//$info=json_decode($tx);
		//$sql_update="UPDATE txs SET sender_id='$sender_id',recipient_id='$recipient_id' WHERE tid=$tid";
		
		if($type=="OracleRegisterTx" ||$type=="NameClaimTx" || $type=="NameRevokeTx" ||$type=="NameTransferTx"||$type=="NamePreclaimTx"||$type=="NameUpdateTx"){
			$sender_id=$tx->tx->account_id;			
			$sql_update="UPDATE txs SET sender_id='$sender_id' WHERE tid=$tid";
			}
		
		if($type=="OracleQueryTx"||$type=="OracleResponseTx" ||$type=="OracleExtendTx" ){
			$sender_id=str_replace("ok_","ak_",$tx->tx->oracle_id);
			$sql_update="UPDATE txs SET sender_id='$sender_id' WHERE tid=$tid";
			}
		
		if($type=="ContractCreateTx"){
			$sender_id=$tx->tx->owner_id;
			$sql_update="UPDATE txs SET sender_id='$sender_id' WHERE tid=$tid";
			}
		
		if($type=="ContractCallTx"){
			$sender_id=$tx->tx->caller_id;
			$sql_update="UPDATE txs SET sender_id='$sender_id' WHERE tid=$tid";
			}
		
		if($type=="ChannelCreateTx"){
			$sender_id=$tx->tx->initiator_id;
			$recipient_id=$tx->tx->responder_id;
			$sql_update="UPDATE txs SET sender_id='$sender_id',recipient_id='$recipient_id' WHERE tid=$tid";
			}
		
		if($type=="ChannelDepositTx" || $type=="ChannelForceProgressTx"|| $type=="ChannelCloseSoloTx" || $type=="ChannelCloseMutualTx" || $type=="ChannelSettleTx" ){
			$sender_id=$tx->tx->from_id;
			$sql_update="UPDATE txs SET sender_id='$sender_id' WHERE tid=$tid";
			}
		
		if($type=="ChannelWithdrawTx"){
			$recipient_id=$tx->tx->to_id;
			$sql_update="UPDATE txs SET recipient_id='$recipient_id' WHERE tid=$tid";
			}
		//echo "$tid:$sender_id:$recipient_id\n";
		

		if(pg_query($db1, $sql_update)){
			echo "$tid...updated.\n";
			}
		}
	}



function updateSpendTx(){
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db1 = pg_connect($conn_string);
	
	$sql="SELECT tid,tx FROM txs WHERE txtype='SpendTx' and sender_id is NULL limit 2000";
	$result = pg_query($db1, $sql);
	while ($row = pg_fetch_row($result)) {
		$tid=$row[0];
		$tx=$row[1];
		$info=json_decode($tx);
		$sender_id=$info->tx->sender_id;
		$recipient_id=$info->tx->recipient_id;
		//echo "$tid:$sender_id:$recipient_id\n";
		$sql_update="UPDATE txs SET sender_id='$sender_id',recipient_id='$recipient_id' WHERE tid=$tid";
		//$result_update = pg_query($db1, $sql_update);
		if(pg_query($db1, $sql_update)){
			echo "$tid...updated.\n";
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
