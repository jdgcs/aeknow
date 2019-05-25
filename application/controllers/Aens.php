<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	public function index()
	{	
		$this->load->model('aenses');	
		$data=$this->aenses->regStatus();
		$data['status']="";
		$data['aename']="";
		$this->load->view('en/aens_index.html',$data);
		$this->output->cache(10);
	}
	
	function query($aename=""){
		$this->load->model('aenses');	
		$aename=trim(strtolower($aename));
		$data=$this->aenses->query($aename);
		if($data['status']=="available"){	
			$this->load->library('session');
			$this->session->set_userdata('aename',$aename);
			$this->session->set_userdata('querytime',time());		
			$this->load->view('en/aens_reg.html',$data);
		}else{
			$this->load->view('en/aens.html',$data);
			}
	}
	
	function postreg(){
		$aename=trim(strtolower($this->input->post('aename')));
		$akaddress=trim($this->input->post('akaddress'));		
		$this->load->library('session');
		$this->session->set_userdata('akaddress',$akaddress);		
		$lasttime=$this->session->userdata('querytime');
		if(time()-$lasttime<2){
				//echo "Too quick";
				$data['status']="Too quick";
				$data['aename']=$aename;
				$this->load->view('en/aens.html',$data);
			}else{
				//echo "Recorded.<br />";
				$this->session->set_userdata('querytime',time());
				$this->load->model('aenses');	
				$aename=strtolower($aename)	;
				$data=$this->aenses->savetodb($aename,$akaddress);
				$this->load->view('en/aens.html',$data);
			}
			
		//echo "$aename:$akaddress recorded.";
		}
		
		
		function checkmyaens($akaddress=""){
			$this->load->model('aenses');	
			$data=$this->aenses->getNames($akaddress);
			$this->load->view('en/aens_list.html',$data);
			}

}

