<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Miners extends CI_Model {
	
	public function getMinerIndex(){
		$this->load->database();
		//$timetag=(time()-(24*60*60))*1000; time>$timetag AND
		$topminersql="select beneficiary,count(*) from miner WHERE orphan is FALSE group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		$counter=0;
		$blockcounter=0;
		$data['topminers']= "";
		$data['lastmined']= "";
		foreach ($query->result() as $row)
		{
			$counter++;
			$blockcounter=$blockcounter+$row->count;
			
			if($counter<21){
				$showaddress=$row->beneficiary;
				$trueaddress=$showaddress;
				$alias=$this->getalias($trueaddress);
				if($showaddress==$alias){
					$showaddress="ak_****".substr($showaddress,-4);
				}else{
					$showaddress=$alias;
					}
				$minedblocks=$row->count;
				$percentage=round((($minedblocks*100)/$this->GetTopHeight()),2);
				//<td>".$this->getTotalReward($trueaddress)." AE</td>
				$data['topminers'].= "<tr><td>".$counter."</td><td><a href=/address/wallet/$trueaddress>".$showaddress."</a></td><td><span class='badge bg-blue'>".$minedblocks."</span></td><td>$percentage %</td><td>".$this->getTotalReward($trueaddress)." AE</td></tr>";
			}
		}

		$data['blocksmined']= $blockcounter;
		$data['totalminers']= $query->num_rows();
		$data['totalaemined']=$this->getTotalMined();
		
		////////////////////////////top 20 miners last 24h////////////////////////////////////////////
		$timetag=(time()-(24*60*60))*1000; 
		$blocksnum_24=0;
		$getblockssql="SELECT count(*) FROM miner WHERE time>$timetag AND orphan is FALSE";
		$query = $this->db->query($getblockssql);
		$row = $query->row();
		$blocksnum_24=$row->count;
		$data['total_24']=$blocksnum_24;
		
		$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		$counter=0;
		$blockcounter=0;
		$data['topminers_24']= "";
		$data['lastmined']= "";
		$data['piechart']= "";
		$data['totalhashrate']=0;
		$piecounter=0;
		foreach ($query->result() as $row)
		{
			$counter++;
			$blockcounter=$blockcounter+$row->count;		
				
			if($counter<11){
				$minedblocks=$row->count;
				$showaddress=$row->beneficiary;
				$trueaddress=$row->beneficiary;
				$alias=$this->getalias($trueaddress);
				if($showaddress==$alias){
					$showaddress="ak_****".substr($showaddress,-4);
				}else{
					$showaddress=$alias;
					}
				
				if($counter<7){
					$piecounter=$piecounter+$minedblocks;
					$data['piechart'].=' {label: "'.$showaddress.'('.round((($minedblocks*100)/$blocksnum_24),2).'%)'.'", value: '.$minedblocks.'},'."\n";
					}
				
				
				$percentage=round((($minedblocks*100)/$blocksnum_24),2);
				if($counter==1){
					$beehashrate=$this->getHashRate();
					$data['totalhashrate']=round(($beehashrate[0]/$percentage)*10,2);}
				//<td>".$this->getTotalReward($trueaddress)." AE</td>
				$data['topminers_24'].= "<tr><td>".$counter."</td><td><a href=/address/wallet/$trueaddress>".$showaddress."</a></td><td><span class='badge bg-blue'>".$minedblocks."</span></td><td>$percentage%</td></tr>";
			}
		}
		
		$data['piechart'].=' {label: "else('.round(((($blocksnum_24-$piecounter)*100)/$blocksnum_24),2).'%)'.'", value: '.($blocksnum_24-$piecounter).'}';
		
		////////////////////////////////Latest 20 Transactions////////////////////////
		$trans_sql="SELECT * from transactions order by block_height desc,nonce desc limit 20";		
		$query = $this->db->query($trans_sql);
		$data['lasttxs']="";
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$hash=$row->hash;
			$showhash="th_****".substr($hash,-4);
			$block_hash=$row->block_hash;
			$show_block_hash="mh_****".substr($block_hash,-4);
			$txtime=$this->getTxsTime($block_hash);
			$sender_id=$row->sender_id;
			$alias=$this->getalias($sender_id);
			
			
			if($alias!=$sender_id){
				$show_sender_id=$alias;
			}else{
				$show_sender_id="ak_****".substr($sender_id,-4);
				}
				
				
			$recipient_id=$row->recipient_id;
			$alias=$this->getalias($recipient_id);			
			if($alias!=$recipient_id){
				$show_recipient_id=$alias;
			}else{
				$show_recipient_id="ak_****".substr($recipient_id,-4);
				}
			
			//$show_recipient_id="ak_****".substr($recipient_id,-4);
			$amount=$row->amount;
			$amount=round($amount/1000000000000000000,2);
			//$data['lasttxs'].="<tr><td>$counter</td><td>$showhash</td><td>$amount</td><td><a href=/address/wallet/$sender_id>$show_sender_id</a></td><td><a href=/address/wallet/$recipient_id>$show_recipient_id</a></td><td>$txtime</td></tr>";
			$data['lasttxs'].="<tr><td>$counter</td><td>$amount</td><td><a href=/address/wallet/$sender_id>$show_sender_id</a></td><td><a href=/address/wallet/$recipient_id>$show_recipient_id</a></td><td><a href=/block/transaction/$hash>$showhash</a></td><td>$txtime</td></tr>";

			}
			
		
		/////////////////////////////////Last 20 blocks/////////////////////////
		$counter=0;
		$query = $this->db->query('select beneficiary,height,time from miner WHERE orphan is FALSE order by height desc LIMIT 20;');
		foreach ($query->result() as $row)
		{			
			$counter++;
			$millisecond=$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			$minedtime=date('i:s',$whenmined);
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
				$data['lastmined'].="<tr><td> <a href=/block/height/$height>$height</a></td><td><a href=/address/wallet/$trueaddress>".$showaddress."</a></td><td>".$minedtime."</td><td><span class='badge bg-green'>Normal</span></td></tr>";			
			}else{
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td><a href=/address/wallet/$trueaddress>".$showaddress."</a></td><td>".$minedtime."</td><td><span class='badge bg-yellow'>Forked</span></td></tr>";				
				}
		}
		
		//////////////////////////////get difficulty////////////////////////////
		$url="http://52.77.168.79:3013/v2/status";
		$websrc=$this->getwebsrc($url);
		$data['peer_count']=0;
		if(strpos($websrc,"difficulty")>0){
			$pattern='/{"difficulty":(.*),"genesis_key_block_hash":"(.*)","listening":(.*),"node_revision":"(.*)","node_version":"(.*)","peer_count":(.*),"pending_transactions_count":(.*),"protocols":(.*),"solutions":(.*),"syncing":(.*)}/i';
			preg_match($pattern,$websrc, $match);
			$data['difficulty']=$match[1];
			$data['difficultyfull']=$data['difficulty'];
			//$data['difficulty']=round($data['difficulty']/10000000000,2);
			$data['difficulty']=round($data['difficulty']/16777216/1000,0)." K";
			
			$data['peer_count']=$match[6];
		}
		
		
		
		$currentheight=$data['blocksmined']+1;
		
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		
		return $data;
		}
	
	
	
