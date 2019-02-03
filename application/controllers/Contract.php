<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contract extends CI_Controller {
	public function index()
	{	
		$this->load->model('contracts');	
		$data=$this->contracts->getContractList();
		$this->load->view('contracts_index.html',$data);
		$this->output->cache(5);
	}
		
	public function detail($cthash="")
	{	
		$this->load->model('contracts');	
		$data=$this->contracts->getContractDetail($cthash);
		$this->load->view('contracts_detail.html',$data);
		$this->output->cache(1);
	}
	
}
