<?php
/**
 * Каталог
 * 
 * добавление, удаление, редактирование.
 * @author delta <info@delta-ltd.ru>, <aleksandr.it@delta-ltd.ru>
 * @version 1.1
 */



/**
 * 
 * 
 * 
 * 
 */
class catalog_c {

	//use catalog_cRender;


	protected static $_VIEW_P_DIR_OPT;
	protected static $_VIEW_P_ALL_FIELD;
	protected static $_TABLE_P_IMAGES;

	protected  $catsIDS = array();
	protected  $goodsIDS = array();

	protected $modx;





	public function __construct($modx) {
		$this->modx = $modx;

		self::$_VIEW_P_DIR_OPT = 'v_product_dir_and_option' ;
		self::$_VIEW_P_ALL_FIELD = 'v_product_all_field' ;
		self::$_TABLE_P_IMAGES =  $this->modx->getFullTableName( '_product_images' );

		//echo $modx::$_TABLE_TVNAMES;
	}











	/**
	 * Возвращает список ид категорий
	 * 
	 * 
	 * @param integer $id родитель
	 * @param integer $limiter количество возвращаемых элементов, если не указано - будут возвращены все
	 * @return mixed
	 */
	public function getCatFromID ($id, $limiter = false, $push = false){
		$this->catsIDS = array();
		if (is_numeric($limiter)) $limiter = ' LIMIT '.$limiter ;
		$tableSC = $this->modx->getFullTableName('site_content');
		$result = $this->modx->db->select("id", $tableSC,  "parent=" . $id ." AND published = '1' AND deleted = '0' AND isfolder = '1' ".$limiter); 
		if( $this->modx->db->getRecordCount( $result ) >= 1 ) {
			while( $row = $this->modx->db->getRow( $result ) ){  
				$this->catsIDS[] = $row['id'] ; 
			}
		}
		if ($push) return $this->catsIDS;
		else return $this;
		
	}




	/**
	 * Возвращает список ид всех вложенных категорий
	 * 
	 * @param integer $id родитель
	 * @param boolean $addParent включать или нет в цепочку родителя
	 * @return array
	 */
	function getTreeCat($id = false , $addParent = true, $recursive = false){
		$idsCats = array();
		$tmp = array();

		if (!is_numeric($id)) {
			$id=$this->documentIdentifier;
		}

		if ($addParent) {
			if (!in_array($id , $idsCats))  {
				array_push($idsCats , $id);
			}
		}

		if (count($ids = $this->getCatFromID ($id , false , true)) > 0 && is_array($ids)) {
			foreach ($ids as $key => $value) {
				if (!in_array($value , $idsCats))  {
					array_push($idsCats , $value);
					$tmp = $this->getTreeCat($value, false , true); 
					foreach ($tmp as $key2 => $value2) {
						if (!in_array($value2 , $idsCats))  {
							array_push($idsCats , $value2);
						}
					}
				}
			}
		}
		if ($recursive) return $idsCats;
		else {
			$this->catsIDS = $idsCats;
			return $this;
		}	
	}




