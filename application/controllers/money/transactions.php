<?php

class Transactions extends My_Controller {

	function all(){
		$this->mysmarty->assign('transactions',$this->load->activeModelReturn('model_money_transactions',array(NULL,NULL,'SELECT v_money_transactions.* FROM v_money_transactions JOIN money_transactions ON v_money_transactions.item_id = money_transactions.item_id '.
			$this->filters(true).' GROUP BY v_money_transactions.item_id '.$this->order_by('date DESC, item_id DESC').$this->limit_by()
		)));
		
		$accounts = $this->load->activeModelReturn('model_money_accounts',array(NULL,'WHERE family_id = '.$this->mylogin->user()->family_id.' ORDER BY name ASC'));
		$account_array[''] = 'All';
		foreach($accounts as $account){
			$account_array[$account->id()] = $account->name;
		}
		
		$categories = $this->load->activeModelReturn('model_money_catagories',array(NULL,NULL,'SELECT jc.description as description, jc.money_category_id as id, c.description as parent FROM money_catagories AS c RIGHT JOIN money_catagories AS jc ON c.money_category_id = jc.parent WHERE jc.parent <> 0 AND c.family_id = '.$this->mylogin->user()->family_id.' ORDER BY c.description, jc.description'));
		$category_array[''] = 'All';
		foreach($categories as $category){
			$category_array[$category->parent][$category->id] = $category->description;
		}
		
		$this->mysmarty->assign('filters',array(
			array(
				'type' => 'data_range','start_name' => 'date','end_name' => 'date'
			),
			array(
				'label' => 'Type','name' => 'trans_type','type' => 'select','options' => array(
					'' => 'All',
					'-1' => 'Debit',
					'1' => 'Credit')
			),
			array(
				'label' => 'Confirmed','name' => 'confirmed','type' => 'select','options' => array(
					'' => 'All',
					'1' => 'Confirmed',
					'0' => 'Un-Confirmed')
			),
			array(
				'label' => 'Account','name' => 'account_id','type' => 'select','options' => $account_array
			),
			array(
				'label' => 'Category','name' => 'category_id','type' => 'select','options' => $category_array
			)
		));
		
		$this->mysmarty->assign('headers',array(
			array('label'=>''),
			array('label'=>'Date','sort'=>'date'),
			array('label'=>'Amount','sort'=>'v_money_transactions.amount'),
			array('label'=>'Account','sort'=>'account_id'),
			array('label'=>'Type','sort'=>'trans_type'),
			array('label'=>'Category (top)'),
			array('label'=>'Category')
		));
		
		$this->mysmarty->assign('section_title','Money Transactions: All');
		
		$this->mysmarty->assign('title_buttons',array('
			<a href="money/transactions/add" class="model inline_button" data-menu-click="money-all" data-selection="money-add" class="model inline_button">Add Transaction</a>'
		));
		
		$this->mysmarty->assign('money_stats',$this->load->activeModelReturn('model_money_transactions',array(NULL,NULL,'
			SELECT
				ROUND(SUM(mi.trans_type*mt.amount),2) as "net_gain_loss",
				ROUND(AVG(if(mi.trans_type=1,mt.amount,0)),2) as "average_in",
				ROUND(AVG(if(mi.trans_type=-1,mt.amount,0)),2) as "average_out",
				ROUND(SUM(if(mi.trans_type=1,mt.amount,0)),2) as "total_in",
				ROUND(SUM(if(mi.trans_type=-1,mt.amount,0)),2) as "total_out",
				SUM(if(mi.trans_type=1,1,0)) as "count_in",
 				SUM(if(mi.trans_type=-1,1,0)) as "count_out"
			FROM money_transactions AS mt
			JOIN money_items AS mi
				ON mt.item_id = mi.item_id
			'.$this->filters(true))));
			
		$this->mysmarty->assign('extra_data',$this->mysmarty->view('money/transactions/stats',false,true));
		
		$this->mysmarty->assign('inner_loop','money/transactions/all');
		$this->json->setData($this->mysmarty->view('data_grid',false,true));
		$this->json->outputData();
	}
	
	function filter(){
		$this->mysmarty->assign('transactions',$this->load->activeModelReturn('model_money_transactions',array(NULL,NULL,'SELECT v_money_transactions.* FROM v_money_transactions JOIN money_transactions ON v_money_transactions.item_id = money_transactions.item_id '.
			$this->filters(true).' GROUP BY v_money_transactions.item_id '.$this->order_by('date DESC').$this->limit_by()
		)));
		
		$this->mysmarty->assign('money_stats',$this->load->activeModelReturn('model_money_transactions',array(NULL,NULL,'
			SELECT
				ROUND(SUM(mi.trans_type*mt.amount),2) as "net_gain_loss",
				ROUND(AVG(if(mi.trans_type=1,mt.amount,0)),2) as "average_in",
				ROUND(AVG(if(mi.trans_type=-1,mt.amount,0)),2) as "average_out",
				ROUND(SUM(if(mi.trans_type=1,mt.amount,0)),2) as "total_in",
				ROUND(SUM(if(mi.trans_type=-1,mt.amount,0)),2) as "total_out",
				SUM(if(mi.trans_type=1,1,0)) as "count_in",
 				SUM(if(mi.trans_type=-1,1,0)) as "count_out"
			FROM money_transactions AS mt
			JOIN money_items AS mi
				ON mt.item_id = mi.item_id
			'.$this->filters(true))));
			
		$this->json->setExtra($this->mysmarty->view('money/transactions/stats',false,true));
		$this->json->setData($this->mysmarty->view('money/transactions/all',false,true));
		$this->json->outputData();
	}
	
	function view($id=0){
		if($id!=0){
			$this->mysmarty->assign('accounts',$this->load->activeModelReturn('model_money_accounts',array(NULL,'WHERE family_id = '.$this->mylogin->user()->family_id.' ORDER BY name ASC')));
			$this->mysmarty->assign('categories',$this->load->activeModelReturn('model_money_catagories',array(NULL,NULL,'SELECT jc.description as description, jc.money_category_id as id, c.description as parent FROM money_catagories AS c RIGHT JOIN money_catagories AS jc ON c.money_category_id = jc.parent WHERE jc.parent <> 0 AND c.family_id = '.$this->mylogin->user()->family_id.' ORDER BY c.description, jc.description')));
			$this->mysmarty->assign('item',$this->load->activeModelReturn('model_money_items',array($id)));
			$this->json->setData($this->mysmarty->view('money/transactions/view',false,true));
		}else{
			$this->json->setMessage('Unknown Transaction');
		}
		
		$this->json->outputData();
	}
	
	function add(){
		$this->mysmarty->assign('accounts',$this->load->activeModelReturn('model_money_accounts',array(NULL,'WHERE family_id = '.$this->mylogin->user()->family_id.' AND hide = 0 ORDER BY name ASC')));
		$this->mysmarty->assign('categories',$this->load->activeModelReturn('model_money_catagories',array(NULL,NULL,'SELECT jc.description as description, jc.money_category_id as id, c.description as parent FROM money_catagories AS c RIGHT JOIN money_catagories AS jc ON c.money_category_id = jc.parent WHERE jc.parent <> 0 AND c.family_id = '.$this->mylogin->user()->family_id.' ORDER BY c.description, jc.description')));
		$this->mysmarty->assign('item',$this->load->activeModelReturn('model_money_items',array(0)));
		$this->json->setData($this->mysmarty->view('money/transactions/view',false,true));
		$this->json->outputData();
	}
	
	function transfer(){
		$this->mysmarty->assign('accounts',$this->load->activeModelReturn('model_money_accounts',array(NULL,'WHERE family_id = '.$this->mylogin->user()->family_id.' AND hide = 0 ORDER BY name ASC')));
		$this->json->setData($this->mysmarty->view('money/transactions/transfer',false,true));
		$this->json->outputData();
	}
	
	function save(){
		$this->load->activeModel('model_money_items','trans_item',array($this->input->post('item_id',0)));
		$this->trans_item->family_id = $this->mylogin->user()->family_id;
		$this->trans_item->account_id = $this->input->post('account_id',0);
		$this->trans_item->trans_type = $this->input->post('trans_type',-1);
		$this->trans_item->description = mysql_real_escape_string($this->input->post('description'));
		$this->trans_item->date = $this->input->post('date',date('Y-m-d'));
		if($this->trans_item->isNew()){
			$this->trans_item->deleted = 0;
			$this->trans_item->added_by = $this->mylogin->user()->id();
			$this->trans_item->added_datetime = date('Y-m-d H:i:s');
			$this->trans_item->confirmed = 0;
			$this->trans_item->bank_date = NULL;
		}else{
			$this->db->query('DELETE FROM money_transactions WHERE item_id = '.$this->trans_item->id());
		}
		
		$this->trans_item->save();
		
		foreach($this->input->post('catagory_id') as $index => $cat){
			if($_POST['amount'][$index]!=''){
				$tran = $this->load->activeModelReturn('model_money_transactions',array(0));
				$tran->item_id = $this->trans_item->id();
				$tran->category_id = $cat;
				$tran->amount = $_POST['amount'][$index];
				$tran->save();
			}
		}
		
		$this->json->setData(true);
		$this->json->setMessage('Transaction Saved');
		$this->json->outputData();
	}
	
	function transfer_save(){
		$tran_in_save = false;
		$tran_out_save = false;
		
		$cat_in = $this->db->query('SELECT money_category_id FROM money_catagories WHERE system = "trans_in" LIMIT 1')->row_array();
		$cat_out = $this->db->query('SELECT money_category_id FROM money_catagories WHERE system = "trans_out" LIMIT 1')->row_array();
		
		$item_in = $this->load->activeModelReturn('model_money_items',array(0));
		$item_in->family_id = $this->mylogin->user()->family_id;
		$item_in->account_id = $this->input->post('account_id_to',0);
		$item_in->trans_type = 1;
		$item_in->description = mysql_real_escape_string($this->input->post('description'));
		$item_in->date = $this->input->post('date',date('Y-m-d'));
		$item_in->deleted = 0;
		$item_in->added_by = $this->mylogin->user()->id();
		$item_in->added_datetime = date('Y-m-d H:i:s');
		$item_in->confirmed = 0;
		$item_in->bank_date = NULL;
		if($item_in->save()){
			$tran_in = $this->load->activeModelReturn('model_money_transactions',array(0));
			$tran_in->item_id = $item_in->id();
			$tran_in->category_id = $cat_in['money_category_id'];
			$tran_in->amount = $this->input->post('amount',0);
			$tran_in_save = $tran_in->save();
		}
		
		$item_out = $this->load->activeModelReturn('model_money_items',array(0));
		$item_out->family_id = $this->mylogin->user()->family_id;
		$item_out->account_id = $this->input->post('account_id_from',0);
		$item_out->trans_type = -1;
		$item_out->description = mysql_real_escape_string($this->input->post('description'));
		$item_out->date = $this->input->post('date',date('Y-m-d'));
		$item_out->deleted = 0;
		$item_out->added_by = $this->mylogin->user()->id();
		$item_out->added_datetime = date('Y-m-d H:i:s');
		$item_out->confirmed = 0;
		$item_out->bank_date = NULL;
		if($item_out->save()){
			$tran_out = $this->load->activeModelReturn('model_money_transactions',array(0));
			$tran_out->item_id = $item_out->id();
			$tran_out->category_id = $cat_out['money_category_id'];
			$tran_out->amount = $this->input->post('amount',0);
			$tran_out_save = $tran_out->save();
		}
		
		if($tran_in_save==true && $tran_out_save==true){
			$this->json->setData(true);
			$this->json->setMessage('Transfer Saved');
		}else{
			$this->json->setData(false);
			$this->json->setMessage('Transfer failed to save');
		}

		$this->json->outputData();
	}
	
	/*function delete_single_transaction($item_id=false){
		if($item_id && is_numeric($item_id)){
			$item = $this->load->activeModelReturn('model_money_items',array($item_id));
			if($item->delete()){
				if($this->db->query('DELETE FROM money_transactions WHERE item_id = '.$item_id)){
					echo 'Deleted';
				}else{
					echo 'Deleted item but failed to delete transactions';
				}
			}else{
				echo 'Failed to delete item';
			}
		}
	}*/
}

?>