<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tests extends CI_Model {
	
	public function getContractList(){
	$this->load->database();
	//$sql="select distinct tx->'tx'->'contract_id' as cthash FROM txs WHERE tx->'tx' @> '{\"type\": \"ContractCallTx\"}' group by tx->'tx'->'block desc' ;";
	//$sql="SELECT cthash,block_height FROM (SELECT DISTINCT ON (tx->'tx'->'contract_id') tx->'block_height' as block_height, tx->'tx'->'contract_id' as cthash FROM txs  WHERE tx->'tx' @> '{\"type\": \"ContractCallTx\"}') as tbl_contracts order by block_height desc;";
	//echo "$sql";
	//$sql="SELECT cthash,block_height FROM (SELECT DISTINCT ON (tx->'tx'->'contract_id') tx->'block_height' as block_height, tx->'tx'->'contract_id' as cthash FROM txs  WHERE txtype='ContractCallTx') as tbl_contracts order by block_height desc;";
	//$sql="SELECT cthash,block_height FROM (SELECT DISTINCT ON (tx->'tx'->'contract_id') tx->'block_height' as block_height, tx->'tx'->'contract_id' as cthash FROM txs  WHERE txtype='ContractCallTx') as tbl_contracts order by block_height desc;";
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
		
		$counter++;		
		
		$block_height= $row->lastcall;		
		$owner_id=$row->owner_id;
		
		$cthash_show="ct_****".substr($cthash,-4);
		$owner_id_show="ak_****".substr($owner_id,-4);
		$alias=$this->getalias($owner_id);
		if($owner_id!=$alias){
			$owner_id_show=$alias;
			}
		
		$owner_id="<a href=/address/wallet/$owner_id>$owner_id_show</a>";
		$cthashlink="<a href=/contract/detail/$cthash>$cthash_show</a>";
		$block_height="<a href=/block/height/$block_height>$block_height</a>";
		$data['cttable'].="<tr><td>$counter</td><td>$cthashlink</td><td>$owner_id</td><td>$block_height</td><td>$ctype</td></tr>";
		
		
	}
	
	return $data;
}
	
	public function wealth500($offset){
		$this->load->database();
		$startpoint=$offset*500;
		$totalcoin=$this->getTotalCoins();
		
		$str="{\"top500\":[";
		$sql="select * from accountsinfo WHERE balance is not NULL order by balance desc limit 500 offset $startpoint";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row){
			$readtime=$row->readtime;
			$readtime=date("Y-m-d H:i:s",$readtime);
			$wealth=$row->balance/1000000000000000000;
			$percentage=round(($wealth/$totalcoin)*100,6);
			$str.="{\"ak\":\"".$row->address."\",\"balance\":".$row->balance.",\"per\":".$percentage.",\"lastupdate\":\"".$readtime."\"},";
			}
			
		$str.="]}END";
		$str=str_replace(",]}END","]}",$str);
		
		return $str;	
		
		}
		
	public function getTotalCoins(){
		$this->load->database();
		$trans_sql="SELECT * FROM suminfo ORDER BY sid DESC LIMIT 1";		
		$query = $this->db->query($trans_sql);

		foreach ($query->result() as $row){
			
			}
		
		$data=$this->object_array($row);
		
		return $data['total_coins'];
		}	
		
		
		
	public function getCallInfo($call_data,$contract_id){
		$this->load->database();
		$sql="SELECT * FROM contracts_token WHERE address='$contract_id'";
		$query = $this->db->query($sql);
		$row = $query->row();
		
		if($row->ctype=="AEX9"){
			$decimal=$row->decimal;
			return $this->decode_token_transfer($call_data,$decimal);
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
	
	public function getToken($ak){
		$this->load->database();
		$tobecheck=str_replace("ak_","",$ak);
		$tmpaddress=$this->base58_decode($tobecheck);		
		$hexaddress=substr($tmpaddress,0,64);
		
		$sql="SELECT * from tokens where address='$hexaddress'";
		//echo $sql;
		$query = $this->db->query($sql);
		$counter=0;
		$str="{\"tokens\":[";
		
		foreach ($query->result() as $row){
			if(trim($row->contract)!=""){
				$tokeninfo=$this->getTokenInfo($row->contract);				
				$str.='{"tokenname":"'.$tokeninfo['name'].'","decimal":'.$tokeninfo['decimal'].',"contract":"'.$row->contract.'","balance":"'.$row->balance.'"},';
			}
			//$aens[$counter]['expire_height']=$row->expire_height;
			}
		$str.="]}END";
		$str=str_replace(",]}END","]}",$str);
		
		return $str;
		}
	
	public function getTokenInfo($contract){
		$this->load->database();
		$sql="SELECT alias,decimal FROM contracts_token WHERE address='$contract'";
		$query = $this->db->query($sql);
		$row = $query->row();	
		$data['name']=$row->alias;
		$data['decimal']=$row->decimal;
		return $data;
		}	
		
		
	public function getAENSBidding($ak){
		$this->load->database();
		$sql="SELECT distinct aensname FROM txs_aens WHERE txtype='NameClaimTx' AND sender_id='$ak' AND nameowner is null";	
		$query = $this->db->query($sql);
		$counter=0;
		$str="{\"names\":[";		

		foreach ($query->result() as $row){
			//$aens[$row->aensname]=$row->expire_height;
			if(trim($row->aensname)!=""){
				$info=$this->queryAENSBidding($row->aensname);
				$str.='{"aensname":"'.$row->aensname.'","lastbidder":"'.$info['sender_id'].'","lastprice":"'.$info['amount'].'"},';
			}
			//$aens[$counter]['expire_height']=$row->expire_height;
			}
		$str.="]}END";
		$str=str_replace(",]}END","]}",$str);
		//$data['count']=$counter;
		//$data=$this->object_array($aens);
		//$data=json_encode($str);
		
		//$sql="SELECT distinct aensname,expire_height FROM txs_aens WHERE txtype='NameClaimTx' AND sender_id='$ak' AND nameowner is NULL order by expire_height";	
		
		return $str;
		
		}
		
		
	public function queryAENSBidding($aensname){
		$this->load->database();
		$sql="SELECT sender_id,amount FROM txs_aens WHERE aensname='$aensname' order by block_height desc LIMIT 1";	
		$query = $this->db->query($sql);
		
		$data['sender_id']="";
		$data['amount']=0;
		
		foreach ($query->result() as $row){
			$data['sender_id']=trim($row->sender_id);
			$data['amount']=$row->amount;
			}
			
	
		
		return $data;
		
		}		
	public function getMinerIndex(){
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
		
		//$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$topminersql="select data->>'beneficiary' as beneficiary,count(*) from keyblocks WHERE (data->>'time')::numeric >$timetag  AND orphan is NULL AND height>$tagheight group by beneficiary order by count desc;";
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
		//$trans_sql="SELECT * from transactions order by block_height desc,nonce desc limit 20";		
		$trans_sql="SELECT * FROM txs ORDER BY tid desc LIMIT 20";
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
	
	public function getTransactions($page,$type){
		//$data['hash']=$transactionhash;
		if($page<1){$page=1;}
		$perpage=20;
		$data['title']="Transactions";
		$data['page']=$page;	
		$data['txtype']=$type;		
		$this->load->database();
		
		$sql_count="SELECT count(*) from txs";
		$sql="SELECT * from tx order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
		if($type=="aens"){
			$sql="SELECT * from tx WHERE txtype='NameRevokeTx' OR txtype='NameClaimTx' OR txtype='NameTransferTx' OR txtype='NamePreclaimTx' OR txtype='NameUpdateTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from tx WHERE txtype='NameRevokeTx' OR txtype='NameClaimTx' OR txtype='NameTransferTx' OR txtype='NamePreclaimTx' OR txtype='NameUpdateTx'";
			}
		if($type=="contract"){
			$sql="SELECT * from tx WHERE txtype='ContractCreateTx' OR txtype='ContractCallTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from tx WHERE txtype='ContractCreateTx' OR txtype='ContractCallTx'";
			}
		if($type=="channel"){
			$sql="SELECT * from tx WHERE txtype='ChannelDepositTx' OR txtype='ChannelSnapshotSoloTx' OR txtype='ChannelSlashTx' OR txtype='ChannelForceProgressTx' OR txtype='ChannelCreateTx' OR txtype='ChannelCloseSoloTx' OR txtype='ChannelCloseMutualTx' OR txtype='ChannelWithdrawTx' OR txtype='ChannelSettleTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from tx WHERE txtype='ChannelDepositTx' OR txtype='ChannelCreateTx' OR txtype='ChannelCloseSoloTx' OR txtype='ChannelCloseMutualTx' OR txtype='ChannelWithdrawTx' OR txtype='ChannelSettleTx'";
			}
		if($type=="oracle"){
			$sql="SELECT * from tx WHERE txtype='OracleExtendTx' OR txtype='OracleQueryTx' OR txtype='OracleResponseTx' OR txtype='OracleRegisterTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from tx WHERE txtype='OracleExtendTx' OR txtype='OracleQueryTx' OR txtype='OracleResponseTx' OR txtype='OracleRegisterTx' ";
			}
			
		if($type=="spend"){
			$sql="SELECT * from tx WHERE txtype='SpendTx' order by tid desc LIMIT $perpage offset ".($page-1)*$perpage;
			$sql_count="SELECT count(*) from tx WHERE txtype='SpendTx'";
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
				
				
				$data['txstable'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td><a href=/address/wallet/$recipient_id>$recipient_id_show</a></td><td>$txtype</td><td>$time</td></tr>";
			}else{
				$data['txstable'].="<tr><td colspan=\"4\"><a href=/block/transaction/$txhash>$txhash</a></td><td>$txtype</td><td>$time</td></tr>";		
				}
			}
		
		
		return $data;
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
		return date("Y-m-d H:i:s",$totalmins);	
		}	
	
	public function getPools(){
		$this->load->database();
		$table="";
		$sql="SELECT * FROM pools WHERE poolname='beepool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";
		
		$sql="SELECT * FROM pools WHERE poolname='f2pool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";
		
		$sql="SELECT * FROM pools WHERE poolname='uupool' order by pid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$poolname=$row->poolname;
		$hashrate=round($row->hashrate,2);
		$estreward=round($row->estreward,2);
		$updatetime=date("Y-m-d H:i:s",$row->updatetime); 
		$table.="<tr><td>$poolname</td><td>$hashrate K/s</td><td>$estreward AE/K</td><td>$updatetime</td></tr>";	
		
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
		
		$data['totalhashrate']=round(($data['totalhashrate']/1000)*($blockcounter/$top3block),2);
		
		return $data['totalhashrate'];
		
	}
	
	
	
	public function getNetworkStatus(){
		$this->load->database();
		$data['maxtps']=116;
		
		$trans_sql="SELECT * FROM suminfo ORDER BY sid DESC LIMIT 1";		
		$query = $this->db->query($trans_sql);

		foreach ($query->result() as $row){
			
			}
		
		$data=$this->object_array($row);
		$tobemined=259856369;
		$data['mined_rate']=$data['mined_coins']/$tobemined;//number_format(($data['mined_coins']/259856369‬)*100,2);
		$data['lastblocktime']=time()-$data['updatetime'];	
		
		
		///////////////////////////////////////////get blocks info////////////////////////////
		$data['topheight']= $data['block_height'];
		//$data['totalaemined']=$this->getTotalMined();	
		//$data['totalcoins']=276450333.49932+$this->getTotalMined();
		$data['totalcoins']=$data['total_coins'];
		$data['totalaemined']=$data['totalcoins']-276450333.49932;
		
		//$sql="SELECT time FROM miner WHERE height=1";
		//$query = $this->db->query($sql);
		//$row = $query->row();
		//$totalmins= (time()-($row->time/1000))/60;		
		$totalmins= (time()-(1543373685748/1000))/60;	//1543373685748 is the first block time
		
		$totalheight=$data['topheight'];		
		$data['avgminsperblock']=round($totalmins/$totalheight,6);
		
		$url=DATA_SRC_SITE."v2/key-blocks/height/$totalheight";
		$websrc=$this->getwebsrc($url);
		$data['lastime']="";
		if(strpos($websrc,"time")>0){
			//$pattern='/(.*),"time":(.*),"version(.*)/i';
			//preg_match($pattern,$websrc, $match);
			//$data['lastime']=$match[2];
			$info=json_decode($websrc);			
			$data['lastime']=$info->time;
			$millisecond=substr($data['lastime'],0,strlen($data['lastime'])-3); 
			$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			$data['lastime']=date('i:s',$whenmined);
			}
			
		$sql="SELECT count(*) FROM miner WHERE orphan is TRUE";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['totalorphan']= floatval($row->count);		
		
		
		/////////////////Transactions info//////////////////
		//$sql="SELECT count(*),sum(fee) from transactions";
		$data['totalfee']=$data['total_fee'];
		$data['totaltxs']=$data['total_transactions'];
		//$data['totalfee']=floatval(number_format($row->sum/1000000000000000000, 18, '.', ''));
		$data['totalfee']=0;//remove fee
		$period=(time()-1543373685)/(3600*24);		
		$data['avgtxsperday']=round($data['totaltxs']/$period,2);
		$data['avgtxspersec']=round($data['totaltxs']/(time()-1543373685),2);
		$data['avgfee']=floatval(number_format($data['totalfee']/$data['totaltxs'],18, '.', ''));
		
		
		
		
		///////////////////////////////////////
		//////////////////////////////get difficulty////////////////////////////		
		
		$data['difficulty']=$data['mining_difficulty'];
		$data['difficultyfull']=floatval($data['difficulty']);
		//$data['difficulty']=round($data['difficulty']/10000000000,2);
		$data['difficulty']=round($data['difficulty']/16777216/1000,0)." K";			
		//$data['peer_count']=floatval($match[6]);
		$data['peer_count']=$data['nodes_total'];
		
		//////////////////////////////get hashrate////////////////////////////
		//$data['totalhashrate']=0;		
		$data['totalhashrate']=$data['mining_hashrate'];
		
		//////////////////////////get 	current reward////////////////////////
		
		$data['currentreward']=$data['mining_reward'];
		//$data['totalaemined']=$this->getTotalMined();
		
		///////////////////////////get pending txs/////////////////////////
		$url="http://127.0.0.1:3113/v2/debug/transactions/pending";
		$websrc=$this->getwebsrc($url);
		$data['pendingtxs']=substr_count($websrc, '"tx":');
		
		////////////////////////get price////////////////////////
		
		$data['price']=$data['price_usdt'];
		
		///////////////////update time//////////////////////
		$data['timestamp']=time();
		
		$data['maxtps']=$data['max_tps'];
		///////////////////////////////////////////get last ////////////////////////////
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
		
		
	public function getWalletInfo($ak,$page=1,$type='all'){
		$perpage=50;
		$data['page']=$page;
		$data['activities']="";		
		$data['type']=$type;
		
		$url=DATA_SRC_SITE."v2/accounts/$ak";
		$websrc=$this->getwebsrc($url);
		$data['account']=$ak;
		$data['balance']=0;
		if(strpos($websrc,"balance")==TRUE){
			//$pattern='/{"balance":(.*),"id":"(.*)","nonce":(.*)}/i';
			//preg_match($pattern,$websrc, $match);
			$info=json_decode($websrc);
			//$data['balance']=$match[1]/1000000000000000000;
			$data['balance']=$info->balance/1000000000000000000;
		}
		
		
///////////////////////////////////////get mining
		$this->load->database();
		
		$sql= "select height,data FROM keyblocks WHERE benifit='$ak' AND orphan is NULL order by height desc LIMIT 100";
		$query = $this->db->query($sql);
		$data['blocksmined']=0;
		$data['blocksmined']= $query->num_rows();
		
		$data['totalblocks']="";
		$counter=0;
		$minedtime="";
		$data['totalreward']=0;
		foreach ($query->result() as $row){
			$data['activities']=' <a class="pull-right"> &nbsp; <span class="badge bg-blue">Mining</span></a>'; 
			$counter++;
			$blockheight=$row->height;
			$info=json_decode($row->data);
			$millisecond =$info->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$minedtime=date("Y-m-d H:i:s",$millisecond);
			$reward=($this->getReward($blockheight+1))*0.891;
			$data['totalreward']=$data['totalreward']+$reward;			
			if($counter<101){				
				$data['totalblocks'].="<tr><td>".$counter."</td><td><a href=/block/height/$blockheight>".$blockheight."</a></td><td>".$reward."</td><td>".$minedtime."</td></tr>";
			}
			}
		/////////////////////////////////////////////get Transactions//////////////////////////////////
		$ok=str_replace("ak_","ok_",$ak);
		
		//$sql="SELECT * FROM txs WHERE tx->'tx' @>'{\"sender_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"recipient_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"account_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"owner_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"caller_id\": \"$ak\"}'::jsonb OR tx->'tx' @>'{\"oracle_id\": \"$ok\"}'::jsonb OR  tx->'tx' @>'{\"initiator_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"responder_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"from_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"to_id\": \"$ak\"}'::jsonb ORDER BY tid desc LIMIT $perpage offset ".($page-1)*$perpage;
		$sql="SELECT * FROM tx WHERE sender_id='$ak' OR recipient_id='$ak' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;

		//$sql= "select * FROM txs WHERE recipient_id='$ak' OR sender_id='$ak' order by block_height desc,nonce desc LIMIT $perpage offset ".($page-1)*$perpage;
		if($type=='in'){
			$sql="SELECT * FROM tx WHERE  recipient_id='$ak' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;
			}
		if($type=='out'){
			$sql="SELECT * FROM tx WHERE sender_id='$ak' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;
			}
		if($type=='contracts'){
			$sql="SELECT * FROM tx WHERE txtype='ContractCallTx' or txtype='ContractCreateTx' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;
			}
		$query = $this->db->query($sql);
		$counter=0;
		$data['totaltxs']="";
		foreach ($query->result() as $row){
			$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$utc=$row->utc;
			//$txdata=json_decode($row->tx);
			$amount=$row->amount;
			//$block_hash=$txdata->block_hash;
			$block_height=$row->block_height;
			//$time=$this->getTransactionTime($txdata->block_hash);
			$time=substr($utc,0,10);
			$time=date("H:i:s",$time);			
			
			if($txtype=='SpendTx'||$txtype=='NameTransferTx'){				
				$txhash_show="th_****".substr($txhash,-4);
				if($txtype=='NameTransferTx'){
					$amount="NameTransferTx =>";
					}else{
					$amount=$amount/1000000000000000000;
				}
				$recipient_id=$row->recipient_id;			
				$recipient_id_show="ak_****".substr($recipient_id,-4);
				$alias=$this->getalias($recipient_id);
				if($recipient_id!=$alias){
					$recipient_id_show=$alias;
					}
							
				$sender_id=$row->sender_id;
				$sender_id_show="ak_****".substr($sender_id,-4);
				$alias=$this->getalias($sender_id);
				if($sender_id!=$alias){
					$sender_id_show=$alias;
					}
				
				if($sender_id==$ak){
						$senderlink="$sender_id_show";
						$recipientlink="<span class='badge bg-yellow'>OUT</span><a href='/address/wallet/$recipient_id'>$recipient_id_show</a>";
					}else{
						$senderlink="<a href='/address/wallet/$sender_id'>$sender_id_show</a>";
						$recipientlink="<span class='badge bg-green'>&nbsp; IN &nbsp; </span>$recipient_id_show";
					}
				//$utctime=round(($row->time/1000),0);
				//$utctime= date("Y-m-d H:i:s",$utctime);		
				
				
				$data['totaltxs'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td>$senderlink</td><td>$recipientlink</td><td>$txtype</td><td>$time</td></tr>";
			}else{
				$data['totaltxs'].="<tr><td colspan=\"4\"><a href=/block/transaction/$txhash>$txhash</a></td><td>$txtype</td><td>$time</td></tr>";		
				}
			}
		
		
		//return $data;
		//	}
		//$data['transaction_count']=$query->num_rows();
		
		//$sql="SELECT count(*) FROM txs WHERE tx->'tx' @>'{\"sender_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"recipient_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"account_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"owner_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"caller_id\": \"$ak\"}'::jsonb OR tx->'tx' @>'{\"oracle_id\": \"$ok\"}'::jsonb OR  tx->'tx' @>'{\"initiator_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"responder_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"from_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"to_id\": \"$ak\"}'::jsonb";
		$sql="SELECT count(*) FROM tx WHERE  sender_id='$ak' OR  recipient_id='$ak'";
		if($type=='in'){
			$sql="SELECT count(*) FROM tx WHERE  recipient_id='$ak'";
			}
			
		if($type=='out'){
			$sql="SELECT count(*) FROM tx WHERE sender_id='$ak'";
			}
		
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['transaction_count']=$row->count; 
		if($data['transaction_count']>0){
			$data['activities'].=' <a class="pull-right">&nbsp; <span class="badge bg-green">Transaction</span></a>'; 
			}
		
		$data['totalpage']=round($data['transaction_count']/$perpage,0)+1;
		
		$data['notes']="From the blockchain, to the blockchain.";
		$alias=$this->getalias($ak);
		if($ak!=$alias){
			$sql="SELECT remark FROM addressinfo WHERE address='$ak'";
			$query = $this->db->query($sql);
			$row = $query->row();
			$data['notes']="<b>$alias:</b> " .$row->remark;
			}
		
		/////////////////////////////////////////////Check Genisis//////////////////////////////////
		$sql= "select count(*) FROM accountsinfo WHERE address='$ak' and remark='genesis'";
		$query = $this->db->query($sql);
		$row = $query->row();		
		if($row->count>0){
			$data['activities'].=' <a class="pull-right"><span class="badge bg-yellow">Genesis</span></a>'; 
			}
		
		$tobecheck=str_replace("ak_","",$ak);
		if(!$this->checkAddress($tobecheck)){
			$data['account']="Invalid address";
			}
		/////////////////////////////////////////////Get Tokens//////////////////////////////////
		$tmpaddress=$this->base58_decode($tobecheck);
		$hexaddress=substr($tmpaddress,0,64);
		$sql="SELECT DISTINCT contract FROM tokens WHERE address='$hexaddress'";
		$query = $this->db->query($sql);
		$counter=0;
		$data['tokens']="";
		foreach ($query->result() as $row){
			$token=$this->getTokenName($row->contract);
			$balance=$this->getTokenBalance($row->contract,$hexaddress);
			$data['tokens'].="<b>$token</b>: $balance<br/>";
			}
		
		
		/////////////////////////////////////////////Get AENS names//////////////////////////////////
		$data['aensname']=0;
			
		$sql="SELECT count(distinct(aensname)) FROM txs_aens WHERE nameowner='$ak'";
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['aensname']=$row->count;
		
		return $data;
		
		}

public function getTokenBalance($contract,$hexaddress){
	$this->load->database();
	$sql="SELECT decimal FROM contracts_token WHERE address='$contract'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$decimal=$row->decimal;
	
	$sql="SELECT balance FROM tokens WHERE address='$hexaddress' and contract='$contract'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$balance=$row->balance;
	
	return $balance/pow(10,$decimal);
	}


public function getTokenName($contract){
	$this->load->database();
	$name="";
	$sql="SELECT alias FROM contracts_token WHERE address='$contract'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$name=$row->alias;
	
	return $name;
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
			$data['prev_hash']='<a href="../microblock/'.$data['prev_hash'].'">'.$data['prev_hash'].'</a>';
		}
	
		$utctime=round(($data['time']/1000),0);
		$utctime= date("Y-m-d H:i:s",$utctime);		
		$data['time'].="($utctime UTC)";
		$data['reward']=$this->getReward($data['height']);
		
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
	
	public function getMicroBlockTransactions($microblockhash){
		$data['hash']=$microblockhash;
		$url=DATA_SRC_SITE.'v2/micro-blocks/hash/'.$microblockhash.'/transactions';
		$websrc=$this->getwebsrc($url);
		//echo $websrc;
		$data['txstable']="";
		$counter=0;
		if(strpos($websrc,"block_hash")>0){
			if(strpos($websrc,"ttl")==TRUE){		
				$pattern='/hash":"(.*)","block_height":(.*),"hash":"(.*)","signatures":\["(.*)"\],"tx":{"amount":(.*),"fee":(.*),"nonce":(.*),"payload":"(.*)","recipient_id":"(.*)","sender_id":"(.*)","ttl":(.*),"type":"(.*)","version":(.*)}/U';
				preg_match_all($pattern,$websrc, $matches);
				for($i=0;$i<count($matches[1]);$i++){
					$block_hash=$matches[1][$i];
					$block_height=$matches[2][$i];
					$hash=$matches[3][$i];
					$signatures=$matches[4][$i];
					$amount=$matches[5][$i];
					$fee=$matches[6][$i];
					$nonce=$matches[7][$i];
					$payload=$matches[8][$i];
					$recipient_id=$matches[9][$i];
					$sender_id=$matches[10][$i];
					$ttl=$matches[11][$i];
					$type=$matches[12][$i];
					$version=$matches[13][$i];			
					//echo "txhash:$hash\n";
					
					$txhash=$hash;
					$counter++;
					$txhash_show="th_****".substr($txhash,-4);
					$amount=$amount/1000000000000000000;
					$recipient_id_show="ak_****".substr($recipient_id,-4);								
					$sender_id_show="ak_****".substr($sender_id,-4);
					$data['txstable'].="<tr><td>$counter</td><td><a href=../../transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td><a href=/address/wallet/$recipient_id>$recipient_id_show</a></td></tr>";

					};
				//p//rint_r();
				//echo count($matches);
				
				}else{
				$pattern='/hash":"(.*)","block_height":(.*),"hash":"(.*)","signatures":\["(.*)"\],"tx":{"amount":(.*),"fee":(.*),"nonce":(.*),"payload":"(.*)","recipient_id":"(.*)","sender_id":"(.*)","type":"(.*)","version":(.*)}}/U';	
				preg_match_all($pattern,$websrc, $matches);
				for($i=0;$i<count($matches[1]);$i++){
					$block_hash=$matches[1][$i];
					$block_height=$matches[2][$i];
					$hash=$matches[3][$i];
					$signatures=$matches[4][$i];
					$amount=$matches[5][$i];
					$fee=$matches[6][$i];
					$nonce=$matches[7][$i];
					$payload=base64_encode($matches[8][$i]);
					$recipient_id=$matches[9][$i];
					$sender_id=$matches[10][$i];			
					$type=$matches[11][$i];
					$version=$matches[12][$i];			
					//echo "txhash:$hash\n";
					
					$txhash=$hash;$counter++;
					$txhash_show="th_****".substr($txhash,-4);
					$amount=$amount/1000000000000000000;
					$recipient_id_show="ak_****".substr($recipient_id,-4);								
					$sender_id_show="ak_****".substr($sender_id,-4);
					$data['txstable'].="<tr><td>$counter</td><td><a href=../../transaction/$txhash>$txhash_show</a></td><td>$amount</td><td><a href=/address/wallet/$sender_id>$sender_id_show</a></td><td><a href=/address/wallet/$recipient_id>$recipient_id_show</a></td></tr>";

					};	
				//print_r($matches);
				//echo count($matches);
				
				}
			

			}
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
	
	public function getTransactionInfo($transactionhash){
		$data['hash']=$transactionhash;		
		$data['table_data']="";
		
		$url=DATA_SRC_SITE.'v2/transactions/'.$transactionhash;
		$websrc=$this->getwebsrc($url);
		if(strpos($websrc,"block_hash")>0){
			$txData=json_decode($websrc);
			$data['table_data'].='<tr><th colspan="3"><center><h2>'.$txData->tx->type.'</h2></center></th></tr>';
			
			$table= (array)json_decode($websrc,true);

			foreach ($table as $key=>$content){
				if($key=='tx'){
					$data['table_data'].='<tr><td rowspan="'.count($content).'">Tx</td>';
					foreach ($content as $key_tx=>$content_tx){			
						//echo "--".$key_tx,': ',$content_tx,"\n";
						if($key_tx=="recipient_id" ||$key_tx=="sender_id" || $key_tx=="account_id" || $key_tx=="caller_id"){
							$content_tx="<a href=/address/wallet/$content_tx>$content_tx</a>";
							}					
						
						if($key_tx=="fee" || $key_tx=="gas"){
							$aefee=number_format($content_tx/1000000000000000000, 18, '.', '');
							$content_tx=$aefee." AE($content_tx ættos )";
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
							$content=$content."   ".$data['confirmed'];
							}
							
							if($key=="block_hash"){
								$content="<a href=/block/microblock/$content>$content</a>";
								}
							
							
							$data['table_data'].='<tr><td><b>'.$key.'</b> </td><td  colspan="2">'.$content.'</td></tr>';
						}
					}
				}
			
			
			
			return $data;		
			
			}else{echo "NULL";return $data;}
		
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
	
	
	
	public function genBlocksIndex($page=1){
		$data['title']= "Blocks";
		$data['page']=$page;
		$perpage=100;
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
	
	
	
	
	public function try(){
		$beehashrate=$this->getHashRate();
		$blocksnum_24=324;
		$minedblocks=212;
		$data['totalhashrate']=round(($beehashrate[0]*$blocksnum_24)/$minedblocks,2)." M";
		echo $data['totalhashrate'];
		}
	
	public function getTotalMined(){
		$latestheight=$this->GetTopHeight();
		$totalmined=0;
		for($i=1;$i<$latestheight+1;$i++){
			$totalmined=$totalmined+$this->getReward($i);
			}
		return $totalmined;
		}
		
	
	
	
	public function getBlockInfo_old($height){
		
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
	
	public function getMicroBlockTransactions_old($microblockhash){
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
	public function getTransactionInfo_old($transactionhash){
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
	public function getMicroBlockInfo_old($microblockhash){
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
	
	
	public function getMicroBlocks_old($microblockheight){
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
		
		
		private function getTxsTime($block_hash){
		$this->load->database();
		$sql="SELECT time from microblock WHERE hash='$block_hash' limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		//$totalmins=time()- round(($row->time/1000),0);
		$totalmins=round(($row->time/1000),0);
		return date("Y-m-d H:i:s",$totalmins);
		}
		

}
