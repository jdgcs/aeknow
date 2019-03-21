<?php
include "config.php";
//firstTimeImport();
while(1){
	routineImport();
	sleep(2);
	echo ".";
	}

function routineImport(){
	//$sql="SELECT hash FROM microblocks WHERE height>".(GetTopHeight()-5)." order by height desc";
	$sql="SELECT hash FROM microblock WHERE height>".(GetTopHeight()-3)." order by time desc";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessTransactions($row[0]);	
	}
}

function firstTimeImport(){
	$sql="SELECT hash FROM microblock order by height ";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	while ($row = pg_fetch_row($result_query)) {
		ProcessTransactions($row[0]);	
	}
}

function ProcessTransactions($hash){
        $url=DATA_SRC_SITE."v2/micro-blocks/hash/$hash/transactions";
        //echo " $url\n";
        $ttl=0;
        $websrc=getwebsrc($url);
        if(strpos($websrc,"hash")==false){echo $websrc ;return 0;}
        $info=json_decode($websrc);
        $txcounter=count($info->transactions);	
        for($m=0;$m<$txcounter;$m++){
                $type=$info->transactions[$m]->tx->type;
                //print_r($info->transactions[$m]);
                //sleep(200);
                $txhash=$info->transactions[$m]->hash;
                $block_hash=$info->transactions[$m]->block_hash;        
                $sql="SELECT txhash from txs WHERE txhash='$txhash'";
                $conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
                $db1 = pg_connect($conn_string);
                $result_query1 = pg_query($db1, $sql);

                if (pg_num_rows($result_query1) == 0) {
						$tx=json_encode($info->transactions[$m]);
                        $sql_insert="INSERT INTO txs(txtype,txhash,tx) VALUES('$type','$txhash','$tx')";
                        
                        if($type=="SpendTx"){
							$sender_id=$info->transactions[$m]->tx->sender_id;
							$recipient_id=$info->transactions[$m]->tx->recipient_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id,recipient_id) VALUES('$type','$txhash','$tx','$sender_id','$recipient_id')";
							checkAccountDB($sender_id);checkAccountDB($recipient_id);
							}
                        
                        if($type=="OracleRegisterTx" ||$type=="NameClaimTx" || $type=="NameRevokeTx" ||$type=="NameTransferTx"||$type=="NamePreclaimTx"||$type=="NameUpdateTx"){
							$sender_id=$info->transactions[$m]->tx->account_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id) VALUES('$type','$txhash','$tx','$sender_id')";
							}
                        
                        if($type=="OracleQueryTx"||$type=="OracleResponseTx" ||$type=="OracleExtendTx" ){
							$sender_id=str_replace("ok_","ak_",$info->transactions[$m]->tx->oracle_id);
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id) VALUES('$type','$txhash','$tx','$sender_id')";
							}
                        
                        if($type=="ContractCreateTx"){
							$sender_id=$info->transactions[$m]->tx->owner_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id) VALUES('$type','$txhash','$tx','$sender_id')";
							}
                        
                        if($type=="ContractCallTx"){
							$sender_id=$info->transactions[$m]->tx->caller_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id) VALUES('$type','$txhash','$tx','$sender_id')";
							}
                        
                        if($type=="ChannelCreateTx"){
							$sender_id=$info->transactions[$m]->tx->initiator_id;
							$recipient_id=$info->transactions[$m]->tx->responder_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id,recipient_id) VALUES('$type','$txhash','$tx','$sender_id','$recipient_id')";
							}
                        
                        if($type=="ChannelDepositTx" || $type=="ChannelForceProgressTx"|| $type=="ChannelCloseSoloTx" || $type=="ChannelCloseMutualTx" || $type=="ChannelSettleTx" ){
							$sender_id=$info->transactions[$m]->tx->from_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,sender_id) VALUES('$type','$txhash','$tx','$sender_id')";
							}
                        
                        if($type=="ChannelWithdrawTx"){
							$recipient_id=$info->transactions[$m]->tx->to_id;
							$sql_insert="INSERT INTO txs(txtype,txhash,tx,recipient_id) VALUES('$type','$txhash','$tx','$recipient_id')";
							}
                        
                        
                        
                        //echo "$sql_insert\n";sleep(2);
                        
                        $result_insert = pg_query($db1, $sql_insert);
                      //  echo "$sql_insert\n";sleep(20);
                        echo "$type $txhash inerted.\n";
                        }//else{echo "$type $txhash in DB.\n";}

                }
}

function checkAccountDB($address){
        $conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
        $db2 = pg_connect($conn_string);
        $sql = "SELECT * FROM accountsinfo WHERE address='$address'";
        $result_query = pg_query($db2, $sql);
        if (pg_num_rows($result_query) == 0) {
                $sql_insert="INSERT INTO accountsinfo(address) VALUES('$address')";
                        $result_query1 = pg_query($db2, $sql_insert);echo ".";
                }
        
        $ak=$address;
        $url=DATA_SRC_SITE."v2/accounts/$ak";
		$websrc=getwebsrc($url);
		
		$balance=0;
		if(strpos($websrc,"balance")==TRUE){
			   $info=json_decode($websrc);
			   $balance=$info->balance;
			   $readtime=time();
			$sql_update="UPDATE accountsinfo SET balance=$balance,readtime=$readtime WHERE address='$ak'";
			$result_insert = pg_query($db2, $sql_update);echo "U";
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
