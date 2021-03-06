<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Network extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('networks');
		$data=$this->networks->getNetworkStatus();
		
		$this->load->model('languages');	
		$data['mylang']=$this->languages->getPreferredLanguage();
		
		$this->load->view('en/networkstatus.html',$data);
		$this->output->cache(3);
	}
	
	public function mempool(){
		$this->load->model('networks');
		echo $this->networks->getMempoolInfo();		
		}
	
	public function api($type=""){
		if($type=="network"){
			$this->load->model('networks');
			$data['api']=$this->networks->getNetworkStatus();
			$this->load->view('en/api.html',$data);
			$this->output->cache(3);
			}
		}
}

