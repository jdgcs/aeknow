<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('transactions');
		$data=$this->transactions->getTransactions(1);
		$this->load->view('transaction_index.html',$data);
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

