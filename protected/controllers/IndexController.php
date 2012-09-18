<?php
/**
 * class to auntificate admin
 * @author
 */
class IndexController extends BaseController {
	protected $params;
    function __construct ($registry, $params)
	{
        $this->registry = $registry;
		$this->tb_p = "product";
		$this->tb_lang_p = $this->key_lang.'_'.$this->tb_p;
        parent::__construct($registry, $params);
    }

	function indexAction()
    {
		$vars['translate'] = $this->translation;///Переводы интерфейса
        $view = new View($this->registry);
        $vars['slider'] = $this->db->rows("SELECT * FROM slider 
										   
										   LEFT JOIN ".$this->key_lang."_slider s
										   ON s.slider_id=slider.id
										   
										   WHERE active=? 
										   ORDER BY sort ASC", array(1));
		$vars['catalog'] = $this->db->rows("SELECT tb.id, tb.url, tb2.name
											  FROM catalog tb 
												LEFT JOIN ".$this->key_lang."_catalog tb2
												ON tb.id=tb2.cat_id
												
											  WHERE tb.sub='9'
											  ORDER BY sort ASC");
		$vars['body'] = $this->getPage('home');
		$vars['slider'] = $view->Render('slider.phtml', $vars);

		$vars['top_ban'] = $this->getBlock(6);
		
		include($_SERVER['DOCUMENT_ROOT'].'/protected/modules/catalog/CatalogController.php');///Include catalog controllers
		
		////Top products
		$vars['product_h']=$this->db->rows(CatalogController::query_products('AND tb4.status_id=?'), array(2));

        $data['meta'] = $vars['body'];
        $data['styles'] = array('slider.css');
        //$data['scripts'] = array('slider.js');
        $data['content'] = $view->Render('main.phtml', $vars);
		return $this->Render($data);
	}
}
?>