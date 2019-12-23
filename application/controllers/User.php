<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function index()
	{	
		//get data to be shown from models
		$this->load->model('users');
		$data=$this->users->getUserData();		
		
		//get the language of the browser
		$this->load->model('languages');	
		//$data['mylang']=$this->languages->getPreferredLanguage();
		$data['mylang']="en";
		$this->load->view('en/user.html',$data);
		$this->output->cache(2);
	}
	
	public function vote($ak,$starttime=0,$endtime=0)
	{	
		$this->load->model('users');
		$data=$this->users->getVoteData($ak);		
		$this->load->view('en/vote.html',$data);
		$this->output->cache(2);
	}

}

