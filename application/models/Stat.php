<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stat extends CI_Model {

		public function getHashrate(){
			$data['title']="Aeternity Mining Hashrate";		
			$data['tabledata']='{"period": "2018-12-28 17:55:47", "hashrate":4319.245}';
			$data['tabledata_bee']='{"period": "2018-12-28 17:55:47", "hashrate":3186}';
			$data['tabledata_f2']='{"period": "2018-12-28 17:55:47", "hashrate":1046}';			
			$data['tabledata_uu']='{"period": "2018-12-28 17:55:47", "hashrate":87.245}';
			
			$this->load->database();
			$nowtime=time();
			$step=round(($nowtime-1545990947)/100,0);
			
			for($i=0;$i<100;$i++){
				$sql="SELECT hashrate,updatetime from pools WHERE poolname='beepool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate_bee= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				$data['tabledata_bee'].=',{"period": "'.$updatetime.'", "hashrate":'.$hashrate_bee.'}';
				
				$sql="SELECT hashrate,updatetime from pools WHERE poolname='f2pool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate_f2= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				$data['tabledata_f2'].=',{"period": "'.$updatetime.'","hashrate":'.$hashrate_f2.'}';
				
				$sql="SELECT hashrate,updatetime from pools WHERE poolname='uupool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate_uu= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				$data['tabledata_uu'].=',{"period": "'.$updatetime.'","hashrate":'.$hashrate_uu.'}';
				
				$hashrate=$hashrate_bee+$hashrate_f2+$hashrate_uu;
				$data['tabledata'].=',{"period": "'.$updatetime.'","hashrate":'.$hashrate.'}';
				
				//$data['tabledata'].=',{"period": "'.$updatetime.'","hashrate_bee":'.$hashrate_bee.',"hashrate_f2":'.$hashrate_f2.',"hashrate_uu":'.$hashrate_uu.'}';
				}
			
			return $data;
		}
		
	}