public function getBlockHeight($keyblockhash){
		$this->load->database();
		$sql="SELECT * from miner WHERE hash='$keyblockhash' AND orphan is FALSE";
		$query = $this->db->query($sql);
		if($query->num_rows()==0){return -1;}
		$row = $query->row();
		return $row->height;
		}	

	private function getReward($blockheight){
		$blockheight=$blockheight+1;
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->reward/10;
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
		
		
		private function getTxsTime($block_hash){
		$this->load->database();
		$sql="SELECT time from microblock WHERE hash='$block_hash' limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		//$totalmins=time()- round(($row->time/1000),0);
		$totalmins=round(($row->time/1000),0);
		return date("Y-m-d H:i:s",$totalmins);
		}
		
		private function getTotalReward($ak){
		$this->load->database();
		$sql= "select height,time FROM miner WHERE beneficiary='$ak' AND orphan is FALSE order by hid desc";
		$query = $this->db->query($sql);
		$data['blocksmined']=0;
		$data['blocksmined']= $query->num_rows();
		
		$data['totalblocks']="";
		$counter=0;
		$minedtime="";
		$data['totalreward']=0;
		foreach ($query->result() as $row){
			$counter++;
			$blockheight=$row->height;
			$millisecond =$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$minedtime=date("Y-m-d H:i:s",$millisecond);
			$reward=$this->getReward($blockheight+1);
			$data['totalreward']=$data['totalreward']+$reward;	
			}
		
		return $data['totalreward'];
		}	
	
	public function getTotalMined(){
		$latestheight=$this->GetTopHeight();
		$totalmined=0;
		for($i=1;$i<$latestheight+1;$i++){
			$totalmined=$totalmined+$this->getReward($i);
			}
		return $totalmined;
		}
		
	public function getHashRate(){
		$file_handle = fopen ( "/dev/shm/hashrate", "r" );
		while ( ! feof ( $file_handle ) ) {
			$linesrc= trim(fgets ( $file_handle ));	
			if($linesrc!=""){	
				$tmpstr=explode("#",$linesrc);	
				return $tmpstr;			
			}
		}
		fclose($file_handle);
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


private function GetTopHeight()	{
	$url="http://127.0.0.1:3013/v2/blocks/top";
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

}