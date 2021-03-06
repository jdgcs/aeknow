<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class V3 extends CI_Controller {
	
	public function debug($type,$function){
		$poststr = $this->security->xss_clean($this->input->raw_input_stream);
		//$request = json_decode($stream_clean);
		//$tx = $request->tx;
		//$tx='{ "tx": "'.$tx.'"}';
		//echo "GOT;$poststr";
		$this->load->model('v2s');
		$response=$this->v2s->debugLink($type,$function,$poststr);
		
		header('Content-Type: application/json');
		echo $response;
		}
		
	public function oracles($oracle_id,$function=""){
		$this->load->model('v2s');
		$data=$this->v2s->getOracle($oracle_id,$function);
		$this->load->view('en/blank.html',$data);
		}
	
	public function tx($ak,$limit=20,$offset=0){
		$this->load->model('v2s');
		$this->v2s->getTx($ak,$limit,$offset);
		}
	
	public function txbh($ak,$startheight=0,$endheight=0){
		$this->load->model('v2s');
		$this->v2s->getTxByHeight($ak,$startheight,$endheight);
		}
			
	public function accounts($ak){
		$this->load->model('v2s');
		$data=$this->v2s->getAccount($ak);
		$this->load->view('en/blank.html',$data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		$this->output->cache(1/4);
		}
	
	public function v2($method,$ak){
		if($method=="transactions"){
				$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
				$request = json_decode($stream_clean);
				$tx = $request->tx;
				$tx='{ "tx": "'.$tx.'"}';
				//echo "post tx:$tx<br>";
				
				$this->load->model('v2s');
				$data=$this->v2s->postTx($tx);
				$this->output->set_header("Access-Control-Allow-Origin: * ");
				//$response = json_encode($data);
				$response = $data;
				header('Content-Type: application/json');
				echo $response;
			}else{
		
				$this->load->model('v2s');
				$data=$this->v2s->getV2($method,$ak);
				$this->load->view('en/blank.html',$data);
				$this->output->set_header("Access-Control-Allow-Origin: * ");
				$this->output->cache(1/4);
		}
		}
	
	public function api(){
		$this->load->model('v2s');
		$data=$this->v2s->getAPI();
		$this->load->view('en/blank.html',$data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		$this->output->cache(1/4);
		}
	
	public function blocks($functions){
		$this->load->model('v2s');
		$data=$this->v2s->getBlocks($functions);
		$this->load->view('en/blank.html',$data);
		$this->output->cache(1/4);
		}
		
	public function names($aens){
		$this->load->model('v2s');
		$data=$this->v2s->getName($aens);
		echo $data;
		}
	
	public function gettx($tx=""){
		$this->load->model('v2s');
		$data=$this->v2s->postTransaction($tx);
		$this->load->view('en/transaction_post.html',$data);
		$this->output->cache(1);
		}
	
	public function transactions(){
		$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
		$request = json_decode($stream_clean);
		$tx = $request->tx;
		$tx='{ "tx": "'.$tx.'"}';
		//echo "post tx:$tx<br>";
		
		$this->load->model('v2s');
		$data=$this->v2s->postTx($tx);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$response = json_encode($data);
		$response = $data;
		header('Content-Type: application/json');
		echo $response;
		}

}

