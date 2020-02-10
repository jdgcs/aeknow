<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	
	public function indexv2(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENS();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS_new.html',$data);
		$this->output->cache(3);
		}
	
	public function expiring(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENSexpiring();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS_expiring.html',$data);
		$this->output->cache(10);
		}
	
	
	public function finished(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENS_New();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS_finished.html',$data);
		$this->output->cache(3);
		}
	
	public function byblocknew(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENSbyBlock_New();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS_byexpiring.html',$data);
		//$this->output->cache(3);
		}
		
	public function byblock(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENSbyBlock_New();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS_byexpiring.html',$data);
		$this->output->cache(3);
		}
	
	public function viewbids($name){
		$this->load->model('aenses');	
		$data=$this->aenses->showBids_New($name);
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS_bidslog.html',$data);
		$this->output->cache(3);
		}
		
	public function indexnew(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENS_New();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS.html',$data);
		$this->output->cache(3);
		}
		
	public function index(){
		$this->load->model('aenses');	
		$data=$this->aenses->showAENS_New();
		$data['status']="";
		$data['aename']="";
		$this->load->view('AENS.html',$data);
		$this->output->cache(3);
		}
	
	public function index_old()
	{	
		$this->load->model('aenses');	
		$data=$this->aenses->regStatus();
		$data['status']="";
		$data['aename']="";
		$this->load->view('en/aens_index.html',$data);
		$this->output->cache(3);
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

