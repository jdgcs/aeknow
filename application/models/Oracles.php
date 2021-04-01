<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Oracles extends CI_Model {
	
public function getFinishDetail($txhash,$checkoption){
	$this->load->database();
	$url=DATA_SRC_SITE."v2/transactions/$txhash";
	$websrc=$this->getwebsrc($url);
	$data['payload']="";
	$data['txhash']=$txhash;
	$data['title']="Prediction details of $txhash";
	
	if(strpos($websrc,"payload")>0){
		$info=json_decode($websrc);
		$payload=$info->tx->payload;
		$str=bin2hex(base64_decode(str_replace("ba_","",$payload)));
		$fordecode=base64_decode(hex2bin(substr($str,0,strlen($str)-8)));
		$info=json_decode($fordecode);
		$data['title']=$info->title;
		$data['ak']=$info->ak;
		$data['options']=$info->options;
		$data['description']=$info->description;
		$data['returnrate']=$info->returnrate;
		
		$data['oracle_id']=$info->oracle_id;
		$data['oracle_query']=$info->oracle_query;
		$data['startheight']=$info->startheight;
		$data['endheight']=$info->endheight;		
		
		$data['payload']=$fordecode;
		$data['oracle_json']=$data['payload'];
		
		
		$data['predictstats']=$this->getPredictstats($txhash);
		
		//get stats
		$oracle_json=$data['oracle_json'];
		$startheight=$data['startheight']-1;
		$endheight=$data['endheight'];
		$ak=$data['ak'];
		$returnrate=$data['returnrate'];
		
		//get income txs
		$sql="SELECT * FROM txs WHERE recipient_id='$ak' AND txtype='SpendTx' AND block_height>$startheight AND block_height<$endheight order by block_height desc";
		$query_txs = $this->db->query($sql);
		
		for($i=1;$i<11;$i++){
			$myoption[$i]=0;
		}
		
		$data['txstable']="";
		foreach ($query_txs->result() as $row){//get options
			$tx=json_decode($row->tx);
			$amount=$tx->tx->amount/1000000000000000000;
			$option=substr(sprintf("%.2f",$amount),0,-1);
			$select=$option-floor($option);
			$count=intval(round($select*10));
			if($count==0){$count=10;}	
			$myoption[$count]=$myoption[$count]+$amount;
			
			//get transactions table
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
			
			$txhash_show="th_****".substr($txhash,-4);
			
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
			if($checkoption!=$count){
				$data['txstable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td>$time</td></tr>";
			}else{
				$data['txstable'].="<tr><td>G<a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td>$time</td></tr>";				
				}
			//end transactions table
			}
			
		
		$data['rewardtable']="";
		//get rewardtable txs
		$sql="SELECT * FROM txs WHERE sender_id='$ak' AND txtype='SpendTx' AND block_height>$endheight order by block_height";
		$query = $this->db->query($sql);				
		$data['rewardtable']="";
		foreach ($query->result() as $row){//get options
			$tx=json_decode($row->tx);
			$amount=$tx->tx->amount/1000000000000000000;
			$option=substr(sprintf("%.2f",$amount),0,-1);
			$select=$option-floor($option);
			$count=intval(round($select*10));
			if($count==0){$count=10;}	
			//$myoption[$count]=$myoption[$count]+$amount;
			
			//get transactions table
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
			
			$txhash_show="th_****".substr($txhash,-4);
			
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
			
			$data['rewardtable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td>$time</td></tr>";
			
			//end transactions table
			}
			
		//count total effective tokens
		$chartdata="";
		$info=json_decode($oracle_json);
		$alltokens=0;
		$effectivetokens=0;		
		for($i=0;$i<count($info->options);$i++){
			$option_init=$info->options[$i]->option_init;
			$option=$info->options[$i]->option;			
			$option_index=$info->options[$i]->index;						
			if(trim($info->options[$i]->option)!=""){	
				//$chartdata.='{label: "1.继续走低，破9100，但9000支撑(54.36%)", value: 262},'				
				$effectivetokens=$effectivetokens+$myoption[$option_index]+$option_init;	
						
			}				
			$alltokens=$alltokens+$myoption[$option_index]+$option_init;	
			}
		//get pie chart
		$chartdata="";
		$info=json_decode($oracle_json);			
		for($i=0;$i<count($info->options);$i++){
			$option_init=$info->options[$i]->option_init;
			$option=$info->options[$i]->option;			
			$option_index=$info->options[$i]->index;						
			if(trim($info->options[$i]->option)!=""){	
				$thisoption=$myoption[$option_index]+$option_init;
				$percentage=round((($thisoption)/$effectivetokens)*100,2);
				$chartdata.='{label: "'.$option_index.'.'.$option.'('.$percentage.'%)", value: '.$thisoption.'},';
			}				
			$alltokens=$alltokens+$myoption[$option_index]+$option_init;	
			}
		$data['chartdata']=substr($chartdata,0,strlen($chartdata)-1);	
		//list options in realtime
		$stats='';
	
	$data['inittoken']	=0;
	for($i=0;$i<count($info->options);$i++){
		if(trim($info->options[$i]->option)!=""){
			$option=$info->options[$i]->option;
			$option_init=$info->options[$i]->option_init;
			$option_index=$info->options[$i]->index;
			$prediction=$myoption[$option_index]+$option_init;
			$data['inittoken']=$data['inittoken']+$option_init;
			if($prediction>0){
				$predictrate[$option_index]=round(((($effectivetokens)*$returnrate/100)/$prediction),2);
			}else{$predictrate[$option_index]=0;}	
					
			$stats.="<tr><td>$option_index</td><td>$option</td><td>$option_init</td><td>$prediction</td><td>1:".$predictrate[$option_index]."</td></tr>";
			}
		}
	$stats.='</table>';
	$data['effectivetokens']=$effectivetokens;
	$data['alltokens']=$alltokens;
	
	//$stats.="<br/> <b>有效token</b>:".$effectivetokens	."；<b>参与token</b>:".$alltokens		;
	$data['predictstats']=	$stats;
		
		
	//get the winning return
	
	$data['wintable']="";
	$data['totalreturn']=0;
		foreach ($query_txs->result() as $row){//get options
			$tx=json_decode($row->tx);
			$amount=$tx->tx->amount/1000000000000000000;
			$option=substr(sprintf("%.2f",$amount),0,-1);
			$select=$option-floor($option);
			$count=intval(round($select*10));
			if($count==0){$count=10;}	
			$count_index=$count;
			
			$myoption[$count]=$myoption[$count]+$amount;
			
			//get transactions table
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
			
			$txhash_show="th_****".substr($txhash,-4);
			
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
			
			$returntokens=0;
			//echo "$checkoption=>$count_index<br/>";
			if($checkoption!=$count_index){	 			
				$data['wintable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td>".$predictrate[$count_index]."</td><td>$returntokens</td></tr>";
			}else{
				$returntokens=$amount*$predictrate[$count_index];
				$data['wintable'].="<tr style=\"background:#F7296E;color:white;\"><td><a href=/block/transaction/$txhash style=\"color:white;\">$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id style=\"color:white;\">$sender_id_show</a></td><td>".$predictrate[$count_index]."</td><td>$returntokens</td></tr>";				
				$data['totalreturn']=$data['totalreturn']+$returntokens;
				}
			//end transactions table
			}	
		
		
		
		}
	
	$data['topheight']=$this->GetTopHeight();
	
	return $data;
	}



public function getPredictionDetail($txhash){
	$this->load->database();
	$url=DATA_SRC_SITE."v2/transactions/$txhash";
	$websrc=$this->getwebsrc($url);
	$data['payload']="";
	$data['txhash']=$txhash;
	$data['title']="Prediction details of $txhash";
	
	if(strpos($websrc,"payload")>0){
		$info=json_decode($websrc);
		$payload=$info->tx->payload;
		$str=bin2hex(base64_decode(str_replace("ba_","",$payload)));
		$fordecode=base64_decode(hex2bin(substr($str,0,strlen($str)-8)));
		$info=json_decode($fordecode);
		$data['title']=$info->title;
		$data['ak']=$info->ak;
		$data['options']=$info->options;
		$data['description']=$info->description;
		$data['returnrate']=$info->returnrate;
		
		$data['oracle_id']=$info->oracle_id;
		$data['oracle_query']=$info->oracle_query;
		$data['startheight']=$info->startheight;
		$data['endheight']=$info->endheight;		
		
		$data['payload']=$fordecode;
		$data['oracle_json']=$data['payload'];
		
		
		$data['predictstats']=$this->getPredictstats($txhash);
		
		//get stats
		$oracle_json=$data['oracle_json'];
		$startheight=$data['startheight']-1;
		$endheight=$data['endheight'];
		$ak=$data['ak'];
		$returnrate=$data['returnrate'];
		
		//get income txs
		$sql="SELECT * FROM txs WHERE recipient_id='$ak' AND txtype='SpendTx' AND block_height>$startheight AND block_height<$endheight order by block_height desc";
		$query = $this->db->query($sql);
		
		for($i=1;$i<11;$i++){
			$myoption[$i]=0;
		}
		
		$data['txstable']="";
		foreach ($query->result() as $row){//get options
			$tx=json_decode($row->tx);
			$amount=$tx->tx->amount/1000000000000000000;
			$option=substr(sprintf("%.2f",$amount),0,-1);
			$select=$option-floor($option);
			$count=intval(round($select*10));
			if($count==0){$count=10;}	
			$myoption[$count]=$myoption[$count]+$amount;
			
			//get transactions table
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
			
			$txhash_show="th_****".substr($txhash,-4);
			
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
			
			$data['txstable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td>$time</td></tr>";
			
			//end transactions table
			}
			
		
		$data['rewardtable']="";
		//get rewardtable txs
		$finishheight=$endheight+100;
		$sql="SELECT * FROM txs WHERE sender_id='$ak' AND txtype='SpendTx' AND block_height>$endheight AND block_height <$finishheight order by block_height";
		$query = $this->db->query($sql);				
		$data['rewardtable']="";
		foreach ($query->result() as $row){//get options
			$tx=json_decode($row->tx);
			$amount=$tx->tx->amount/1000000000000000000;
			$option=substr(sprintf("%.2f",$amount),0,-1);
			$select=$option-floor($option);
			$count=intval(round($select*10));
			if($count==0){$count=10;}	
			//$myoption[$count]=$myoption[$count]+$amount;
			
			//get transactions table
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$txdata=json_decode($row->tx);
			$block_hash=$txdata->block_hash;
			$time=$this->getTransactionTime($txdata->block_hash,$txhash);
			
			
			$txhash_show="th_****".substr($txhash,-4);
			
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
			
			$data['rewardtable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$recipient_id_show</a></td><td>$time</td></tr>";
			
			//end transactions table
			}
			
		//count total effective tokens
		$chartdata="";
		$info=json_decode($oracle_json);
		$alltokens=0;
		$effectivetokens=0;		
		for($i=0;$i<count($info->options);$i++){
			$option_init=$info->options[$i]->option_init;
			$option=$info->options[$i]->option;			
			$option_index=$info->options[$i]->index;						
			if(trim($info->options[$i]->option)!=""){	
				//$chartdata.='{label: "1.继续走低，破9100，但9000支撑(54.36%)", value: 262},'				
				$effectivetokens=$effectivetokens+$myoption[$option_index]+$option_init;	
						
			}				
			$alltokens=$alltokens+$myoption[$option_index]+$option_init;	
			}
		//get pie chart
		$chartdata="";
		$info=json_decode($oracle_json);			
		for($i=0;$i<count($info->options);$i++){
			$option_init=$info->options[$i]->option_init;
			$option=$info->options[$i]->option;			
			$option_index=$info->options[$i]->index;						
			if(trim($info->options[$i]->option)!=""){	
				$thisoption=$myoption[$option_index]+$option_init;
				$percentage=round((($thisoption)/$effectivetokens)*100,2);
				$chartdata.='{label: "'.$option_index.'.'.$option.'('.$percentage.'%)", value: '.$thisoption.'},';
			}				
			$alltokens=$alltokens+$myoption[$option_index]+$option_init;	
			}
		$data['chartdata']=substr($chartdata,0,strlen($chartdata)-1);	
		//list options in realtime
		$stats='';
	
		
	for($i=0;$i<count($info->options);$i++){
		if(trim($info->options[$i]->option)!=""){
			$option=$info->options[$i]->option;
			$option_init=$info->options[$i]->option_init;
			$option_index=$info->options[$i]->index;
			$prediction=$myoption[$option_index]+$option_init;
			if($prediction>0){
				$predictrate[$option_index]=round(((($effectivetokens)*$returnrate/100)/$prediction),2);
			}else{$predictrate[$option_index]=0;}	
					
			$stats.="<tr><td>$option_index</td><td>$option</td><td>$option_init</td><td>$prediction</td><td>1:".$predictrate[$option_index]."</td></tr>";
			}
		}
	$stats.='</table>';
	$data['effectivetokens']=$effectivetokens;
	$data['alltokens']=$alltokens;
	
	//$stats.="<br/> <b>有效token</b>:".$effectivetokens	."；<b>参与token</b>:".$alltokens		;
	$data['predictstats']=	$stats;
		}
	
	$data['topheight']=$this->GetTopHeight();
	
	return $data;
	}

public function getPredictstats($txhash){
	return "";
	}

public function getOracleList(){
	$this->load->database();
	$topheight=$this->GetTopHeight();
	//$sql="SELECT DISTINCT(CONCAT(aid ,oid) ) as oracle_id FROM (SELECT (tx->'tx'->'oracle_ttl'->>'value')::numeric as ttl, (tx->>'block_height')::numeric as block_height,regexp_replace(((tx->'tx'->'account_id')::text),'ak_','ok_') as aid,(tx->'tx'->'oracle_id')::text as oid from txs WHERE txtype='OracleRegisterTx' or txtype='OracleExtendTx') as tbl_active WHERE (ttl+block_height)>$topheight;";
	//echo "$sql";
	//$sql="select distinct(sender_id) as oracle_id from tx where txtype='OracleExtendTx' AND block_height >".($topheight-4800);
	$sql="with t as (select distinct(sender_id),max(block_height) as block_height from tx where txtype='OracleExtendTx' OR txtype='OracleRegisterTx' OR txtype='OracleRespondTx' group by sender_id) select sender_id,block_height from t order by t.block_height desc;";
	$query = $this->db->query($sql);
	$data['ortable']="";$counter=0;
	$data['ortable_all']="";$counter_all=0;
	
	foreach ($query->result() as $row){
		$counter++;
		$oracle_id=$row->sender_id;
		$block_height=$row->block_height;
		$oracle_id=str_replace('"','',$oracle_id);
		$oracle_id=str_replace("ak_","ok_",$oracle_id);
		$account_id=str_replace("ok_","ak_",$oracle_id);
		
		$data['ortable'].="<tr><td>$counter</td><td><a href=/oracle/id/$oracle_id>$oracle_id</a></td><td><a href=/address/wallet/$account_id>$account_id</a></td><td><span class='badge bg-green'>$block_height</span></td></tr>";		
	}
	
	
	
	//$sql="SELECT DISTINCT(CONCAT(aid ,oid) ) as oracle_id FROM (SELECT (tx->'tx'->'oracle_ttl'->>'value')::numeric as ttl, (tx->>'block_height')::numeric as block_height,regexp_replace(((tx->'tx'->'account_id')::text),'ak_','ok_') as aid,(tx->'tx'->'oracle_id')::text as oid from txs WHERE txtype='OracleRegisterTx' or txtype='OracleExtendTx') as tbl_active WHERE (ttl+block_height)<$topheight;";
	$sql="select distinct(sender_id) as oracle_id from tx where txtype='OracleRegisterTx' LIMIT 100;";
	$query = $this->db->query($sql);	
	foreach ($query->result() as $row){
		$counter_all++;
		$oracle_id=$row->oracle_id;
		$oracle_id=str_replace('"','',$oracle_id);
		$oracle_id=str_replace("ak_","ok_",$oracle_id);
		$account_id=str_replace("ok_","ak_",$oracle_id);		
		//$data['ortable_all'].="<tr><td>$counter_all</td><td><a href=/oracle/id/$oracle_id>$oracle_id</a></td><td><a href=/address/wallet/$account_id>$account_id</a></td><td><span class='badge bg-red'>Inactive</span></td></tr></tr>";		
		$data['ortable_all'].="<tr><td>$counter_all</td><td><a href=/oracle/id/$oracle_id>$oracle_id</a></td><td><a href=/address/wallet/$account_id>$account_id</a></td><td></td></tr></tr>";		
	}
	
	return $data;
}


public function getOracleDetail($oracle_id){
	$url=DATA_SRC_SITE."v2/oracles/$oracle_id";
	$data['ortable']="";//$counter=0;
	$data['oracle_id']=$oracle_id;
	$account=str_replace("ok_","ak_",$oracle_id);
	$websrc=$this->getwebsrc($url);
	if(strpos($websrc,"id")>0){
		$orData=json_decode($websrc);
		
		$query_fee=$orData->query_fee/1000000000000000000;
		$query_format=$orData->query_format;
		$response_format=$orData->response_format;
		$ttl=$orData->ttl;
		if(property_exists($orData,"vm_version")){
			$vm_version=$orData->vm_version;
		}else{
			$vm_version="NULL";
		}
		$account_id=str_replace("ok_","ak_",$oracle_id);
		
		
		$account_id="<a href=/address/wallet/$account_id>$account_id</a>";
		//$cthashlink="<a href=/contract/detail/$cthash>$cthash</a>";
		$data['ortable'].="<tr><td>$oracle_id</td><td>$query_fee (AE)</td><td>$query_format</td><td>$response_format</td><td>$ttl</td><td>$vm_version</td><td>$account_id</td></tr>";
	}	
	
	
	$data['querytable']="";
	$counter=0;
	$this->load->database();
	//$sql="SELECT  tx->'hash' as txhash,tx->'block_height' as block_height,txtype from txs WHERE txtype='OracleQueryTx' OR txtype='OracleResponseTx' AND tx->'tx' @>'{\"oracle_id\": \"$oracle_id\"}'::jsonb order by tid desc limit 100 ;";
	//$sql="SELECT txhash,block_height,txtype from txs WHERE (txtype='OracleQueryTx' OR txtype='OracleResponseTx') AND sender_id='$account' order by tid desc limit 100";
	$sql="SELECT txhash,block_height,txtype from tx WHERE (txtype='OracleQueryTx' OR txtype='OracleRespondTx') AND sender_id='$account' order by tid desc limit 100";
	//query faster
	$query = $this->db->query($sql);
	
	foreach ($query->result() as $row){
		$counter++;
		$txhash=$row->txhash;
		$txtype=$row->txtype;
		$txhash=str_replace("\"","",$txhash);
		$block_height=$row->block_height;
		$block_height="<a href=/block/height/$block_height>$block_height</a>";
		$txhash="<a href=/block/transaction/$txhash>$txhash</a>";
		$data['querytable'].="<tr><td>$counter</td><td>$txhash</td><td>$txtype</td><td>$block_height</td></tr>";
		}
	
	
	return $data;
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

}

