<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dev extends CI_Controller {

	public function index()
	{	
		$this->load->view('developer.html');
	}
	
	

}

