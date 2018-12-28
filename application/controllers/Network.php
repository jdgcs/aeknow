<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Network extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('networks');
		$data=$this->networks->getNetworkStatus();
		$this->load->view('networkstatus.html',$data);
	}
	
	public function p($page)
	{	
		if(is_numeric($page)){
			$this->load->model('transactions');
			$data=$this->transactions->getTransactions($page);
			$this->load->view('transaction_index.html',$data);
		}else{
			echo "NULL";
			}
	}

}

