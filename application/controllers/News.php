<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News extends CI_Controller {

	
	public function index()
	{			
		$this->load->view('en/news.html');
		//$this->output->cache(1/4);
	}	
	
}

