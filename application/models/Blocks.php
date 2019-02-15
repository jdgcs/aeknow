<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blocks extends CI_Model {
	
	public function genBlocksIndex($page=1){
		$data['title']= "Blocks";
		$data['page']=$page;
		$perpage=50;
		/////////////////////////////////////Get key,micro and orphan blocks number//////////////////////////////
		$this->load->database();
		$data['keyblockheight']=$this->GetTopHeight();
		
		$data['totalpage']=round($data['keyblockheight']/$perpage,0);
		
		$sql="SELECT count(*)  FROM microblock;";
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['microblockheight']=$row->count; 
		
		$sql="SELECT count(*) FROM miner WHERE orphan is TRUE;";
		$query = $this->db->query($sql);
		$row = $query->row();	
		$data['orphanblockheight']=$row->count;
		
		/////////////////////////////////Last 20 key blocks/////////////////////////
		$counter=0;
		$data['lastmined']= "";
		$data['includemicro']=0;
		
		
		$sql="select beneficiary,height,time from miner WHERE orphan is FALSE order by height desc LIMIT $perpage offset ".($page-1)*$perpage;
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{			
			$counter++;
			$millisecond=$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			//$whenmined=time()-$millisecond;
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
			
			$sql1="SELECT count(*)  FROM microblock WHERE height=$height";
			$query1 = $this->db->query($sql1);
			$row1 = $query1->row();		
			$data['includemicro']=$row1->count; 
		
			if($this->notOrphan($height)){
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td>".$data['includemicro']."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$this->getReward($height)."</td><td><span class='badge bg-green'>Normal</span></td></tr>";			
			}else{
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td>".$data['includemicro']."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$this->getReward($height)."</td><td><span class='badge bg-yellow'>Forked</span></td></tr>";				
				}
		}
		
		////////////////////////////////Last 100 Micro blocks/////////////////////////
		$data['microblocks']="";
		$counter=0;
		$sql="select hash,height,time from microblock order by height desc limit 100";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$millisecond=$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			//$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			$minedtime=date('Y-m-d H:i:s',$millisecond);
			//$showaddress=$this->strMiddleReduceWordSensitive ($row->beneficiary, 30);
			
			$showaddress=$row->hash;
			$trueaddress=$row->hash;
			$alias=$this->getalias($trueaddress);
			$showaddress="mh_****".substr($showaddress,-4);				
			$height=$row->height;
			$transactions=$this->getMicroBlockTransNum($trueaddress);
			$transactions="<a href=/block/microblock/$trueaddress/transactions>$transactions(View details)</a>";
			
			$data['microblocks'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td><a href=/block/microblock/$trueaddress>".$showaddress."</a></td><td>$transactions</td></tr>";				

			}
		
		
		
		////////////////////////////////Last 100 Orphan blocks/////////////////////////
		$data['orphanblocks']="";
		$counter=0;
		$sql="select beneficiary,height,time from miner WHERE orphan is TRUE order by height desc LIMIT 100;";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$millisecond=$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			//$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			//$minedtime=date('i:s',$whenmined);
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
			
			$data['orphanblocks'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$this->getReward($height)."</td><td><span class='badge bg-yellow'>Orphan</span></td></tr>";				

			}
			
			return $data;
		}
	
	
	public function getBlockInfo($height){		
		$url=DATA_SRC_SITE.'v2/generations/height/'.$height;
		$websrc=$this->getwebsrc($url);
		$data['microblocks']=0;		
		if(strpos($websrc,"key_block")>0){
			$pattern='/{"key_block":{"beneficiary":"(.*)","hash":"(.*)","height":(.*),"miner":"(.*)","nonce":(.*),"pow":(.*),"prev_hash":"(.*)","prev_key_hash":"(.*)","state_hash":"(.*)","target":(.*),"time":(.*),"version":(.*)},"micro_blocks":(.*)}/i';
			preg_match($pattern,$websrc,$match);			
			$data['benificiary']=$match[1];
			$data['hash']=$match[2];
			$data['height']=$match[3];
			$data['miner']=$match[4];
			$data['nonce']=$match[5];
			$data['pow']=$match[6];
			$data['prev_hash']=$match[7];
			$data['prev_key_hash']=$match[8];
			$data['state_hash']=$match[9];
			$data['target']=$match[10];
			$data['time']=$match[11];
			$data['version']=$match[12];
			$data['micro_blocks']=$match[13];
			}
		
		if(strlen($data['micro_blocks'])>10){
			$pattern='/"(.*)"/U';
			preg_match_all($pattern,$data['micro_blocks'],$matches);
			$data['microblocks']=count($matches[1]);
			}	
		
		if($data['microblocks']>0){
			$data['microblocks']="<a href=/block/microblocks/$height>".$data['microblocks']." (Click to view all microblocks)</a>";
			}
		$tagstr="tag ".$data['prev_hash'];
		if(strpos($tagstr,"mh_")>0){
			$data['prev_hash']='<a href="/block/microblock/'.$data['prev_hash'].'">'.$data['prev_hash'].'</a>';
		}
	
		$utctime=round(($data['time']/1000),0);
		$utctime= date("Y-m-d H:i:s",$utctime);		
		$data['time'].="($utctime UTC)";
		$data['reward']=$this->getReward($data['height']);
		
		return $data;
		}
	
	public function getMicroBlockTransactions($microblockhash){
		$data['hash']=$microblockhash;
		$url=DATA_SRC_SITE.'v2/micro-blocks/hash/'.$microblockhash.'/transactions';
		$websrc=$this->getwebsrc($url);
		//echo $websrc;
		$data['txstable']="";
		$counter=0;
		if(strpos($websrc,"block_hash")>0){
			$info=json_decode($websrc);
			$txcounter=count($info->transactions);
			for($m=0;$m<$txcounter;$m++){
				$counter++;
				$type=$info->transactions[$m]->tx->type;
				$txhash=$info->transactions[$m]->hash;
				//$block_hash=$info->transactions[$m]->block_hash;	
				$data['txstable'].="<tr><td>$counter</td><td><a href=/block/transaction/$txhash>$txhash</a></td><td>$type</td></tr>";
				}
			}
		return $data;
	}	
	
	
	public function getTransactionInfo($transactionhash){
		$data['hash']=$transactionhash;		
		$data['table_data']="NULL";
		
		$url=DATA_SRC_SITE.'v2/transactions/'.$transactionhash;
		$websrc=$this->getwebsrc($url);
		if(strpos($websrc,"block_hash")>0){
			$txData=json_decode($websrc);
			$data['table_data']='<tr><th colspan="3"><center><h2>'.$txData->tx->type.'</h2></center></th></tr>';
			
			$table= (array)json_decode($websrc,true);

			foreach ($table as $key=>$content){
				if($key=='tx'){
					$data['table_data'].='<tr><td rowspan="'.count($content).'">Tx</td>';
					foreach ($content as $key_tx=>$content_tx){			
						//echo "--".$key_tx,': ',$content_tx,"\n";
						if($key_tx=="recipient_id" ||$key_tx=="sender_id" || $key_tx=="account_id" || $key_tx=="caller_id" || $key_tx=="owner_id"){
							$content_tx="<a href=/address/wallet/$content_tx>$content_tx</a>";
							}					
						
						if($key_tx=="fee" || $key_tx=="gas"){
							$aefee=number_format($content_tx/1000000000000000000, 18, '.', '');
							$content_tx=$aefee." AE($content_tx ættos )";
						}
						
						if($key_tx=="amount" && $txData->tx->type=="SpendTx"){
							$aefee=number_format($content_tx/1000000000000000000, 18, '.', '');
							$content_tx=$aefee." AE($content_tx ættos )";
							}
						
						if($key_tx=="payload"){						
							$content_tx=htmlspecialchars($content_tx)";
						}
						
						if(!is_string($content_tx) ){
							$content_tx=json_encode($content_tx);
							}
						$data['table_data'].='<tr><td><b>'.$key_tx.'</b> </td><td>'.$content_tx.'</td></tr>';
						}
					}else{					
					if($key=='signatures'){
						//echo $key,': ',$content[0],"\n";
						$data['table_data'].='<tr><td ><b>'.$key.'</b> </td><td  colspan="2">'.$content[0].'</td></tr>';
						}else{
							//echo $key,': ',$content,"\n";
							if($key=="block_height"){
							$data['confirmed']=$this->GetTopHeight()-$content;
							if($data['confirmed']>1){
								$data['confirmed']="<span class='badge bg-green'>".$data['confirmed']." blocks confirmed </span>";
								}else{
								$data['confirmed']="<span class='badge bg-yellow'>".$data['confirmed']." block confirmed </span>";
								}							
							$content="<a href=/block/height/$content>".$content."</a>   ".$data['confirmed'];
							}
							
							if($key=="block_hash"){
								$content="<a href=/block/microblock/$content>$content</a>";
								}
							
							
							$data['table_data'].='<tr><td><b>'.$key.'</b> </td><td  colspan="2">'.$content.'</td></tr>';
						}
					}
				}
			
			
			
			return $data;		
			
			}else{return $data;}
		
		return $data;	
		}
	
	
	
	public function getMicroBlockInfo($microblockhash){
		$data['hash']=$microblockhash;
		$url=DATA_SRC_SITE.'v2/micro-blocks/hash/'.$microblockhash.'/header';
		$websrc=$this->getwebsrc($url);
		
		if(strpos($websrc,"prev_hash")>0){
			$pattern='/{"hash":"(.*)","height":(.*),"pof_hash":"(.*)","prev_hash":"(.*)","prev_key_hash":"(.*)","signature":"(.*)","state_hash":"(.*)","time":(.*),"txs_hash":"(.*)","version":(.*)}/i';
			preg_match($pattern,$websrc,$match);
			$data['height']=$match[2];
			$data['pof_hash']=$match[3];
			$data['prev_hash']=$match[4];
			$data['prev_key_hash']=$match[5];
			$data['signature']=$match[6];
			$data['state_hash']=$match[7];
			$data['time']=$match[8];
			$utctime=round(($match[8]),0);
			$utctime= date("Y-m-d H:i:s",$utctime);		
			$data['time'].="($utctime UTC)";		
			$data['txs_hash']=$match[9];
			$data['version']=$match[10];
			
			$data['transactions']=$this->getMicroBlockTransNum($microblockhash);
			$data['previousblock']="<a href=/block/height/".($data['height']-1)."> Previous Key Block<< </a>";
			$data['nextblock']="<a href=/block/height/".($data['height']+1)."> >>Next Key Block </a>";
			}else{echo "NULL";return 0;}
		
		return $data;
		}	
	
	
	public function getMicroBlocks($microblockheight){
		$url=DATA_SRC_SITE.'v2/generations/height/'.$microblockheight;
		$websrc=$this->getwebsrc($url);
		$data['mblockstable']="";
		$data['hash']=$microblockheight;
		$data['microblocks']=0;		
		$counter=0;
		if(strpos($websrc,"key_block")>0){
			$pattern='/{"key_block":{"beneficiary":"(.*)","hash":"(.*)","height":(.*),"miner":"(.*)","nonce":(.*),"pow":(.*),"prev_hash":"(.*)","prev_key_hash":"(.*)","state_hash":"(.*)","target":(.*),"time":(.*),"version":(.*)},"micro_blocks":(.*)}/i';
			preg_match($pattern,$websrc,$match);			
			$data['micro_blocks']=$match[13];
			}
		
		if(strlen($data['micro_blocks'])>10){
			$pattern='/"(.*)"/U';
			preg_match_all($pattern,$data['micro_blocks'],$matches);
			for($i=0;$i<count($matches[1]);$i++){
				$hash=$matches[1][$i];
				$counter++;
			
				$txsnum=$this->getMicroBlockTransNum($hash)."(<a href=/block/microblock/$hash/transactions>View details</a>)";
				
				$data['mblockstable'].="<tr><td>$counter</td><td><a href=/block/microblock/$hash>$hash</a></td><td>$txsnum</td></tr>";
				
				}
			//var_dump($matches);
			//$data['microblocks']=count($matches[1]);
			}	
		return $data;
		}
	
	
	public function getMicroblockTime($microblockhash){
		$data['hash']=$microblockhash;
		$url=DATA_SRC_SITE.'v2/micro-blocks/hash/'.$microblockhash.'/header';
		$websrc=$this->getwebsrc($url);
		
		if(strpos($websrc,"prev_hash")>0){
			$pattern='/{"hash":"(.*)","height":(.*),"pof_hash":"(.*)","prev_hash":"(.*)","prev_key_hash":"(.*)","signature":"(.*)","state_hash":"(.*)","time":(.*),"txs_hash":"(.*)","version":(.*)}/i';
			preg_match($pattern,$websrc,$match);			
			return $match[8];
			}else{echo "NULL";return 0;}
		
		//return $data;
		
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
