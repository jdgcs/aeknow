<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aens extends CI_Controller {
	public function index()
	{	echo "aens";
	}
	
	function query($aename){
		$this->load->model('aenses');
		echo $this->aenses->query($aename);
		//$this->load->view('mblocks.html',$data);
		}

}

