<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	public function index()
	{	
		$data['status']="";
		$data['aename']="";
		$this->load->view('aens.html',$data);
	}
	
	function query($aename=""){
		$this->load->model('aenses');
		$this->load->library('form_validation');
		 
		$data=$this->aenses->query($aename);
		if($data['status']=="available"){			
			$this->load->view('reg.html',$data);
		}else{
			$this->load->view('aens.html',$data);
			}
	}
	
	function postreg(){
		$aename=$this->input->post('aename');
		$akaddress=$this->input->post('akaddress');
		
		$this->load->library('session');
		$this->session->set_userdata('aename',$aename);
		$this->session->set_userdata('akaddress',$akaddress);
		
		echo "Sessid:".$this->session->session_id;
		echo "IP:".$this->session->ip_address;	
		
		
		echo "$aename:$akaddress recorded.";
		}

}
