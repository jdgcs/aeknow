<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function index()
	{	
		//default user page
		$this->load->model('users');
		$data=$this->users->getUserData();		
		
		$this->load->model('languages');	
		$data['mylang']=$this->languages->getPreferredLanguage();
		
		$this->load->view('en/user.html',$data);
		//$this->output->cache(1/2);
	}
	
	public function vote($ak,$starttime=0,$endtime=0)
	{	
		$this->load->model('users');
		$data=$this->users->getVoteData($ak);		
		$this->load->view('en/vote.html',$data);
	}

}

