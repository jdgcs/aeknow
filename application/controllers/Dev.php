<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dev extends CI_Controller {

	public function index()
	{	
		
		//get the language of the browser
		$this->load->model('languages');	
		$data['mylang']=$this->languages->getPreferredLanguage();
				
		$this->load->view('en/developer.html',$data);
	}
	
	

}

