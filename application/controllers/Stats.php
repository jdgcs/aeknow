<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stats extends CI_Controller {

	public function index()
	{	
		$this->load->view('stats.html');
	}
	
	public function hashrate()
	{	
		$this->load->model('stat');	
		$data=$this->stat->getHashrate();			
		$this->load->view('en/stats_hashrate.html',$data);
		$this->output->cache(30);
		
	}

}

