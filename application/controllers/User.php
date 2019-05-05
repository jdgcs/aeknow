<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function index()
	{	
		//default user page
		$this->load->model('users');
		$data=$this->users->getUserData();
		
		$this->load->view('en/user.html',$data);
	}
	

}

