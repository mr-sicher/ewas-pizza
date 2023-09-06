<?php	// UTF-8 marker äöüÄÖÜß€

//error_reporting(E_ALL); 

class Error
{

	protected $_database = null;

	protected function __construct() 
	{
		//$this->_database = /* to do: create instance of class MySQLi */
	}
	
	protected function __destruct()	
	{
		// to do: close database
	}
	
	protected function generateView() 
	{
		$this->generatePageHeader('Kunde');
		$this->generatePageFooter();
	}


	
	protected function generatePageHeader($headline = "") 
	{


		$headline = htmlspecialchars($headline);
		header("Content-type: text/html; charset=UTF-8");
		echo <<<EOT
    		<!DOCTYPE html>
			<html>
				<head>
					<meta charset="UTF-8"/>
					<title>Zugriff verweigert!</title>
					<style type="text/css">
							body { color: #000000; background-color: #FFFFFF; }
							a:link { color: #0000CC; }
							p, address {margin-left: 3em;}
							span {font-size: smaller;}
						</style>
					<link rev="made" href="mailto:postmaster@localhost" />
				</head>
				<body>
					<h1>Zugriff verweigert!</h1>
					<p>
					    Der Zugriff auf das angeforderte Objekt ist nicht möglich.
					    Entweder kann es vom Server nicht gelesen werden oder es
					    ist zugriffsgeschützt.
					</p>
					<p>
						Sofern Sie dies für eine Fehlfunktion des Servers halten,
						informieren Sie bitte den 
						<a href="mailto:postmaster@localhost">Webmaster</a>
						hierüber.
					</p>
					<h2>Error 403</h2>
					<address>
						<a href="/">localhost</a><br />
						<span>Apache/1.0.0 (Win32) OpenSSL/1.0.0 PHP/1.0.0</span>
					</address>
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

	public static function main() {
		try {
			$page = new Error();
			$page->processReceivedData();
			$page->generateView();
		}
		catch (Exception $e) {
			header("Content-type: text/plain; charset=UTF-8");
			echo $e->getMessage();
		}
	}


}
Error::main();







