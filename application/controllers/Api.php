<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('networks');
		$data['api']=$this->networks->getNetworkStatus();
		$this->load->view('en/api.html',$data);
		$this->output->cache(2);
	}
	
	public function tx($ak,$limit=20,$offset=0){
		$this->load->model('apis');
		$this->apis->getTx($ak,$limit,$offset);
		}
	
	public function aens($ak){
		$this->load->model('apis');
		$data=$this->apis->getAENS($ak);
		$this->load->view('en/blank.html',$data);
		//$this->output->cache(2);
		}	
	
	public function account($ak){
		$this->load->model('apis');
		$data=$this->apis->getAccount($ak);
		$this->load->view('en/blank.html',$data);
		$this->output->cache(2);
		}	
		
	public function totalcoins(){
		$this->load->model('apis');
		echo $this->apis->getTotalCoins();		
		}
	
	public function network(){
		$this->load->model('networks');
		$data['api']=$this->networks->getNetworkStatus();
		$this->load->view('en/api.html',$data);
		$this->output->cache(2);
		}
}

