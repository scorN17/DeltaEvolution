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


	/* 
	* 	DECLARED IN user_u
	*
	protected $username = false;
	protected $password = false;
	protected $isLogged = false;
	*/

	/*
	public function __construct(){
		parent::__construct();
		//$this->db->connect();
		//$this->checkAutoLogin();
	}
	*/

	public $urlXParams = false; //scorn

	protected $rootCatalog = 3;



	public function __construct() {
		parent::__construct();
		self::$_USER_TABLE = $this->getFullTableName( '_users' );
	}




	/**
	 * Алиасы карточек товаров, доп параметры в урле
	 * 
	 *  evolution/catalog/_s_fghgfh.html
	 *  evolution/catalog/_sort_priceUp/
	 * @param string $url docIdentifier из getDocumentIdentifier()
	 * @return void
	 * @todo если есть /catalog/ и .html - это карточка товара, иначе искать _ и вычленять параметры
	 */
	public function urlXParams(&$url){
		if (preg_match("/(.*\/)(_(.*))$/", $url , $matches)){
			$url = $matches[1];
		}
		$this->urlXParams = $matches[3]; //затычка
	}




	public function sendStrictURI(){
		if($this->urlXParams){
			return; //scorn
		}else {
			parent::sendStrictURI();
		}
	}
	




	function getDocumentIdentifier($method) {
		// function to test the query and find the retrieval method
		$docIdentifier= $this->config['site_start'];
		switch ($method) {
			case 'alias' :
				$docIdentifier= $this->db->escape($_REQUEST['q']);
				break;
			case 'id' :
				if (!is_numeric($_GET['id'])) {
					$this->sendErrorPage();
				} else {
					$docIdentifier= intval($_GET['id']);
				}
				break;
			default:
				if(strpos($_SERVER['REQUEST_URI'],'index.php')!==false) {
					list(,$_) = explode('index.php', $_SERVER['REQUEST_URI'], 2);
					if(substr($_,0,1)==='/') $this->sendErrorPage();
				}
		}
		$this->urlXParams($docIdentifier);
		return $docIdentifier;
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
