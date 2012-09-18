<?php
/*
 * вывод каталога компаний и их данных
 */
class PagesController extends BaseController{
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
			parent::__construct($registry, $params);
			$this->tb = "pages";
			$this->registry = $registry;
       }

       public function indexAction()
	   {
           $view = new View($this->registry);
           $settings = Registry::get('user_settings');
           $vars['translate'] = $this->translation;
		   $vars['message']='';
           $vars['body'] = $this->getPage($this->params['topic']);
           if(!isset($vars['body']['form']))return Router::act('error', $this->registry);
			
		   if(isset($_POST['send']))
			{
				$error="";
				if(!Captcha3D::check($_POST['captcha']))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
				$error.=$this->validate($_POST['email'], 'email');
				$error.=$this->validate(array($_POST['name'], $_POST['text'], $_POST['captcha']));
				if($error=="")
				{
					$text = "
					ФИО: {$_POST['name']}	<br />
					E-mail: {$_POST['email']}	<br />
					Город: {$_POST['city']}<br />
					Телефон: {$_POST['phone']}<br /><br />
					
					Сообщение: {$_POST['text']}	";
					//$this->sendMail($email, $name, $city, $tel, $text);
					$settings = Registry::get('user_settings');
					send_mime_mail($_POST['name'], // имя отправителя
								"info@".$_SERVER['HTTP_HOST'], // email отправителя
								$settings['sitename'], // имя получателя
								$settings['email'], // email получателя
								"utf-8", // кодировка переданных данных
								"windows-1251", // кодировка письма
								"Сообщения от посетителя сайта ".$settings['sitename'], // тема письма
								$text // текст письма
								);
					$vars['message']="<font style='color:green;'>".$this->translation['message_sent']."</font>";			
				}
				else $vars['message'] = $error;
			}
           if($vars['body']['form']==1)
		   {
			   $data['styles'] = array('validationEngine.jquery.css', 'user.css');
			   if($this->key_lang=='ru')$scr='jquery.validationEngine-ru.js';
			   else $scr='jquery.validationEngine-en.js';
        	   $data['scripts'] = array('jquery.validationEngine.js', $scr);
			   $vars['form'] = $view->Render('feedback.phtml',	$vars);
		   }
		   elseif($vars['body']['form']==2)
		   {
			   $vars['type_comment'] = $vars['body']['type'];
			   $vars['comments'] = $this->db->rows("SELECT * FROM `comments` WHERE content_id=? AND active=? AND type=? ORDER  BY date DESC", array($vars['body']['id'], 1, $vars['type_comment']));
			   $vars['form'] = $view->Render('comments.phtml',	$vars);
			   $data['styles'] = array('comments.css');
		   }

           ////Meta
           $data['meta'] = $vars['body'];
		   $data['breadcrumbs'] = array($vars['body']['name']);
           $data['content'] = $view->Render('body.phtml', $vars);
           return $this->Render($data);
		}
}
?>