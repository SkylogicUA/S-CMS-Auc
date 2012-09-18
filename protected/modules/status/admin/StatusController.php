<?php
/*
 * вывод каталога компаний и их данных
 */
class StatusController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "product_status";
        $this->name = "Статусы товаров";
		$this->registry = $registry;
		//$this->db->row("SELECT FROM `moderators_permission` WHERE `id`=?", array($_SESSION['admin']['id']));
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
		if(isset($_POST['name'], $_POST['comment'])&&$_POST['name']!="")
		{
			if($_POST['url']=='')$url = translit($_POST['name']);
			else $url = translit($_POST['url']);

            $id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET comment=?", array($_POST['comment']));
			
			$message = $this->checkUrl($this->tb, $url, $id);
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `".$this->tb."_id`=?", array($_POST['name'], $id));
			}
			$message.= messageAdmin('Данные успешно добавлены');
		}
		else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
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
				if(isset($_POST['save_id'], $_POST['name'], $_POST['url'], $_POST['comment']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						if($_POST['url'][$i]=='')$url = translit($_POST['name'][$i]);
						else $url = translit($_POST['url'][$i]);
						
						$message = $this->checkUrl($this->tb, $url, $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->tb."` SET `comment`=? WHERE id=?", array($_POST['comment'][$i], $_POST['save_id'][$i]));
						$this->db->query("UPDATE `".$this->key_lang_admin."_".$this->tb."` SET `name`=? WHERE ".$this->tb."_id=?", array($_POST['name'][$i], $_POST['save_id'][$i]));
					}
					$message .= messageAdmin('Данные успешно сохранены');
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
		$vars['list'] = $this->db->rows("SELECT *
										 FROM ".$this->tb." tb
										 
										 LEFT JOIN ".$this->key_lang_admin."_".$this->tb." tb2
										 ON tb.id=tb2.".$this->tb."_id 
										 
										 ORDER BY tb.`id` DESC");
		return $vars;
	}
}
?>