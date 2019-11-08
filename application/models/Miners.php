<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Miners extends CI_Model {
	public function getMinerIndex(){
		echo "upgrading miner's page...";
		}
	
	public function getMinerIndex1(){
		$this->load->database();
		$topheight=$this->GetTopHeight();
		$counter=0;
		$blockcounter=0;
		$data['topminers']= "";
		$data['lastmined']= "";
		
		//$timetag=(time()-(24*60*60))*1000; time>$timetag AND
		//$topminersql="select beneficiary,count(*) from miner WHERE orphan is FALSE group by beneficiary order by count desc;";
		$topminersql="select data->>'beneficiary' as beneficiary,count(*) from keyblocks WHERE orphan is NULL group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		
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
				$percentage=round((($minedblocks*100)/$topheight),2);
				//<td>".$this->getTotalReward($trueaddress)." AE</td>
				$data['topminers'].= "<tr><td>".$counter."</td><td><a href=/address/wallet/$trueaddress>".$showaddress."</a></td><td><span class='badge bg-blue'>".$minedblocks."</span></td><td>$percentage %</td><td>".$this->getTotalReward($trueaddress)." AE</td></tr>";
			}
		}

		$data['blocksmined']= $topheight;
		$data['totalminers']= $query->num_rows();
		$data['totalaemined']=9999;
		//$data['totalaemined']=$this->getTotalMined();
		
		////////////////////////////top 20 miners last 24h////////////////////////////////////////////
		/*
		$timetag=(time()-(24*60*60))*1000; 
		$tagheight=$topheight-600;
		$blocksnum_24=0;
		//$getblockssql="SELECT count(*) FROM miner WHERE time>$timetag AND orphan is FALSE";
		$getblockssql="SELECT count(*) FROM keyblocks WHERE (data->>'time')::numeric >$timetag AND orphan is NULL AND height>$tagheight";
		$query = $this->db->query($getblockssql);
		$row = $query->row();
		$blocksnum_24=$row->count;
		$data['total_24']=$blocksnum_24;
		*/
		$blocksnum_24=480;
		$data['total_24']=480;
		
		$counter=0;
		$blockcounter=0;
		$data['topminers_24']= "";
		$data['lastmined']= "";
		$data['piechart']= "";
		$data['totalhashrate']=0;
		$piecounter=0;
		
		/*
		//$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$topminersql="select data->>'beneficiary' as beneficiary,count(*) from keyblocks WHERE (data->>'time')::numeric >$timetag  AND orphan is NULL AND height>$tagheight group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		
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
		}*/
		
		$data['piechart'].=' {label: "else('.round(((($blocksnum_24-$piecounter)*100)/$blocksnum_24),2).'%)'.'", value: '.($blocksnum_24-$piecounter).'}';
			
		
	/*	
		////////////////////////////////Latest 20 Transactions////////////////////////
		//$trans_sql="SELECT * from transactions order by block_height desc,nonce desc limit 20";		
		$trans_sql="SELECT * FROM txs WHERE block_height is not NULL ORDER BY block_height desc,tid desc LIMIT 20";
		$query = $this->db->query($trans_sql);
		$data['lasttxs']="";
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
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
			
		*/
		
		/*
		/////////////////////////////////Last 20 blocks/////////////////////////
		$counter=0;
		$sql='select beneficiary,height,time from miner WHERE orphan is FALSE order by height desc LIMIT 20;';
		$sql="select data->>'beneficiary' as beneficiary,height,(data->>'time')::numeric as time from keyblocks WHERE orphan is NULL order by height desc LIMIT 20;";
		$query = $this->db->query($sql);
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
		*/
		
		
		//////////////////////////////get difficulty////////////////////////////
		$url=DATA_SRC_SITE."v2/status";
		$websrc=$this->getwebsrc($url);
		$data['peer_count']=0;
		if(strpos($websrc,"difficulty")>0){
			//$pattern='/{"difficulty":(.*),"genesis_key_block_hash":"(.*)","listening":(.*),"node_revision":"(.*)","node_version":"(.*)","peer_count":(.*),"pending_transactions_count":(.*),"protocols":(.*),"solutions":(.*),"syncing":(.*)}/i';
			//preg_match($pattern,$websrc, $match);
			$info=json_decode($websrc);
			$data['difficulty']=$info->difficulty;
			$data['difficultyfull']=$data['difficulty'];
			//$data['difficulty']=round($data['difficulty']/10000000000,2);
			$data['difficulty']=round($data['difficulty']/16777216/1000,0)." K";
			
			$data['peer_count']=$info->peer_count;;
		}
		
		
		//////////////////////////////get hashrate////////////////////////////
		$data['totalhashrate']=0;		
		//$data['totalhashrate']=$this->getHashRate();
		
		
		
		//////////////////////////get 	current reward////////////////////////
		$currentheight=$data['blocksmined']+1;		
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		
		/*$data['pools']=$this->getPools();*/
		$data['pools']="";
		
		return $data;
		}
		
		
		
		
		public function getMinerIndex0(){
		$this->load->database();
		$topheight=$this->GetTopHeight();
		//$timetag=(time()-(24*60*60))*1000; time>$timetag AND
		//$topminersql="select beneficiary,count(*) from miner WHERE orphan is FALSE group by beneficiary order by count desc;";
		$topminersql="select data->>'beneficiary' as beneficiary,count(*) from keyblocks WHERE orphan is NULL group by beneficiary order by count desc;";
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
				$percentage=round((($minedblocks*100)/$topheight),2);
				//<td>".$this->getTotalReward($trueaddress)." AE</td>
				$data['topminers'].= "<tr><td>".$counter."</td><td><a href=/address/wallet/$trueaddress>".$showaddress."</a></td><td><span class='badge bg-blue'>".$minedblocks."</span></td><td>$percentage %</td><td>".$this->getTotalReward($trueaddress)." AE</td></tr>";
			}
		}

		$data['blocksmined']= $topheight;
		$data['totalminers']= $query->num_rows();
		$data['totalaemined']=$this->getTotalMined();
		
		////////////////////////////top 20 miners last 24h////////////////////////////////////////////
		$timetag=(time()-(24*60*60))*1000; 
		$tagheight=$topheight-600;
		$blocksnum_24=0;
		//$getblockssql="SELECT count(*) FROM miner WHERE time>$timetag AND orphan is FALSE";
		$getblockssql="SELECT count(*) FROM keyblocks WHERE (data->>'time')::numeric >$timetag AND orphan is NULL AND height>$tagheight";
		$query = $this->db->query($getblockssql);
		$row = $query->row();
		$blocksnum_24=$row->count;
		$data['total_24']=$blocksnum_24;
		
		
		$counter=0;
		$blockcounter=0;
		$data['topminers_24']= "";
		$data['lastmined']= "";
		$data['piechart']= "";
		$data['totalhashrate']=0;
		$piecounter=0;
		
		
		//$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$topminersql="select data->>'beneficiary' as beneficiary,count(*) from keyblocks WHERE (data->>'time')::numeric >$timetag  AND orphan is NULL AND height>$tagheight group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		
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
		//$trans_sql="SELECT * from transactions order by block_height desc,nonce desc limit 20";		
		$trans_sql="SELECT * FROM txs WHERE block_height is not NULL ORDER BY block_height desc,tid desc LIMIT 20";
		$query = $this->db->query($trans_sql);
		$data['lasttxs']="";
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
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
			
		
		/////////////////////////////////Last 20 blocks/////////////////////////
		$counter=0;
		$sql='select beneficiary,height,time from miner WHERE orphan is FALSE order by height desc LIMIT 20;';
		$sql="select data->>'beneficiary' as beneficiary,height,(data->>'time')::numeric as time from keyblocks WHERE orphan is NULL order by height desc LIMIT 20;";
		$query = $this->db->query($sql);
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
		$url=DATA_SRC_SITE."v2/status";
		$websrc=$this->getwebsrc($url);
		$data['peer_count']=0;
		if(strpos($websrc,"difficulty")>0){
			//$pattern='/{"difficulty":(.*),"genesis_key_block_hash":"(.*)","listening":(.*),"node_revision":"(.*)","node_version":"(.*)","peer_count":(.*),"pending_transactions_count":(.*),"protocols":(.*),"solutions":(.*),"syncing":(.*)}/i';
			//preg_match($pattern,$websrc, $match);
			$info=json_decode($websrc);
			$data['difficulty']=$info->difficulty;
			$data['difficultyfull']=$data['difficulty'];
			//$data['difficulty']=round($data['difficulty']/10000000000,2);
			$data['difficulty']=round($data['difficulty']/16777216/1000,0)." K";
			
			$data['peer_count']=$info->peer_count;;
		}
		
		
		//////////////////////////////get hashrate////////////////////////////
		$data['totalhashrate']=0;		
		$data['totalhashrate']=$this->getHashRate();
		
		
		
		//////////////////////////get 	current reward////////////////////////
		$currentheight=$data['blocksmined']+1;		
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		
		$data['pools']=$this->getPools();
		
		return $data;
		}
		
		
	
