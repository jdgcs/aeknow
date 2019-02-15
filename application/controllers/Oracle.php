<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Oracle extends CI_Controller {
	public function index()
	{	
		$this->load->model('oracles');	
		$data=$this->oracles->getOracleList();
		$this->load->view('oracles_index.html',$data);
		//$this->output->cache(5);
	}
		
	public function id($oracle_id="")
	{	
		$this->load->model('oracles');	
		$data=$this->oracles->getOracleDetail($oracle_id);
		$this->load->view('oracles_detail.html',$data);
		//$this->output->cache(1);
	}
	
}

