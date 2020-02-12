<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apis extends CI_Model {
	
	public function getTx($ak,$limit=20,$offset=0){
		$this->load->database();
		$trans_sql="SELECT txhash,txtype FROM txs WHERE sender_id='$ak' OR  recipient_id='$ak' ORDER BY block_height desc,tid desc LIMIT $limit offset ".$offset;		
		$query = $this->db->query($trans_sql);

		$counter=0;
		$results="";
		$results.= "{\"txs\":[";
		foreach ($query->result() as $row){
			//$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$results.= "{\"txtype\":\"$txtype\",\"txhash\":\"$txhash\"},";	
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
	
	public function getAENS($ak){
		$this->load->database();
		$sql="SELECT distinct aensname,expire_height FROM txs_aens WHERE nameowner='$ak' order by expire_height";	
		$query = $this->db->query($sql);
		//$aenscounter=0;
		
		foreach ($query->result() as $row){
			//$aens[$counter]['aensname']=$row->aensname;
			//$aens[$counter]['expire_height']=$row->expire_height;
			}
			
		$data=$this->object_array($row);
		
		return $data;
		
		}
	
	
	public function getTotalCoins(){
		$this->load->database();
		$trans_sql="SELECT * FROM suminfo ORDER BY sid DESC LIMIT 1";		
		$query = $this->db->query($trans_sql);

		foreach ($query->result() as $row){
			
			}
		
		$data=$this->object_array($row);
		//$tobemined=259856369;
		//$data['mined_rate']=$data['mined_coins']/$tobemined;//number_format(($data['mined_coins']/259856369‬)*100,2);
		//$data['lastblocktime']=time()-$data['updatetime'];	
		
		
		return $data['total_coins'];
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
		
		
	public function getMempoolInfo(){
		$url="http://127.0.0.1:3113/v2/debug/transactions/pending";
		$websrc=$this->getwebsrc($url);
		return $websrc;
		}
	
	public function getNetworkStatus(){
		$this->load->database();
		$data['maxtps']=116;
		///////////////////////////////////////////get blocks info////////////////////////////
		$data['topheight']= floatval($this->GetTopHeight());
		$data['totalaemined']=$this->getTotalMined();	
		
		$sql="SELECT time FROM miner WHERE height=1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$totalmins= (time()-($row->time/1000))/60;		
		$totalheight=$data['topheight'];		
		$data['avgminsperblock']=round($totalmins/$totalheight,2);
		
		$url=DATA_SRC_SITE."v2/key-blocks/height/$totalheight";
		$websrc=$this->getwebsrc($url);
		$data['lastime']="";
		if(strpos($websrc,"time")>0){
			//$pattern='/(.*),"time":(.*),"version(.*)/i';
			//preg_match($pattern,$websrc, $match);
			$info=json_decode($websrc);
			//$data['lastime']=$match[2];
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
		$sql="SELECT count(*),sum(fee) from transactions";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['totaltxs']=floatval($row->count);
		$data['totalfee']=floatval(number_format($row->sum/1000000000000000000, 18, '.', ''));
		$period=(time()-1543373685)/(3600*24);		
		$data['avgtxsperday']=round($data['totaltxs']/$period,2);
		$data['avgtxspersec']=round($data['totaltxs']/(time()-1543373685),2);
		$data['avgfee']=floatval(number_format($data['totalfee']/$data['totaltxs'],18, '.', ''));
		
		
		
		
		///////////////////////////////////////
		//////////////////////////////get difficulty////////////////////////////
		$url=DATA_SRC_SITE."v2/status";
		$websrc=$this->getwebsrc($url);
		$data['peer_count']=0;
		if(strpos($websrc,"difficulty")>0){
			$pattern='/{"difficulty":(.*),"genesis_key_block_hash":"(.*)","listening":(.*),"node_revision":"(.*)","node_version":"(.*)","peer_count":(.*),"pending_transactions_count":(.*),"protocols":(.*),"solutions":(.*),"syncing":(.*)}/i';
			preg_match($pattern,$websrc, $match);
			$data['difficulty']=$match[1];
			$data['difficultyfull']=floatval($data['difficulty']);
			//$data['difficulty']=round($data['difficulty']/10000000000,2);
			$data['difficulty']=round($data['difficulty']/16777216/1000,0)." K";			
			$data['peer_count']=floatval($match[6]);
		}
		
		//////////////////////////////get hashrate////////////////////////////
		$data['totalhashrate']=0;		
		$data['totalhashrate']=$this->getHashRate();
		
		//////////////////////////get 	current reward////////////////////////
		$currentheight=$data['topheight'];
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		//$data['totalaemined']=$this->getTotalMined();
		
		///////////////////////////get pending txs/////////////////////////
		$url="http://127.0.0.1:3113/v2/debug/transactions/pending";
		$websrc=$this->getwebsrc($url);
		$data['pendingtxs']=substr_count($websrc, '"tx":');
		
		////////////////////////get price////////////////////////
		$sql="SELECT price FROM aenetwork order by rid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['price']=floatval($row->price);
		
		///////////////////update time//////////////////////
		$data['timestamp']=time();
		
		return $data;
		}


private function getReward($blockheight){
		$blockheight=$blockheight+1;
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return ($row->reward/10)*0.891;
		}
		
private function getReward_calc($blockheight){
		$blockheight=$blockheight+1;
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->reward/10;
		}
		
public function getHashRate(){
		$this->load->database();
		$timetag=(time()-(24*60*60))*1000; 
		$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
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
			$totalmined=$totalmined+$this->getReward_calc($i);
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
