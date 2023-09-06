<?php

//httpd.exe -f ./conf/myconfig.conf

error_reporting(E_ALL);

require_once 'Page.php';

class Index extends Page {

	protected function __construct(){
		parent::__construct();
	}


	protected function __destruct() 
	{
		parent::__destruct();
	}


	protected function getViewData()
	{
		// to do: fetch data for this view from the database
	}
	protected function generateView() 
	{
		$this->getViewData();
		$this->generatePageHeader('Übersicht');
		// to do: call generateView() for all members
		// to do: output view of this page

		echo <<<EOT
			<nav>
				<ul>
					<li><a href="bestellung.php" type="text/html">Bestellung</a></li>
					<li><a href="kunde.php?bestellung=MQ==" type="text/html">Kunde</a></li>
					<li><a href="baecker.php" type="text/html">Bäcker</a></li>
					<li><a href="fahrer.php" type="text/html">Fahrer</a></li>
				</ul>
			</nav>
EOT;



		$this->generatePageFooter();
	}

	public static function main() {
		try {
			$page = new Index();
			$page->processReceivedData();
			$page->generateView();
		}
		catch (Exception $e) {
			header("Content-type: text/plain; charset=UTF-8");
			echo $e->getMessage();
		}
	}


}
Index::main();



