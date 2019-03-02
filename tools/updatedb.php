<?php
include "config.php";
/*
function FirstSpider(){
	for($i=0;$i<645;$i++){
		$url="http://52.10.46.160:3013/v2/key-blocks/height/$i"
		}
	}
	**/
//$websrc='{"key_block":{"beneficiary":"ak_2jeZdTjvq1pzWaisdkVXtKphxLi1BTswBfmiwnQWkadBqwidoA","hash":"kh_2fCovqZxPAmsW5B3AnUF9kG6psTywuzeJezVSEkA6r1VV4QDX2","height":643,"miner":"ak_7zXxPt4SZego8Tbt1d4LqjmAbPHXaB8MTLY9B2ePmTKdC46Qa","nonce":10771920957829361054,"pow":[60421166,104849277,123973928,147813356,155435793,183407509,194548223,198040150,208183816,208580651,208673372,223765194,240596789,257706107,271448668,282082846,300931379,301301355,306364185,326428264,326765357,329606881,329864163,344306208,367931446,372896229,374776080,375412590,376355404,393202444,404602444,408539671,417415497,425369102,438928741,451579899,458597028,470206446,482944679,491049260,526708104,533852027],"prev_hash":"kh_j1dJQtiAE59CTv3CVBaNkwiMWe6hMJGJ6Uzj8fiCjNmjhDdqV","prev_key_hash":"kh_j1dJQtiAE59CTv3CVBaNkwiMWe6hMJGJ6Uzj8fiCjNmjhDdqV","state_hash":"bs_2X154KWm1LEKj3X9RiUhE4nsAWWVTBTCcYuzajjZfwJAkoQ6g4","target":536910087,"time":1543060783242,"version":31}}';
//$url="http://52.10.46.160:3013/v2/key-blocks/height/459";
while(1){
$dbheight=GetDBHeight();
$topheight=GetTopHeight();
$spiderheight=1;
if($topheight>$dbheight){
	if($dbheight>10){$spiderheight=$dbheight-10;}
	for($i=$spiderheight;$i<$topheight+1;$i++){
		ProcessHTML($i);usleep(1000);
		}
	}
sleep(5);
}

function ProcessHTML($height){
	$url=DATA_SRC_SITE."v2/key-blocks/height/$height";//http://35.178.61.73
//$url="http://35.178.61.73:3013/v2/key-blocks/height/$height";
	//echo "$url\n";
	$websrc=getwebsrc($url);
	$info=json_decode($websrc);
	//echo $websrc;
	//$pattern='/{"beneficiary\":\"(.*)\",\"hash\":\"(.*)\",\"height\":(.*),\"miner\":\"(.*)\",\"nonce\":(.*),\"pow\":(.*),\"prev_hash\":\"(.*)\",\"prev_key_hash\":\"(.*)\",\"state_hash\":\"(.*)\",\"target\":(.*),\"time\":(.*),\"version\":(.*)}/i';
//	$pattern='//i';
	//preg_match($pattern,$websrc, $match);
	$beneficiary=$info->beneficiary;
	$hash=$info->hash;
	$height=$info->height;
	$miner=$info->miner;
	$nonce=$info->nonce;
	$pow=json_encode($info->pow);
	$prev_hash=$info->prev_hash;
	$prev_key_hash=$info->prev_key_hash;
	$state_hash=$info->state_hash;
	$target=$info->target;
	$time=$info->time;
	$version=$info->version;
	//$info=$info->info;
	
	$sql="select height,beneficiary,hid,orphan FROM miner WHERE height=$height";
	//echo $sql."\n";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
			if (!$result_query) {
				echo pg_last_error($db);
				exit;
			}
	if (pg_num_rows($result_query) == 0) {
		$sql="INSERT INTO miner(beneficiary,hash,height,miner,nonce,pow,prev_hash,prev_key_hash,state_hash,target,time,version,totaltxt,orphan) VALUES('$beneficiary','$hash',$height,'$miner',$nonce,'$pow','$prev_hash','$prev_key_hash','$state_hash',$target,$time,$version,'$websrc',FALSE)";
		//echo $sql."\n";
		 $result_insert = pg_query($db, $sql);
            if (!$result_insert) {
                echo pg_last_error($db);
                //exit;
            }else{echo "Height:$height inerted.\n";updateTotalMined();}
		}else{
			while ($row = pg_fetch_row($result_query)) {
				if($row[1]!=$beneficiary &&($row[3] == FALSE)){
					$orphan_sql= "UPDATE miner SET orphan=TRUE WHERE hid=".$row[2];					
					$result_orphan = pg_query($db, $orphan_sql);echo "Orphan found.\n";
					$orphan_insert="INSERT INTO miner(beneficiary,hash,height,miner,nonce,pow,prev_hash,prev_key_hash,state_hash,target,time,version,totaltxt,orphan) VALUES('$beneficiary','$hash',$height,'$miner',$nonce,'$pow','$prev_hash','$prev_key_hash','$state_hash',$target,$time,$version,'$websrc',FALSE)";		
					$result_orphan_new = pg_query($db, $orphan_insert);echo "Long chain inserted.\n";
					}
			}
			
			}
	//return $match[3];
	//print_r($match);sleep(2);

	}
//echo GetDBHeight();
//echo GetTopHeight();
//echo $websrc;
function GetDBHeight(){
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$sql = "SELECT height FROM miner order by height desc limit 1";
	$result_query = pg_query($db, $sql);
			if (!$result_query) {
				echo pg_last_error($db);
				exit;
			}
	if (pg_num_rows($result_query) == 0) {return 1;}
	while ($row = pg_fetch_row($result_query)) {
		return $row[0];
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

function updateTotalMined(){
	$totalcoin= 276450333.49932+getTotalMined();
	$filename="/dev/shm/totalcoin";
	$myfile = fopen($filename, "w");
	fwrite($myfile, $totalcoin);
	fclose($myfile);
	}


function getTotalMined(){
		$latestheight=GetTopHeight();
		$totalmined=0;
		for($i=1;$i<$latestheight+1;$i++){
			$totalmined=$totalmined+getReward($i);
			}
		return $totalmined;
		}

function getReward($blockheight){
		$blockheight=$blockheight+1;
		$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
		$db = pg_connect($conn_string);
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$result_query = pg_query($db, $sql);			

		while ($row = pg_fetch_row($result_query)) {
			return $row[0]/10;
			 }
		}
