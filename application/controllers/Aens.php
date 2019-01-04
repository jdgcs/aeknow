<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	public function index()
	{	
		$this->load->view('aens.html');
	}
	
	function query($aename){
		$this->load->model('aenses');
		$this->load->library('form_validation');
		 
		$data=$this->aenses->query($aename);
		if($data['status']=="available"){			
			$this->load->view('reg.html',$data);
		}else{
			echo $data['status'];
			}
	}

}

