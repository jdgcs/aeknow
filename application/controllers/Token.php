<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token extends CI_Controller {
	
	public function index(){
		$this->load->model('tokens');	
		$data=$this->tokens->getTokenList();
		$this->load->view('token_index.html',$data);
		$this->output->cache(3);
		}	
		
	public function all(){
		$this->load->model('tokens');	
		$data=$this->tokens->getAllTokenList();
		$this->load->view('token_all.html',$data);
		$this->output->cache(3);
		}	
		
	public function top($address,$offset=0){
		$this->load->model('tokens');	
		$data=$this->tokens->getAllTokenTopList($address,$offset);
		$this->load->view('token_top.html',$data);
		//$this->output->cache(3);
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

