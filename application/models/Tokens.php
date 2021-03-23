<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tokens extends CI_Model {

public function getTokenList(){
	$this->load->database();
	$sql="SELECT * FROM contracts_token WHERE ctype='AEX9' AND cert=true order by alias";
	$query = $this->db->query($sql);
	$data['tokens']="";
	$data['counter']=0;
	foreach ($query->result() as $row){
		$tokenname=$row->alias;
		$remark=$row->remark;
		$supply=$row->supply;
		$holders=$row->holders;
		//$holders="counting";
		$transactions=$row->calltime;
		$lastcall=$row->lastcall;
		$address=$row->address;
		$data['counter']++;
		$tokenname ="<a href=/contract/detail/$address target=_blank>$tokenname</a>";
		$holders="<a href=/token/top/$address target=_blank>$holders</a>";
		
		$data['tokens'] .="<tr><td>$tokenname</td><td>$remark</td><td>$supply</td><td>$holders</td><td>$transactions</td><td>$lastcall</td></tr>";
		
		}
	
	return $data;
	}	

public function getAllTokenTopList($address,$offset){
	$data['tokenaddress']=$address;
	$offset=$offset*500;
		
	$data['wealth500']="";
	$data['totalcoin']=0;
		
		
	$this->load->database();
	$sql="select count(*) from token WHERE contract='$address'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$data['totaladdress']=$row->count;
	$data['page']=$offset/500;
	
	
	$sql="select supply from contracts_token WHERE address='$address'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$data['totalcoin']=$row->supply;
	
	$sql="SELECT * FROM token WHERE contract='$address' order by balance desc limit 500 offset $offset";
	$query = $this->db->query($sql);
	$data['tokens']="";
	$data['counter']=0;
	$counter=0;
	foreach ($query->result() as $row){
		$counter++;
		$data['tokenname']=$row->alias;
		$wealth=$row->balance/pow(10,$row->decimal);
		$address=$row->account;
		$address_show="ak_****".substr($address,-4);
		$alias=$this->getalias($address);
		if($alias==$address){
			$address="<a href=/address/wallet/$address>$address_show</a>";
			}else{
			$address="<a href=/address/wallet/$address>$address_show($alias)</a>";
			}	
			$percentage=round(($wealth/$data['totalcoin'])*100,6);
			$data['wealth500'].="<tr><td>$counter</td><td>$address</td><td>$wealth</td><td>$percentage %</td></tr>";
			//$data['totalcoin']=$data['totalcoin']+$wealth;		
		}
		
	$data['totalpage']=ceil($data['totaladdress']/500)-1;
	return $data;
	}
	
	
public function getAllTokenList(){
	$this->load->database();
	$sql="SELECT * FROM contracts_token WHERE ctype='AEX9' order by alias LIMIT 500";
	$query = $this->db->query($sql);
	$data['tokens']="";
	$data['counter']=0;
	foreach ($query->result() as $row){
		$tokenname=$row->alias;
		$remark=$row->remark;
		$supply=$row->supply;
		$holders=$row->holders;
		//$holders="counting";
		$transactions=$row->calltime;
		$lastcall=$row->lastcall;
		$address=$row->address;
		$data['counter']++;
		$tokenname ="<a href=/contract/detail/$address target=_blank>$tokenname</a>";
		
		$data['tokens'] .="<tr><td>$tokenname</td><td>$remark</td><td>$supply</td><td>$transactions</td><td>$lastcall</td></tr>";
		
		}
	
	return $data;
	}	
	
public function CheckToken($token){
	$this->load->database();
	$sql="SELECT * FROM contracts_token WHERE alias='$token' or address='$token'";
	$query = $this->db->query($sql);
	$list="";
	foreach ($query->result() as $row){
		$contract_id=$row->address;
		$alias=$row->alias;
		$list.="<li><a href=/contract/detail/$contract_id>$alias</a></li>";
		}
	
	return $list;
	}


public function getalias($address){
		$this->load->database();
		$sql="SELECT alias from addressinfo WHERE address='$address' limit 1";
		$query = $this->db->query($sql);
		$row = $query->row();		
		
		if($query->num_rows()>0){
			//echo  $row->alias;
			return $row->alias;
			}
		return $address;
		}
}


