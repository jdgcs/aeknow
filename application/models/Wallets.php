<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallets extends CI_Model {

		public function getWalletInfo($ak,$page=1){
		$perpage=50;
		$data['activities']="";
		$data['page']=$page;
		$url=DATA_SRC_SITE."v2/accounts/$ak";
		$websrc=$this->getwebsrc($url);
		$data['account']=$ak;
		$data['balance']=0;
		if(strpos($websrc,"balance")==TRUE){
			$pattern='/{"balance":(.*),"id":"(.*)","nonce":(.*)}/i';
			preg_match($pattern,$websrc, $match);
			$data['balance']=$match[1]/1000000000000000000;
		}
		
		

		$this->load->database();
		$sql= "select height,time FROM miner WHERE beneficiary='$ak' AND orphan is FALSE order by hid desc";
		$query = $this->db->query($sql);
		$data['blocksmined']=0;
		$data['blocksmined']= $query->num_rows();
		
		$data['totalblocks']="";
		$counter=0;
		$minedtime="";
		$data['totalreward']=0;
		foreach ($query->result() as $row){
			$counter++;
			$blockheight=$row->height;
			$millisecond =$row->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$minedtime=date("Y-m-d H:i:s",$millisecond);
			$reward=$this->getReward($blockheight+1);
			$data['totalreward']=$data['totalreward']+$reward;
			if($counter<101){
				$data['totalblocks'].="<tr><td>".$counter."</td><td><a href=/block/height/$blockheight>".$blockheight."</a></td><td>".$reward."</td><td>".$minedtime."</td></tr>";
			}
			}
		/////////////////////////////////////////////get Transactions//////////////////////////////////
		$sql= "select block_height,block_hash,hash,amount,recipient_id, sender_id FROM transactions WHERE recipient_id='$ak' OR sender_id='$ak' order by block_height desc,nonce desc LIMIT $perpage offset ".($page-1)*$perpage;
		$query = $this->db->query($sql);
		$counter=0;
		$data['totaltxs']="";
		foreach ($query->result() as $row){
			$counter++;
			if($counter<101){
				$block_height=$row->block_height;
				$block_hash=$row->block_hash;
				$block_hash_show="mh_****".substr($block_hash,-4);
				$hash=$row->hash;
				$hash_show="th_****".substr($hash,-4);
				//$amount=number_format($row->amount/1000000000000000000, 18, '.', '');
				$amount=$row->amount/1000000000000000000;
				$recipient_id=$row->recipient_id;
				$recipient_id_show="ak_****".substr($recipient_id,-4);
				$sender_id=$row->sender_id;
				$sender_id_show="ak_****".substr($sender_id,-4);
				
				$sql_gettime="SELECT time FROM microblock WHERE hash='$block_hash'";
				$query_time = $this->db->query($sql_gettime);
				$row = $query_time->row();
				if($query->num_rows()>0){
					$millisecond =$row->time;
					$realtime=$millisecond;
					$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
					$txstime=$realtime."(".date("Y-m-d H:i:s",$millisecond)." UTC)";
					
					if($sender_id==$ak){
						$senderlink="$sender_id_show";
						$recipientlink="<span class='badge bg-yellow'>OUT</span><a href='/address/wallet/$recipient_id'>$recipient_id_show</a>";
					}else{
						$senderlink="<a href='/address/wallet/$sender_id'>$sender_id_show</a>";
						$recipientlink="<span class='badge bg-green'>&nbsp; IN &nbsp; </span>$recipient_id_show";
					}
				}
				
			
				$data['totaltxs'].="
				<td><a href=/block/transaction/$hash>$hash_show</a></td>
				<td>$amount</td>
				<td>$senderlink</td>
				<td>$recipientlink</td>
					
				<td>$txstime</td>	
				</tr>";
			}
			}
		$sql= "select count(*) FROM transactions WHERE recipient_id='$ak' OR sender_id='$ak'";
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['transaction_count']=$row->count; 
		
		$data['totalpage']=round($data['transaction_count']/$perpage,0);
		
		$data['notes']="From the blockchain, to the blockchain.";
		$alias=$this->getalias($ak);
		if($ak!=$alias){
			$sql="SELECT remark FROM addressinfo WHERE address='$ak'";
			$query = $this->db->query($sql);
			$row = $query->row();
			$data['notes']="<b>$alias:</b> " .$row->remark;
			}
		return $data;
		
		}
	
	
	private function getReward($blockheight){
		$this->load->database();
		$sql="SELECT reward from aeinflation WHERE blockid<$blockheight ORDER BY blockid desc LIMIT 1";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->reward/10;
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
		
	private function getwebsrc($url) {
	$curl = curl_init ();
	$agent = "User-Agent: AE-testbot";
	
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
