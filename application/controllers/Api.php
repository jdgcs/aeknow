<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{


	public function index()
	{
		$this->load->model('networks');
		$data['api'] = $this->networks->getNetworkStatus();
		$this->load->view('en/api.html', $data);
		$this->output->cache(2);
	}
	public function wealth500($offset = 0)
	{
		//$this->load->model('tests');
		//$data['info']=$this->tests->wealth500($offset);
		$this->load->model('apis'); //api online
		$data['info'] = $this->apis->wealth500($offset);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		$this->output->cache(30);
	}
	public function tx($ak, $limit = 20, $offset = 0)
	{
		$this->load->model('apis');
		$this->apis->getTx($ak, $limit, $offset);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
	}
	
	public function txs($ak, $limit = 20, $offset = 0)
	{
		$this->load->model('apis'); 
		$this->apis->getTxs($ak, $limit, $offset);
		
		$this->output->set_header("Access-Control-Allow-Origin: * ");
	}
	
	public function spendtx($ak, $limit = 20, $offset = 0)
	{
		$this->load->model('apis');
		$data['info'] =$this->apis->getSpendTx($ak, $limit, $offset);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		
	}

	public function aens($ak)
	{
		$this->load->model('apis');
		//$data['api']=$this->apis->getAENS($ak);
		$data['info'] = $this->apis->getAENS($ak);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function token($ak)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getToken($ak);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function tokens($ak)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getTokens($ak);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function tokentxs($ak,$contract_id,$limit = 20, $offset = 0)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getTokenTxs($ak,$contract_id,$limit, $offset);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}
	
	public function tokentx($txhash)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getTokenTx($txhash);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function tokentop($contract_id, $offset = 0)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getTokenTop($contract_id, $offset);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function mytoken($ak, $contract_id)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getSingleToken($ak, $contract_id);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function tokenlist($ak, $caller)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getTokenTable($ak, $caller);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function contracttx($txhash)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getContractTx($txhash);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function aensbidding($ak)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->getAENSBidding($ak);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		//$this->output->cache(2);
	}

	public function aensquery($aensname)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->queryAENS($aensname);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		$this->output->cache(2);
	}
	
	public function checkquery($aensname)
	{
		$this->load->model('apis');
		$data['info'] = $this->apis->checkAENS($aensname);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
	}
	
	public function account($ak)
	{
		$this->load->model('apis');
		$data = $this->apis->getAccount($ak);
		$this->load->view('en/blank.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		$this->output->cache(2);
	}

	public function totalcoins()
	{
		$this->load->model('apis');
		echo $this->apis->getTotalCoins();
		$this->output->set_header("Access-Control-Allow-Origin: * ");
	}

	public function network()
	{
		$this->load->model('networks');
		$data['api'] = $this->networks->getNetworkStatus();
		$this->load->view('en/api.html', $data);
		$this->output->set_header("Access-Control-Allow-Origin: * ");
		$this->output->cache(2);
	}
}
