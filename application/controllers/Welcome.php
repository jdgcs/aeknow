<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

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
	{
		
		//$this->load->view('miners.html');
		$this->load->database();
		//$query = $this->db->query('select beneficiary,count(*) from miner WHERE orphan is FALSE group by beneficiary order by count desc;');
		$query = $this->db->query('select benifit as beneficiary,count(*) from keyblocks WHERE orphan is NULL group by beneficiary order by count desc;');

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
		
		$data['title']="Total miners rank by blocks mined";
		$this->load->view('welcome_message',$data);
		$this->output->cache(3);
	}
	public function in24h(){
		//$this->load->view('miners.html');
		//$timetag=(time()-(24*60*60))*1000; 
		$this->load->database();
		//$sql="select beneficiary,count(*) from miner WHERE time>$timetag AND orphan is FALSE group by beneficiary order by count desc;";
		$tagheight= GetTopHeight()-480;
		$sql="select benifit as beneficiary,count(*) from keyblocks WHERE height>$tagheight AND orphan is NULL group by benifit order by count desc;";
		
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
		//$sql1="select count(*) FROM miner WHERE beneficiary='$beneficiary' AND orphan is FALSE";
		$sql1="select count(*) FROM keyblocks WHERE benifit='$beneficiary' AND orphan is NULL";
		$query1 = $this->db->query($sql1);
		$row = $query1->row();
		return $row->count;
		}
		
public function GetTopHeight()	{
	$url=DATA_SRC_SITE."v2/blocks/top";
	$websrc=$this->getwebsrc($url);
	$info=json_decode($websrc);
	if(strpos($websrc,"key_block")==TRUE){		
		return $info->key_block->height;
	}
		
	if(strpos($websrc,"micro_block")==TRUE){
		return $info->micro_block->height;
		}
	
	return 1;
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

