<?php
/*
 * вывод каталога компаний и их данных
 */

class LanguageController extends BaseController{

	protected $params;
	protected $db;

	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "language";
        $this->name = "Языки";
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
			
		$view = new View($this->registry);
		$vars['Language']=array();
		$dir=getcwd().'/tpl/admin/images/flags/';
		$Language =scandir($dir);
		
		sort($Language ); 
		$default = $this->db->rows_key("Select `language`,`language`  FROM `".$this->tb."`    " ) ; 
		foreach($Language as $ky=>$val)
		{
			$fileParts  = pathinfo($dir.$val );
			 
			if($val<>'..' and $val<>'.'  and $fileParts["extension"]=='png' ) 
			{
				$index=substr($val,0,-4); 
				if( !in_array($index, array_keys($default))) 
					$vars['Language'][$index]=$index;
			}
		
		} 
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}

	private function add()
	{
		$message='';
		if(isset($_POST['comment'],$_POST['language'])&&$_POST['comment']!=''&&$_POST['language']!='0')
		{
			try
			{
				$this->db->beginTransaction();  // start Transaction 
				$rollBack='';
				
				$param = array($_POST['comment'],$_POST['language'],'domen'.$_POST['language']);  
				$insert_id=$this->db->insert_id("INSERT INTO `".$this->tb."` SET `comment`=?,`language`=?,`domen`=?", $param);
				if(!$insert_id)$rollBack.='ok'; 
							   
				$language_default=$this->db->cell("Select `language`  FROM `".$this->tb."` WHERE `default`=?", array(1)); 
				
				$Tables=$this->db->rows('show tables');
				$mass =array();
				sort($Tables);
				
				
				$db_name  =$this->registry['db_settings']["name"];  
				
				$array=$Tables;
				$filtered_array = array_filter($array, 
												function ($element ) use ($language_default) 
													{ 
													 
														$mm =explode('_',$element[key($element)]);
														return ($mm[0] == $language_default);
													}
												); 
				
				foreach($filtered_array as $ky => $val)
				{ 
					$nTablDef = $val["Tables_in_{$db_name}"]; // имя текущщей таблицы
					$nTablNew = str_replace($language_default."_",$_POST['language'].'_',$val["Tables_in_{$db_name}"]); // имя новой таблицы
					$CreatTablDef = $this->db->row("SHOW CREATE TABLE `{$nTablDef}` " ); // структура текк. таблицы 
					 
					$patterns = array();
					$patterns[] = '/'.$nTablDef.'/';
					$patterns[] = '/CONSTRAINT(.*)FOREIGN KEY/Uis'; 
					 
					$replacements = array();
					$replacements[] = ''.$nTablNew.'';
					$replacements[] = 'CONSTRAINT `FK_'.$nTablNew.time().'`FOREIGN KEY ';
					
					$CreatTabl_new=preg_replace($patterns, $replacements, $CreatTablDef["Create Table"]); // структура Новой таблицы
					$query=$this->db->query($CreatTabl_new); if(!$query) $rollBack.='ok';
					
					$COLUMNS=$this->db->rows("SHOW  COLUMNS FROM `$nTablDef` ");
					$COLUMNS=arrayKeys($COLUMNS,'Field') ;	
					$SqlINSERT="INSERT INTO `$nTablNew` (`".implode('`,`',array_keys($COLUMNS) )."`) 
									SELECT `".implode('`,`',array_keys($COLUMNS) )."` FROM `$nTablDef`;";
					$query=$this->db->query($SqlINSERT);   if(!$query) $rollBack.='ok'; 
					
				}	
				
				if($rollBack == 'ok')
				{ 
					$this->db->rollBack(); // отмена всех add
					$message.= messageAdmin('При добавление произошли ошибки', 'error');
				}
				else
				{
					$this->db->commit();   // save Transaction
					$message.= messageAdmin('Данные успешно добавлены'); 
				}
			} 
			catch(PDOException $e) 
			{
				$this->db->rollBack();// отмена всех add
				$message.= messageAdmin('При добавление произошли ошибки!', 'error');	
			}
		}
		else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
 		return $message;
	}
	 
	private function save()
	{
		$message='';   
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else{
			if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
			{
				if(isset($_POST['save_id'], $_POST['comment']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						$def= (isset($_POST['default'][0]) and $_POST['default'][0]== $_POST['save_id'][$i])?1:0; 
						$param = array($_POST['comment'][$i], $def, $_POST['save_id'][$i]);  
                        $this->db->query("UPDATE `".$this->tb."` SET `comment`=?, `default`=? WHERE id=?", $param);
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
		$id = $this->params['delete'];
		
		$default=$this->db->cell("Select `default`  FROM `".$this->tb."` WHERE `id`=?", array($id)) ; 
		if($default==1)$message .= messageAdmin('При удалении произошли ошибки<br /> Нельзя удалить основной язык!', 'error');
		
		if(isset($this->registry['access']))
			$message = $this->registry['access'];		
	 	elseif($default<>1)
		{
			 if(isset($this->params['delete']) && $this->params['delete']>0)
			 {
 				$key=$this->db->cell("Select `language`  FROM `".$this->tb."` WHERE `id`=?", array($id)) ;
			  
			  	$db_name  =$this->registry['db_settings']["name"];  
			  
				$tables=$this->db->rows('show tables');
				$mass =array();
				sort($tables);
			 	 
				$my_value = $key; 
				$array=$tables;
				$filtered_array = array_filter($array, function ($element) use ($my_value) 
											  { $mm =explode('_',$element[key($element)]);
												return ($mm[0] == $my_value);
											  }); 
				
				 
				foreach($filtered_array as $ky=>$val)
				{
					 $this->db->query("DROP TABLE `".$val["Tables_in_{$db_name}"]."`  " );
				}	
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		} 
		return $message;
	}


	private function listView()
	{
		$vars['list'] = $this->db->rows("SELECT 

											tb.*

										 FROM ".$this->tb." tb

											ORDER BY tb.`sort` ASC");

		return $vars;
	}
}
?>