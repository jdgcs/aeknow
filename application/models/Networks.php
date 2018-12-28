<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tests extends CI_Model {
	
	public function getNetworkStatus(){
		$this->load->database();
		///////////////////////////////////////////get blocks info////////////////////////////
		$data['topheight']= $this->GetTopHeight();
		$data['totalaemined']=$this->getTotalMined();	
		
		$sql="SELECT time FROM miner WHERE height=1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$totalmins= (time()-($row->time/1000))/60;		
		$totalheight=$data['topheight'];		
		$data['avgminsperblock']=round($totalmins/$totalheight,2);
		
		$url="http://127.0.0.1:3013/v2/key-blocks/height/$totalheight";
		$websrc=$this->getwebsrc($url);
		$data['lastime']="";
		if(strpos($websrc,"time")>0){
			$pattern='/(.*),"time":(.*),"version(.*)/i';
			preg_match($pattern,$websrc, $match);
			$data['lastime']=$match[2];
			$millisecond=substr($data['lastime'],0,strlen($data['lastime'])-3); 
			$whenmined=time()-$millisecond;
			//$minedtime=$whenmined;
			$data['lastime']=date('i:s',$whenmined);
			}
			
		$sql="SELECT count(*) FROM miner WHERE orphan is TRUE";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['totalorphan']= $row->count;		
		
		
		/////////////////Transactions info//////////////////
		$sql="SELECT count(*),sum(fee) from transactions";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['totaltxs']=$row->count;
		$data['totalfee']=number_format($row->sum/1000000000000000000, 18, '.', '');
		$period=(time()-1543373685)/(3600*24);		
		$data['avgtxsperday']=round($data['totaltxs']/$period,2);
		$data['avgtxspersec']=round($data['totaltxs']/(time()-1543373685),2);
		$data['avgfee']=number_format($data['totalfee']/$data['totaltxs'],18, '.', '');
		
		
		
		
		///////////////////////////////////////
		//////////////////////////////get difficulty////////////////////////////
		$url="http://127.0.0.1:3013/v2/status";
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
		
		//////////////////////////////get hashrate////////////////////////////
		$data['totalhashrate']=0;
		$sql="SELECT hashrate FROM pools order by pid desc limit 3";
		$query = $this->db->query($sql);
		//$row = $query->row();
		foreach ($query->result() as $row){
			$data['totalhashrate']=$data['totalhashrate']+$row->hashrate;
		}
		$data['totalhashrate']=round($data['totalhashrate']/1000,2);
		
		//////////////////////////get 	current reward////////////////////////
		$currentheight=$data['topheight'];
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		//$data['totalaemined']=$this->getTotalMined();
		
		
		return $data;
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
