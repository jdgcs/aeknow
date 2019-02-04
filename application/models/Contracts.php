<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contracts extends CI_Model {

public function getContractList(){
	$this->load->database();
	//$sql="select distinct tx->'tx'->'contract_id' as cthash FROM txs WHERE tx->'tx' @> '{\"type\": \"ContractCallTx\"}' group by tx->'tx'->'block desc' ;";
	$sql="SELECT cthash,block_height FROM (SELECT DISTINCT ON (tx->'tx'->'contract_id') tx->'block_height' as block_height, tx->'tx'->'contract_id' as cthash FROM txs  WHERE tx->'tx' @> '{\"type\": \"ContractCallTx\"}') as tbl_contracts order by block_height desc;";
	//echo "$sql";
	$query = $this->db->query($sql);
	$data['cttable']="";$counter=0;
	
	foreach ($query->result() as $row){
		$cthash=$row->cthash;
		$block_height=$row->block_height;
		$cthash=str_replace("\"","",$cthash);
		//$block_height=$row->block_height;
		$url=DATA_SRC_SITE."v2/contracts/$cthash";
		
		$counter++;
		$websrc=$this->getwebsrc($url);
		
		//echo "$url;$websrc";
		if(strpos($websrc,"id")>0){
			$ctData=json_decode($websrc);
			$owner_id=$ctData->owner_id;
			$owner_id="<a href=/address/wallet/$owner_id>$owner_id</a>";
			$cthashlink="<a href=/contract/detail/$cthash>$cthash</a>";
			$block_height="<a href=/block/height/$block_height>$block_height</a>";
			$data['cttable'].="<tr><td>$counter</td><td>$cthashlink</td><td>$owner_id</td><td>$block_height</td></tr>";
		}
		
	}
	
	return $data;
}


public function getContractDetail($cthash){
	$url=DATA_SRC_SITE."v2/contracts/$cthash";
	$data['cttable']="";//$counter=0;
	$websrc=$this->getwebsrc($url);
	if(strpos($websrc,"id")>0){
		$ctData=json_decode($websrc);
		$owner_id=$ctData->owner_id;
		
		$owner_id="<a href=/address/wallet/$owner_id>$owner_id</a>";
		$cthashlink="<a href=/contract/detail/$cthash>$cthash</a>";
		//$data['cttable'].="<tr><td>$counter</td><td>$cthashlink</td><td>$owner_id</td></tr>";
	}	
	$data['owner_id']=$owner_id;
	$data['cthash']=$cthash;
	$this->load->database();
	$sql="select tx->'hash' as txhash,tx->'block_height' as block_height FROM txs WHERE tx->'tx' @> '{\"type\": \"ContractCallTx\"}' AND tx->'tx' @> '{\"contract_id\": \"$cthash\"}' order by tid desc limit 100;";
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){
		//$counter++;
		$txhash=$row->txhash;
		$txhash=str_replace("\"","",$txhash);
		$block_height=$row->block_height;
		$block_height="<a href=/block/height/$block_height>$block_height</a>";
		$txhash="<a href=/block/transaction/$txhash>$txhash</a>";
		$data['cttable'].="<tr><td>$block_height</td><td>$cthash</td><td>$txhash</td></tr>";
		}
	return $data;
	}

private function getwebsrc($url) {
	$curl = curl_init ();
	$agent = "User-Agent: AEKnow-bot";
	
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
	//	echo 'Page error: ' . $response_code . $html;	
		$html='Page error: ' . $response_code.$html;
	} 
	curl_close ( $curl ); // close the connection

	return $html; // and finally, return $html
}

public function checkAddress($address){
		//Thanks Jungle @Beepool    
        $hex = $this->base58_decode($address);
    
        if (strlen($hex)!=72){
            return false;
        }
    
        $bs = pack("H*", substr($hex, 0,64));
    
        $checksum = hash("sha256", hash("sha256", $bs, true));
    
        $checksum = substr($checksum, 0, 8);
    
        if(substr($hex, 64,8)!==$checksum){
            return false;
        }
    
        return true;
    
    }
    
public function base58_decode($base58)
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
}
