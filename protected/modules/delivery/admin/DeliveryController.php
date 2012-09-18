<?php
/*
 * вывод каталога компаний и их данных
 */
class DeliveryController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "delivery";
		$this->tb_lang = $this->key_lang_admin.'_'.$this->tb;
        $this->name = "Способ доставки";
		$this->registry = $registry;
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$view = new View($this->registry);
		$vars['list'] = $view->Render('view.phtml', $this->listView());
		$data['content'] = $view->Render('list.phtml', $vars);
		return $this->Render($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}

	private function add()
	{
		$message='';
		if(isset($_POST['name']))
		{
            $id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET `price`=?, active=?", array($_POST['price'], $_POST['active']));
			
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `delivery_id`=?", array($_POST['name'], $id));
			}
			$message.= messageAdmin('Данные успешно добавлены');
		}
		//else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
		return $message;
	}
	
	
	private function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
			{
				
				if(isset($_POST['save_id'], $_POST['name']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
                        $this->db->query("UPDATE `".$this->tb."` SET price=? WHERE id=?", array($_POST['price'][$i], $_POST['save_id'][$i]));
						$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=? WHERE delivery_id=?", array($_POST['name'][$i], $_POST['save_id'][$i]));
					}
					$message .= messageAdmin('Данные успешно сохранены');
					if(isset($_POST['base']))$this->db->query("UPDATE `".$this->tb."` SET base=? WHERE id=?", array(1, $_POST['base']));
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	private function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				for($i=0; $i<=count($_POST['id']) - 1; $i++)
				{
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	private function listView()
	{
		$vars['list'] = $this->db->rows("SELECT 
											tb.*, tb2.name
										 FROM ".$this->tb." tb
										 
										 LEFT JOIN ".$this->tb_lang." tb2
										 ON tb.id=tb2.delivery_id
											ORDER BY tb.`sort` ASC");
		return $vars;
	}
}
?>