<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class V2 extends CI_Controller {
	
	public function gettx($tx=""){
		$this->load->model('transactions');
		$data=$this->transactions->postTransaction($tx);
		$this->load->view('en/transaction_post.html',$data);
		}

}

