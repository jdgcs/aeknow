<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stat extends CI_Model {

		public function getHashrate(){
			$data['title']="Aeternity Mining Hashrate";		
			$data['tabledata']='{"period": "2018-12-28 17:55:47", "hashrate": 3186, "hashrate_f2": 1046}';
			//$data['tabledata_f2']='{"period_f2": "2018-12-28 17:55:47", "hashrate_f2": 1046}';
			
			$this->load->database();
			$nowtime=time();
			$step=round(($nowtime-1545990947)/100,0);
			
			for($i=0;$i<100;$i++){
				$sql="SELECT hashrate,updatetime from pools WHERE poolname='beepool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				//$data['tabledata'].=',{"period": "'.$updatetime.'", "hashrate":'.$hashrate.'}';
				
				$sql="SELECT hashrate,updatetime from pools WHERE poolname='f2pool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate_f2= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				//{ y: '2011 Q3', item1: 4912, item2: 1969 },
				$data['tabledata'].=',{"period": "'.$updatetime.'", "hashrate":'.$hashrate.', "hashrate_f2":'.$hashrate_f2.'}';
				
				}
			
			return $data;
		}
		
	}
