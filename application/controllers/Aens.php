<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	public function index()
	{	echo "aens";
	}
	
	function query($aename){
		$this->load->model('aenses');
		if($this->aenses->query($aename)=="available"){
			$data['aename']=$aename;
			$this->load->view('reg.html',$data);
		}
	}

}

