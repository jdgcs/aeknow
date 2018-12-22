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
		$query = $this->db->query('select beneficiary,count(*) from miner WHERE orphan is FALSE group by beneficiary order by count desc;');

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
}

