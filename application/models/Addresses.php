<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Addresses extends CI_Model {
	
	public function getWealth500(){
		$data['wealth500']="";
		$this->load->database();
		$sql="select * from accountsinfo order by balance desc limit 500";
		$query = $this->db->query($sql);
		$counter=0;
		foreach ($query->result() as $row){
			$counter++;
			$address=$row->address;
			$address_show="ak_****".substr($address,-4);
			$alias=$this->getalias($address);
			$viptag="";
			if($row->remark=="genesis"){
				$viptag='<i class="fa fa-hand-spock-o margin-r-5" title="The Genesis: Live long and prosper"></i>';
				}
			if($alias==$address){
				$address="$viptag <a href=/address/wallet/$address>$address_show</a>";
				}else{
				$address="$viptag <a href=/address/wallet/$address>$address_show($alias)</a>";
				}
			
			$wealth=$row->balance/1000000000000000000;
			$readtime=$row->readtime;
			$readtime=date("Y-m-d H:i:s",$readtime)."(UTC)";
			$data['wealth500'].="<tr><td>$counter</td><td>$address</td><td>$wealth</td><td>$readtime</td></tr>";
			}
			
		$sql="select count(*) from accountsinfo";
		$query = $this->db->query($sql);
		$row = $query->row();
		$data['totaladdress']=$row->count;
		return $data;
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