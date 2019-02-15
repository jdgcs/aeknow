<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Oracles extends CI_Model {

public function getOracleList(){
	$this->load->database();
	$topheight=$this->GetTopHeight();
	$sql="SELECT DISTINCT(CONCAT(aid ,oid) ) as oracle_id FROM (SELECT (tx->'tx'->'oracle_ttl'->>'value')::numeric as ttl, (tx->>'block_height')::numeric as block_height,regexp_replace(((tx->'tx'->'account_id')::text),'ak_','ok_') as aid,(tx->'tx'->'oracle_id')::text as oid from txs WHERE txtype='OracleRegisterTx' or txtype='OracleExtendTx') as tbl_active WHERE (ttl+block_height)>$topheight;";
	//echo "$sql";
	$query = $this->db->query($sql);
	$data['ortable']="";$counter=0;
	$data['ortable_all']="";$counter_all=0;
	
	foreach ($query->result() as $row){
		$counter++;
		$oracle_id=$row->oracle_id;
		$oracle_id=str_replace('"','',$oracle_id);
		$account_id=str_replace("ok_","ak_",$oracle_id);
		
		$data['ortable'].="<tr><td>$counter</td><td>$oracle_id</td><td><a href=/address/wallet/$account_id>$account_id</a></td><td><span class='badge bg-green'>Active</span></td></tr>";		
	}
	
	
	
	$sql="SELECT DISTINCT(CONCAT(aid ,oid) ) as oracle_id FROM (SELECT (tx->'tx'->'oracle_ttl'->>'value')::numeric as ttl, (tx->>'block_height')::numeric as block_height,regexp_replace(((tx->'tx'->'account_id')::text),'ak_','ok_') as aid,(tx->'tx'->'oracle_id')::text as oid from txs WHERE txtype='OracleRegisterTx' or txtype='OracleExtendTx') as tbl_active WHERE (ttl+block_height)<$topheight;";
	$query = $this->db->query($sql);	
	foreach ($query->result() as $row){
		$counter_all++;
		$oracle_id=$row->oracle_id;
		$oracle_id=str_replace('"','',$oracle_id);
		$account_id=str_replace("ok_","ak_",$oracle_id);		
		$data['ortable_all'].="<tr><td>$counter_all</td><td>$oracle_id</td><td><a href=/address/wallet/$account_id>$account_id</a></td><td><span class='badge bg-red'>Lived</span></td></tr></tr>";		
	}
	
	return $data;
}


public function getOracleDetail($cthash){
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

private function GetTopHeight()	{
	$url=DATA_SRC_SITE."v2/blocks/top";
	$websrc=$this->getwebsrc($url);
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
