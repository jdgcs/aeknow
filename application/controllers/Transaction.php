<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('transactions');
		$data=$this->transactions->getTransactions(1,"all");
		$this->load->view('en/transaction_index.html',$data);
	}
	
	public function p($page=1,$type="all")
	{	
		if(is_numeric($page)){
			$this->load->model('transactions');
			$data=$this->transactions->getTransactions($page,$type);
			$this->load->view('en/transaction_index.html',$data);
		}else{
			echo "NULL";
			}
	}
	
	public function posttx($tx=""){
		$this->load->model('transactions');
		$data=$this->transactions->postTransaction($tx);
		$this->load->view('en/transaction_post.html',$data);
		}

}

