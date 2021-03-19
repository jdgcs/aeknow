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
		$holders="counting";
		$transactions=$row->calltime;
		$lastcall=$row->lastcall;
		$address=$row->address;
		$data['counter']++;
		$tokenname ="<a href=/contract/detail/$address target=_blank>$tokenname</a>";
		
		$data['tokens'] .="<tr><td>$tokenname</td><td>$remark</td><td>$supply</td><td>$holders</td><td>$transactions</td><td>$lastcall</td></tr>";
		
		}
	
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
		$holders="counting";
		$transactions=$row->calltime;
		$lastcall=$row->lastcall;
		$address=$row->address;
		$data['counter']++;
		$tokenname ="<a href=/contract/detail/$address target=_blank>$tokenname</a>";
		
		$data['tokens'] .="<tr><td>$tokenname</td><td>$remark</td><td>$supply</td><td>$holders</td><td>$transactions</td><td>$lastcall</td></tr>";
		
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

}


