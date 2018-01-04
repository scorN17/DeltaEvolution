<?php
/**
* Управление таблицами и представлениями MySQL
* 
* Пока без интерфейса
* Только перезапись данных из файлов
* @author delta <info@delta-ltd.ru>
* @version 1.0
* @package void
*/

define('MODX_API_MODE', true);
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession();
$modx->minParserPasses=2;




dropTables();

loadFromFile('deltaEvo.sql');
loadFromFile('fullModel.sql');
loadFromFile('testData.sql');



/**
 * Заливает файл в базу
 * @global $modx
 * @param string $fileName Файл, который необходимо импортировать
 * @return boolean
 * @todo Проверить возможность использования exec() на хостингах и подключение с паролем
 */
function loadFromFile ($fileName = 'fullModel.sql'){
	global $modx;
	$res = false;
	$dbaseP = preg_replace('/\`/ui' , '' , $modx->db->config['dbase']);
	if ($modx->db->config['pass'] != '') {
		$pass = '-p ' .$modx->db->config['pass'];
	}
	$command='mysql -h ' .$modx->db->config['host'] .' -u ' .$modx->db->config['user'] .' ' .$pass .' ' . $dbaseP .' < '.__DIR__.'\\'.$fileName;
	//echo $command;
	exec($command,$output=array(),$worked);
	switch($worked){
	    case 0:
	        $res = true;
	        break;
	    case 1:
	        $res = false;
	        break;
	}
	return $res;
}



/**
 * Возвращает список БД
 * @global $modx
 * @return array
 */
function showDatabases(){
	global $modx;
	$res = [];
	$result = $modx->db->query('SHOW DATABASES');
	while($row = $modx->db->getRow($result)) {
	   //print_r($row);
		$res[] = $row;
	}
	return $res;
}




/**
 * Удаляет все таблицы и представления в БД
 *
 * Игнорирут внешние связи
 * @global $modx
 * @return void
 */
function dropTables(){
	global $modx;
	$baseName = preg_replace('/\`/ui' , '' , $modx->db->config['dbase']);
	$tableList = [];
	$result = $modx->db->query('SHOW TABLES');
	while($row = $modx->db->getValue($result)) {
	   $tableList[] = $row;
	}
	//pre($tableList);
	$modx->db->query('SET FOREIGN_KEY_CHECKS=0');
	$obj = new ArrayObject( $tableList );
	$it = $obj->getIterator();
	$rewind = false;
	while($it->valid()){
	   // echo $it->key() . "=" . $it->current() . "\n";
	    if ( ! $modx->db->query("DROP TABLE IF EXISTS `".$baseName."`.`".$it->current()."` ")) {
	    	$rewind = true;
	    }else {
	    	$modx->db->query("DROP VIEW IF EXISTS `".$baseName."`.`".$it->current()."` ");
	    	$rewind = false;
	    }
	    $it->next();
	    if  (! $it->valid() && $rewind ) $it->rewind(); 
	}
}





function pre($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}








?>