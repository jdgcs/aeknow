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
				}else{
				$data['status']= "$aename has been registered:<br />".$websrc."<br />";
				}
			return $data;
		}
		
		public function savetodb($aename,$akaddress){
			$this->load->database();
			$data['status']="";
			$sql="SELECT * from regaens WHERE aename='$aenam' AND akaddress='$akaddress'";
			$query = $this->db->query($sql);
			if($query->num_rows()==0){
				$sql_insert="INSERT INTO regaens(aename,akaddress,claimer,regpath) VALUES('$aename','$akaddress','','pub')";
				$query = $this->db->query($sql_insert);
				$data['status']= "$aename has been recorded for registering, it will be resgisterd in 2~3 blocks.";
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
