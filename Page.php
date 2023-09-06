<?php	// UTF-8 marker äöüÄÖÜß€

error_reporting(E_ALL); 
require_once('dbinfo.php');
//echo $db_host;

abstract class Page
{

	protected $_database = null;

	protected function __construct() 
	{
		//$this->_database = /* to do: create instance of class MySQLi */
		$this->_database = mysqli_connect($GLOBALS["db_host"], $GLOBALS["db_username"], $GLOBALS["db_password"], $GLOBALS["db_struct"]);
		if(!$this->_database->set_charset("utf8"))
			throw new Exception("charset failed: ".$this->_database->error);
	}
	
	protected function __destruct()	
	{
		// to do: close database
		$this->_database->close();
	}
	
	protected function generatePageHeader($headline = "", $refresh = -1) 
	{
        $refreshTag = "";
        if($refresh != -1){
            $refreshTag = '<meta http-equiv="refresh" content="'.$refresh.'; URL=" />';
        }

		$headline = htmlspecialchars($headline);
		header("Content-type: text/html; charset=UTF-8");
		echo <<<EOT
    		<!DOCTYPE html>
    			<html>
    				<head>
    					<meta charset="UTF-8"/>
                        $refreshTag
    					<link rel="stylesheet" type="text/css" href="css/pizza.css" />
    					<script src="scripts/bestellungen.js"></script>
    					<title>$headline</title>
    				</head>
    				<body>
    					<header>
    						<noscript> <section class="noscript"> <section>Um den vollen Funktionsumfang dieser Webseite zu erfahren, benötigen Sie JavaScript.</section> <section>Hier finden Sie die <a class="null" href="http://www.enable-javascript.com/de/" target="_blank">Anleitung wie Sie JavaScript in Ihrem Browser einschalten</a>.</section> </section> </noscript>
    					</header>
EOT;
		
	}

	protected function generatePageFooter() 
	{
		// to do: output common end of HTML code
		echo '</body></html> ';
	}

	protected function processReceivedData() 
	{
		if (get_magic_quotes_gpc()) {
			throw new Exception("Bitte schalten Sie magic_quotes_gpc in php.ini aus!");
		}
	}
} 
//? >
