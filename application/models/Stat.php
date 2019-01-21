<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stat extends CI_Model {

		public function getHashrate(){
			$data['title']="Aeternity Mining Hashrate";		
			$data['tabledata']='{"period": "2018-12-28 17:55:47", "hashrate": 16608}';
			$this->load->database();
			$nowtime=time();
			$step=round(($nowtime-1545990947)/100,0);
			
			for($i=0;$i<100;$i++){
				$sql="SELECT hashrate,updatetime from pool WHERE poolname='beepool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				$data['tabledata'].=',{"period": "'.$updatetime.'", "difficulty":'.$hashrate.'}';
				}
			
			return $data;
		}
		
	}
