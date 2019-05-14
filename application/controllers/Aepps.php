<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aepps extends CI_Controller {
	public function index()
	{	
		$data['status']="";
		$data['aename']="";
		$this->load->view('en/aepps.html',$data);
	} 

}

