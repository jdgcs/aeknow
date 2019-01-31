<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Name extends CI_Controller {
	public function index($name="")
	{	
		$this->load->model('names');	
		$ak=$this->names->getAddress($name);
		$page=1;$type='all';
		
		if($ak!="NULL"){
			$this->load->model('Wallets');	
			$data=$this->Wallets->getWalletInfo($ak,$page,$type);
			$this->load->view('account.html',$data);
		}else{echo "NULL Name.";}
	}
	
	
	public function show($name="")
	{	
		$this->load->model('names');	
		$ak=$this->names->getAddress($name);
		$page=1;$type='all';
		
		if($ak!="NULL"){
			$this->load->model('Wallets');	
			$data=$this->Wallets->getWalletInfo($ak,$page,$type);
			$this->load->view('account.html',$data);
		}else{echo "NULL Name.";}
	}
}

