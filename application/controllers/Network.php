<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Network extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('networks');
		$data=$this->networks->getNetworkStatus();
		$this->load->view('networkstatus.html',$data);
		$this->output->cache(1/4);
	}
	
	
}

