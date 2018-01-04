<?php
/**
 * Каталог
 * 
 * добавление, удаление, редактирование.
 * @author delta <info@delta-ltd.ru>
 * @version 1.0
 */



/**
 * 
 * 
 * 
 * 
 */
trait catalog_c {



	/**
	 * Возвращает список ид категорий
	 * 
	 * 
	 * @param integer $id родитель
	 * @param integer $limiter количество возвращаемых элементов, если не указано - будут возвращены все
	 * @return mixed
	 */
	public function getCatFromID ($id, $limiter = false){
		$ids=false;
		if (is_numeric($limiter)) $limiter = ' LIMIT '.$limiter ;
		$tableSC = $this->getFullTableName('site_content');
		$result = $this->db->select("id", $tableSC,  "parent=" . $id ." AND published = '1' AND deleted = '0' AND isfolder = '1'  ".$limiter); 
		if( $this->db->getRecordCount( $result ) >= 1 ) {
	        while( $row = $this->db->getRow( $result ) ){  
	            $ids[] = $row['id'] ; 
	        }
	    }
	    return $ids;
	}




	/**
	 * Возвращает список ид всех вложенных категорий
	 * 
	 * @param integer $id родитель
	 * @param boolean $addParent включать или нет в цепочку родителя
	 * @return array
	 */
	function getTreeCat($id = false , $addParent = true){
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

		if (count($ids = $this->getCatFromID ($id)) > 0 && is_array($ids)) {
			foreach ($ids as $key => $value) {
				if (!in_array($value , $idsCats))  {
					array_push($idsCats , $value);
					$tmp = $this->getTreeCat($value); 
					foreach ($tmp as $key2 => $value2) {
						if (!in_array($value2 , $idsCats))  {
							array_push($idsCats , $value2);
						}
					}
				}
			}
		}
		return $idsCats;
	}




	/**
	 * Возвращает массив доп. поля и TV для массива c id категорий
	 * 
	 * @param array $arrID список ид категорий
	 * @return mxied
	 */
	function getFields($arrID){
		if (!is_array($arrID)) return false;

		$table = $this->getFullTableName('site_content');
		$tableTV = $this->getFullTableName('site_tmplvar_contentvalues');
		$tableNAMES = $this->getFullTableName('site_tmplvars');

		$ids=false;
		foreach ($arrID as $key => $value) {
			$result = $this->db->query("SELECT pagetitle, parent, alias, id, isfolder, content, menuindex  FROM  ".$table." WHERE id =  ".$value.$limiter); 
			if( $this->db->getRecordCount( $result ) >= 1 ) {
				while( $row = $this->db->getRow( $result ) ) { 
		            $ids[$value]['fields'] = $row;  
		        }
			}

			$result = $this->db->query("SELECT v.value, n.name FROM  ".$tableTV." AS v 
										INNER JOIN ".$tableNAMES." AS n ON n.id = v.tmplvarid
										WHERE v.contentid =  ".$value); 
			if( $this->db->getRecordCount( $result ) >= 1 ) {
				while( $row = $this->db->getRow( $result ) ) {  
		            $ids[$value]['tv'][$row['name']] = $row['value'];  
		        }
			}
		}
	    return $ids;
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
	function sortGods ($ids , $field, $order = 'ASC', $type = 'fields'){
		if (!is_array($ids)) return false;
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
		    //return ($a[$type][$field] < $b[$type][$field]) ? $fn_compaire_a : $fn_compaire_b;
		});
		return $ids;
	}





	/**
	 * Срез массива ids
	 * 
	 * Для пагинации
	 * @param array $ids массив (с полями)
	 * @param integer $from от (нумерация от нуля)
	 * @param integer $limiter количество
	 * @return array
	 */
	function sliceGods ($ids, $from, $limiter){
		if (!is_array($ids)) return false;
		return array_slice ($ids , $from, $limiter , true);
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
	function getGoodsFromID ($id = false, $limiter = false , $xParams= false , $showInnerCats =false){
		$ids=false;
		if (!is_numeric($id)) {
			$id=$this->documentIdentifier;
		}
		if (is_numeric($limiter)) $limiter = ' LIMIT '.$limiter ;
		$tablePD = ('v_product_dir_and_option');
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
			$sql = "SELECT * FROM ".$tablePD." `pd`
				WHERE ".($showInnerCats ? $dopSqlPrnt :  " pd.id_sc=" . $id )." AND pd.visible > 0 ".$limiter; 
			$result = $this->db->query($sql);
			if( $this->db->getRecordCount( $result ) >= 1 ) {
				$k = 0;
		        while( $row = $this->db->getRow( $result ) ){  
		            $ids[$k++]['id'] = $row['id_product'];
		        }
		    }
		    return $ids;
		}
	}







}
