<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blocks extends CI_Model {
	
	public function genBlocksIndex($page=1){
		$data['title']= "Blocks";
		$data['page']=$page;
		$perpage=20;
		/////////////////////////////////////Get key,micro and orphan blocks number//////////////////////////////
		$this->load->database();
		$data['keyblockheight']=$this->GetTopHeight();
		
		$data['totalpage']=round($data['keyblockheight']/$perpage,0);
		
		$sql="SELECT count(*)  FROM microblocks;";
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['microblockheight']=$row->count; 
		
		$sql="SELECT count(*) FROM keyblocks WHERE orphan is TRUE;";
		$query = $this->db->query($sql);
		$row = $query->row();	
		$data['orphanblockheight']=$row->count;
		
		/////////////////////////////////Last 20 key blocks/////////////////////////
		$counter=0;
		$data['lastmined']= "";
		$data['includemicro']=0;
		
		
		//$sql="select beneficiary,height,time from miner WHERE orphan is FALSE order by height desc LIMIT $perpage offset ".($page-1)*$perpage;
		$sql="select benifit,data FROM keyblocks WHERE orphan is NULL order by height desc LIMIT $perpage offset ".($page-1)*$perpage;
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{			
			$counter++;
			$info=json_decode($row->data);
			$millisecond=$info->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			//$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			$minedtime=date('Y-m-d H:i:s',$millisecond);
			//$showaddress=$this->strMiddleReduceWordSensitive ($row->beneficiary, 30);
			$showaddress=$row->benifit;
			$trueaddress=$row->benifit;
			$alias=$this->getalias($trueaddress);
				if($showaddress==$alias){
					$showaddress="ak_****".substr($showaddress,-4);
				}else{
					$showaddress=$alias;
					}
			$height=$info->height;
			
			$sql1="SELECT count(*)  FROM microblocks WHERE height=$height";
			$query1 = $this->db->query($sql1);
			$row1 = $query1->row();		
			$data['includemicro']=$row1->count; 
		
			if($this->notOrphan($height)){
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td>".$data['includemicro']."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$this->getReward($height)."</td><td><span class='badge bg-green'>Normal</span></td></tr>";			
			}else{
				$data['lastmined'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td>".$data['includemicro']."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$this->getReward($height)."</td><td><span class='badge bg-yellow'>Forked</span></td></tr>";				
				}
		}
		
		////////////////////////////////Last 20 Micro blocks/////////////////////////
		$data['microblocks']="";
		$counter=0;
		//$sql="select hash,height,time from microblock order by height desc limit 100";
		$sql="select hash,height,data from microblocks order by height desc limit 20";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$info=json_decode($row->data);
			$millisecond=$info->time;
			
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
		//$sql="select beneficiary,height,time from miner WHERE orphan is TRUE order by height desc LIMIT 100;";
		$sql="select data from keyblocks WHERE orphan is TRUE order by height desc LIMIT 100;";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$info=json_decode($row->data);
			$millisecond=$info->time;
			//$millisecond=$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			//$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			//$minedtime=date('i:s',$whenmined);
			$minedtime=date('Y-m-d H:i:s',$millisecond);
			//$showaddress=$this->strMiddleReduceWordSensitive ($row->beneficiary, 30);
			
			//$showaddress=$row->beneficiary;
			//$trueaddress=$row->beneficiary;
			
			$showaddress=$info->beneficiary;
			$trueaddress=$info->beneficiary;
			
			$alias=$this->getalias($trueaddress);
				if($showaddress==$alias){
					$showaddress="ak_****".substr($showaddress,-4);
				}else{
					$showaddress=$alias;
					}
			//$height=$row->height;
			$height=$info->height;
			
			$data['orphanblocks'].="<tr><td><a href=/block/height/$height>$height</a></td><td>".$minedtime."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$this->getReward($height)."</td><td><span class='badge bg-yellow'>Orphan</span></td></tr>";				

			}
			
			return $data;
		}
	
	
	public function getBlockInfo($height){		
		$url=DATA_SRC_SITE.'v2/generations/height/'.$height;
		$websrc=$this->getwebsrc($url);
		$data['microblocks']=0;		
		if(strpos($websrc,"key_block")>0){
			/*$pattern='/{"key_block":{"beneficiary":"(.*)","hash":"(.*)","height":(.*),"miner":"(.*)","nonce":(.*),"pow":(.*),"prev_hash":"(.*)","prev_key_hash":"(.*)","state_hash":"(.*)","target":(.*),"time":(.*),"version":(.*)},"micro_blocks":(.*)}/i';
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
			$data['micro_blocks']=$match[13];*/
			$info=json_decode($websrc);
			$data['beneficiary']=$info->key_block->beneficiary;
			$data['hash']=$info->key_block->hash;
			$data['height']=$info->key_block->height;
			$data['miner']=$info->key_block->miner;
			$data['nonce']=$info->key_block->nonce;
			$data['pow']=json_encode($info->key_block->pow);
			$data['prev_hash']=$info->key_block->prev_hash;
			$data['prev_key_hash']=$info->key_block->prev_key_hash;
			$data['state_hash']=$info->key_block->state_hash;
			$data['target']=$info->key_block->target;
			$data['time']=$info->key_block->time;
			$data['version']=$info->key_block->version;
			$data['micro_blocks']=json_encode($info->micro_blocks);
			
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
						
						if($key_tx=="oracle_id"){
							$content_tx="<a href=/oracle/id/$content_tx>$content_tx</a>";
							}	
						
						if($key_tx=="contract_id"){
							$comtarct_info=$this->getContractinfo($txData->tx->contract_id);
							$content_tx=$content_tx.$comtarct_info;
							}
						
						if($key_tx=="call_data"){
							$call_info=$this->getCallInfo($content_tx,$txData->tx->contract_id);
							$content_tx=$content_tx.$call_info;
							}
							
							
						if($key_tx=="payload"){							
							$str=bin2hex(base64_decode(str_replace("ba_","",$content_tx)));
							$fordecode=strip_tags(hex2bin(substr($str,0,strlen($str)-8)));
							$content_tx=$content_tx."<br />(Decoded:<br/><textarea style=\"width:100%;\">".$fordecode."</textarea>)";
							}
							
						if($key_tx=="fee" || $key_tx=="gas"){
							$aefee=number_format($content_tx/1000000000000000000, 18, '.', '');
							$content_tx=$aefee." AE($content_tx ættos )";
						}
						
						if($key_tx=="amount" && $txData->tx->type=="SpendTx"){
							//$aefee=number_format($content_tx/1000000000000000000, 18, '.', '');
							$aefee=$content_tx/1000000000000000000;
							$content_tx=$aefee." AE($content_tx ættos )";
							}						
															
						if(!is_string($content_tx) ){
							$content_tx=json_encode($content_tx);
							}
						
						if($key_tx=="query" || $key_tx=="response"){
							$content_tx=htmlspecialchars($content_tx);
							}	
						
						
						$data['table_data'].='<tr><td width="100px"><b>'.$key_tx.'</b> </td><td  style="word-wrap:break-word;word-break:break-all;">'.$content_tx.'</td></tr>';
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
	
	public function getCallInfo($call_data,$contract_id){
		$this->load->database();
		$sql="SELECT * FROM contracts_token WHERE address='$contract_id'";
		$query = $this->db->query($sql);
		$row = $query->row();
		
		if($row->ctype=="AEX9"){
			$decimal=$row->decimal;
			$data= $this->decode_token_transfer($call_data,$decimal);
			$toaddress="<a href=/address/wallet/".$data['address'].">".$data['address']."</a> ";
			$returnstr="<br/>(Transfer ".$data['amount']." ".$row->alias." to:".$toaddress.")";
			
			return $returnstr;
			}
		
		
		$data['address']="";
		
		return $data;
		}
		
	public function getContractinfo($contract_id){
		$this->load->database();
		$sql="SELECT * FROM contracts_token WHERE address='$contract_id'";
		$query = $this->db->query($sql);
		$row = $query->row();
		
		if($row->ctype=="AEX9"){
			return "(".$row->alias.")";
			}
		
		return "";
		}
	
	
	public function decode_token_transfer($call_data,$decimal){//获取正确的返回调用
	$erlpath="/home/ae/ae/lima53/erts-9.3.3.13/bin/escript";
	$clipath="/home/ae/ae/lima53/erts-9.3.3.13/bin/aesophia_cli";
	$tokenaddress="/home/ae/ae/lima53/erts-9.3.3.13/bin/contracts/aex9.aes";
	
	$cmd="$erlpath $clipath $tokenaddress -b fate --call_result $call_data --call_result_fun meta_info";
	
	//echo "$cmd\n";
	exec($cmd,$ret);
	$addresstmp="";
	$amounttmp=0;
	for($i=0;$i<count($ret);$i++){
		if(strpos($ret[$i],"{address")>0 && strpos($ret[$i-1],"tuple,")>0){
			$addresstmp=$ret[$i+1].$ret[$i+2].$ret[$i+3];
			$amounttmp=$ret[$i+4];
			}
		}
	$addresstmp=str_replace("<<","",$addresstmp);
	$addresstmp=str_replace("\n","",$addresstmp);	
	$addresstmp=str_replace(">>},","",$addresstmp);	
	$amounttmp=str_replace("}}}}","",trim($amounttmp));	
	
	$data['address']=$this->getAKfromHex(bin2hex($this->toAddress($addresstmp)));	
	$data['amount']=floatval(number_format($amounttmp/pow(10,$decimal), 18, '.', ''));
	
	return $data;
	}
	
	
	public function toAddress($str){
	$mystr="";
	$tmpstr=explode(",",$str);
	for($i=0;$i<count($tmpstr);$i++){
		$mystr.=chr($tmpstr[$i]);
		}
	return $mystr;
	}

	public function getAKfromHex($hex){	
	$bs = pack("H*", $hex);
	$checksum = hash("sha256", hash("sha256", $bs, true));   
	$myhash=substr($checksum,0,8);
	$fullstr=$hex.$myhash;
	//echo "$fullstr\n";
	
	return "ak_". $this->base58_encode(hex2bin($fullstr));
	}

	public function base58_encode($string)
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);
        if (is_string($string) === false) {
            return false;
        }
        if (strlen($string) === 0) {
            return '';
        }
        $bytes = array_values(unpack('C*', $string));
        $decimal = $bytes[0];
        for ($i = 1, $l = count($bytes); $i < $l; $i++) {
            $decimal = bcmul($decimal, 256);
            $decimal = bcadd($decimal, $bytes[$i]);
        }
        $output = '';
        while ($decimal >= $base) {
            $div = bcdiv($decimal, $base, 0);
            $mod = bcmod($decimal, $base);
            $output .= $alphabet[$mod];
            $decimal = $div;
        }
        if ($decimal > 0) {
            $output .= $alphabet[$decimal];
        }
        $output = strrev($output);
        foreach ($bytes as $byte) {
            if ($byte === 0) {
                $output = $alphabet[0] . $output;
                continue;
            }
            break;
        }
        return (string) $output;
    }
	
	
	
	public function getMicroBlockInfo($microblockhash){
		$data['hash']=$microblockhash;
		$url=DATA_SRC_SITE.'v2/micro-blocks/hash/'.$microblockhash.'/header';
		$websrc=$this->getwebsrc($url);
		
		if(strpos($websrc,"prev_hash")>0){
			//$pattern='/{"hash":"(.*)","height":(.*),"pof_hash":"(.*)","prev_hash":"(.*)","prev_key_hash":"(.*)","signature":"(.*)","state_hash":"(.*)","time":(.*),"txs_hash":"(.*)","version":(.*)}/i';
			//preg_match($pattern,$websrc,$match);
			$info=json_decode($websrc);
			/*
			$data['height']=$match[2];
			$data['pof_hash']=$match[3];
			$data['prev_hash']=$match[4];
			$data['prev_key_hash']=$match[5];
			$data['signature']=$match[6];
			$data['state_hash']=$match[7];
			$data['time']=$match[8];
			$utctime=round(($match[8])/1000,0);
			$utctime= date("Y-m-d H:i:s",$utctime);		
			$data['time'].="($utctime UTC)";		
			$data['txs_hash']=$match[9];
			$data['version']=$match[10];
			* */
			
			$data['height']=$info->height;
			$data['pof_hash']=$info->pof_hash;
			$data['prev_hash']=$info->prev_hash;
			$data['prev_key_hash']=$info->prev_key_hash;
			
			if($data['prev_hash']!=$data['prev_key_hash']){
				$data['prev_hash']="<a href=/block/microblock/".$data['prev_hash'].">".$data['prev_hash']."</a>";
				}else{
				$data['prev_hash']="<a href=/block/keyblock/".$data['prev_key_hash'].">".$data['prev_key_hash']."</a>";	
					}
			
			$data['prev_key_hash']="<a href=/block/keyblock/".$data['prev_key_hash'].">".$data['prev_key_hash']."</a>";
			$data['signature']=$info->signature;
			$data['state_hash']=$info->state_hash;
			$data['time']=$info->time;
			$utctime=round(($info->time)/1000,0);
			$utctime= date("Y-m-d H:i:s",$utctime);		
			$data['time'].="($utctime UTC)";		
			$data['txs_hash']=$info->txs_hash;
			$data['version']=$info->version;
			
			
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
			//$pattern='/{"key_block":{"beneficiary":"(.*)","hash":"(.*)","height":(.*),"miner":"(.*)","nonce":(.*),"pow":(.*),"prev_hash":"(.*)","prev_key_hash":"(.*)","state_hash":"(.*)","target":(.*),"time":(.*),"version":(.*)},"micro_blocks":(.*)}/i';
			//preg_match($pattern,$websrc,$match);			
			$info=json_decode($websrc);
			//$data['micro_blocks']=$match[13];
			//$data['micro_blocks']=json_encode($info->key_block->micro_blocks);
			//$data['micro_blocks']=json_encode($info->micro_blocks);
			$data['micro_blocks']=$info->micro_blocks;
			}
		
		//if(strlen($data['micro_blocks'])>10){
		if(count($data['micro_blocks'])>0){
			//$pattern='/"(.*)"/U';
			//preg_match_all($pattern,$data['micro_blocks'],$matches);
			//$matches=json_decode($data['micro_blocks']);
			$matches=$data['micro_blocks'];
			for($i=0;$i<count($matches);$i++){
				$hash=$matches[$i];
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
			//$pattern='/{"hash":"(.*)","height":(.*),"pof_hash":"(.*)","prev_hash":"(.*)","prev_key_hash":"(.*)","signature":"(.*)","state_hash":"(.*)","time":(.*),"txs_hash":"(.*)","version":(.*)}/i';
			//preg_match($pattern,$websrc,$match);		
			$info=json_decode($websrc);	
			return $info->time;
			}else{echo "NULL";return 0;}
		
		//return $data;
		
		}	
		
	public function getBlockHeight($keyblockhash){
		$url=DATA_SRC_SITE.'v2/key-blocks/hash/'.$keyblockhash;
		$websrc=$this->getwebsrc($url);
		if(strpos($websrc,"prev_hash")>0){
			$info=json_decode($websrc);	
			return $info->height;
			}else{echo "NULL";return 0;}
		//$this->load->database();
		//$sql="SELECT * from keyblocks WHERE hash='$keyblockhash' AND orphan is NULL";
		//$query = $this->db->query($sql);
		//if($query->num_rows()==0){return -1;}
		//$row = $query->row();
		//return $row->height;
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
			//$pattern='/{"count":(.*)}/i';
			//preg_match($pattern,$websrc, $match);
			//echo  $match[1];
			$info=json_decode($websrc);
			return $info->count;
			//return $match[1];
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
	$agent = "User-Agent: AEKnow urgent balancer";
	
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
		$sql="select count(*) FROM keyblocks WHERE height='$height' and orphan is TRUE";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($row->count==1){return FALSE;}
		return TRUE;
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
