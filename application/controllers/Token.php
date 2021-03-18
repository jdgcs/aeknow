<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token extends CI_Controller {
	
	public function index(){
		$this->load->model('tokens');	
		$data=$this->tokens->getTokenList();
		$this->load->view('token_index.html',$data);
		//$this->output->cache(5);
		}	
		
		
	//check the information of token
	public function checktoken(){		
		$token=trim(strtoupper($this->input->post('tokenname')));	
		$this->load->model('tokens');	
		$data=$this->tokens->CheckToken($token);
		if(trim($data)==""){
				echo "OK";
			}else{
				echo $data;
				}
		}

}

