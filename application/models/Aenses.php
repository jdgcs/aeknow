<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aenses extends CI_Model {

		
		public function showAENSexpiring(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$target_height=$topheight+20*480;//<20 days
			$data['topheight']=$topheight;
		
						
			//$sql="select tx FROM txs_aens where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is not NULL group by recipient_id) order by block_height desc;";
			//$sql="select tx FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is not NULL group by aensname) order by block_height desc;";
			//$sql="select distinct(aensname),expire_height,nameowner from txs_aens WHERE expire_height is not null AND expire_height<$target_height order by expire_height;";
			$sql="select distinct(aensname),expire_height,nameowner from txs_aens WHERE expire_height is not null order by expire_height;";
			$query = $this->db->query($sql);
			$data['expiring']="";
			$data['expired']="";
			
			foreach ($query->result() as $row){
				$aensname=$row->aensname;
				$biddetails="<a href=/aens/viewbids/$aensname>Bid detail</a>";
				
				$aensname="<a href=/$aensname>$aensname</a>";
				$expire_height=$row->expire_height;
				$nameowner=$row->nameowner;
				$account_id_show="ak_****".substr($nameowner,-4);
				$nameowner="<a href=/address/wallet/$nameowner>$account_id_show</a>";
				
				$est_days=round(($expire_height-$topheight)/480,2);
				if($expire_height>$topheight){
				$data['expiring'].="<tr><td>$expire_height(~ $est_days days)</td><td>$aensname</td><td>$nameowner</td><td>$biddetails</td></tr>\n";
			}else{
				$data['expired'].="<tr><td>$expire_height</td><td>$aensname</td><td>$nameowner</td><td>$biddetails</td></tr>\n";
				}

			
			}
						
			
			return $data;
			}
		
		
		public function showAENSbyBlock_New(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['topheight']=$topheight;
		
						
			$sql="select tx FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NULL group by aensname) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			
			for($i=0;$i<100000;$i++){
				$mytable[$i]="";
				}
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				$bidtimes=$this->getBidCount($name);
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				//$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
				
				
				if($length<13){
					if($leftheight<1){$leftheight=1;}
					$est_days=round(($leftheight)/480,2);
					$mytable[$leftheight].="<tr><td>$leftheight(~ $est_days days)</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
				}
			
			}
			$table=ksort($mytable);
			//print_r($mytable);
			
			foreach ($mytable as $col) {
				$data['inauction'].=$col;
			}
			
			
			
			
			$sql="select tx FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NOT NULL group by aensname) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			$data['registered_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['registered_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$bidtimes=$this->getBidCount($name);
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				$data['latest100'].="<tr><td>$aename</td><td>$length</td><td>$name_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$height</td></tr>\n";
			}
			
			return $data;
			}
			
		
		public function showAENSbyBlock(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['topheight']=$topheight;
		
						
			$sql="select tx FROM txs where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NULL group by recipient_id) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			
			for($i=0;$i<100000;$i++){
				$mytable[$i]="";
				}
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				$bidtimes=$this->getBidCount($name);
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				//$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
				
				
				if($length<13){
					if($leftheight<1){$leftheight=1;}
					$mytable[$leftheight].="<tr><td>$leftheight</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
				}
			
			}
			$table=ksort($mytable);
			//print_r($mytable);
			
			foreach ($mytable as $col) {
				$data['inauction'].=$col;
			}
			
			
			
			
			$sql="select tx FROM txs where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NOT NULL group by recipient_id) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			$data['registered_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['registered_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$bidtimes=$this->getBidCount($name);
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				$data['latest100'].="<tr><td>$aename</td><td>$length</td><td>$name_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$height</td></tr>\n";
			}
			
			return $data;
			}
			
		public function showAENSFinished_New(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['topheight']=$topheight;
		
						
			$sql="select tx FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NULL group by aensname) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				$bidtimes=$this->getBidCount($name);
				
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
					
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				//$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
			}
			
			
			$sql="select tx,pointers FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NOT NULL group by aensname) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			$data['registered_count']=0;
			
			for($i=0;$i<60000;$i++){
				$mytable[$i]="";
				}
				
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$pointer=$row->pointer;
				$data['registered_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$bidtimes=$this->getBidCount($name);
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				
				$info_pointer=json_decode($pointer);
				
				$expired=$info_pointer->ttl;				
				//$expired=$expired+$height;				
				$leftheight=$expired-$topheight;
				if($leftheight<1){$leftheight=1;}
				
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
				
				$mytable[$leftheight].="<tr><td>$height</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$leftheight</td></tr>\n";
			}
			
			$table=ksort($mytable);
			//print_r($mytable);
			
			foreach ($mytable as $col) {
				$data['latest100'].=$col;
			}
			
					
			return $data;
			}
			
		public function showAENSFinished(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['topheight']=$topheight;
		
						
			$sql="select tx FROM txs where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NULL group by recipient_id) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				$bidtimes=$this->getBidCount($name);
				
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
					
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				//$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
			}
			
			
			$sql="select tx,pointer FROM txs where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NOT NULL group by recipient_id) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			$data['registered_count']=0;
			
			for($i=0;$i<60000;$i++){
				$mytable[$i]="";
				}
				
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$pointer=$row->pointer;
				$data['registered_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$bidtimes=$this->getBidCount($name);
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				
				$info_pointer=json_decode($pointer);
				
				$expired=$info_pointer->ttl;				
				//$expired=$expired+$height;				
				$leftheight=$expired-$topheight;
				if($leftheight<1){$leftheight=1;}
				
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
				
				$mytable[$leftheight].="<tr><td>$height</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$leftheight</td></tr>\n";
			}
			
			$table=ksort($mytable);
			//print_r($mytable);
			
			foreach ($mytable as $col) {
				$data['latest100'].=$col;
			}
			
					
			return $data;
			}
		
		
		public function showAENS_New(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['topheight']=$topheight;
		
						
			$sql="select tx FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NULL group by aensname) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				$bidtimes=$this->getBidCount($name);
				
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
					
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
			}
			
			
			$sql="select tx FROM txs_aens where(aensname,block_height) in(SELECT aensname,max(block_height) from txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NOT NULL group by aensname) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			$data['registered_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['registered_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$bidtimes=$this->getBidCount($name);
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				$data['latest100'].="<tr><td>$aename</td><td>$length</td><td>$name_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$height</td></tr>\n";
			}
			
			return $data;
			}
		
		public function showAENS(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['topheight']=$topheight;
		
						
			$sql="select tx FROM txs where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NULL group by recipient_id) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				$bidtimes=$this->getBidCount($name);
				
				$higher="";
				if($bidtimes>1){
					$higherrate=round((($name_fee-$init_fee)/$init_fee)*100,2);
					$higher.="(<font color=red>+$higherrate%</font>)";
					}
					
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee$higher</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$expired(~$est)</td></tr>\n";
			}
			
			
			$sql="select tx FROM txs where(recipient_id,block_height) in(SELECT recipient_id,max(block_height) from txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NOT NULL group by recipient_id) order by block_height desc;";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			$data['registered_count']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$data['registered_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$bidtimes=$this->getBidCount($name);
				$bidtimes="<a href=/aens/viewbids/$name target=_blank>$bidtimes</a>";
				$data['latest100'].="<tr><td>$aename</td><td>$length</td><td>$name_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$bidtimes</td><td>$height</td></tr>\n";
			}
			
			return $data;
			}
		
		
		public function showBids_New($name){
			$name=strtolower($name);
			$data['name']=$name;
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$sql="SELECT name_id from txs_aens WHERE aensname='$name' order by block_height desc LIMIT 1 ";
			$query = $this->db->query($sql);
			$row = $query->row();
			$name_id= $row->name_id;
		
						
			$sql="select tx,txtype FROM txs_aens where aensname='$name' or name_id='$name_id' order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			$bidtimes=$this->getBidCount($name);
			foreach ($query->result() as $row){
				$txtype=$row->txtype;
				$tx=$row->tx;
				$info=json_decode($tx);
				$txhash=$info->hash;
				$height=$info->block_height;
				
				if($txtype=='NameClaimTx'){
					$mybid=$bidtimes-$data['inauction_count'];					
					$data['inauction_count']++;					
					$name=$info->tx->name;				
					
					$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
					$account_id=$info->tx->account_id;
					$account_id_show="ak_****".substr($account_id,-4);
					$name_fee=$info->tx->name_fee/1000000000000000000;
					$data['burning']=$data['burning']+$name_fee;
					$init_fee=$this->calcFee($name);
					$length=strlen($name)-6;
					$expired=$this->calcExpired($name);				
					$expired=$expired+$height;
					
					$leftheight=$expired-$topheight;
					$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
					
					$passedheight=$topheight-$height;
					
					
					$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$name_fee</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$mybid</td><td>$expired(~$est)</td></tr>\n";
				}else{
					if($txtype=='NameTransferTx'){
						$sender_id=$info->tx->account_id;
						$recipient_id=$info->tx->recipient_id;
						
						$sender_id_show="From: <a href=/address/wallet/$sender_id>ak_****".substr($sender_id,-4)."</a>";
						$recipient_id_show="To: <a href=/address/wallet/$recipient_id>ak_****".substr($recipient_id,-4)."</a>";						
						$txhash_show="<a href=/block/transaction/$txhash>$txhash</a>";
						
						$data['inauction'].="<tr><td>$height</td><td>$txtype</td><td>$sender_id_show => $recipient_id_show</td><td>$txhash_show</td></tr>\n";
					}else{
						$txhash_show="<a href=/block/transaction/$txhash>$txhash</a>";
						$data['inauction'].="<tr><td>$height</td><td>$txtype</td><td>$txhash_show</td></tr>\n";
						}
					}
			}
			
			
			return $data;
			}
		
		public function showNamesofOwner($nameowner){			
			$this->load->database();
			$topheight=$this->GetTopHeight();
			$data['nameowner']=$nameowner;	
		
						
			$sql="SELECT distinct aensname,expire_height FROM txs_aens WHERE nameowner='$nameowner' order by expire_height;";
			$query = $this->db->query($sql);
			$data['mynames']="";
			$data['namecounter']=0;			
			
			foreach ($query->result() as $row){
				$aensname=$row->aensname;
				$expire_height=$row->expire_height;
				if(trim($aensname)!=""){
					$bidtimes=$this->getBidCount($aensname);
					$data['namecounter']++;					
					$leftheight=round(($expire_height-$topheight)/480,2);					
					$data['mynames'].="<tr><td>".$data['namecounter']."</td><td>$aensname</td><td>$expire_height</td><td>~$leftheight days</td><td><a href=/aens/viewbids/$aensname>$bidtimes</a></td></tr>\n";
				}
			}
			
			
			return $data;
			}
		
			
			
		public function showBids($name){
			$name=strtolower($name);
			$data['name']=$name;
			$this->load->database();
			$topheight=$this->GetTopHeight();
		
						
			$sql="select tx FROM txs_aens where aensname='$name' order by block_height desc;";
			$query = $this->db->query($sql);
			$data['inauction']="";
			$data['burning']=0;
			$data['inauction_count']=0;
			$bidtimes=$this->getBidCount($name);
			foreach ($query->result() as $row){
				$mybid=$bidtimes-$data['inauction_count'];
				$tx=$row->tx;
				$data['inauction_count']++;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$height=$info->block_height;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burning']=$data['burning']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$expired=$this->calcExpired($name);				
				$expired=$expired+$height;
				
				$leftheight=$expired-$topheight;
				$est=date("Y-m-d H:i:s", (time()+$leftheight*3*60));
				
				$passedheight=$topheight-$height;
				
				
				$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$name_fee</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$mybid</td><td>$expired(~$est)</td></tr>\n";
			}
			
			
			return $data;
			}
		
		public function getNameCountofOwner($nameowner){
			$name=strtolower($name);
			$topheight=$this->GetTopHeight();
			
			$this->load->database();		
			$sql="SELECT count(distinct(aensname)) FROM txs_aens WHERE nameowner='$nameowner' AND expire_height>$topheight";
			$query_count = $this->db->query($sql);
			
			foreach ($query_count->result() as $row){
				return $row->count;
			}
			
			return 0;
			
			}
			
		public function getBidCount($name){
			$name=strtolower($name);
			
			$this->load->database();		
			$sql="SELECT count(*) FROM txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND aensname='$name'";
			$query_count = $this->db->query($sql);
			
			foreach ($query_count->result() as $row){
				return $row->count;
			}
			
			return 0;
			
			}
			
		public function getBidCount_old($name){
			$name=strtolower($name);
			
			$this->load->database();		
			$sql="SELECT count(*) FROM txs WHERE block_height>161150 AND txtype='NameClaimTx' AND recipient_id='$name'";
			$query_count = $this->db->query($sql);
			
			foreach ($query_count->result() as $row){
				return $row->count;
			}
			
			return 0;
			
			}
			
			
		public function statAENS(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
		
			$sql="SELECT count(*) FROM txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is not NULL";
			$query = $this->db->query($sql);
			$data['totalreg']=0;
			foreach ($query->result() as $row){
				$data['totalreg']=$row->count;
			}
			
			$sql="SELECT COUNT(DISTINCT(aensname)) FROM txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NULL";
			$query = $this->db->query($sql);
			$data['inauction_count']=0;
			foreach ($query->result() as $row){
				$data['inauction_count']=$row->count;
			}
			
			
			$sql="SELECT tx FROM txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is NULL order by block_height desc";
			$query = $this->db->query($sql);
			$data['inauction']="";
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$passedheight=$topheight-$height;
				
				$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td></tr>\n";
			}
			
			
			$sql="SELECT tx FROM txs_aens WHERE block_height>161150 AND txtype='NameClaimTx' AND pointers is not NULL order by block_height desc limit 100";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				
				$data['latest100'].="<tr><td>$aename</td><td>$length</td><td>$name_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$height</td></tr>\n";
			}
			
			return $data;
			}
			
		public function statAENS_old(){
			$this->load->database();
			$topheight=$this->GetTopHeight();
		
			$sql="SELECT count(*) FROM txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is not NULL";
			$query = $this->db->query($sql);
			$data['totalreg']=0;
			foreach ($query->result() as $row){
				$data['totalreg']=$row->count;
			}
			
			$sql="SELECT COUNT(DISTINCT(recipient_id)) FROM txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NULL";
			$query = $this->db->query($sql);
			$data['inauction_count']=0;
			foreach ($query->result() as $row){
				$data['inauction_count']=$row->count;
			}
			
			
			$sql="SELECT tx FROM txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is NULL order by block_height desc";
			$query = $this->db->query($sql);
			$data['inauction']="";
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$txhash=$info->hash;
				$aename="<a href=/block/transaction/$txhash target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				$passedheight=$topheight-$height;
				
				$data['inauction'].="<tr><td>$height(+$passedheight)</td><td>$aename</td><td>$length</td><td>$name_fee</td><td>$init_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td></tr>\n";
			}
			
			
			$sql="SELECT tx FROM txs WHERE block_height>161150 AND txtype='NameClaimTx' AND pointer is not NULL order by block_height desc limit 100";
			$query = $this->db->query($sql);
			$data['latest100']="";
			$data['burned']=0;
			foreach ($query->result() as $row){
				$tx=$row->tx;
				$info=json_decode($tx);
				$name=$info->tx->name;
				$aename="<a href=/$name target=_blank>$name</a>";
				$account_id=$info->tx->account_id;
				$account_id_show="ak_****".substr($account_id,-4);
				$name_fee=$info->tx->name_fee/1000000000000000000;
				$data['burned']=$data['burned']+$name_fee;
				$init_fee=$this->calcFee($name);
				$length=strlen($name)-6;
				$height=$info->block_height;
				
				$data['latest100'].="<tr><td>$aename</td><td>$length</td><td>$name_fee</td><td><a href=/address/wallet/$account_id>$account_id_show</a></td><td>$height</td></tr>\n";
			}
			
			return $data;
			}
		
		
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
			
			
			$tobecheck=str_replace("ak_","",$akaddress);
			if(!$this->checkAddress($tobecheck)){
				$data['status']='<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Invalid ak_address:'.$akaddress.'</h4>
              </div>';
				return $data;
			}
						
			
			$sql="SELECT * from regaens WHERE aename='$aename'";
			$query = $this->db->query($sql);
			if($query->num_rows()==0){
				$worker=$this->getWorker();
				$claimer=$worker['claimer'];
				$regpath=$worker['regpath'];
				//$sql_insert="INSERT INTO regaens(aename,akaddress,claimer,regpath) VALUES('$aename','$akaddress','ak_pANDBzM259a9UgZFeiCJyWjXSeRhqrBQ6UCBBeXfbCQyP33Tf','')";
				$sql_insert="INSERT INTO regaens(aename,akaddress,claimer,regpath) VALUES('$aename','$akaddress','$claimer','$regpath')";
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


//Get a worker who did the least jobs to regaens
private function getWorker(){
	$this->load->database();
	$data['claimer']="ak_pANDBzM259a9UgZFeiCJyWjXSeRhqrBQ6UCBBeXfbCQyP33Tf";
	$data['regpath']="";
	
	$sql="SELECT waddress,wpath FROM workers ORDER BY jobs ASC LIMIT 1";
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){
		$waddress=$row->waddress;
		$data['claimer']=$waddress;
		$data['regpath']=$row->wpath;
		
		$sql_update="UPDATE workers SET jobs=jobs+1 WHERE waddress='$waddress'";
		$query_update= $this->db->query($sql_update);		
		}
	
	return $data;
	
	}		
	
	
