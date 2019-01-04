<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	public function index()
	{	echo "aens";
	}
	
	function query($aename){
		$this->load->model('aenses');
		$data=$this->aenses->query($aename);
		if($data['status']=="available"){			
			$this->load->view('reg.html',$data);
		}else{
			echo $data['status'];
			}
	}

}

