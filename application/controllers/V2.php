<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class V2 extends CI_Controller {
	
	public function gettx($tx=""){
		$this->load->model('v2s');
		$data=$this->v2s->postTransaction($tx);
		$this->load->view('en/transaction_post.html',$data);
		}
	
	public function transactions(){
		$this->input->post();
		$tx= $this->input->post('tx');
		echo "tx:$tx<br>";
		$tx='{ "tx": "'.$tx.'"}';
		echo "post tx:$tx<br>";
		$this->load->model('v2s');
		$data=$this->v2s->postTx($tx);
		echo $data;
		}

}

