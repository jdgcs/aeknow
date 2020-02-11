<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Address extends CI_Controller {

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
	 
	 public function minerindex(){
		$this->load->model('miners');
		$data=$this->miners->getMinerIndex();
		$this->load->view('minerboard_new.html',$data);
		$this->output->cache(3);
		}
	 
	public function index()
	{	//echo "miners";
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
				$data['topminers'].= "<tr><td>".$counter."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td><span class='badge bg-blue'>".$minedblocks."</span></td><td>$percentage %</td><td>".$this->getTotalReward($trueaddress)." AE</td></tr>";
			}
		}

		$data['blocksmined']= $blockcounter;
		$data['totalminers']= $query->num_rows();
		
		////////////////////////////top 20 miners last 24h////////////////////////////////////////////
		$timetag=(time()-(24*60*60))*1000; 
		$blocksnum_24=0;
		$getblockssql="SELECT count(*) FROM miner WHERE time>$timetag AND orphan is FALSE";
		$query = $this->db->query($getblockssql);
		$row = $query->row();
		$blocksnum_24=$row->count;
		
		$topminersql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$query = $this->db->query($topminersql);
		$counter=0;
		$blockcounter=0;
		$data['topminers_24']= "";
		$data['lastmined']= "";
		foreach ($query->result() as $row)
		{
			$counter++;
			$blockcounter=$blockcounter+$row->count;
			
			if($counter<21){
				$showaddress=$row->beneficiary;
				$trueaddress=$row->beneficiary;
				$alias=$this->getalias($trueaddress);
				if($showaddress==$alias){
					$showaddress="ak_****".substr($showaddress,-4);
				}else{
					$showaddress=$alias;
					}
				$minedblocks=$row->count;
				$percentage=round((($minedblocks*100)/$blocksnum_24),2);
				//<td>".$this->getTotalReward($trueaddress)." AE</td>
				$data['topminers_24'].= "<tr><td>".$counter."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td><span class='badge bg-blue'>".$minedblocks."</span></td><td>$percentage%</td></tr>";
			}
		}
		
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
			$show_sender_id="ak_****".substr($sender_id,-4);
			$recipient_id=$row->recipient_id;
			$show_recipient_id="ak_****".substr($recipient_id,-4);
			$amount=$row->amount;
			$amount=$amount/1000000000000000000;
			//$data['lasttxs'].="<tr><td>$counter</td><td>$showhash</td><td>$amount</td><td><a href=/miner/viewaccount/$sender_id>$show_sender_id</a></td><td><a href=/miner/viewaccount/$recipient_id>$show_recipient_id</a></td><td>$txtime</td></tr>";
			$data['lasttxs'].="<tr><td>$counter</td><td>$amount</td><td><a href=/miner/viewaccount/$sender_id>$show_sender_id</a></td><td><a href=/miner/viewaccount/$recipient_id>$show_recipient_id</a></td><td>$txtime</td></tr>";

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
				$data['lastmined'].="<tr><td>".$row->height."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$minedtime."</td><td><span class='badge bg-green'>Normal</span></td></tr>";			
			}else{
				$data['lastmined'].="<tr><td>".$row->height."</td><td><a href=/miner/viewaccount/$trueaddress>".$showaddress."</a></td><td>".$minedtime."</td><td><span class='badge bg-yellow'>Forked</span></td></tr>";				
				}
		}
		
		//////////////////////////////get difficulty////////////////////////////
		$url=DATA_SRC_SITE."v2/status";
		$websrc=$this->getwebsrc($url);
		$pattern='/{"difficulty":(.*),"genesis_key_block_hash":"(.*)","listening":(.*),"node_revision":"(.*)","node_version":"(.*)","peer_count":(.*),"pending_transactions_count":(.*),"protocols":(.*),"solutions":(.*),"syncing":(.*)}/i';
		preg_match($pattern,$websrc, $match);
		$data['difficulty']=$match[1];
		$data['difficultyfull']=$data['difficulty'];
		//$data['difficulty']=round($data['difficulty']/10000000000,2);
		$data['difficulty']=round($data['difficulty']/16777216,0);
		
		$data['peer_count']=$match[6];
		
		
		
		$currentheight=$data['blocksmined']+1;
		
		$sql="SELECT reward FROM aeinflation WHERE blockid<$currentheight order by blockid desc limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['currentreward']=$row->reward/10;
		
		$this->load->view('miners.html',$data);
		$this->output->cache(3/4);
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
		$this->output->cache(3);
		//$this->output->cache(1);
		}	


public function wallet($ak=NULL,$page=1,$type='all'){	
		if($page<1){$page=1;}	
		$this->load->model('Wallets');	
		$data=$this->Wallets->getWalletInfo($ak,$page,$type);
		$this->load->view('en/account.html',$data);
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
		
	public function wealth5000(){
		$this->load->model('Addresses');
		$data=$this->Addresses->getWealth500();
		$this->load->view('en/wealth500.html',$data);
		$this->output->cache(30);
		}	
	
	public function wealth500($offset=0){
		$this->load->model('Addresses');
		$data=$this->Addresses->getTopAccount($offset);
		$this->load->view('en/top.html',$data);
		$this->output->cache(10);
		}
	
	public function topfrom($offset=0){
		$this->load->model('Addresses');
		$data=$this->Addresses->getTopAccount($offset);
		$this->load->view('en/top.html',$data);
		$this->output->cache(10);
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
	$info=json_decode($websrc);
	if(strpos($websrc,"key_block")==TRUE){		
		return $info->key_block->height;
	}
		
	if(strpos($websrc,"micro_block")==TRUE){
		return $info->micro_block->height;
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

