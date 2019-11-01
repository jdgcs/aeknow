<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Name extends CI_Controller {
	public function index($name="")
	{	
		$this->load->model('names');	
		$ak=$this->names->getAddress($name);
		$page=1;$type='all';
		
		if(strpos($ak,"ak_")>0){
			$this->load->model('Wallets');	
			$data=$this->Wallets->getWalletInfo($ak,$page,$type);
			$this->load->view('en/account.html',$data);
		}else{echo "$ak";}
	}
	
	
	public function show($name="")
	{	
		$this->load->model('names');	
		$ak=$this->names->getAddress($name);
		$page=1;$type='all';
		
		if(strpos($ak,"ak_")>0){
			$this->load->model('Wallets');	
			$data=$this->Wallets->getWalletInfo($ak,$page,$type);
			$this->load->view('en/account.html',$data);
		}else{echo "$ak";}
	}
	
	public function namelist($ak=""){
		$this->load->model('names');
		$data=$this->names->getNameList($ak);
		$this->load->view('name_list.html',$data);
		}
}

