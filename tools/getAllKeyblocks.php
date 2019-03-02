<?php
include "config.php";

while(1){
	$dbheight=GetDBHeight();
	$topheight=GetTopHeight();
	$spiderheight=1;
	if($topheight>$dbheight){
		if($dbheight>10){$spiderheight=$dbheight-10;}
		for($i=$spiderheight;$i<$topheight+1;$i++){
			ProcessHTML($i);usleep(10000);
			}
		}
	sleep(5);
}

function ProcessHTML($height){
	//$url=DATA_SRC_SITE."v2/key-blocks/height/$height";
	$url="http://35.178.61.73:3013/v2/key-blocks/height/$height";
	$websrc=getwebsrc($url);	
	
	$sql="select height FROM keyblocks WHERE height=$height";
	//echo $sql."\n";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
			if (!$result_query) {
				echo pg_last_error($db);
				exit;
			}
	if (pg_num_rows($result_query) == 0) {
		$sql="INSERT INTO keyblocks(height,data) VALUES($height,'$websrc')";
		//echo $sql."\n";
		 $result_insert = pg_query($db, $sql);
            if (!$result_insert) {
                echo pg_last_error($db);
                //exit;
            }else{echo "Height:$height inerted.\n";}
		}
	}

function GetDBHeight(){
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$sql = "SELECT height FROM keyblocks order by height desc limit 1";
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
/*
 {"micro_block":{"hash":"mh_2otYHyjyxaA327PCMAoCT5c9Aoiv4Ap1Rx3jZnwbYvn5amqivH","height":3067,"pof_hash":"no_fraud","prev_hash":"kh_2JUhRfB1degM3LjVaxRP9PCentMhqC49KDDuCzRS96L2NKxFEJ","prev_key_hash":"kh_2JUhRfB1degM3LjVaxRP9PCentMhqC49KDDuCzRS96L2NKxFEJ","signature":"sg_2fxh4kZwN45HJo4aiQoJViSHrzTrsBLindojkPNuZUZz3phDzLxvoPHEZpNMAaMkD9gwRHM8i3e8ySt42sGehGc1HQENd","state_hash":"bs_2Vocro2sHqc2temptLikibZzhCowiy6joEBvHPSFGU2Z78aeue","time":1543914687559,"txs_hash":"bx_2jojKnqiZ6PgUEwf2c4Z8gH6fZimSLCUaQUtY61k1ByYzZQRe7","version":1}}
 {"micro_block":{"hash":"mh_mcupTqhUw3zKpSPkhNzkcPzoEhaJoT8b3QtsY1DPbm3BhyVB","height":3180,"pof_hash":"no_fraud","prev_hash":"mh_zDNafTXjWBbqHGPB4eD3UmA4HzvsEEtXo1ei51wRvPvpZKTrd","prev_key_hash":"kh_2XD2etP4em2J33xRtTEn2SgkCn8VVSWbC58PEgnPhBEdSN7wkk","signature":"sg_Tn2AyqeNYmdEawzFhWmK1QTMxqFzrmwBmshk1CgXJ2tHAETJqrNbvHGtWavPwU453Yfdgehe5fjBeMQiZCPkSyHhNgwqX","state_hash":"bs_27FZ7EWwzsfDQb6no8iGMJ9vLwb4ahC8LoHFNSugWQnTYo6Ybd","time":1543933713494,"txs_hash":"bx_rLifrNvTTQvAs2JiXvkibbac6tzC3LYnkrUrLSGiGfYZySK4X","version":1}}
  {"micro_block":{"hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","height":3639,"pof_hash":"no_fraud","prev_hash":"kh_P3WDCYyW6CLn4e8dQbCbHjDekn54muepoNyFT9NRw1MDVytN9","prev_key_hash":"kh_P3WDCYyW6CLn4e8dQbCbHjDekn54muepoNyFT9NRw1MDVytN9","signature":"sg_PW3Gwqm69wGX7Co2TurxjQ9mfMozFDxgodjGU8dwAbNTh1CwFDTKxri18Si2adqkcdXZw2sxFi5XAdYSx5UCjL6TKuDDR","state_hash":"bs_mCGgDYBqfgNS3d46UY3qsjewnktDx7Codrc5nfr5hEDRs2xLQ","time":1544015618780,"txs_hash":"bx_QYsZ4kQE3SDhKi2YNRqvZ11CwuXuf43xgvPKCTQUKPhGSoa8m","version":1}}
 {"micro_block":{"hash":"mh_84MEYCNdJvRiJeW94khQZ7wUhReWahG3KUwT5knP7h6z83iyz","height":3663,"pof_hash":"no_fraud","prev_hash":"mh_2wFkCK5eMbaUXfFH7v9cX5a1qodYruCFJdSQ42PHQ5VGPJNzhJ","prev_key_hash":"kh_2F1QtvjQUiTbPxgVriqHtT7tGChJQSCLdRxAFSDebScdddPvLZ","signature":"sg_JZB7X1FmKbpE88cMrUwaShUYCmL3TRAHnwy8wex758sSkQ7rdugH1f4LGBV9cFBb2ajbWqqaRJZWCuD1UbgJiH48LryU4","state_hash":"bs_H8pYTHPs75iUySAwaw9P1NSL1z1s1nCdQ7pTsYaMtqFBiFqwr","time":1544019093392,"txs_hash":"bx_2kZZNJsTCi2jA7QtwGoCFWxJ5P2qTdMdiLanCeZovbqskNhsEu","version":1}}
                {"hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","height":3639,"pof_hash":"no_fraud","prev_hash":"kh_P3WDCYyW6CLn4e8dQbCbHjDekn54muepoNyFT9NRw1MDVytN9","prev_key_hash":"kh_P3WDCYyW6CLn4e8dQbCbHjDekn54muepoNyFT9NRw1MDVytN9","signature":"sg_PW3Gwqm69wGX7Co2TurxjQ9mfMozFDxgodjGU8dwAbNTh1CwFDTKxri18Si2adqkcdXZw2sxFi5XAdYSx5UCjL6TKuDDR","state_hash":"bs_mCGgDYBqfgNS3d46UY3qsjewnktDx7Codrc5nfr5hEDRs2xLQ","time":1544015618780,"txs_hash":"bx_QYsZ4kQE3SDhKi2YNRqvZ11CwuXuf43xgvPKCTQUKPhGSoa8m","version":1}
 {"transactions":[{"block_hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","block_height":3639,"hash":"th_2VTeuFjwvXZjzRphfiCsRBhcxa85uf2QWAG1Ebx1Ny3BqS8kba","signatures":["sg_aHHn9JC5y1gTCXNRybzhGBTKh7bv7nze2UXvrWNaMANyCdCkYhCmhi67T4D6z2h8ZaJ2HteYRvbR1hHWUXd8yZVKmypLH"],"tx":{"amount":1000000000,"fee":30000,"nonce":1184,"payload":"posted at 1544015541903","recipient_id":"ak_2aesGg6iGWmAngmQQoVfpEH7Z6fZ6XvUairgNfdBhmd7uTDsxN","sender_id":"ak_dzNxUcKsLSiE9p6pkjdLpP1ai8UsCuhAZMeDTCjUh31DBQVNv","ttl":3660,"type":"SpendTx","version":1}},{"block_hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","block_height":3639,"hash":"th_r5rbE1jasC2Feebm46TnehGNvDi3vdXtQUx4df9nVYWWmwi8f","signatures":["sg_XmCKErX9y56MkEu2oWwHeU99RzhP2EJr182SdqHmZ4Jv7uczK2Sr7gENTzfVmVMuwmHsXQPPa3PFNCakMZfFYqGBVNMhU"],"tx":{"amount":1000000000,"fee":30000,"nonce":1185,"payload":"posted at 1544015552072","recipient_id":"ak_2DpsuKez9gr1FFSSw5kN3Twf7gfoTrnrN4t6J4SYXLNZPgXqRX","sender_id":"ak_dzNxUcKsLSiE9p6pkjdLpP1ai8UsCuhAZMeDTCjUh31DBQVNv","ttl":3660,"type":"SpendTx","version":1}},{"block_hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","block_height":3639,"hash":"th_VHWLRehnnupckaa8w5oHRJm8CGMbsQVCAeLzRLWMM9pR6AdmP","signatures":["sg_Knttdd6fac9AMKqYZpbXB6Eko4qCBxiq35vYYzemZwR1zsXHbs8PRZDSQvjyyDASDyUDv9To5k8W7zZz1UPm3UFpYKvfE"],"tx":{"amount":1000000000,"fee":30000,"nonce":1186,"payload":"posted at 1544015560718","recipient_id":"ak_8ysT9nTxb5jyHnXaGM6MxxU6o73DRC9FVPKa1968yxivnvZAH","sender_id":"ak_dzNxUcKsLSiE9p6pkjdLpP1ai8UsCuhAZMeDTCjUh31DBQVNv","ttl":3660,"type":"SpendTx","version":1}},{"block_hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","block_height":3639,"hash":"th_zF1JMkp2rEEdtgpfD53VFBUGCC5ffMJ99KzrgCambvjsWTaHU","signatures":["sg_6kFtzqbc4Qm5QkApMWNH1FwoH1YFapTUAYs1k6Svkh48zyXbkZbATkgbkaDK7KHXcr3xtdggn9ku2UNt2eq71Sym8gsqs"],"tx":{"amount":1000000000,"fee":30000,"nonce":1187,"payload":"posted at 1544015569946","recipient_id":"ak_2W3wx9JjBv64J2Ma7iAXyygAgfMEHM2avMzvMbSqs151uVTU5U","sender_id":"ak_dzNxUcKsLSiE9p6pkjdLpP1ai8UsCuhAZMeDTCjUh31DBQVNv","ttl":3660,"type":"SpendTx","version":1}},{"block_hash":"mh_wvjSxwjVn7HYonzLgRpE4nL486j4QV8kCq5VNXKLyF3YQDa5t","block_height":3639,"hash":"th_2CCvqmNu46Unu3AXqoAh4ErGbgtJgfFasHfRzxQdj832rxBU4v","signatures":["sg_4sFeDykrEaUGpoqwrfZX92ehb66XC2AmnRp2TgTenxocBuKUAGUy1eajjJh6tDgvrbZjWn31piaBUrJR5Gw7UgBybnGAS"],"tx":{"amount":1000000000,"fee":30000,"nonce":1188,"payload":"posted at 1544015578571","recipient_id":"ak_2e2JAJFCjwsjUiXitATpWYbEikH9CpEUdjyDmeE3guX5QKKdhF","sender_id":"ak_dzNxUcKsLSiE9p6pkjdLpP1ai8UsCuhAZMeDTCjUh31DBQVNv","ttl":3660,"type":"SpendTx","version":1}}]}
 
 curl -X GET "http://127.0.0.1:3013/v2/transactions/bx_2jojKnqiZ6PgUEwf2c4Z8gH6fZimSLCUaQUtY61k1ByYzZQRe7" -H "accept: application/json" 
 * */

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
