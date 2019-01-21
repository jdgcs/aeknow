<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stat extends CI_Model {

		public function getHashrate(){
			$data['title']="Aeternity Mining Hashrate";		
			$data['tabledata']='{"period": "2018-12-28 17:55:47", "hashrate":4319.245}';
			$data['tabledata_bee']='{"period": "2018-12-28 17:55:47", "hashrate:3186}';
			$data['tabledata_f2']='{"period_f2": "2018-12-28 17:55:47", "hashrate_f2":1046}';			
			$data['tabledata_uu']='{"period_uu": "2018-12-28 17:55:47", "hashrate_f2":1046}';
			
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
				$data['tabledata_f2'].=',{"period_f2": "'.$updatetime.'","hashrate_f2":'.$hashrate_f2.'}';
				
				$sql="SELECT hashrate,updatetime from pools WHERE poolname='uupool' AND updatetime > ".($nowtime-(100-$i)*$step) ." ORDER BY pid ASC LIMIT 1";
				$query = $this->db->query($sql);
				$row = $query->row();
				$hashrate_uu= $row->hashrate;
				$updatetime=date("Y-m-d H:i:s",$row->updatetime);
				$data['tabledata_f2'].=',{"period_uu": "'.$updatetime.'","hashrate_uu":'.$hashrate_uu.'}';
				
				$hashrate=$hashrate_bee+$hashrate_f2+$hashrate_uu;
				$data['tabledata'].=',{"period": "'.$updatetime.'","hashrate":'.$hashrate.'}';
				
				//$data['tabledata'].=',{"period": "'.$updatetime.'","hashrate_bee":'.$hashrate_bee.',"hashrate_f2":'.$hashrate_f2.',"hashrate_uu":'.$hashrate_uu.'}';
				}
			$data['tabledata_bee']='{"period": "2018-12-28 17:55:47", "hashrate":4319.245},{"period": "2018-12-28 09:55:47","hashrate":4319.245},{"period": "2018-12-28 15:38:14","hashrate":4480.889},{"period": "2018-12-28 21:22:49","hashrate":4405.636},{"period": "2018-12-29 03:07:16","hashrate":4243.497},{"period": "2018-12-29 08:48:33","hashrate":4031.628},{"period": "2018-12-29 14:32:01","hashrate":4262.588},{"period": "2018-12-29 20:16:44","hashrate":4328.193},{"period": "2018-12-30 01:56:01","hashrate":4299.956},{"period": "2018-12-30 07:40:36","hashrate":4228.257},{"period": "2018-12-30 13:26:55","hashrate":4340.091},{"period": "2018-12-30 19:07:05","hashrate":4339.207},{"period": "2018-12-31 00:51:42","hashrate":4329.347},{"period": "2018-12-31 06:31:44","hashrate":4236.805},{"period": "2018-12-31 12:17:53","hashrate":4289.144},{"period": "2018-12-31 18:00:29","hashrate":4388.943},{"period": "2018-12-31 23:39:57","hashrate":4343.32},{"period": "2019-01-01 05:24:43","hashrate":4422.207},{"period": "2019-01-01 11:10:39","hashrate":4653.484},{"period": "2019-01-01 16:49:15","hashrate":4611.724},{"period": "2019-01-01 22:32:35","hashrate":4620.373},{"period": "2019-01-02 04:15:10","hashrate":4538.183},{"period": "2019-01-02 10:00:21","hashrate":4454.359},{"period": "2019-01-02 15:40:46","hashrate":4499.132},{"period": "2019-01-02 21:26:13","hashrate":4486.368},{"period": "2019-01-03 03:10:53","hashrate":4539.22},{"period": "2019-01-03 08:52:21","hashrate":4529.859},{"period": "2019-01-03 14:37:38","hashrate":4297.739},{"period": "2019-01-03 20:17:34","hashrate":3380.848},{"period": "2019-01-04 02:02:39","hashrate":3393.356},{"period": "2019-01-04 07:43:43","hashrate":3397.615},{"period": "2019-01-04 13:25:31","hashrate":3350.689},{"period": "2019-01-04 19:07:59","hashrate":3305.249},{"period": "2019-01-05 00:52:19","hashrate":3237.585},{"period": "2019-01-05 06:37:26","hashrate":3242.564},{"period": "2019-01-05 12:18:52","hashrate":3388.184},{"period": "2019-01-05 18:03:37","hashrate":3504.817},{"period": "2019-01-05 23:44:21","hashrate":3580.691},{"period": "2019-01-06 05:26:28","hashrate":3553.844},{"period": "2019-01-06 11:10:03","hashrate":3681.769},{"period": "2019-01-06 16:51:55","hashrate":3861.529},{"period": "2019-01-06 22:38:20","hashrate":3864.944},{"period": "2019-01-07 04:19:07","hashrate":3870.679},{"period": "2019-01-07 10:00:44","hashrate":3643.712},{"period": "2019-01-07 15:54:09","hashrate":3749.023},{"period": "2019-01-07 21:28:09","hashrate":3778.101},{"period": "2019-01-08 03:13:19","hashrate":3703.997},{"period": "2019-01-08 08:54:55","hashrate":3562.355},{"period": "2019-01-08 14:35:18","hashrate":3534.587},{"period": "2019-01-08 20:19:19","hashrate":3479.935},{"period": "2019-01-09 02:03:31","hashrate":3441.42},{"period": "2019-01-09 07:44:23","hashrate":3569.768},{"period": "2019-01-09 13:29:02","hashrate":3837.948},{"period": "2019-01-09 19:11:13","hashrate":3802.8},{"period": "2019-01-10 00:56:07","hashrate":3691.448},{"period": "2019-01-10 06:36:19","hashrate":3598.347},{"period": "2019-01-10 12:23:48","hashrate":3834.304},{"period": "2019-01-10 18:02:34","hashrate":4020.257},{"period": "2019-01-10 23:48:41","hashrate":3952.975},{"period": "2019-01-11 13:08:03","hashrate":2715.488},{"period": "2019-01-11 13:08:03","hashrate":2715.488},{"period": "2019-01-11 16:55:57","hashrate":3717.283},{"period": "2019-01-11 22:40:43","hashrate":3715.96},{"period": "2019-01-12 04:21:06","hashrate":3661.587},{"period": "2019-01-12 10:06:24","hashrate":3562.796},{"period": "2019-01-12 15:48:55","hashrate":3604.347},{"period": "2019-01-12 21:29:17","hashrate":3737.997},{"period": "2019-01-13 03:16:00","hashrate":3776.496},{"period": "2019-01-13 08:56:58","hashrate":3898.369},{"period": "2019-01-13 14:39:16","hashrate":4046.795},{"period": "2019-01-13 20:25:00","hashrate":4129.548},{"period": "2019-01-14 02:05:07","hashrate":4155.32},{"period": "2019-01-14 07:50:25","hashrate":4321.257},{"period": "2019-01-14 13:32:48","hashrate":4447.148},{"period": "2019-01-14 19:15:50","hashrate":4542.895},{"period": "2019-01-15 00:56:04","hashrate":4389.692},{"period": "2019-01-15 06:42:39","hashrate":4381.86},{"period": "2019-01-15 12:22:38","hashrate":4325.157},{"period": "2019-01-15 18:06:13","hashrate":3561.423},{"period": "2019-01-15 23:51:04","hashrate":3349.68},{"period": "2019-01-16 05:32:37","hashrate":3397.051},{"period": "2019-01-16 11:16:04","hashrate":3658.977},{"period": "2019-01-16 16:57:46","hashrate":4067.636},{"period": "2019-01-16 22:40:35","hashrate":4036.385},{"period": "2019-01-17 04:25:15","hashrate":4191.145},{"period": "2019-01-17 10:07:16","hashrate":4202.835},{"period": "2019-01-17 15:50:08","hashrate":3837.541},{"period": "2019-01-17 21:31:45","hashrate":3682.416},{"period": "2019-01-18 03:18:03","hashrate":3662.033},{"period": "2019-01-18 09:01:06","hashrate":3497.675},{"period": "2019-01-18 14:44:14","hashrate":3489.971},{"period": "2019-01-18 20:26:09","hashrate":3424.583},{"period": "2019-01-19 02:11:23","hashrate":3370.861},{"period": "2019-01-19 07:54:27","hashrate":3382.735},{"period": "2019-01-19 13:37:12","hashrate":3466.735},{"period": "2019-01-19 19:20:24","hashrate":3722.2},{"period": "2019-01-20 01:02:15","hashrate":3778.213},{"period": "2019-01-20 06:43:15","hashrate":4156.596},{"period": "2019-01-20 12:28:20","hashrate":4685.925},{"period": "2019-01-20 18:10:49","hashrate":4867.357},{"period": "2019-01-20 23:50:38","hashrate":4898.325} ';
			return $data;
		}
		
	}
