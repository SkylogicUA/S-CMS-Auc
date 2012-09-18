<?php
/**
 * class to Ajax action
 * @author mvc
 */

class AjaxAdminController extends BaseController{
	
	function __construct ($registry, $params)
	{
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		
	}
	
	///On/off
	function activeAction()
	{
		if(isset($_POST['id'], $_POST['tb']))
		{
			if($_POST['tb']=='meta_data')$tb='meta';
			else $tb=$_POST['tb'];
			$data=array();
			$data['message'] ='';			
			if(!$this->checkAccess('edit', $tb))$data['message'] = messageAdmin('Отказано в доступе', 'error');
			
			if($_POST['tb2']!='undefined')$tb=$_POST['tb2'];
			if($data['message']=='')
			{
				$_POST['id']=str_replace("active", "", $_POST['id']);
				//$tb=$_POST['tb'];
				$row=$this->db->row("SELECT `active` FROM `$tb` WHERE `id`=?", array($_POST['id']));
				if($row['active']==1)
				{
					$this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", array(0, $_POST['id']));
					$data['active']='<div class="selected-status status-d"><a> Выкл. </a></div>';
				}
				else{
					$this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", array(1, $_POST['id']));
					$data['active']='<div class="selected-status status-a"><a> Вкл. </a></div>';
				}
				$data['message']=messageAdmin('Данные успешно сохранены');
			}
			echo json_encode($data);
		}
	}
	
