<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contracts extends CI_Model {

public function getContractList(){
	$this->load->database();
	$sql="SELECT * FROM contracts_token ORDER BY lastcall desc";

	$query = $this->db->query($sql);
	$data['cttable']="";$counter=0;
	
	foreach ($query->result() as $row){
		$cthash=$row->address;
		$cthash=str_replace("\"","",$cthash);	
		$ctype=$row->ctype;
		if(trim($ctype)==""){
			$ctype="Contract";
			}
		
		$alias=$row->alias;	
		if(trim($alias)==""){
			$alias="ct_****".substr($cthash,-4);
			}
			
		$cthash_show=$alias;	
		$counter++;		
		
		$block_height= $row->lastcall;	
		$calltime=$row->calltime;	
		$owner_id=$row->owner_id;
		
		
		$owner_id_show="ak_****".substr($owner_id,-4);
		$alias=$this->getalias($owner_id);
		if($owner_id!=$alias){
			$owner_id_show=$alias;
			}
		
		$owner_id="<a href=/address/wallet/$owner_id>$owner_id_show</a>";
		$cthashlink="<a href=/contract/detail/$cthash>$cthash_show</a>";
		$block_height="<a href=/block/height/$block_height>$block_height</a>";
		$data['cttable'].="<tr><td>$counter</td><td>$cthashlink</td><td>$ctype</td><td>$owner_id</td><td>$block_height</td><td>$calltime</td></tr>";
		
		
	}
	
	return $data;
}


public function getContractDetail($cthash,$page=1){
	$perpage=100;	
	$this->load->database();
	////Get basic info from db
	$sql="SELECT * FROM contracts_token WHERE address='$cthash'";
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){	
		
		$data['cthash']=$row->address;
		$data['owner_id']=$row->owner_id;
		$data['owner_id_show']="ak_****".substr($data['owner_id'],-4);
		$data['ctype']=$row->ctype;
		$data['alias']=$row->alias;	
		$data['decimal']=$row->decimal;	
		$data['remark']=$row->remark;	
		$data['lastcall']=$row->lastcall;
		$data['calltime']=$row->calltime;
		}
	if(trim($data['alias'])==""){$data['alias']="Contract";}
	if(trim($data['ctype'])==""){$data['ctype']="Contract";}
	////Get realtime balance
	$url=DATA_SRC_SITE."v2/accounts/$cthash";	
	$websrc=$this->getwebsrc($url);
	if(strpos($websrc,"balance")>0){
		$ctData=json_decode($websrc);
		$data['balance']=$ctData->balance/1000000000000000000;	
	}	
	
	
	$sql_count="SELECT count(*) from tx WHERE recipient_id='$cthash'";
	$query = $this->db->query($sql_count);
	$row = $query->row();
	$data['totaltxs']=$row->count;
	$data['page']=$page;
	$data['totalpage']=round($data['totaltxs']/$perpage,0);
	
	$data['cttable']="";//$counter=0;
	////get last 100 calls
	//$sql="select tx->'hash' as txhash,tx->'block_height' as block_height FROM txs WHERE txtype='ContractCallTx' AND tx->'tx' @> '{\"contract_id\": \"$cthash\"}' order by tid desc limit 100;";
	$sql="SELECT txhash,block_height,sender_id,amount FROM tx WHERE recipient_id='$cthash' order by tid desc limit 100 offset ".($page-1)*$perpage;;
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){
		//$counter++;
		$txhash=$row->txhash;		
		$txhash_show="th_****".substr($txhash,-4);
		
		$amount=$row->amount/1000000000000000000;	
		$block_height=$row->block_height;
		$sender_id=$row->sender_id;
		$sender_id_show="ak_****".substr($sender_id,-4);
		$alias=$this->getalias($sender_id);
		if($sender_id!=$alias){
			$sender_id_show=$alias;
			}
			
		$sender_id="<a href=/address/wallet/$sender_id>$sender_id_show</a>";
		$block_height="<a href=/block/height/$block_height>$block_height</a>";
		$txhash="<a href=/block/transaction/$txhash>$txhash_show</a>";
		$data['cttable'].="<tr><td>$block_height</td><td>$sender_id</td><td>$txhash</td></tr>";
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

private function getalias($address){
		$this->load->database();
		$sql="SELECT alias from addressinfo WHERE address='$address' limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();		
		
		if($query->num_rows()>0){
			//echo  $row->alias;
			return $row->alias;
			}
		return $address;
		}

}


