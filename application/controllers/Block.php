<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Block extends CI_Controller {

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
	{	$this->load->model('blocks');
		$data=$this->blocks->genBlocksIndex();
		
		//get the language of the browser
		$this->load->model('languages');	
		$data['mylang']=$this->languages->getPreferredLanguage();
		$data['mylang']="en";
		
		$this->load->view('en/blocks_v2.html',$data);
		$this->output->cache(5);
	}
	
	public function p($page)
	{	
		if(is_numeric($page)){
			if($page<1){$page=1;}
			$this->load->model('blocks');			
			$data=$this->blocks->genBlocksIndex($page);
			
			//get the language of the browser
			$this->load->model('languages');	
			$data['mylang']=$this->languages->getPreferredLanguage();
			$data['mylang']="en";
		
		
			$this->load->view('en/blocks_v2.html',$data);
		}else{
			echo "NULL";
			}
	}
	
	public function height($height){
		$topheight=$this->GetTopHeight();
		if($height<1 || $height>$topheight){echo "Not in DB."; return 0;}
		$this->load->model('blocks');
		$data=$this->blocks->getBlockInfo($height);
		
		//get the language of the browser
			$this->load->model('languages');	
			$data['mylang']=$this->languages->getPreferredLanguage();
			
		$this->load->view('en/block_v2.html',$data);
				
		if($height<($topheight-10)){
			//$this->output->cache(300);
		}
		}
	
	public function keyblock($keyblockhash){
		$height=-1;
		$this->load->model('blocks');
		$height=$this->blocks->getBlockHeight($keyblockhash);
		if($height<0){echo "Not in DB."; return 0;}
		$data=$this->blocks->getBlockInfo($height);
		$this->load->view('en/block_v2.html',$data);
		$this->output->cache(3);
		}
	
	public function transaction($transactionhash=""){
		//echo "building...";
		if(trim($transactionhash)!=""){
			$this->load->model('blocks');
			$data=$this->blocks->getTransactionInfo($transactionhash);	
			
			//get the language of the browser
			$this->load->model('languages');	
			$data['mylang']=$this->languages->getPreferredLanguage();
				
			$this->load->view('en/tx_detail.html',$data);		
		}else{echo "NULL";}
		//$this->output->cache(3);
		}
	
	public function microblock($microblockhash="",$transactions=NULL){
		//echo"building...";
		$this->load->model('blocks');
		if($transactions=="transactions"){
			$data=$this->blocks->getMicroBlockTransactions($microblockhash);
			$this->load->view('en/transactions.html',$data);
			}else{
			$data=$this->blocks->getMicroBlockInfo($microblockhash);
			$this->load->view('en/mblock.html',$data);
			}
		//$this->output->cache(3);
		}
		
	public function microblocks($microblockheight=1){
		$this->load->model('blocks');
		$data=$this->blocks->getMicroBlocks($microblockheight);
		$this->load->view('en/mblocks.html',$data);
		}
	
	
		
	private function getTransactionsNum($mhash){
		$this->load->database();
		$sql="SELECT count(*) from transactions WHERE block_hash='$mhash'";
		$query = $this->db->query($sql);
		$row = $query->row();		
		return $row->count;
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

