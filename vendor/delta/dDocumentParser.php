<?php

/**
 *
 *
 *
 * 
 */
class dDocumentParser extends DocumentParser {
	use user_u; 
	use user_uOrders;
	use catalog_c;
	use catalog_cRender;


	protected $rootCatalog = 3;
	protected static $_LANGUAGE_ID = 1;
	public $urlXParams = false;
	protected static $_TABLE_SC;
	protected static $_TABLE_TV;
	protected static $_TABLE_TVNAMES;

	



	public function __construct() {
		parent::__construct();

		self::$_TABLE_SC = $this->getFullTableName('site_content');
		self::$_TABLE_TV = $this->getFullTableName('site_tmplvar_contentvalues');
		self::$_TABLE_TVNAMES = $this->getFullTableName('site_tmplvars');

		$this->init_catalog_c();
	}




	/**
	 * Алиасы карточек товаров, доп параметры в урле
	 * 
	 *  evolution/catalog/_s_fghgfh.html
	 *  evolution/catalog/_sort_priceUp/
	 * @param string $url docIdentifier из getDocumentIdentifier()
	 * @return void
	 * @todo Отделять пагинацию, сортировку и фильтры слешем, 
	 * @todo сделать проверку алиаса на существование, отправлять на 404
	 */
	public function urlXParams(&$url){
		$urlOrigin = $url;
		if (preg_match("/(.*\/)(_(.*))/usi", $url , $matches)){
			$url = $matches[1];
		}

		$aliasPattern = "/_([a-z-\d]+)\\".$this->config['friendly_url_suffix']."/isu"; // mutch 1
		$filterPattern = "/(_f(\d{1,4}))((_v\d{1,9}(\-\d{1,9})?)+)/isu"; // mutch 2, mutch 3
		$pagePattern = "/(_page)_(\d{1,5})/isu"; // mutch 2
		$sortPattern = "/_sort_([a-z]{1,20})_(asc|desc)/isu"; // mutch 1, mutch 2
		
		if (preg_match($aliasPattern, $urlOrigin , $matches)){
			$this->urlXParams['alias'] = $matches[1];
		}

		if (preg_match_all($filterPattern, $urlOrigin , $matches)){
			if (is_array($matches) 
				&& array_key_exists(2,$matches) 
				&& array_key_exists(3,$matches) 
				&& is_array($matches[2]) 
				&& count($matches[2]))
			foreach ($matches[2] as $key => $value) {
				$this->urlXParams['filter'][$value] = $matches[3][$key];
			}
		}

		if (preg_match($pagePattern, $urlOrigin , $matches)){
			$this->urlXParams['page'] = $matches[2];
		}

		if (preg_match($sortPattern, $urlOrigin , $matches)){
			$this->urlXParams['sort']['field'] = $matches[1];
			$this->urlXParams['sort']['order'] = $matches[2];
		}
	}



	public function sendStrictURI(){
		if(is_array($this->urlXParams) && count($this->urlXParams)){
			return; //scorn
		}else {
			parent::sendStrictURI();
		}
	}
	



	function getDocumentIdentifier($method) {
		$tmp = parent::getDocumentIdentifier($method);
		$this->urlXParams($tmp);
		return $tmp;
	}





	/** 
	 * Convert URL tags [~...~] to URLs
	 *
	 * REDECLARE Для использования F в ссылках. [~1F~] - F - будет сгенерирован полный УРЛ
	 * @param string $documentSource
	 * @return string
	 */
	function rewriteUrls($documentSource) {
		// rewrite the urls
		$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
		$http_host = $_SERVER['HTTP_HOST']; 

		if ($this->config['friendly_urls'] == 1) {
			$aliases= array ();
			/* foreach ($this->aliasListing as $item) {
				$aliases[$item['id']]= (strlen($item['path']) > 0 ? $item['path'] . '/' : '') . $item['alias'];
				$isfolder[$item['id']]= $item['isfolder'];
			} */
			if (is_array($this->documentListing)){
			foreach($this->documentListing as $key=>$val){
				$aliases[$val] = $key;
				$isfolder[$val] = $this->aliasListing[$val]['isfolder'];
			}
			}

			if ($this->config['aliaslistingfolder'] == 1) {
				preg_match_all('!\[\~([0-9]+)(F)?\~\]!ise', $documentSource, $match);
				$ids = implode(',', array_unique($match['1']));
				if ($ids) {
					$res = $this->db->select("id,alias,isfolder,parent,alias_visible", $this->getFullTableName('site_content'),  "id IN (".$ids.") AND isfolder = '0'");
					while( $row = $this->db->getRow( $res ) ) {
						if ($this->config['use_alias_path'] == '1') {
							$parent = $row['parent'];
							$path   = $aliases[$parent];

							while ( isset( $this->aliasListing[$parent] ) && $this->aliasListing[$parent]['alias_visible'] == 0 ) {
								$path   = $this->aliasListing[$parent]['path'];
								$parent = $this->aliasListing[$parent]['parent'];
							}

							$aliases[$row['id']] = $path . '/' . $row['alias'];
						} else {
							$aliases[$row['id']] = $row['alias'];
						}
						$isfolder[$row['id']] = '0';
					}
				}
			}
			$in= '!\[\~([0-9]+)(F)?\~\]!is';
			$isfriendly= ($this->config['friendly_alias_urls'] == 1 ? 1 : 0);
			$pref= $this->config['friendly_url_prefix'];
			$suff= $this->config['friendly_url_suffix'];    
			$documentSource = preg_replace_callback(
				$in,
				function($m) use($aliases, $isfolder, $isfriendly, $pref, $suff , $scheme , $http_host ) {
					global $modx;
					$thealias = $aliases[$m[1]];
					$thefolder = $isfolder[$m[1]];

					$fullLink = '';
					if (isset($m[2]) && $m[2] == 'F'){
						$fullLink = "{$scheme}://{$http_host}" . '/';
					}

					if( $isfriendly && isset($thealias) ){
						//found friendly url
						$out = $fullLink.($modx->config['seostrict'] == '1' ? $modx->toAlias($modx->makeFriendlyURL($pref, $suff, $thealias, $thefolder, $m[1])) : $modx->makeFriendlyURL($pref, $suff, $thealias, $thefolder, $m[1]));
					}
					else{
						//not found friendly url
						$out = $fullLink.$modx->makeFriendlyURL($pref, $suff, $m[1]);
					}
					return $out;
				},
				$documentSource
			);          
		} else {
			$in= '!\[\~([0-9]+)(F)?\~\]!is';
			$out= "index.php?id=" . '\1';
			$fullLink = '';
			if (preg_match('!\[\~([0-9]+)(F)\~\]!is' , $documentSourceClone)) {
				$fullLink = "{$scheme}://{$http_host}" . '/';
			}
			$documentSource= preg_replace($in, $fullLink.$out, $documentSource);
		}
		return $documentSource;
	}






	public function pre($arr){
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
	}


	



	
}
