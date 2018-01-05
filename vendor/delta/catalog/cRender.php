<?php
/**
 * Каталог
 * 
 * парсинг чанков , вывод
 * @author delta <info@delta-ltd.ru>, <aleksandr.it@delta-ltd.ru>
 * @version 1.0
 */



/**
 * 
 * 
 * 
 * 
 */
trait catalog_cRender {

	protected  $bufer = '';



	/**
	 * Верстка. Блоки категорий
	 * 
	 * @param string $chunkName - чанк категории
	 * @param mixed $mode - false - вернуть верстку, 'print' - напечатать
	 * @return string
	 */
	public function renderAll ($chunkName , $what , $mode = false){
		$this->bufer = '';
		if (($ids = $this->selectTarget($what , $typeField)) === false) return $this;
		//$this->toPlaceholder('islogged', '0' , 'px.');
		$list = new ArrayObject( $ids );
		$iterator = $list->getIterator();
		$bodyChunk = $this->getChunk($chunkName) ;

		while($iterator->valid()){
			/**
			 * @todo Запилить prerenderChunk - пихать в [+px.alias+] - ссылку на карточку товара
			 */
			if ($typeField == 'goodsIDS') {
				//$bodyChunk = prerenderChunk($bodyChunk);
			} 
			
			$tmp = $this->parseText($bodyChunk , $iterator->current()['fields'] , '[+px.');
			if (array_key_exists('tv' , $iterator->current()))
				$tmp = $this->parseText($tmp , $iterator->current()['tv'] , '[+px.');
			$this->bufer .= $tmp;
			$iterator->next();
		}
		//$this->bufer = $this->rewriteUrls($bufer);		
		if ($mode == 'print') {
			echo $this->bufer;
			$this->bufer = '';
		}
		return $this->bufer;
	}




}