<?php
include "config.php";

while(1){
	routineImport();
	sleep(2);
	echo ".";
	}

function routineImport(){
	$sql="SELECT address FROM contracts_token";
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
	$url=DATA_SRC_SITE."v2/contracts/$hash/store";
	$websrc=getwebsrc($url);
	$ctData=json_decode($websrc);
	$url="http://localhost:3113/v2/debug/contracts/code/decode-data";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db1 = pg_connect($conn_string);
	
	for($i=0;$i<count($ctData->store);$i++){
		//echo $ctData->store[$i]->key."=>".$ctData->store[$i]->value."\n";
		$key=$ctData->store[$i]->key;
		$value=$ctData->store[$i]->value;
		$jsonStr= "{ \"data\": \"$value\", \"sophia-type\": \"int\"}";
		$return= http_post_json($url, $jsonStr);  
		//print_r($return);
		$value_decoded=json_decode($return[1]); 
		if(strlen($key)>120){
			//echo  substr($key,strlen($key)-64,64)."=>".$value_decoded->data->value."\n";
			$address=substr($key,strlen($key)-64,64);
			$balance=$value_decoded->data->value;
			
			$sql="SELECT balance FROM tokens WHERE address='$address' AND contract ='$hash'";
			$db1 = pg_connect($conn_string);
			$result_query1 = pg_query($db1, $sql);
			
			if (pg_num_rows($result_query1) == 0) {
					$sql_insert="INSERT INTO tokens(address,contract,balance) VALUES('$address','$hash',$balance)";
					$result_insert = pg_query($db1, $sql_insert);
					echo "tx $address inerted.\n";
				}else{
					$needupdate=TRUE;
					while ($row1= pg_fetch_row($result_query1)) {
						if($row1[0]==$balance){$needupdate=FALSE;};	
					}
					
					if($needupdate){
						$sql_update="UPDATE tokens SET balance=$balance WHERE address='$address' AND contract ='$hash'";
						$result_update = pg_query($db1, $sql_update);
						echo "tx $address updated.\n";
						}
					
					}
		}
		}	

}

function http_post_json($url, $jsonStr)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
 
    return array($httpCode, $response);
}

function base58_decode($base58)
    {
        $origbase58 = $base58;
        $return = "0";
    
        for ($i = 0; $i < strlen($base58); $i++) {
            // return = return*58 + current position of $base58[i]in self::$base58chars
            $return = gmp_add(gmp_mul($return, 58), strpos("123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz", $base58[$i]));
        }
        $return = gmp_strval($return, 16);
        for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == "1"; $i++) {
            $return = "00" . $return;
        }
        if (strlen($return) % 2 != 0) {
            $return = "0" . $return;
        }
        return $return;
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
