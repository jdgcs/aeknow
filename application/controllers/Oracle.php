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
		
	public function detail($cthash="")
	{	
		$this->load->model('contracts');	
		$data=$this->contracts->getContractDetail($cthash);
		$this->load->view('contracts_detail.html',$data);
		$this->output->cache(1);
	}
	
}

