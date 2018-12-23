<?php
$file_handle = fopen ( "inflation.csv", "r" );
	while ( ! feof ( $file_handle ) ) {
		$linesrc= trim(fgets ( $file_handle ));	
		if($linesrc!=""){	
			ImportDB($linesrc);
		}
	}
fclose($file_handle);

function ImportDB($linesrc){
	$tmpstr=explode(";",$linesrc);
	$blokid=$tmpstr[0];
	$reward=$tmpstr[1]/100000000000000000;
	$totalamount=$tmpstr[2]/100000000000000000;
	$inflation=$tmpstr[3];
	
	$sql="INSERT INTO aeinflation(blockid,reward,totalamount,inflation) VALUES($blokid,$reward,$totalamount,$inflation)";
	$conn_string = "host=127.0.0.1 port=5432 dbname=postgres user=postgres";
	$db = pg_connect($conn_string);
	$result_query = pg_query($db, $sql);
	
	}
