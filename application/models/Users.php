<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Model {
	
	public function getVoteData($ak){
		$this->load->database();
		$sql="SELECT count(DISTINCT(sender_id)) as uniquevoters_num,count(*) as votes_num FROM txs WHERE recipient_id='$ak' AND block_height>71860 AND block_height<80542";
		$query = $this->db->query($sql);
		$row = $query->row();
		//$data['uniquevoters_num']=$row->uniquevoters_num;
		$data['votes_num']=$row->votes_num;
		
		
		$trans_sql="SELECT * FROM txs WHERE recipient_id='$ak' AND block_height>71860 AND block_height<80542 ORDER BY tid DESC";		
		$query = $this->db->query($trans_sql);
		$data['voteresult']="";
		$data['coins_num']=0;
		$data['uniquevoters_num']=0;
		//$data['votes_num']=0;
		$counter=0;
		$data['ak']=$ak;
		$tagstr="tag";
		
		$weight=0;
		
		$weight1=0;
		$weight2=0;
		$weight2_sum=0;
		$weight3=0;
		$weight3_sum=0;
		$weight4=0;
		$weight4_sum=0;
		$weight5=0;
		$weight5_sum=0;
		
		foreach ($query->result() as $row){
			$info=json_decode($row->tx);
			if(strpos($info->tx->payload,"vote")>0){
				//$data['votes_num']=$data['votes_num']+1;
				$sender=$row->sender_id;
				$sender_short="ak_****".substr($sender,-4);
				
				$payload=json_decode(stripslashes($info->tx->payload));
				
				
				$txhash=$row->txhash;
				$txhash_short="th_****".substr($txhash,-4);
				
				$block_height=$row->block_height;
				//$block_hash=$row->block_hash;
				
				//$time=0;
				$time=$this->getTransactionTime($info->block_hash);
				
				$singlebalance=$this->getBalance($row->sender_id);
				$balance=number_format($singlebalance,2,'.','');
				if(strpos($tagstr,$sender)<1){//if not count, then add
					$data['coins_num']=$data['coins_num']+$singlebalance;
					$data['uniquevoters_num']=$data['uniquevoters_num']+1;
					$voteoption=$payload->vote->option;
					if($voteoption==0){$weight1=$weight1+$singlebalance;}
					if($voteoption>0 && $voteoption<6){
						$weight2=$weight2+$singlebalance;
						$weight2_sum=$weight2_sum+$singlebalance*$voteoption;
						}
					if($voteoption>5 && $voteoption<11){$weight3=$weight3+$singlebalance;$weight3_sum=$weight3_sum+$singlebalance*$voteoption;}
					if($voteoption>10 && $voteoption<16){$weight4=$weight4+$singlebalance;$weight4_sum=$weight4_sum+$singlebalance*$voteoption;}
					if($voteoption>15){$weight5=$weight5+$singlebalance;$weight5_sum=$weight5_sum+$singlebalance*$voteoption;}
					
					$weight=$weight+$singlebalance*$voteoption;
					$tagstr.=$sender;
					
					$data['voteresult'].="<tr><td><span class=\"label label-success pull-right\">".($data['votes_num']-$counter)."</span></td><td><a href=/address/wallet/$sender title=$sender>$sender_short</a></td><td>$voteoption</td><td>$balance</td><td><a href=/block/transaction/$txhash title=$txhash>$txhash_short</a> </td><td> <a href=/block/height/$block_height>$block_height</a> </td><td> $time</td><td>Effective</td></tr>";
				
				}else{
					
					$data['voteresult'].="<tr><td><span class=\"label label-warning pull-right\">".($data['votes_num']-$counter)."</span></td><td><a href=/address/wallet/$sender title=$sender>$sender_short</a></td><td>$voteoption</td><td>$balance</td><td><a href=/block/transaction/$txhash title=$txhash>$txhash_short</a></td><td> <a href=/block/height/$block_height>$block_height</a> </td><td> $time</td><td>Duplicated</td></tr>";
				
					}
				
				
				$counter++;
			}
			}
		$data['weight1']=round(($weight1*100)/$data['coins_num'],2);
		$data['weight2']=round(($weight2*100)/$data['coins_num'],2);
		$data['weight3']=round(($weight3*100)/$data['coins_num'],2);
		$data['weight4']=round(($weight4*100)/$data['coins_num'],2);
		$data['weight5']=round(($weight5*100)/$data['coins_num'],2);
		
		$data['weighted_result']=$weight/($data['coins_num']-$weight1);
		
		//$data['weighted_result']=number_format($data['weighted_result'],2,'.','');
	
		$data['coins_num']=number_format($data['coins_num'],2,'.','');
		
		$weight1=round($weight1,2);
		$weight2=round($weight2,2);
		$weight3=round($weight3,2);
		$weight4=round($weight4,2);
		$weight5=round($weight5,2);
		
		
		$weight2_sum=round($weight2_sum/10,2);
		$weight3_sum=round($weight3_sum/10,2);
		$weight4_sum=round($weight4_sum/10,2);
		$weight5_sum=round($weight5_sum/10,2);
		
		$data['table_data']="{y: '0', a: 0, b: $weight1},
        {y: '1-5', a: $weight2_sum, b: $weight2},
        {y: '5-10', a: $weight3_sum, b: $weight3},
        {y: '10-15', a: $weight4_sum, b: $weight4},
        {y: '15-20', a: $weight5_sum, b: $weight5}";
		
		return $data;

		}
	
	private function getBalance($ak){
		$this->load->database();
		$sql="SELECT balance FROM accountsinfo WHERE address='$ak'";
		$query = $this->db->query($sql);
		$row = $query->row();
		
		return $row->balance/1000000000000000000;
		}
	
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
		//return date("H:i:s",$totalmins);	
		return date("Y-m-d H:i:s",$totalmins);	
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
