<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallets extends CI_Model {
		public function getWalletInfo($ak,$page=1,$type='all',$txtype='SpendTx'){
		//act as v55 with new db structure
		$perpage=50;
		$data['page']=$page;
		$data['activities']="";		
		$data['type']=$type;
		
		$url=DATA_SRC_SITE."v2/accounts/$ak";
		$websrc=$this->getwebsrc($url);
		$data['account']=$ak;
		$data['balance']=0;
		if(strpos($websrc,"balance")==TRUE){
			//$pattern='/{"balance":(.*),"id":"(.*)","nonce":(.*)}/i';
			//preg_match($pattern,$websrc, $match);
			$info=json_decode($websrc);
			//$data['balance']=$match[1]/1000000000000000000;
			$data['balance']=$info->balance/1000000000000000000;
		}
		
		
///////////////////////////////////////get mining
		$this->load->database();
		
		$sql= "select height,data FROM keyblocks WHERE benifit='$ak' AND orphan is NULL order by height desc LIMIT 100";
		$query = $this->db->query($sql);
		$data['blocksmined']=0;
		$data['blocksmined']= $query->num_rows();
		
		$data['totalblocks']="";
		$counter=0;
		$minedtime="";
		$data['totalreward']=0;
		foreach ($query->result() as $row){
			$data['activities']=' <a class="pull-right"> &nbsp; <span class="badge bg-blue">Mining</span></a>'; 
			$counter++;
			$blockheight=$row->height;
			$info=json_decode($row->data);
			$millisecond =$info->time;
			$millisecond=substr($millisecond,0,strlen($millisecond)-3); 
			$minedtime=date("Y-m-d H:i:s",$millisecond);
			$reward=($this->getReward($blockheight+1))*0.891;
			$data['totalreward']=$data['totalreward']+$reward;			
			if($counter<101){				
				$data['totalblocks'].="<tr><td>".$counter."</td><td><a href=/block/height/$blockheight>".$blockheight."</a></td><td>".$reward."</td><td>".$minedtime."</td></tr>";
			}
			}
		/////////////////////////////////////////////get Transactions//////////////////////////////////
		$ok=str_replace("ak_","ok_",$ak);
		
		//$sql="SELECT * FROM txs WHERE tx->'tx' @>'{\"sender_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"recipient_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"account_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"owner_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"caller_id\": \"$ak\"}'::jsonb OR tx->'tx' @>'{\"oracle_id\": \"$ok\"}'::jsonb OR  tx->'tx' @>'{\"initiator_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"responder_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"from_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"to_id\": \"$ak\"}'::jsonb ORDER BY tid desc LIMIT $perpage offset ".($page-1)*$perpage;
		$sql="SELECT * FROM tx WHERE sender_id='$ak' OR recipient_id='$ak' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;

		//$sql= "select * FROM txs WHERE recipient_id='$ak' OR sender_id='$ak' order by block_height desc,nonce desc LIMIT $perpage offset ".($page-1)*$perpage;
		if($type=='in'){
			$sql="SELECT * FROM tx WHERE  recipient_id='$ak' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;
			}
		if($type=='out'){
			$sql="SELECT * FROM tx WHERE sender_id='$ak' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;
			}
		if($type=='contracts'){
			$sql="SELECT * FROM tx WHERE txtype='ContractCallTx' or txtype='ContractCreateTx' ORDER BY utc desc LIMIT $perpage offset ".($page-1)*$perpage;
			}
		$query = $this->db->query($sql);
		$counter=0;
		$data['totaltxs']="";
		foreach ($query->result() as $row){
			$counter++;
			$txhash=$row->txhash;
			$txtype=$row->txtype;
			$utc=$row->utc;
			//$txdata=json_decode($row->tx);
			$amount=$row->amount;
			//$block_hash=$txdata->block_hash;
			$block_height=$row->block_height;
			//$time=$this->getTransactionTime($txdata->block_hash);
			$time=substr($utc,0,10);
			$time=date("Y-m-d H:i:s",$time);			
			
			if($txtype=='SpendTx'||$txtype=='NameTransferTx'||$txtype=='ContractCallTx'){				
				$txhash_show="th_****".substr($txhash,-4);
				if($txtype=='NameTransferTx'){
					$amount="NameTransferTx =>";
					}else{
						if($txtype=='ContractCallTx'){							
							if($amount>0){$txtype="AEX9";}
							$amount=($amount/1000000000000000000)." ".$this->getContractAlias($row->contract_id);							
							}else{
								$amount=($amount/1000000000000000000)." AE";
								}					
				}
				
				
					
				$recipient_id=$row->recipient_id;			
				$recipient_id_show="ak_****".substr($recipient_id,-4);
				$alias=$this->getalias($recipient_id);
				if($recipient_id!=$alias){
					$recipient_id_show=$alias;
					}
							
				$sender_id=$row->sender_id;
				$sender_id_show="ak_****".substr($sender_id,-4);
				$alias=$this->getalias($sender_id);
				if($sender_id!=$alias){
					$sender_id_show=$alias;
					}
				
				if($sender_id==$ak){
						$senderlink="$sender_id_show";
						$recipientlink="<span class='badge bg-yellow'>OUT</span><a href='/address/wallet/$recipient_id'>$recipient_id_show</a>";
					}else{
						$senderlink="<a href='/address/wallet/$sender_id'>$sender_id_show</a>";
						$recipientlink="<span class='badge bg-green'>&nbsp; IN &nbsp; </span>$recipient_id_show";
					}
				//$utctime=round(($row->time/1000),0);
				//$utctime= date("Y-m-d H:i:s",$utctime);		
				
				
				$data['totaltxs'].="<tr><td><a href=/block/transaction/$txhash>$txhash_show</a></td><td>$amount</td><td>$senderlink</td><td>$recipientlink</td><td>$txtype</td><td>$time</td></tr>";
			}else{
				$data['totaltxs'].="<tr><td colspan=\"4\"><a href=/block/transaction/$txhash>$txhash</a></td><td>$txtype</td><td>$time</td></tr>";		
				}
			}
		
		
		//return $data;
		//	}
		//$data['transaction_count']=$query->num_rows();
		
		//$sql="SELECT count(*) FROM txs WHERE tx->'tx' @>'{\"sender_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"recipient_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"account_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"owner_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"caller_id\": \"$ak\"}'::jsonb OR tx->'tx' @>'{\"oracle_id\": \"$ok\"}'::jsonb OR  tx->'tx' @>'{\"initiator_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"responder_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"from_id\": \"$ak\"}'::jsonb OR  tx->'tx' @>'{\"to_id\": \"$ak\"}'::jsonb";
		$sql="SELECT count(*) FROM tx WHERE  sender_id='$ak' OR  recipient_id='$ak'";
		if($type=='in'){
			$sql="SELECT count(*) FROM tx WHERE  recipient_id='$ak'";
			}
			
		if($type=='out'){
			$sql="SELECT count(*) FROM tx WHERE sender_id='$ak'";
			}
		
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['transaction_count']=$row->count; 
		if($data['transaction_count']>0){
			$data['activities'].=' <a class="pull-right">&nbsp; <span class="badge bg-green">Transaction</span></a>'; 
			}
		
		$data['totalpage']=round($data['transaction_count']/$perpage,0)+1;
		
		$data['notes']="From the blockchain, to the blockchain.";
		$alias=$this->getalias($ak);
		if($ak!=$alias){
			$sql="SELECT remark FROM addressinfo WHERE address='$ak'";
			$query = $this->db->query($sql);
			$row = $query->row();
			$data['notes']="<b>$alias:</b> " .$row->remark;
			}
		
		/////////////////////////////////////////////Check Genisis//////////////////////////////////
		$sql= "select count(*) FROM accountsinfo WHERE address='$ak' and remark='genesis'";
		$query = $this->db->query($sql);
		$row = $query->row();		
		if($row->count>0){
			$data['activities'].=' <a class="pull-right"><span class="badge bg-yellow">Genesis</span></a>'; 
			}
		
		$tobecheck=str_replace("ak_","",$ak);
		if(!$this->checkAddress($tobecheck)){
			$data['account']="Invalid address";
			}
		/////////////////////////////////////////////Get Tokens//////////////////////////////////
		$sql="SELECT * FROM token WHERE account='$ak'";
		//echo $sql;
		$query = $this->db->query($sql);
		$counter=0;
		$data['tokens']="";
		foreach ($query->result() as $row){
			$token=$row->alias;
			$decimal=$row->decimal;
			$contract=$row->contract;
			$balance=round($row->balance/pow(10,$decimal),2);
			$data['tokens'].="<b><a href=/contract/detail/$contract target=_blank>$token</a></b>: $balance<br/>";
			}
		
		
		/////////////////////////////////////////////Get AENS names//////////////////////////////////
		$data['aensname']=0;
			
		$sql="SELECT count(distinct(aensname)) FROM txs_aens WHERE nameowner='$ak'";
		$query = $this->db->query($sql);
		$row = $query->row();		
		$data['aensname']=$row->count;
		
		return $data;	
			
			}
		
		
public function getContractAlias($contract_id){//get the owner_id of a contract
		$this->load->database();
		$sql="SELECT alias FROM contracts_token WHERE address='$contract_id'";
		$query = $this->db->query($sql);
		$row = $query->row();	
		return $row->alias;
		}
		
public function getAKbyNameHash($name_id){
	$this->load->database();
	$sql="SELECT nameowner FROM txs_aens WHERE name_id='$name_id' LIMIT 1";
	$query = $this->db->query($sql);
	$row = $query->row();
	return $row->nameowner;
	}
	
public function getTokenBalance($contract,$hexaddress){
	$this->load->database();
	$sql="SELECT decimal FROM contracts_token WHERE address='$contract'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$decimal=$row->decimal;
	
	$sql="SELECT balance FROM tokens WHERE address='$hexaddress' and contract='$contract'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$balance=$row->balance;
	
	return $balance/pow(10,$decimal);
	}


public function getTokenName($contract){
	$this->load->database();
	$name="";
	$sql="SELECT alias FROM contracts_token WHERE address='$contract'";
	$query = $this->db->query($sql);
	$row = $query->row();
	$name=$row->alias;
	
	return $name;
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
	$agent = "User-Agent: AEKnow.org";
	
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

public function checkAddress($address){
		//Thanks Jungle @Beepool    
        $hex = $this->base58_decode($address);
    
        if (strlen($hex)!=72){
            return false;
        }
    
        $bs = pack("H*", substr($hex, 0,64));
    
        $checksum = hash("sha256", hash("sha256", $bs, true));
    
        $checksum = substr($checksum, 0, 8);
    
        if(substr($hex, 64,8)!==$checksum){
            return false;
        }
    
        return true;
    
    }
    
public function base58_decode($base58)
    {
        $origbase58 = $base58;
        $return = "0";
    
        for ($i = 0; $i < strlen($base58); $i++) {
            // return = return*58 + current position of $base58[i]in self::$base58chars
            $return = gmp_add(gmp_mul($return, 58), strpos("123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz", $base58[$i]));
        }
        $return = gmp_strval($return, 16);
        for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == "1"; $i++) {
            $return = "00" . $return;
        }
        if (strlen($return) % 2 != 0) {
            $return = "0" . $return;
        }
        return $return;
    }
}