public function getPools(){
		$this->load->database();
		$table="";
		$sql="SELECT * FROM pools WHERE poolname='beepool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$hashrate=($hashrate*42)/600;
		//$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		//$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";
		$table.="<tr><td>$poolname</td><td>$hashrate Kg/s</td><td>$updatetime</td></tr>";
	
		$sql="SELECT * FROM pools WHERE poolname='f2pool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$hashrate=($hashrate*42)/600;
		//$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		//$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";
		$table.="<tr><td>$poolname</td><td>$hashrate Kg/s</td><td>$updatetime</td></tr>";
		
		
		$sql="SELECT * FROM pools WHERE poolname='2miners' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$hashrate=($hashrate*42)/600;
		//$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		//$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";
		$table.="<tr><td>$poolname</td><td>$hashrate Kg/s</td><td>$updatetime</td></tr>";
		
		
		$sql="SELECT * FROM pools WHERE poolname='uupool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$hashrate=($hashrate*42)/600;
		//$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		//$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";	
		$table.="<tr><td>$poolname</td><td>$hashrate Kg/s</td><td>$updatetime</td></tr>";
		
		return $table;
		}
			
public function getHashRate(){
		$this->load->database();
		$timetag=(time()-(24*60*60))*1000; 
		//$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$topminersql="select data->>'beneficiary' as beneficiary,count(*) from keyblocks WHERE (data->>'time')::numeric >$timetag  AND orphan is NULL group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		
		$counter=0;
		$blockcounter=0;
		$top3block=0;
		foreach ($query->result() as $row){
			$counter++;
			$blockcounter=$blockcounter+$row->count;	
			if($counter<4){
				$top3block=$top3block+$row->count;
				}
			}
		
		//////////////////////////////get hashrate////////////////////////////
		$data['totalhashrate']=0;
		$sql="SELECT hashrate FROM pools WHERE poolname='beepool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		//$row = $query->row();
		foreach ($query->result() as $row){
			$data['totalhashrate']=$data['totalhashrate']+$row->hashrate;
		}
		
		$sql="SELECT hashrate FROM pools WHERE poolname='f2pool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		//$row = $query->row();
		foreach ($query->result() as $row){
			$data['totalhashrate']=$data['totalhashrate']+$row->hashrate;
		}
		
		$sql="SELECT hashrate FROM pools WHERE poolname='uupool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		//$row = $query->row();
		foreach ($query->result() as $row){
			$data['totalhashrate']=$data['totalhashrate']+$row->hashrate;
		}
		
		$sql="SELECT hashrate FROM pools WHERE poolname='2miners' order by pid desc limit 1";
		$query = $this->db->query($sql);
		//$row = $query->row();
		foreach ($query->result() as $row){
			$data['totalhashrate']=$data['totalhashrate']+$row->hashrate;
		}
		
		$data['totalhashrate']=round(($data['totalhashrate']/1000)*($blockcounter/$top3block),2);
		
		return round(($data['totalhashrate']*1000*42)/600,2);
		
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
		$sql="select count(*) FROM keyblocks WHERE height='$height' and orphan is TRUE";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($row->count==1){return FALSE;}
		return TRUE;
		}
		
	private function getTotalReward($ak){
		$this->load->database();
		//$sql= "select height,time FROM miner WHERE beneficiary='$ak' AND orphan is FALSE order by hid desc";
		$sql= "select data->>'height' as height,data->>'time' as time FROM keyblocks WHERE data @> '{\"beneficiary\": \"$ak\"}'::jsonb AND orphan is NULL order by kid desc";
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
		
	private function getTransactionTime($block_hash,$txhash){
		$this->load->database();
		$totalmins=-1;
		//$sql="SELECT time from microblock WHERE hash='$block_hash' limit 1";
		$sql="SELECT data->'time' as time from microblocks WHERE hash='$block_hash' limit 1";
		
		
		$query = $this->db->query($sql);
		$row = $query->row();
		if($query->num_rows()>0){
			$totalmins=round(($row->time/1000),0);
		}
		
		if($totalmins<0){//If there is no microblocks in database, which is caused by fork, then use onchain data directly
			$url=DATA_SRC_SITE.'v2/transactions/'.$txhash;
			$websrc=$this->getwebsrc($url);
			if(strpos($websrc,"hash")==false){return "Calculating";}
			$info=json_decode($websrc);
			$block_hash=$info->block_hash;
			$url=DATA_SRC_SITE.'v2/micro-blocks/hash/'.$block_hash.'/header';
			
			$websrc=$this->getwebsrc($url);
			if(strpos($websrc,"hash")==false){return "Calculating";}
			$info=json_decode($websrc);
			$totalmins=round(($info->time/1000),0);			
			//return "Calculating";
			}
		
		return date("Y-m-d H:i:s",$totalmins);	
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
	
	return 1;
	}

}
