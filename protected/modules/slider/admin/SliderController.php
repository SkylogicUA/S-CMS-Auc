<?php
/*
 * вывод каталога компаний и их данных
 */
class SliderController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "slider";
        $this->name = "Слайдер";
		$this->tb_lang = $this->key_lang_admin.'_'.$this->tb;
		$this->registry = $registry;

        $this->width = 666;
        $this->height = 253;
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
		if(isset($_POST['name'], $_POST['url']))
		{
            $id=$this->db->insert_id("INSERT INTO `".$this->tb."` SET url=?, active=?", array($_POST['url'], $_POST['active']));
			$this->db->query("INSERT INTO `".$this->tb_lang."` SET `name`=?, slider_id=?", array($_POST['name'], $id));
            if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")
            {
                $dir="files/slider/";
                resizeImage($_FILES['photo']['tmp_name'], "", $dir.$id.".jpg", $this->width, $this->height);
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
				if(isset($_POST['save_id'], $_POST['name'], $_POST['url']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
                        $param = array($_POST['url'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->tb."` SET `url`=? WHERE id=?", $param);
						$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=? WHERE slider_id=?", array($_POST['name'][$i], $_POST['save_id'][$i]));

                        if(isset($_FILES['photo']['tmp_name'][$i])&&$_FILES['photo']['tmp_name'][$i]!="")
                        {
                            $dir="files/slider/";
                            resizeImage($_FILES['photo']['tmp_name'][$i], "", $dir.$_POST['save_id'][$i].".jpg", $this->width, $this->height);
                        }
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
                    if(file_exists("files/slider/{$_POST['id'][$i]}.jpg"))unlink("files/slider/{$_POST['id'][$i]}.jpg");
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
                if(file_exists("files/slider/{$id}.jpg"))unlink("files/slider/{$id}.jpg");
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
										 ON tb.id=tb2.slider_id
											ORDER BY tb.`sort` ASC");
		return $vars;
	}
}
?>