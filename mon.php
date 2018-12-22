<?php
echo'
<html>
<head>
<meta http-equiv="refresh" content="10">  
</head>
<body>
';
//while(1){
//	system("clear");
	CheckBeneficiaryData();
	echo "<br />\n";
	CheckMinerData();
	$n=20;
	echo "<br />Last $n blocks:<br />\n";
	ShowLastNBlock($n);
//	sleep(10);
//	}

function CheckBeneficiaryData(){
	//$topheight=GetTopHeight();
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$sql="select distinct(beneficiary) FROM miner";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
			if (!$result_query) {
				echo pg_last_error($db);
				exit;
			}
	$beneficiarycount=0;
	while ($row = pg_fetch_row($result_query)) {
		$beneficiarycount++;
		$beneficiary= $row[0];
		echo "$beneficiary: ";
		$sql1="select count(*) FROM miner WHERE beneficiary='$beneficiary'";
		//$sql1="select count(*) FROM miner WHERE beneficiary='$beneficiary' AND height>".($topheight-100);
		//$sql1="select count(*) FROM miner WHERE beneficiary='$beneficiary' AND height>150";
		$result_beneficiary = pg_query($db, $sql1);
		while ($row1 = pg_fetch_row($result_beneficiary)) {
			echo $row1[0];
			}
		echo "<br/>\n";
		 }
	echo "Total Beneficiary: $beneficiarycount\n";
	}
function ShowLastNBlock($n)	{
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$sql="select beneficiary,height,time FROM miner order by height desc limit $n";
	$result_query = pg_query($db, $sql);
			if (!$result_query) {
				echo pg_last_error($db);
				exit;
			}
	
	$counter=0;
	while ($row = pg_fetch_row($result_query)) {
		$counter++;
		$millisecond =$row[2];
		$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
		$echostr=  $row[1].":".$row[0]." mined at ".date("Y-m-d H:i:s",$millisecond)."  ";
		$echostr=str_pad($echostr,60,0,STR_PAD_RIGHT);
		echo $echostr."<br/>";
		if($counter%2==0){echo "\n";}
		}
	}
	
function CheckMinerData(){
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$sql="select distinct(miner) FROM miner";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
			if (!$result_query) {
				echo pg_last_error($db);
				exit;
			}
	$minercount=0;
	while ($row = pg_fetch_row($result_query)) {
		$minercount++;
		$beneficiary= $row[0];
		//echo "$beneficiary: ";
		$sql1="select count(*) FROM miner WHERE miner='$beneficiary'";
		$result_beneficiary = pg_query($db, $sql1);
		while ($row1 = pg_fetch_row($result_beneficiary)) {
			//echo $row1[0];
			}
		//echo "\n";
		 }
	//echo "Total Miner:$minercount\n";
	}

//function GetBeneficiaryBlocks($beneficiary)
function GetTopHeight()	{
	$url="http://127.0.0.1:3013/v2/blocks/top";
	$websrc=getwebsrc($url);
	$pattern='/{\"key_block\":{"beneficiary\":\"(.*)\",\"hash\":\"(.*)\",\"height\":(.*),\"miner\":\"(.*)\",\"nonce\":(.*),\"pow\":(.*),\"prev_hash\":\"(.*)\",\"prev_key_hash\":\"(.*)\",\"state_hash\":\"(.*)\",\"target\":(.*),\"time\":(.*),\"version\":(.*)}}/i';
	preg_match($pattern,$websrc, $match);
	return $match[3];
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

