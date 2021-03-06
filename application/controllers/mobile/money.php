<?php

class money extends MyM_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->mysmarty->assign('section','money');
	}

	function index(){
		$this->mysmarty->assign('page_title','Money');
		$this->mysmarty->view('mobile/money/index');
	}
	
	function all(){
		$this->mysmarty->assign('page_title','Money: All');
		$this->mysmarty->assign('accounts',$this->load->activeModelReturn('model_money_accounts',array(NULL,'WHERE family_id = '.$this->mylogin->user()->family_id.' ORDER BY name ASC')));
		$this->mysmarty->assign('categories',$this->load->activeModelReturn('model_money_catagories',array(NULL,NULL,'SELECT jc.description as description, jc.money_category_id as id, c.description as parent FROM money_catagories AS c RIGHT JOIN money_catagories AS jc ON c.money_category_id = jc.parent WHERE jc.parent <> 0 AND c.family_id = '.$this->mylogin->user()->family_id.' ORDER BY c.description, jc.description')));
		
		$this->mysmarty->assign('transactions',$this->load->activeModelReturn('model_money_transactions',array(NULL,NULL,'SELECT v_money_transactions.* FROM v_money_transactions JOIN money_transactions ON v_money_transactions.item_id = money_transactions.item_id '.
			$this->filters(true).' GROUP BY v_money_transactions.item_id '.$this->order_by('date DESC, item_id DESC').$this->limit_by(0,200)
		)));
		$this->mysmarty->view('mobile/money/transactions');
	}
	
	function edit($id=0){
		$this->mysmarty->assign('accounts',$this->load->activeModelReturn('model_money_accounts',array(NULL,'WHERE family_id = '.$this->mylogin->user()->family_id.' ORDER BY name ASC')));
		$this->mysmarty->assign('categories',$this->load->activeModelReturn('model_money_catagories',array(NULL,NULL,'SELECT jc.description as description, jc.money_category_id as id, c.description as parent FROM money_catagories AS c RIGHT JOIN money_catagories AS jc ON c.money_category_id = jc.parent WHERE jc.parent <> 0 AND c.family_id = '.$this->mylogin->user()->family_id.' ORDER BY c.description, jc.description')));
		$this->mysmarty->assign('t',$this->load->activeModelReturn('model_money_items',array($id)));
		$this->mysmarty->view('mobile/money/view');
	}
	
	function save(){
		$this->load->activeModel('model_money_items','trans_item',array($this->input->post('item_id',0)));
		$this->trans_item->family_id = $this->mylogin->user()->family_id;
		$this->trans_item->account_id = $this->input->post('account_id',0);
		$this->trans_item->trans_type = $this->input->post('trans_type',-1);
		$this->trans_item->description = mysql_real_escape_string($this->input->post('description'));
		$this->trans_item->date = $this->input->post('date',date('Y-m-d'));
		$this->trans_item->deleted = 0;
		$this->trans_item->added_by = $this->mylogin->user()->id();
		$this->trans_item->added_datetime = date('Y-m-d H:i:s');
		$this->trans_item->confirmed = 0;
		$this->trans_item->bank_date = NULL;
		
		if($this->trans_item->save()){
			
			foreach($this->input->post('catagory_id') as $k => $cat_id){
				$tran = $this->load->activeModelReturn('model_money_transactions',array(0));
				$tran->item_id = $this->trans_item->id();
				$tran->category_id = $cat_id;
				$tran->amount = (isset($_POST['amount'][$k]) && !empty($_POST['amount'][$k]) ? $_POST['amount'][$k] : 0 );
				$tran->save();
			}
			
		}

		$this->mysmarty->assign('page_title','Money');
		$this->mysmarty->view('mobile/money/index');
	}
}

?>