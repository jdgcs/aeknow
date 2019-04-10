<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	
	public function index()
	{	
		$this->load->model('apis');
		$data['api']=$this->apis->getNetworkStatus();
		$this->load->view('api.html',$data);
		$this->output->cache(1/4);
	}
	
	public function tx($ak,$limit=20,$offset=0){
		$this->load->model('apis');
		$this->apis->getTx($ak,$limit,$offset)
		}
		
		
	public function totalcoins(){
		$this->load->model('apis');
		echo $this->apis->getTotalCoins();		
		}
	
	public function network(){
		$this->load->model('apis');
		$data['api']=$this->apis->getNetworkStatus();
		$this->load->view('api.html',$data);
		$this->output->cache(1/4);
		}
}

