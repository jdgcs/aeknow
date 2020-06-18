<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tokens extends CI_Model {
	
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


