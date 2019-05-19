<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class V2 extends CI_Controller {
	
	public function names($aens){
		$this->load->model('v2s');
		$data=$this->v2s->getName($aens);
		echo $data;
		}
	
	public function gettx($tx=""){
		$this->load->model('v2s');
		$data=$this->v2s->postTransaction($tx);
		$this->load->view('en/transaction_post.html',$data);
		}
	
	public function transactions(){
		$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
		$request = json_decode($stream_clean);
		$tx = $request->tx;
		$tx='{ "tx": "'.$tx.'"}';
		//echo "post tx:$tx<br>";
		
		$this->load->model('v2s');
		$data=$this->v2s->postTx($tx);
		
		$response = json_encode($data);
		header('Content-Type: application/json');
		echo $response;
		}

}

