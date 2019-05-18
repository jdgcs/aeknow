<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends CI_Model {
	
	public function postTransaction($tx){
		$tx=urldecode($tx);
		$data['result']="";
		
		$jsonStr ='{ "tx": "'.$tx.'"}';	
		$pubnode="http://127.0.0.1:3013/v2/transactions";
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
		
		
	public function getTransactions($page,$type){
		//$data['hash']=$transactionhash;
		if($page<1){$page=1;}
		$perpage=20;
		$data['title']="Transactions";
		$data['page']=$page;	
		$data['txtype']=$type;		
		$this->load->database();
		
		$sql_count="SELECT count(*) from txs";
		$sql="SELECT * from txs order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
		if($type=="aens"){
			$sql="SELECT * from txs WHERE txtype='NameRevokeTx' OR txtype='NameClaimTx' OR txtype='NameTransferTx' OR txtype='NamePreclaimTx' OR txtype='NameUpdateTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from txs WHERE txtype='NameRevokeTx' OR txtype='NameClaimTx' OR txtype='NameTransferTx' OR txtype='NamePreclaimTx' OR txtype='NameUpdateTx'";
			}
		if($type=="contract"){
			$sql="SELECT * from txs WHERE txtype='ContractCreateTx' OR txtype='ContractCallTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from txs WHERE txtype='ContractCreateTx' OR txtype='ContractCallTx'";
			}
		if($type=="channel"){
			$sql="SELECT * from txs WHERE txtype='ChannelDepositTx' OR txtype='ChannelSnapshotSoloTx' OR txtype='ChannelSlashTx' OR txtype='ChannelForceProgressTx' OR txtype='ChannelCreateTx' OR txtype='ChannelCloseSoloTx' OR txtype='ChannelCloseMutualTx' OR txtype='ChannelWithdrawTx' OR txtype='ChannelSettleTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from txs WHERE txtype='ChannelDepositTx' OR txtype='ChannelCreateTx' OR txtype='ChannelCloseSoloTx' OR txtype='ChannelCloseMutualTx' OR txtype='ChannelWithdrawTx' OR txtype='ChannelSettleTx'";
			}
		if($type=="oracle"){
			$sql="SELECT * from txs WHERE txtype='OracleExtendTx' OR txtype='OracleQueryTx' OR txtype='OracleResponseTx' OR txtype='OracleRegisterTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from txs WHERE txtype='OracleExtendTx' OR txtype='OracleQueryTx' OR txtype='OracleResponseTx' OR txtype='OracleRegisterTx' ";
			}
			
		if($type=="spend"){
			$sql="SELECT * from txs WHERE txtype='SpendTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from txs WHERE txtype='SpendTx'";
			}
		
		
		$query = $this->db->query($sql_count);
		$row = $query->row();
		$data['totaltxs']=$row->count;
		$data['totalpage']=round($data['totaltxs']/$perpage,0);
		$period=(time()-1543373685)/(3600*24);		
		$data['avgtxsperday']=round($data['totaltxs']/$period,2);
		
		
		
		
		$query = $this->db->query($sql);
		$counter=0;
		$data['txstable']="";
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
				
				
				$data['txstable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td><a href=/address/wallet/$recipient_id>$recipient_id_show</a></td><td>$txtype</td><td>$time</td></tr>";
			}else{
				$data['txstable'].="<tr><td colspan=\"4\"><a href=/block/transaction/$txhash>$txhash</a></td><td>$txtype</td><td>$time</td></tr>";		
				}
			}
		
		
		return $data;
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
	
	
	public function getBlockInfo($height){	
		
		$data["height"]=$height;
		//$data['kh']="kh_29Gmo8RMdCD5aJ1UUrKd6Kx2c3tvHQu82HKsnVhbprmQnFy5bn2";
		
		$this->load->database();
		$sql="SELECT * from miner WHERE height=$height AND orphan is FALSE";
		$query = $this->db->query($sql);
		$row = $query->row();
		
		$data['benificiary']= $row->beneficiary;
		$data['hash']= $row->hash;
		$data['miner'] =$row->miner;
		$data['nonce'] =number_format($row->nonce, 0, '', '');
		$data['height'] =$row->height;
		$data['microblocks']=0;
		$data['microblocks']=$this->getMicroBlocksNum($data['height'] );
		if($data['microblocks']>0){
			$data['microblocks']="<a href=/block/microblocks/$height>".$data['microblocks']." (Click to view all microblocks)</a>";
			}
		$data['prev_hash'] =$row->prev_hash;
		$tagstr="tag ".$data['prev_hash'];
		//$data['prev_hash']='<a href="../microblock/'.$data['prev_hash'].'">'.$data['prev_hash'].'</a>';
		if(strpos($tagstr,"mh_")>0){
			$data['prev_hash']='<a href="../microblock/'.$data['prev_hash'].'">'.$data['prev_hash'].'</a>';
		}
		$data['prev_key_hash'] =$row->prev_key_hash;
		$data['state_hash']= $row->state_hash;
		$data['target'] =$row->target;
		$data['time']= $row->time;
		$utctime=round(($row->time/1000),0);
		$utctime= date("Y-m-d H:i:s",$utctime);		
		$data['time'].="($utctime UTC)";		
		$data['version'] =$row->version;		
		$data['remark']= $row->remark;
		$data['reward']=$this->getReward($data['height']);
		
		return $data;
		}
	
	public function getMicroBlockTransactions($microblockhash){
		$data['hash']=$microblockhash;
		$this->load->database();
		$sql="SELECT * from transactions WHERE block_hash='$microblockhash'";
		$query = $this->db->query($sql);
		$counter=0;
		$data['txstable']="";
		foreach ($query->result() as $row){
			$counter++;
			$txhash=$row->hash;
			$txhash_show="th_****".substr($txhash,-4);
			$amount=$row->amount/1000000000000000000;
			$recipient_id=$row->recipient_id;			
			$recipient_id_show="ak_****".substr($recipient_id,-4);
						
			$sender_id=$row->sender_id;
			$sender_id_show="ak_****".substr($sender_id,-4);
			//$utctime=round(($row->time/1000),0);
			//$utctime= date("Y-m-d H:i:s",$utctime);		
			//$time.="($utctime UTC)";
			
			$data['txstable'].="<tr><td>$counter</td><td><a href=../../transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td><a href=/address/wallet/$recipient_id>$recipient_id_show</a></td></tr>";
			}
		
		return $data;
		}
	public function getTransactionInfo($transactionhash){
		$data['hash']=$transactionhash;
		$this->load->database();
		$sql="SELECT * from transactions WHERE hash='$transactionhash'";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($query->num_rows()>0){
			$data['amount']=$row->amount/1000000000000000000;
			$data['block_height']=$row->block_height;
			$data['attofee']=$row->fee;
			$data['fee']=number_format($row->fee/1000000000000000000, 18, '.', '');
			$data['block_hash']=$row->block_hash;
			$data['sender_id']=$row->sender_id;
			$data['block_height']=$row->block_height;
			$data['recipient_id']=$row->recipient_id;
			$data['sender_id']=$row->sender_id;
			$data['nonce']=$row->nonce;
			$data['payload']=$row->payload;
			$block_hash=$row->block_hash;
			$data['confirmed']=$this->GetTopHeight()-$data['block_height'];
			if($data['confirmed']>1){
				$data['confirmed']="<span class='badge bg-green'>".$data['confirmed']." blocks confirmed </span>";
				}else{
				$data['confirmed']="<span class='badge bg-yellow'>".$data['confirmed']." block confirmed </span>";
				}
			
			
			$sql1="SELECT * FROM microblock WHERE hash='$block_hash'";
			//echo "$sql1";
			$query1 = $this->db->query($sql1);
			$row1 = $query1->row();
			$millisecond =$row1->time;
			$data['millisecond']=$millisecond;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$data['time']=$data['millisecond']."(".date("Y-m-d H:i:s",$millisecond);
			//$data['time']=$millisecond;
			
		}else{
			$data['amount']=0;
			$data['block_height']=0;
			$data['block_hash']="";
			$data['sender_id']=0;
			$data['block_height']=0;
			$data['recipient_id']=0;
			$data['sender_id']=0;
			$data['time']=0;
			$data['nonce']=0;
			$data['payload']="";
			}
		
		return $data;
		}
	public function getMicroBlockInfo($microblockhash){
		$data['hash']=$microblockhash;
		$this->load->database();
		$sql="SELECT * from microblock WHERE hash='$microblockhash'";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['height']=$row->height;
		$data['pof_hash']=$row->pof_hash;
		$data['prev_hash']=$row->prev_hash;
		$data['prev_key_hash']=$row->prev_key_hash;
		$data['signature']=$row->signature;
		$data['state_hash']=$row->state_hash;
		$data['time']=$row->time;
		$utctime=round(($row->time/1000),0);
		$utctime= date("Y-m-d H:i:s",$utctime);		
		$data['time'].="($utctime UTC)";		
		$data['txs_hash']=$row->txs_hash;
		$data['version']=$row->version;
		
		
		
		//$sql="SELECT count(*) FROM transactions WHERE block_hash='$microblockhash'";
		//$query = $this->db->query($sql);
		//$row = $query->row();
		$data['transactions']=$this->getMicroBlockTransNum($microblockhash);
		
		////////////////get Previous & Next block//////////////////
		$data['previousblock']="";
		$data['nextblock']="";
		if($data['prev_hash']==$data['prev_key_hash']){
			$data['previousblock']="<a href=../keyblock/".$data['prev_hash']."> Previous Block(Key Block)<< </a>";
			}else{
			$data['previousblock']="<a href=../keyblock/".$data['prev_key_hash'].">  Previous Key Block<< </a> | "."<a href=../microblock/".$data['prev_hash'].">  Previous Micro Block<< </a>";
			}
		
		
		$sql="SELECT hash FROM microblock WHERE prev_hash='".$data['hash']."'";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($query->num_rows()>0){
			$data['nextblock']="<a href=../microblock/".$row->hash."> >>Next Block(Micro Block) </a>";
			}
		
		$sql="SELECT hash FROM miner WHERE prev_hash='".$data['hash']."'";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($query->num_rows()>0){
			$data['nextblock']="<a href=../keyblock/".$row->hash."> >>Next Block(Key Block) </a>";
			}
		
		
		return $data;
		}
	
	
	public function getMicroBlocks($microblockheight){
		$data['mblockstable']="";
		$data['hash']=$microblockheight;
		$this->load->database();
		$sql="SELECT * from microblock WHERE height=$microblockheight order by time asc";
		$query = $this->db->query($sql);
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$hash=$row->hash;
			$data['time']=$row->time;
			$utctime=round(($row->time/1000),0);
			$utctime= date("Y-m-d H:i:s",$utctime);		
			$data['time'].="($utctime UTC)";
			$txsnum=$this->getMicroBlockTransNum($hash)."(<a href=/block/microblock/$hash/transactions>View details</a>)";
			
			$data['mblockstable'].="<tr><td>$counter</td><td><a href=/block/microblock/$hash>$hash</a></td><td>$txsnum</td><td>".$data['time']."</td></tr>";
			}
		
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
	 
		return array($httpCode, $response);
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
	
	return 0;
	}
	
	

}
