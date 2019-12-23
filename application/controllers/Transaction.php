<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('transactions');
		$data=$this->transactions->getTransactions(1,"all");
		$this->load->view('en/transaction_index.html',$data);
		$this->output->cache(3);
	}
	
	public function p($page=1,$type=1000)
	{	
		if(is_numeric($page)){
			$this->load->model('transactions');
			$data=$this->transactions->getTransactions($page,$type);
			$this->load->view('en/transaction_index.html',$data);
			$this->output->cache(3);
		}else{
			echo "NULL";
			}
	}
	
	public function download($ak,$limit="all")
	{	
		$this->load->model('transactions');
		$data=$this->transactions->getTransactionsExcel($ak,$limit);
	}
	
	public function posttx($tx=""){
		$this->load->model('transactions');
		$data=$this->transactions->postTransaction($tx);
		$this->load->view('en/transaction_post.html',$data);
		$this->output->cache(3);
		}

}

