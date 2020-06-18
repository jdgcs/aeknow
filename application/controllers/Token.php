<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token extends CI_Controller {
	
	public function index(){
		echo "AEX9 Token Page";
		}	
	//check the information of token
	public function checktoken(){		
		$token=trim(strtoupper($this->input->post('tokenname')));	
		$this->load->model('tokens');	
		$data=$this->tokens->CheckToken($token);
		echo $data;
		//$this->load->view('en/blank.html',$data);
		//$this->output->cache(3);	
		}

}

