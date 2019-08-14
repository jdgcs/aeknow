<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Oracle extends CI_Controller {
	public function index(){	
		//need to be re-demonstration
		$this->load->model('oracles');	
		$data=$this->oracles->getOracleList();
		$this->load->view('en/oracles_index.html',$data);
		$this->output->cache(5);
	}
		
	public function id($oracle_id=""){	
		//show the deitail of oracle_id with ok_
		$this->load->model('oracles');	
		$data=$this->oracles->getOracleDetail($oracle_id);
		$this->load->view('en/oracles_detail.html',$data);
		$this->output->cache(1);
	}
	
	public function show($ak){
		//show all the oracles of the address ak_
		}
	
	public function prediction($txhash){
		
		//show the dynamic details of a single onchain prediction
		//$this->load->model('oracles');	
		//$data=$this->oracles->getPredictionDetail($txhash);
		
		//$this->load->model('languages');	
		//$data['mylang']=$this->languages->getPreferredLanguage();
		
		//$this->load->view('en/oracles_prediction.html',$data);
		echo "under building";
		}
	
	public function market($txhash){
		
		//show the dynamic details of a single onchain prediction
		$this->load->model('oracles');	
		$data=$this->oracles->getPredictionDetail($txhash);
		
		$this->load->model('languages');	
		$data['mylang']=$this->languages->getPreferredLanguage();
		
		$this->load->view('en/oracles_prediction.html',$data);
		}
		
	
	public function finish($txhash,$option){
		//ready to finish the prediction market
		
		
		}
	
}