public function getNames($akaddress){
		$this->load->database();
		$data['status']="";	
		
		
		if(strpos($akaddress,"k_")<1 || strlen($akaddress)<30){	
			$data['status']="Error address";
			return $data;
			}
		$sql="SELECT aename FROM regaens WHERE akaddress='$akaddress' AND pointer is not NULL order by aename";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row){
			$aename=$row->aename;
			//$akaddress=$row->akaddress;
			$data['status'].='<li><a href="/aens/query/'.$aename.'">'.$aename.'</a></li>';			
		}
		
		return $data;
	}
		
	public function regStatus(){
		$this->load->database();
		
		$sql="SELECT count(*) FROM regaens";
		$query = $this->db->query($sql);
		$data['totalreg']=0;
		foreach ($query->result() as $row){
			$data['totalreg']=$row->count;
		}
		
		$sql="SELECT aename FROM regaens WHERE pointer is not NULL order by nsid desc limit 20";
		$query = $this->db->query($sql);
		$data['latest20']="";
		foreach ($query->result() as $row){
			$aename=$row->aename;
			$aename="<a href=/$aename>$aename</a>";
			$data['latest20'].="<li>$aename</li>\n";
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
    
 public function calcFee($name){
	$name=str_replace(".chain","",$name);
	$length=strlen($name);
	if($length==1){return 570;}
	if($length==2){return 352;}
	if($length==3){return 218;}
	if($length==4){return 135;}
	if($length==5){return 83;}
	if($length==6){return 51;}
	if($length==7){return 32;}
	if($length==8){return 20;}
	if($length==9){return 12;}
	if($length==10){return 8;}
	if($length==11){return 5;}
	if($length>11){return 2.88;}
	
	return 0;
}

public function calcExpired($name){
	$name=str_replace(".chain","",$name);
	$length=strlen($name);
	if($length<5){return 29760;}
	if($length>4 && $length<9){return 14880;}
	if($length>8 && $length<13){return 480;}
	if($length>12){return  0;}
	return  0;
	}

function GetTopHeight()	{
	$url=DATA_SRC_SITE."v2/blocks/top";
	$websrc=$this->getwebsrc($url);
	$info=json_decode($websrc);
	if(strpos($websrc,"key_block")==TRUE){		
		return $info->key_block->height;
	}
		
	if(strpos($websrc,"micro_block")==TRUE){
		return $info->micro_block->height;
		}
	
	return 0;
	}
	
	

}
