<?php
/*
 * вывод каталога компаний и их данных
 */
class BrendController extends BaseController{
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
			parent::__construct($registry, $params);
			$this->tb = "brend";
			$this->registry = $registry;
       }

		public function indexAction()
		{
			$view = new View($this->registry);
			$settings = Registry::get('user_settings');
			$vars['translate'] = $this->translation;
			if(!isset($this->params[$this->tb]))header("Location: ".LINK."/".$this->tb."/all");
		
			if(!isset($this->params[$this->tb])||$this->params[$this->tb]=='all')
			{
				$size_page =10;
				$start_page = 0;
				$cur_page = 0;
				$vars['paging'] = '';
			
				if(isset($this->params['page']))
				{
					$cur_page = $this->params['page'];
					$start_page = ($cur_page-1) * $size_page;//номер начального элемента
				}
				$q="SELECT tb1.*, tb2.name
					 FROM `".$this->tb."` tb1
					 LEFT JOIN ".$this->key_lang."_".$this->tb." tb2
					 ON tb1.id=tb2.".$this->tb."_id
					 WHERE  tb1.active=?
					 ORDER BY tb1.`sort` ASC";
				$sql = $q." LIMIT ".$start_page.", ".$size_page."";
				//echo $sql;
				$count = $this->db->query($q, array(1));//кол страниц
				if($count > $size_page)
				{
					$vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page);//вызов шаблона для постраничной навигации
				}
				$vars['list'] = $this->db->rows($sql, array(1));
				$data['breadcrumbs'] = array($this->translation['brend']);
			}
			else{
			   $vars['brend'] = $this->db->row("SELECT *
											   FROM `".$this->tb."` tb1
											   LEFT JOIN ".$this->key_lang."_".$this->tb." tb2
											   ON tb1.id=tb2.".$this->tb."_id
											   WHERE tb1.url=? AND tb1.active=?",
			   array($this->params[$this->tb], 1));
			   if(!$vars['brend'])return Router::act('error', $this->registry);

				$data['breadcrumbs'] = array('<a href="'.LINK.'/brend/all">'.$this->translation['brend'].'</a>', $vars['brend']['name']);
				$data['meta'] = $vars['brend'];
			}
          
		   $data['content'] = $view->Render($this->tb.'.phtml', $vars);
		   return $this->Render($data);
	}
}
?>