	/////
	function sortAction()
	{
		if(isset($_POST['arr'], $_POST['tb']))
		{
			if($_POST['tb']=='module')$tb='modules';
			elseif($_POST['tb']=='info_blocks')$tb='info';
			else $tb=$_POST['tb'];
			$data=array();
			$data['message'] ='';			
			if(!$this->checkAccess('edit', $tb))$data['message'] = messageAdmin('Отказано в доступе', 'error');
			if($_POST['tb2']!='undefined')$tb=$_POST['tb2'];
			if($data['message']=='')
			{
				$_POST['arr']=str_replace("sort", "", $_POST['arr']);
				preg_match_all("/=(\d+)/",$_POST['arr'],$a);//echo var_dump($a);
				foreach($a[1] as $pos=>$id)
				{
					$pos2=$pos+1;
					//echo"update {$_POST['tb']} set sort='$pos2' WHERE id='".$id."'";
					$this->db->query("update `$tb` set `sort`=? WHERE `id`=?", array($pos2, $id));
				}
				$data['message']=messageAdmin('Данные успешно сохранены');
			}
			echo json_encode($data);
		}
	}

    
	/////Product Gallery tpl
    function photoproductAction()
    {
		if(isset($_REQUEST['id']))
		{
			$res = $this->db->rows("SELECT * FROM `product_photo` tb
											  LEFT JOIN `".$this->key_lang."_product_photo` tb2
											  ON tb.id=tb2.photo_id
											  WHERE product_id=?
											  ORDER BY sort ASC",
			array($_REQUEST['id']));
			
			$this->registry->set('admin', 'product');
			$view = new View($this->registry);
			echo $view->Render('photoproduct.phtml', array('photo'=>$res, 'id'=>$_REQUEST['id']));
		}
    }
	
	
	/////Product Gallery multiload
	function uploadifyproductAction()
    {
		if(isset($_FILES['Filedata'], $_REQUEST['id']))
		{
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$name =str_replace(strchr($_FILES['Filedata']['name'], "."), "", $_FILES['Filedata']['name']);
			$insert_id=$this->db->insert_id("insert into product_photo set product_id=?, active=?", array($_REQUEST['id'], 1));
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_product_photo";
				$param = array($name, $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `photo_id`=?", $param);
			}
			$dir=createDir($_REQUEST['id']);
			resizeImage($tempFile, $dir[1].$insert_id.".jpg", $dir[1].$insert_id."_s.jpg", 146, 94);
			
			switch ($_FILES['Filedata']['error'])
			{     
				case 0:
				 $msg = ""; // comment this out if you don't want a message to appear on success.
				 break;
				case 1:
				  $msg = "The file is bigger than this PHP installation allows";
				  break;
				case 2:
				  $msg = "The file is bigger than this form allows";
				  break;
				case 3:
				  $msg = "Only part of the file was uploaded";
				  break;
				case 4:
				 $msg = "No file was uploaded";
				  break;
				case 6:
				 $msg = "Missing a temporary folder";
				  break;
				case 7:
				 $msg = "Failed to write file to disk";
				 break;
				case 8:
				 $msg = "File upload stopped by extension";
				 break;
				default:
				$msg = "unknown error ".$_FILES['Filedata']['error'];
				break;
			}
			
			if ($msg)
			{
				$stringData = "Error: ".$_FILES['Filedata']['error']." Error Info: ".$msg;
			}
			else{//This is required for onComplete to fire on Mac OSX
				$stringData = "1";
			}
			echo $stringData;
			//$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
			//echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
		}
    }
	
	
	/////Gallery multiload
    function uploadifyAction()
    {
		if(isset($_FILES['Filedata'], $_REQUEST['id']))
		{
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$name =str_replace(strchr($_FILES['Filedata']['name'], "."), "", $_FILES['Filedata']['name']);
			$insert_id=$this->db->insert_id("insert into photo set photos_id=?, active=?", array($_REQUEST['id'], 1));
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_photo";
				$param = array($name, $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `photo_id`=?", $param);
			}
			$dir="files/photos/{$_REQUEST['id']}/";
			if(!is_dir($dir))
			{
				mkdir($dir, 0755) ;
			}
			resizeImage($tempFile, $dir.$insert_id.".jpg", $dir.$insert_id."_s.jpg", 214, 145);
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
			$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
			echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
		}
    }
	
	
	/////Gallery tpl
    function photosAction()
    {
		if(isset($_REQUEST['id']))
		{
			$res = $this->db->rows("SELECT * FROM `photo` tb
											  LEFT JOIN `".$this->key_lang."_photo` tb2
											  ON tb.id=tb2.photo_id
											  WHERE photos_id=?
											  ORDER BY sort ASC",
				array($_REQUEST['id']));
			$this->registry->set('admin', 'photos');
			$view = new View($this->registry);
			echo $view->Render('photos.phtml', array('photo'=>$res));	
		}
    }
	
	
	////Include  modules
    function addModuleAction()
    {
		if($_POST['id'])
		{
			$dir=MODULES.$_POST['id']."/admin/data/info.txt";//echo $dir;
			if(file_exists($dir))
			{
				$lines = file($dir);
				$i=0;
				$data=array();
				foreach ($lines as $line_num => $line)
				{
					if($i==0)$data['name']=$line;
					elseif($i==1)$data['comment']=$line;
					else $data['tables']=$line;
					$i++;
				}
				return json_encode($data);
			}
		}
    }
	
	
	////Create small Photo
    function createphotoAction()
    {
		if(isset($_POST['path'], $_POST['width'], $_POST['height'], $_POST['src']))
		{
			$tmp_dir = "{$_POST['path']}";
			$targ_w = $_POST['width']; 
			$targ_h = $_POST['height'];
			$jpeg_quality = 100;
			
			$src = $_POST['src'];
			$usr = $_POST['usr'];
		
			
			$params = getimagesize($tmp_dir.$src);
			
			$imageType = image_type_to_mime_type($params[2]);
			switch($imageType)
			{
				case "image/gif":
				   $img_r=imagecreatefromgif($tmp_dir . $src); 
				   break;
				case "image/pjpeg":
				case "image/jpeg":
				case "image/jpg":
				   $img_r=imagecreatefromjpeg($tmp_dir . $src); 
				   break;
				case "image/png":
				case "image/x-png":
				   $img_r=imagecreatefrompng($tmp_dir . $src); 
				   break;
			}
			$dst_r = imagecreatetruecolor($targ_w, $targ_h);
			imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);
			$name = $usr;
			header('Content-type: image/jpeg');
			imagejpeg($dst_r, "{$_POST['path']}" . $name . "_s.jpg", $jpeg_quality);
			unlink("{$_POST['path']}" . $name . "_b.jpg");  
			echo json_encode($name . "_s.jpg");
		}
    }
	
	////Create small Photo
    function includephotoAction()
	{ 
		//$this->db->query("UPDATE language SET domen='4' WHERE id='2'");
		//copy("files/default.jpg", 'files/defaul2t.jpg');
		$result = $this->handleUpload($_SESSION['tovar_write']);
	}
	
	
	function handleUpload($id_foto)
    {
        $pref = "";  
        $uploaddir = $_SESSION['path'];
     	
		//copy($_SERVER['DOCUMENT_ROOT']."/files/default.jpg", $_SERVER['DOCUMENT_ROOT'].'/files/defaul2t.jpg');
        $maxFileSize = 100 * 1024 * 1024; 
        //var_info($_GET['qqfile']);
        if(isset($_GET['qqfile']))
        {
            $file = new UploadFileXhr();
        }
        elseif(isset($_FILES['qqfile']))
        {
             $file = new UploadFileForm();
        } 
        else{
            return array('success'=>false);
        }
     
        $pathinfo = pathinfo($file->getName()); 
        $filename = $pathinfo['filename'];            
        $ext = $pathinfo['extension'];
         
        while(file_exists($pref . $uploaddir . $filename . '.' . $ext))
        {
            $local_dataate("Y_m_d_h_i_s");
            $filename .= rand(10, 99);
        }    
        
        $filename2 = $id_foto.'_b' . '.'.$ext;
        //$file->save($pref . $uploaddir . $filename . '.' . $ext);
		//echo $pref . $uploaddir . $filename2." - -".$pref . $uploaddir .$id_foto.'.jpg';
        $file->save($pref.$uploaddir.$filename2, $pref.$uploaddir.$id_foto.'.jpg');
    
        //copy( $pref.$uploaddir.$filename2, $pref.$uploaddir.$filename2);
        return  array("success"=>true);
    }
	
	
	function orderProductAction()
    {
		if($_POST['id'])
		{
			$data=array();
			$data['content']='<option value="0">Выберите товар...</option>';
			$q="SELECT
                tb.*,
                tb2.name
				
				 FROM product tb
	
					LEFT JOIN ".$this->key_lang."_product tb2
					ON tb2.product_id=tb.id
	
					LEFT JOIN product_catalog tb3
					ON tb3.product_id=tb.id
	
				 WHERE tb.active='1' AND tb3.catalog_id=?
				 GROUP BY tb.id
				 ORDER BY tb.`sort` ASC, tb.id DESC";//echo $q;
			$res = $this->db->rows($q, array($_POST['id']));
			if(count($res)!=0)
			{
				foreach($res as $row)
				{
					$data['content'].='<option value="'.$row['id'].'">'.$row['name'].'</option>';
				}
			}
			else $data['content']='<option value="0">Товаров нет...</option>';
			
			return json_encode($data);
		}
    }
	
	function orderProductViewAction()
    {
		if(isset($_POST['id'],$_POST['order_id']))
		{
			
			
			$row = $this->db->row("SELECT tb.*, tb2.name 
								   FROM product tb
								   
								   LEFT JOIN ".$this->key_lang."_product tb2
								   ON tb.id=tb2.product_id 
								   
								   WHERE tb.`id`='{$_POST['id']}'");
			
			$row2 = $this->db->row("SELECT id FROM orders_product WHERE orders_id=? AND product_id=?", array($_POST['order_id'], $_POST['id']));	
			$param = array($_POST['order_id'], $row['name'], $row['price'], $row['discount'], 1, $row['price'], $_POST['id']);		   
			if(!$row2)$this->db->query("INSERT INTO orders_product SET orders_id=?, name=?, price=?, discount=?, amount=?, `sum`=?, `product_id`=?", $param);
			else $this->db->query("UPDATE orders_product SET amount=amount+1, `sum`=`sum`*amount WHERE id=?", array($row2['id']));
			
			$total=0;
			$res = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($_POST['order_id']));
			foreach($res as $row)
			{
				$sum=$row['price']*$row['amount'];
				$total+=$sum;
				
			}	
			
			$res = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($_POST['order_id']));
			
			$this->registry->set('admin', 'orders');
			$view = new View($this->registry);
			$currency = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");
			$data=array();
			$data['content']=$view->Render('orderproduct.phtml', array('product'=>$res, 'total'=>$total, 'currency'=>$currency));
			
			$this->db->query("UPDATE `orders` SET `amount`=?, `sum`=? WHERE `id`=?", array(count($res)-1, $total, $_POST['order_id']));
			//$data['amount'] = count($res)-1;
			$data['total'] = 'Итого: '.$total;
			return json_encode($data);
		}
    }
}
?>