	/**
	 * Возвращает массив доп. поля и TV для массива c id категорий
	 * 
	 * @return mxied
	 */
	function getFields(){
		if (!is_array($this->catsIDS)) return false;
		$ids=false;
		foreach ($this->catsIDS as $key => $value) {
			$result = $this->db->query("SELECT pagetitle, parent, alias, id, isfolder, content, menuindex  
				FROM  ".self::$_TABLE_SC." WHERE id =  ".$value.$limiter); 

			if( $this->db->getRecordCount( $result ) >= 1 ) {
				while( $row = $this->db->getRow( $result ) ) { 
					$ids[$value]['fields'] = $row;  
				}
			}

			$result = $this->db->query("SELECT v.value, n.name FROM  ".self::$_TABLE_TV." AS v 
										INNER JOIN ".self::$_TABLE_TVNAMES." AS n ON n.id = v.tmplvarid
										WHERE v.contentid =  ".$value); 
			if( $this->db->getRecordCount( $result ) >= 1 ) {
				while( $row = $this->db->getRow( $result ) ) {  
					$ids[$value]['tv'][$row['name']] = $row['value'];  
				}
			}
		}
		$this->catsIDS = $ids;
		return $this;
	}




	/**
	 * Сортирует массив ids
	 * 
	 * @param array $ids массив (с полями) для сортировки
	 * @param string $field поле по которому сортировать
	 * @param string $order направление соритровки
	 * @param string $type если хоть что то сюда передать - сортироваться будет по ТВ параметрам (необходимо указать в {@$field})
	 * @return mxied
	 */
	function sortIt ($what , $field, $order = 'ASC', $type = 'fields'){
		if (($ids = $this->selectTarget($what , $typeField)) === false) return $this;
		uasort($ids, function($a, $b) use ($field, $order, $type){
			if ($order== 'DESC') {
				$fn_compaire_a = 1;
				$fn_compaire_b = -1;
			}else {
				$fn_compaire_a = -1;
				$fn_compaire_b = 1;
			}
			if ($type != 'fields') $type = 'tv';

			if ($a[$type][$field] == $b[$type][$field]) {
				return 0;
			}
			return (strnatcmp( $a[$type][$field] , $b[$type][$field])) < 0 ? $fn_compaire_a : $fn_compaire_b;
		});

		$this->{$typeField} = $ids;
		return $this;
	}



	/**
	 * Срез массива ids
	 * 
	 * Для пагинации
	 * @param array $ids массив (с полями)
	 * @param integer $from от (нумерация от нуля)
	 * @param integer $limiter количество
	 * @return mixed
	 */
	function sliceIt ($what, $from, $limiter){
		if (($ids = $this->selectTarget($what , $typeField)) === false) return $this;
		$ids = array_slice ($ids , $from, $limiter , true);
		$this->{$typeField} = $ids;
		return $this;
	}





	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function &selectTarget ($what , &$type = false){
		switch ($what) {
			case 'cats':
				$ids =  &$this->catsIDS;
				$type = 'catsIDS';
				break;

			case 'goods':
				$ids =  &$this->goodsIDS;
				$type = 'goodsIDS';
				break;

			default:
				return false;
		}
		return $ids;
	}






	/**
	 * 
	 * 
	 * 
	 * @return array
	 */
	public function getArrayData ($what){
		if (($ids = $this->selectTarget($what)) === false) return false;
		return $ids;
	}






	/**
	 * Выборка товаров из категории
	 * 
	 * Так же можно выбирать и из всех вложенных категорий
	 * @param array $id массив (с полями)
	 * @param integer $limiter от количество (ограничение кол-ва товаров)
	 * @param array $xParams массив применяемых фильтров
	 * @param boolean $showInnerCats true - выборка товаров и из вложенных категорий
	 * @return array
	 */
	function getGoodsFromCats ($id = false, $limiter = false , $xParams= false , $showInnerCats =false){
		$ids=false;
		if (!is_numeric($id)) {
			$id=$this->documentIdentifier;
		}
		if (is_numeric($limiter)) $limiter = ' LIMIT '.$limiter ;
		//$tableFTI = $modx->getFullTableName('_px_filter_values_toitm');
		//$tableFVD = $modx->getFullTableName('_px_filter_values_descr');

		$dopSql = '';
		$dopSqlPrnt = ' 1 = 1 ';


		/**
		 * @todo Убрать foreach, сделать через implode (,), изменить sql запрос - вместо кучи OR сделать IN 
		 */
		if ($id != $this->rootCatalog && $showInnerCats) {
			$innerCats = $this->getTreeCat($id);
			if (is_array($innerCats)) {
				$dopSqlPrnt = '';
				foreach ($innerCats as $key => $value) {
					$dopSqlPrnt .= $dopSqlPrnt == '' ?  ' pd.id_sc = '.$value.' ': ' OR  pd.id_sc = '.$value.' ' ;
				}
			}
		}else {
			$showInnerCats = false;
		}

 
		/**
		 * @todo выборка id товаров с учетом фильтров 
		 */
		//if (is_array($xParams)) { // не работает - пока не использовать - возможно переписать
		if (false) {
			/*
			$iter = 0 ;
			$idsSect = array();
			foreach ($xParams as $key => $value) {
				$dopSqlFl = '';
				$dopSqlGr = '';
				if ($key == 8 || $key == 9) {
					if ($key == 8) {
						$subOperator= '>';
					}elseif($key == 9) {
						$subOperator= '<';
					}
					$tmpSqlString = ' fvd.name_ru '.$subOperator.'= (SELECT name_ru FROM '.$tableFVD.' WHERE filter_value_id = '.$value[0].' LIMIT 1) '; 
					$dopSqlFl .= $dopSqlFl == '' ?  $tmpSqlString: ' OR  '.$tmpSqlString ;

				}else {
					foreach ($value as $keyIn => $valueIn) {
						$dopSqlFl .= $dopSqlFl == '' ?  ' fti.filter_value_id = '.$valueIn.' ': ' OR  fti.filter_value_id = '.$valueIn.' ' ;
					}
				}
				
				$operator = '=';
				$dopSqlGr .= $dopSqlGr == '' ?  " fti.filter_group_id ".$operator." ".$key." AND (".$dopSqlFl.") " : " AND fti.filter_group_id ".$operator." ".$key." AND (".$dopSqlFl.") " ;
				$sql = "SELECT g.good_id , fti.filter_group_id , fti.filter_value_id FROM ".$tableG." AS g 
					INNER JOIN  ".$tableFTI." AS fti ON fti.itm_id = g.good_id 
					INNER JOIN  ".$tableFVD." AS fvd ON fti.filter_value_id = fvd.filter_value_id 
					WHERE (" .$dopSqlPrnt. ") AND ( ".$dopSqlGr." ) 
					AND g.visible > 0 AND g.updatedon > ".(time()-60*60*24)." LIMIT 28000 ";


				//echo $sql; 

				$result = $modx->db->query($sql); 
				//echo mysql_error();
				if( $modx->db->getRecordCount( $result ) >= 1 ) {
					$k = 0;
					while( $row = $modx->db->getRow( $result ) ){
						$ids[$key][$row['good_id']]['id'] = $row['good_id'];
						$ids[$key][$row['good_id']]['FG'] = $row['filter_group_id'];
						$ids[$key][$row['good_id']]['FD'] = $row['filter_value_id'];
						$idsSect[$key][] = $row['good_id']; // for intersect test
						$lastK = $key;
					}
					$iter++;
				}
			}
			
			$collector = array();
			$results = array();
			if (count($idsSect) > 1) {
				$collector = call_user_func_array ('array_intersect' , $idsSect);
				foreach ($ids as $keyXp => $valueXp) {
					foreach ($valueXp as $keyInn => $valueInn) {
						if (in_array($keyInn ,$collector))
							$results[$keyInn] = $valueInn;
					}
				}
			}else {
				$results = $ids[$lastK];
			}

			return $results;
			*/
		}else {
			$sql = "SELECT * FROM ".self::$_VIEW_P_DIR_OPT." `pd`
				WHERE ".($showInnerCats ? $dopSqlPrnt :  " pd.id_sc=" . $id )." AND pd.visible > 0 GROUP BY pd.id_product ".$limiter; 
			$result = $this->db->query($sql);
			if( $this->db->getRecordCount( $result ) >= 1 ) {
				//$k = 0;
				while( $row = $this->db->getRow( $result ) ){  
					//$ids[$k++]['id'] = $row['id_product'];
					$ids[] = $row['id_product'];
				}
			}
			$this->goodsIDS = $ids;
			return $this;
		}
	}





 

	/**
	 * Выборка всех полей для массива ид товаров
	 * 
	 * 
	 * @return object
	 * @todo Сделать выборку картинок и свойств фильтров (параметров)
	 */
	function getAllGoodFields (){
		if (!is_array($this->goodsIDS)) return false;
		$ids=false;
		foreach ($this->goodsIDS as $key => $value) {
			$result = $this->db->query("SELECT *
				FROM  ".self::$_VIEW_P_ALL_FIELD." WHERE id_product =  ".$value." 
				AND id_language = ".self::$_LANGUAGE_ID); 
			if( $this->db->getRecordCount( $result ) >= 1 ) {
				while( $row = $this->db->getRow( $result ) ) { 
					$ids[$value]['fields'] = $row;  
				}
			}
		}
		$this->goodsIDS = $ids;
		return $this;
	}








}
