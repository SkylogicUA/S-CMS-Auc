<?php
/*
 * вывод каталога компаний и их данных
 */
class ProductController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "product";
		$this->name = "Товары";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
        $this->tb_cat=$this->key_lang.'_catalog';
		$this->tb_photo=$this->key_lang.'_product_photo';
		$this->registry = $registry;
	}

	public function indexAction()
	{
		if(!isset($this->params['product']))return Router::act('error', $this->registry);	
		$vars['message'] = '';
		$vars['translate'] = $this->translation;
		$vars['currency'] = $this->currency();
		$view = new View($this->registry);
		
		///Product
        $vars['product'] = $this->db->row("SELECT *
                                          FROM `".$this->tb."` tb
										  
                                          LEFT JOIN `".$this->tb_lang."` tb2
                                          ON tb2.product_id=tb.id
										  
										  LEFT JOIN `product_catalog` tb3
                                          ON tb3.product_id=tb.id
										  
										  WHERE tb.url=? AND tb.active=?"
										  ,array($this->params['product'], 1));
		if(!$vars['product'])return Router::act('error', $this->registry);										  
		
		

		$q="SELECT
                tb.*,
				tb.price,
				tb.discount,
                tb2.name,
				tb2.body_m,
				tb3.catalog_id,
				tb4.status_id
				
             FROM product tb

				LEFT JOIN ".$this->key_lang."_product tb2
                ON tb2.product_id=tb.id

                LEFT JOIN product_catalog tb3
                ON tb3.product_id=tb.id
				
				LEFT JOIN product_status_set tb4
                ON tb4.product_id=tb.id

             WHERE tb.active='1' AND tb3.catalog_id=? AND tb3.product_id!='{$vars['product']['id']}'
             GROUP BY tb.id
             ORDER BY rand()
			 LIMIT 6";
		$vars['other'] = $this->db->rows($q ,array($vars['product']['catalog_id']));
		
		
		///////More photo
		$vars['photo'] = $this->db->rows("SELECT * 
										  FROM `product_photo` tb
										   LEFT JOIN `".$this->tb_photo."` tb2
										   ON tb.id=tb2.photo_id
										  WHERE tb.product_id=? AND tb.active=?
										  ORDER BY tb.`sort` asc
										  ", array($vars['product']['id'], 1));

		$data['breadcrumbs'] = $this->getBreadCat($vars['product']['catalog_id'], $vars['product']['name']);
		$data['open_link'] = $vars['product']['catalog_id'];
		$data['cur_product']=$vars['product']['id'];	
		$data['meta'] = $vars['product'];									  
		$data['content'] = $view->Render('product_in.phtml', $vars);
		return $this->Render($data);
	}
}
?>