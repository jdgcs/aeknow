<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Model {
	
	public function getUserData(){
		$this->load->database();
		$trans_sql="SELECT * FROM suminfo ORDER BY sid DESC LIMIT 1";		
		$query = $this->db->query($trans_sql);

		foreach ($query->result() as $row){
			
			}
		
		$data=$this->object_array($row);
		$tobemined=259856369;
		$data['mined_rate']=$data['mined_coins']/$tobemined;//number_format(($data['mined_coins']/259856369‬)*100,2);
		$data['lastblocktime']=time()-$data['updatetime'];
		
		/////////////////////////////////Last 10 key blocks/////////////////////////
		$counter=0;
		$data['lastmined']= "";
		$data['includemicro']=0;
		
		
		$sql="select beneficiary,height,time from miner WHERE orphan is FALSE order by height desc LIMIT 10";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{			
			$counter++;
			$millisecond=$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			$minedtime=date('Y-m-d H:i:s',$millisecond);
			//$showaddress=$this->strMiddleReduceWordSensitive ($row->beneficiary, 30);
			$showaddress=$row->beneficiary;
			$trueaddress=$row->beneficiary;
			$alias=$this->getalias($trueaddress);
				if($showaddress==$alias){
					$showaddress="ak_****".substr($showaddress,-4);
				}else{
					$showaddress=$alias;
					}
			$height=$row->height;			
			
			if($this->notOrphan($height)){
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>$whenmined S</td><td><span class='badge bg-green'>Normal</span></td></tr>";			
			}else{
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>$whenmined S</td><td><span class='badge bg-yellow'>Forked</span></td></tr>";				
				}
		}
		
		
		////////////////////////////////Latest 10 Transactions////////////////////////
		//$trans_sql="SELECT * from transactions order by block_height desc,nonce desc limit 20";		
		$trans_sql="SELECT * FROM txs WHERE block_height is not NULL ORDER BY block_height desc,tid desc LIMIT 10";
		$query = $this->db->query($trans_sql);
		$data['lasttxs']="";
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash);
			
			if($txtype=='SpendTx'){				
				$txhash_show="th_****".substr($txhash,-4);
				$amount=$txdata->tx->amount/1000000000000000000;
				$recipient_id=$txdata->tx->recipient_id;			
				$recipient_id_show="ak_****".substr($recipient_id,-4);
				$alias=$this->getalias($recipient_id);
				if($recipient_id!=$alias){
					$recipient_id_show=$alias;
					}
							
				$sender_id=$txdata->tx->sender_id;
				$sender_id_show="ak_****".substr($sender_id,-4);
				$alias=$this->getalias($sender_id);
				if($sender_id!=$alias){
					$sender_id_show=$alias;
					}
				
				//$utctime=round(($row->time/1000),0);
				//$utctime= date("Y-m-d H:i:s",$utctime);		
				
				
				$data['lasttxs'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td><a href=/address/wallet/$recipient_id>$recipient_id_show</a></td><td>$txtype</td><td>$time</td></tr>";
			}else{
				$data['lasttxs'].="<tr><td colspan=\"4\"><a href=/block/transaction/$txhash>$txhash</a></td><td>$txtype</td><td>$time</td></tr>";		
				}
			}
			
		
		
		return $data;
		}
	
	public function object_array($array)
		{
		   if(is_object($array))
		   {
			$array = (array)$array;
		   }
		   if(is_array($array))
		   {
			foreach($array as $key=>$value)
			{
			 $array[$key] = $this->object_array($value);
			}
		   }
		   return $array;
		}
	
	private function getTransactionTime($block_hash){
		$this->load->database();
		$totalmins=0;
		$sql="SELECT time from microblock WHERE hash='$block_hash' limit 1";
		//$sql="SELECT data->>'time' as time from microblocks WHERE hash='$block_hash' limit 1";
		//$sql="SELECT data->>'time' as time from microblocks WHERE data @> '{\"hash\": \"$block_hash\"}'::jsonb limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($query->num_rows()>0){
			$totalmins=round(($row->time/1000),0);
		}
		return date("H:i:s",$totalmins);	
		//return date("Y-m-d H:i:s",$totalmins);	
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
		
	private function notOrphan($height){
		$this->load->database();		
		$sql="select count(*) FROM miner WHERE height='$height' and orphan is TRUE";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($row->count==1){return FALSE;}
		return TRUE;
		}	

private function getReward($blockheight){
		$blockheight=$blockheight+1;
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->reward/10;
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

public function getTotalMined(){
		$latestheight=$this->GetTopHeight();
		$totalmined=0;
		for($i=1;$i<$latestheight+1;$i++){
			$totalmined=$totalmined+$this->getReward($i);
			}
		return $totalmined;
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
	if ($response_code != '200') { //Èç¹ûÎ´ÄÜ»ñÈ¡¸ÃÒ³Ãæ£¨·Ç200·µ»Ø£©£¬ÔòÖØÐÂ³¢ÊÔ»ñÈ¡
	//	echo 'Page error: ' . $response_code . $html;	
		$html='Page error: ' . $response_code.$html;
	} 
	curl_close ( $curl ); // close the connection

	return $html; // and finally, return $html
}

}
