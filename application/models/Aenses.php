<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aenses extends CI_Model {

		public function query($aename){
			$data['status']="";
			$data['aename']=$aename;
			$url=DATA_SRC_SITE.'v2/names/'.$aename;
			$websrc=$this->getwebsrc($url);
			if(strpos($websrc,"Name not found")>0){
					$data['status']= "available";
					$this->load->database();
					$sql="SELECT * FROM regaens WHERE aename='$aename'";
					$query = $this->db->query($sql);
					if($query->num_rows()>0){
						$data['status']= '<div class="alert alert-warning alert-dismissible" style="overflow:auto;">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<h4><i class="icon fa fa-warning"></i> '.$aename.' has been registered by others.</h4>
						  </div>';
						}
				}else{
					$data['status']= '<div class="alert alert-warning alert-dismissible" style="overflow:auto;">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<h4><i class="icon fa fa-warning"></i> '.$aename.' has been registered.</h4>
					'.$websrc.'
				  </div>';
				}
			return $data;
		}
		
		public function savetodb($aename,$akaddress){
			$this->load->database();
			$data['status']="";
			$data['aename']=trim($aename);
			$reglimit=500;
			
			$tagtime=time()-24*3600;
			$sql="SELECT count(*) FROM regaens WHERE akaddress='$akaddress' and updatetime>$tagtime";
			$query = $this->db->query($sql);
			$row = $query->row();		
			if($row->count>$reglimit){
				$data['status']='<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> '.$akaddress.' has registered more than '.$reglimit.' AENS names in the last 24 hours.</h4>
              </div>';
				return $data;
				} 
			
			$checkstr=substr($aename,0,strlen($aename)-5);
			$regex = '/^[a-z0-9]+$/i';
			if(preg_match($regex,$checkstr)){					
			}else{
				$data['status']='<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Invalid aename:'.$aename.'</h4>
              </div>';
				return $data;
				}
			if(strpos($akaddress,"k_")<1 || strlen($akaddress)<30){			
				$data['status']='<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Invalid ak_address:'.$akaddress.'</h4>
              </div>';
				return $data;
				}
			$sql="SELECT * from regaens WHERE aename='$aename'";
			$query = $this->db->query($sql);
			if($query->num_rows()==0){
				$sql_insert="INSERT INTO regaens(aename,akaddress,claimer,regpath) VALUES('$aename','$akaddress','ak_pANDBzM259a9UgZFeiCJyWjXSeRhqrBQ6UCBBeXfbCQyP33Tf','')";
				$query = $this->db->query($sql_insert);
				//$data['status']= "$aename has been recorded for registering, it would be resgisterd in 2~3 blocks.";
				$data['status']= '<div class="alert alert-success alert-dismissible" style="overflow:auto;">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<h4><i class="icon fa fa-check"></i> '.$aename.' has been recorded for registering, it would be registerd in 2~3 blocks.</h4>
						  </div>';
				}else{
				$data['status']= "$aename is waiting to be registered in database.";	
				}
				
			
			return $data;
			
			}
		
public function getNames($akaddress){
		$this->load->database();
		$data['status']="";
		$sql="SELECT aename WHERE akaddress='$adaddress' AND pointer is not NULL";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row){
			$aename=$row->aename;
			//$akaddress=$row->akaddress;
			$data['status'].='<li><a href="/aens/query/'.$aename.'">'.$aename.'</a></li>';			
		}
		
		return $data;
	}
		
				
	private function getwebsrc($url) {
	$curl = curl_init ();
	$agent = "User-Agent: AEKnow-bot";
	
	curl_setopt ( $curl, CURLOPT_URL, $url );

	curl_setopt ( $curl, CURLOPT_USERAGENT, $agent );
	curl_setopt ( $curl, CURLOPT_ENCODING, 'gzip,deflate' );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); //×¥È¡301Ìø×ªºóÍøÖ·
	curl_setopt ( $curl, CURLOPT_AUTOREFERER, true );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $curl, CURLOPT_TIMEOUT, 60 );
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	
	$html = curl_exec ( $curl ); // execute the curl command
	$response_code = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
	if ($response_code != '200') { //Èç¹ûÎ´ÄÜ»ñÈ¡¸ÃÒ³Ãæ£¨·Ç200·µ»Ø£©£¬ÔòÖØÐÂ³¢ÊÔ»ñÈ¡
	//	echo 'Page error: ' . $response_code . $html;	
		$html='Page error: ' . $response_code.$html;
	} 
	curl_close ( $curl ); // close the connection

	return $html; // and finally, return $html
}
}
