<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class V2s extends CI_Model {
	
	public function getTxByHeight($ak,$limit=10,$offset=0){
		$this->load->database();
		$trans_sql="SELECT txhash,txtype,sender_id,tx,block_height,recipient_id FROM txs WHERE sender_id='$ak' OR  recipient_id='$ak' AND block_height>$offset ORDER BY block_height desc,tid desc LIMIT $limit";		
		$query = $this->db->query($trans_sql);

		$counter=0;
		$results="";
		$results.= "{\"txs\":[";
		foreach ($query->result() as $row){
			//$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$sender_id=$row->sender_id;
			$recipient_id=$row->recipient_id;
			$block_height=$row->block_height;
			$tx=json_decode($row->tx);
			$amount=0;
			if($txtype=="SpendTx"){$amount=$tx->tx->amount;}
			
			$results.= "{\"txtype\":\"$txtype\",\"txhash\":\"$txhash\",\"amount\":$amount,\"block_height\":$block_height,\"sender_id\":\"$sender_id\",\"recipient_id\":\"$recipient_id\"},";	
			}
		$results.= "}END";
		
		$results=str_replace(",}END","]}",$results);
		echo $results;
		}
	
	public function getTx($ak,$limit=10,$offset=0){
		$this->load->database();
		$trans_sql="SELECT txhash,txtype,sender_id,tx,block_height,recipient_id FROM txs WHERE sender_id='$ak' OR  recipient_id='$ak' ORDER BY block_height desc,tid desc LIMIT $limit offset ".$offset;		
		$query = $this->db->query($trans_sql);

		$counter=0;
		$results="";
		$results.= "{\"txs\":[";
		foreach ($query->result() as $row){
			//$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$sender_id=$row->sender_id;
			$recipient_id=$row->recipient_id;
			$block_height=$row->block_height;
			$tx=json_decode($row->tx);
			$amount=0;
			if($txtype=="SpendTx"){$amount=$tx->tx->amount;}
			
			$results.= "{\"txtype\":\"$txtype\",\"txhash\":\"$txhash\",\"amount\":$amount,\"block_height\":$block_height,\"sender_id\":\"$sender_id\",\"recipient_id\":\"$recipient_id\"},";	
			}
		$results.= "}END";
		
		$results=str_replace(",}END","]}",$results);
		echo $results;
		}
	
	public function getAccount($ak){		
		$url=DATA_SRC_SITE."v2/accounts/$ak";
		$data['info']=$this->getwebsrc($url);
		return $data;
		}
	
	public function getName($aens){
		$url=DATA_SRC_SITE."v2/names/$aens";
		return $this->getwebsrc($url);
		}
	
	public function postTransaction($tx){
		$tx=urldecode($tx);
		$data['result']="";
		
		$jsonStr ='{ "tx": "'.$tx.'"}';	
		$pubnode=DATA_SRC_SITE."v2/transactions";
		$return= $this->http_post_json($pubnode, $jsonStr); 
		
		if($return[0]==200){
				$info=json_decode($return[1]);
				$txhash=$info->tx_hash;
				$data['result']="Successful: <a href=/block/transaction/$txhash>$txhash</a>";
			}else{	
				$result=$return[1];
				$data['result']="Failed:$result";
			}
		
		return $data;
		}
		
	public function postTx($jsonStr){
		$pubnode=DATA_SRC_SITE."v2/transactions";
		$return= $this->http_post_json($pubnode, $jsonStr); 
		return $return;
		}	
	
	public function getalias($address){
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
		
	private function getTransactionTime($block_hash){
		$this->load->database();
		$totalmins=0;
		$sql="SELECT time from microblock WHERE hash='$block_hash' limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($query->num_rows()>0){
			$totalmins=round(($row->time/1000),0);
		}
		return date("Y-m-d H:i:s",$totalmins);	
		}	
	
	
	
	public function getBlockHeight($keyblockhash){
		$this->load->database();
		$sql="SELECT * from miner WHERE hash='$keyblockhash' AND orphan is FALSE";
		$query = $this->db->query($sql);
		if($query->num_rows()==0){return -1;}
		$row = $query->row();
		return $row->height;
		}	
	
	public function getPow($height){
		$this->load->database();
		$sql="SELECT pow from miner WHERE height=$height AND orphan is FALSE";
		$query = $this->db->query($sql);
		if($query->num_rows()==0){return "No pow info of $height";}
		$row = $query->row();
		return $row->pow;
		}
	private function getMicroBlockTransNum_old($mhash){
		$this->load->database();
		$sql="SELECT count(*) from transactions WHERE block_hash='$mhash'";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->count;
		}
		
	public function getMicroBlockTransNum($mhash){
		$url=DATA_SRC_SITE."v2/micro-blocks/hash/$mhash/transactions/count";
		$websrc=$this->getwebsrc($url);
		if(strpos($websrc,"count")>0){
			$pattern='/{"count":(.*)}/i';
			preg_match($pattern,$websrc, $match);
			//echo  $match[1];
			return $match[1];
		}
		return 0;
		}
	private function getMicroBlocksNum($height){
		$this->load->database();
		$sql="SELECT count(*) from microblock WHERE height=$height";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->count;
		}
	private function getReward($blockheight){
		$blockheight=$blockheight+1;
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->reward/10;
		}
	
public function http_post_json($url, $jsonStr)
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
	 
		return $response;
	}


private function getwebsrc($url) {
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
	
	curl_close ( $curl ); // close the connection

	return $html; // and finally, return $html
}


function GetTopHeight()	{
	$url=DATA_SRC_SITE."v2/blocks/top";
	$websrc=$this->getwebsrc($url);
	$info=json_decode($websrc);
	if(strpos($websrc,"key_block")==TRUE){		
		return $info->key_block->height;
	}
		
	if(strpos($websrc,"micro_block")==TRUE){
		return $info->micro_block->height;
		}
	
	return 0;
	}
	
	

}
