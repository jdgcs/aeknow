<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

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
	public function tokentest(){
		$this->load->model('tests');
		$data=$this->tests->getCallInfo("cb_KxGEoV2hK58AoMLlAP6SFrYeiuRrxi5A5rNjruumGuhbIsuZStUbvgZYb4Za8xB6P8AmHfsB","ct_M9yohHgcLjhpp1Z8SaA1UTmRMQzR4FWjJHajGga8KBoZTEPwC");
		var_dump($data);
		
		$data=$this->tests->getContractinfo("ct_M9yohHgcLjhpp1Z8SaA1UTmRMQzR4FWjJHajGga8KBoZTEPwC");
		var_dump($data);
		}
		
	public function txs($page=1,$type="all")
	{	
		if(is_numeric($page)){
			$this->load->model('tests');
			$data=$this->tests->getTransactions($page,$type);
			$this->load->view('en/transaction_index.html',$data);
		}else{
			echo "NULL";
			}
	}	
	public function txdetail($transactionhash){
		$this->load->model('tests');
		$data=$this->tests->getTransactionInfo($transactionhash);
		$this->load->view('tx_detail.html',$data);
		}	
		
		public function transaction($transactionhash=""){
		//echo "building...";
		if(trim($transactionhash)!=""){
			$this->load->model('tests');
			$data=$this->tests->getTransactionInfo($transactionhash);
			$this->load->view('transaction.html',$data);
		}else{echo "NULL";}
		//$this->output->cache(3);
		}
	
	public function dev(){
		$this->load->view('developer.html');
		}
	public function block($height=88){		
		if($height<1 || $height>$this->GetTopHeight()){echo "Not in DB."; return 0;}
		$this->load->model('Tests');
		$data=$this->Tests->getBlockInfo($height);
		$this->load->view('block_v2.html',$data);
		}
	
	public function hr(){
		$this->load->model('tests');
		echo $this->tests->getHashRate();
		//$this->load->view('mblocks.html',$data);
		}
		
	public function pools(){
		$this->load->model('tests');
		echo $this->tests->getPools();
		//$this->load->view('mblocks.html',$data);
		}
	public function microblocks($microblockheight){
		$this->load->model('tests');
		$data=$this->tests->getMicroBlocks($microblockheight);
		$this->load->view('mblocks.html',$data);
		}
	
	public function microblock($microblockhash,$transactions=NULL){
		//echo"building...";
		$this->load->model('Tests');
		if($transactions=="transactions"){
			$data=$this->Tests->getMicroBlockTransactions($microblockhash);
			$this->load->view('transactions.html',$data);
			}else{
			$data=$this->Tests->getMicroBlockInfo($microblockhash);
			$this->load->view('mblock.html',$data);
			}
		//$this->output->cache(3);
		}
	public function blockindex(){
		$this->load->model('tests');
		$data=$this->tests->genBlocksIndex();
		$this->load->view('blocks_v2.html',$data);
		//$this->output->cache(1);
		}
	
	public function p($page)
	{	
		if(is_numeric($page)){
			if($page<1){$page=1;}
			$this->load->model('blocks');
			$data=$this->blocks->genBlocksIndex($page);
			$this->load->view('blocks_v2.html',$data);
		}else{
			echo "NULL";
			}
	}
	
	
	
	
	public function totalmined(){
		$this->load->model('Tests');
		echo $this->Tests->try()."<br/>";
		echo $this->Tests->getTotalMined()."<br/>";
		echo print_r($this->Tests->getHashRate());
		}
	public function wallet($ak=NULL,$page=1,$type='all'){		
		if($page<1){$page=1;}
		$this->load->model('tests');	
		$data=$this->tests->getWalletInfo($ak,$page,$type);
		$this->load->view('account.html',$data);
		//$this->output->cache(2);
		}
	
	public function wealth500(){
		$this->load->model('Addresses');
		$data=$this->Addresses->getWealth500();
		$this->load->view('wealth500.html',$data);
		}
	
	public function height($height){
		if($height>$this->GetTopHeight()){echo "Not in DB."; return 0;}
		$this->load->model('blocks');
		$data=$this->blocks->getBlockInfo($height);
		$this->load->view('block.html',$data);
		}
	
	
	public function keyblock($keyblockhash){
		$height=-1;
		$this->load->model('blocks');
		$height=$this->blocks->getBlockHeight($keyblockhash);
		if($height<0){echo "Not in DB."; return 0;}
		$data=$this->blocks->getBlockInfo($height);
		$this->load->view('block.html',$data);
		}
	
	public function minerindex(){
		$this->load->model('miners');
		$data=$this->miners->getMinerIndex();
		$this->load->view('minerboard_new.html',$data);
		}
		
	public function network(){
		$this->load->model('tests');
		$data=$this->tests->getNetworkStatus();
		$this->load->view('en/networkstatus.html',$data);
		}	
	

	public function microblocktxs($microblockhash){
		$this->load->model('blocks');
		$data=$this->blocks->getMicroBlockTransNumt($microblockhash);
		//$this->load->view('mblocks.html',$data);
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
	public function index()
	{		
		$this->load->model('tests');
		$data=$this->tests->getMinerIndex();
		$this->load->view('en/minerboard.html',$data);
		//$this->output->cache(1/2);
	}
	public function in24h(){
		//$this->load->view('miners.html');
		$timetag=(time()-(24*60*60))*1000; 
		$this->load->database();
		$sql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$query = $this->db->query($sql);

		$counter=0;
		$blockcounter=0;
		$data['totalminers']="";
		$data['totalminers']= "<table border=1><tr><td>No.</td><td>Account</td><td>Blocks mined</td></tr>";
		foreach ($query->result() as $row)
		{
			$counter++;
			$blockcounter=$blockcounter+$row->count;
			$data['totalminers'].= "<tr><td>".$counter."</td><td><a href=/miner/viewaccount/".$row->beneficiary.">".$row->beneficiary."</a></td><td>".$row->count."</td></tr>";
		}
$data['totalminers'].= "</table>";
		$data['totalminers'].= 'Total Beneficiary Accounts: ' . $query->num_rows()." have mined $blockcounter blocks.";
		
		$data['title']="Miners rank by blocks mined in the past 24 hours";
		$this->load->view('welcome_message',$data);
		$this->output->cache(3);
		}
	private function getMined($beneficiary){
		$sql1="select count(*) FROM miner WHERE beneficiary='$beneficiary' AND orphan is FALSE";
		$query1 = $this->db->query($sql1);
		$row = $query1->row();
		return $row->count;
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

