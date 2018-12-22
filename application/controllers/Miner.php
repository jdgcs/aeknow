<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Miner extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	
	
	public function index()
	{	$this->load->model('miners');
		$data=$this->miners->getMinerIndex();
		$this->load->view('minerboard_new.html',$data);
		$this->output->cache(1/2);
	}
	
	public function inflation(){
		$this->load->database();
		$sql="SELECT * from aeinflation ORDER BY blockid";
		$query = $this->db->query($sql);
	
		$data['rewardtable']="";
		foreach ($query->result() as $row){
			$reward=$row->reward/10;
			$blockid=$row->blockid;
			$totalamount=$row->totalamount/10;
			$eta=1543373685+$blockid*180;
			$eta=date("Y-m-d",$eta);
			$data['rewardtable'].="<tr><td>".$blockid."</td><td>".$reward."</td><td>".$totalamount."</td><td>$eta</td></tr>";
			}
		
		$currentheight=$this->GetTopHeight();
		$data['currentheight']=$currentheight;
		$currentheight=$currentheight+1;
		
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		
		$this->load->view('miningreward.html',$data);
		//$this->output->cache(1);
		}	


public function viewaccount($ak=NULL){		
		//$ak=$this->input->get('ak', TRUE);
		$this->load->helper('url');
		$tagstr="dd".$ak;
		if(strpos($tagstr,"th_")>0){redirect('https://www.aeknow.org/block/transaction/'.$ak);}
		if(strpos($tagstr,"mh_")>0){redirect('https://www.aeknow.org/block/microblock/'.$ak);}
		if(strpos($tagstr,"kh_")>0){redirect('https://www.aeknow.org/block/keyblock/'.$ak);}
		if(strpos($tagstr,"ak_")>0){redirect('https://www.aeknow.org/address/wallet/'.$ak);}
		if(is_numeric($ak)){redirect('https://www.aeknow.org/block/height/'.$ak);}
	
		
	
		$url="http://127.0.0.1:3013/v2/accounts/$ak";
		$websrc=$this->getwebsrc($url);
		$data['account']=$ak;
		$data['balance']=0;
		if(strpos($websrc,"balance")==TRUE){
			$pattern='/{"balance":(.*),"id":"(.*)","nonce":(.*)}/i';
			preg_match($pattern,$websrc, $match);
			$data['balance']=$match[1]/1000000000000000000;
		}
		
		

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
			if($counter<101){
				$data['totalblocks'].="<tr><td>".$counter."</td><td><a href=/block/height/$blockheight>$blockheight</a></td><td>".$reward."</td><td>".$minedtime."</td></tr>";
			}
			}
		//////////////////////////////////////get Transactions/////////////////////////////
		$sql= "select block_height,block_hash,hash,amount,recipient_id, sender_id FROM transactions WHERE recipient_id='$ak' OR sender_id='$ak' order by block_height desc";
		$query = $this->db->query($sql);
		$counter=0;
		$data['totaltxs']="";
		foreach ($query->result() as $row){
			$counter++;
			if($counter<101){
				$block_height=$row->block_height;
				$block_hash=$row->block_hash;
				$block_hash_show="mh_****".substr($block_hash,-4);
				$hash=$row->hash;
				$hash_show="th_****".substr($hash,-4);
				$amount=$row->amount/1000000000000000000;
				$recipient_id=$row->recipient_id;
				$recipient_id_show="ak_****".substr($recipient_id,-4);
				$sender_id=$row->sender_id;
				$sender_id_show="ak_****".substr($sender_id,-4);
				$data['totaltxs'].="<tr><td>$counter</td><td>$block_height</td><td>$block_hash_show</td><td>$hash_show</td>
				<td>$amount</td><td><a href='/address/wallet/$sender_id'>$sender_id_show</a></td><td><a href='/address/wallet/$recipient_id'>$recipient_id_show</a></td></tr>";
			}
			}
		$data['transaction_count']=$query->num_rows();
		
		$data['notes']="From the blockchain, to the blockchain.";
		$alias=$this->getalias($ak);
		if($ak!=$alias){
			$sql="SELECT remark FROM addressinfo WHERE address='$ak'";
			$query = $this->db->query($sql);
			$row = $query->row();
			$data['notes']="<b>$alias:</b> " .$row->remark;
			}
		
		$this->load->view('account.html',$data);
		$this->output->cache(3);
		}
		
	public function accountimg($ak=NULL){
		$this->load->library('Qrcode'); 
		//$ak=$this->input->get('ak', TRUE);
		echo $this->qrcode->png($ak);		
		}
	
	public function aedifficulty(){
		$data['title']="Aeternity Mining Difficulty";		
		$data['tabledata']='{"period": "2018-12-4 10:19:14", "difficulty": 16608}';
		
		$this->load->database();
		$sql="select count(*) from aenetwork";
		$query = $this->db->query($sql);
		$row = $query->row();
		$totalcount= $row->count;
		$step=round(($totalcount/100),0);
		
		
		$sql="select * from aenetwork order by rid asc";
		$query = $this->db->query($sql);
		
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$difftime=date("Y-m-d H:i:s",$row->recordtime);
			$diff=round($row->difficulty/16777216,0);
			
			if(($counter%$step)==0){				
				$data['tabledata'].=',{"period": "'.$difftime.'", "difficulty":'.$diff.'}';
			}
			}
		$data['tabledata'].=',{"period": "'.$difftime.'", "difficulty":'.$diff.'}';
			
		$this->load->view('difficulty.html',$data);
		$this->output->cache(3);
		}
	
	public function blocksinfo(){
		$this->load->database();
		$sql="SELECT time FROM miner WHERE height=1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$totalmins= (time()-($row->time/1000))/60;
		
		$sql="SELECT height FROM miner order by height desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		
		$totalheight=$row->height;
		
		echo "<a href=/>Home</a><br/>$totalmins Minutes mined $totalheight blocks; ".($totalmins/$totalheight)." Minutes per block";
		
		}
	public function peers(){
		$data['title']="Aeternity Peers and Beneficiary addresses";		
		$data['tabledata']='{"period": "2018-12-4 10:19:14", "peers": 1803}';
		$data['tabledata_beneficiary']='{"period": "2018-12-4 10:19:14", "beneficiary": 115}';
		
		$this->load->database();
		$sql="select count(*) from aenetwork";
		$query = $this->db->query($sql);
		$row = $query->row();
		$totalcount= $row->count;
		$step=round(($totalcount/100),0);
		
		
		$sql="select * from aenetwork order by rid asc";
		$query = $this->db->query($sql);
		
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$difftime=date("Y-m-d H:i:s",$row->recordtime);
			$peers=$row->peercount;
			$beneficiary=$row->minercount;
			
			if(($counter%$step)==0){				
				$data['tabledata'].=',{"period": "'.$difftime.'", "peers":'.$peers.'}';
				$data['tabledata_beneficiary'].=',{"period": "'.$difftime.'", "beneficiary":'.$beneficiary.'}';
			}
			}
		$data['tabledata'].=',{"period": "'.$difftime.'", "peers":'.$peers.'}';
		$data['tabledata_beneficiary'].=',{"period": "'.$difftime.'", "beneficiary":'.$beneficiary.'}';
			
		$this->load->view('peers.html',$data);
		$this->output->cache(3);
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
	private function GetTopHeight()	{
	$url=DATA_SRC_SITE."v2/blocks/top";
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


	private function getReward($blockheight){
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->reward/10;
		}
		
		
	private function getMined($beneficiary){
		$sql1="select count(*) FROM miner WHERE beneficiary='$beneficiary' AND orphan is FALSE";
		$query1 = $this->db->query($sql1);
		$row = $query1->row();
		return $row->count;
		}
	
	private function notOrphan($height){
		$this->load->database();		
		$sql="select count(*) FROM miner WHERE height='$height' and orphan is TRUE";
		$query = $this->db->query($sql);
		$row = $query->row();
		if($row->count==1){return FALSE;}
		return TRUE;
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
