<?php

error_reporting(E_ALL);

require_once 'Page.php';

class Baecker extends Page {

	private $_result;

	protected function __construct(){
		parent::__construct();
		$this->_result = array();
	}


	protected function __destruct() 
	{
		parent::__destruct();
	}


	protected function getViewData()
	{
		$sqlabfrage = "SELECT lieferung.ID, speisekarte.name AS Pizza, lieferung.Z_ID FROM lieferung JOIN speisekarte ON lieferung.S_ID = speisekarte.ID WHERE lieferung.Z_ID < 3";// ORDER BY Z_ID DESC";
		$recordset = $this->_database->query($sqlabfrage);
		if(!$recordset)
			throw new Exception("Query failed ".$this->_database->error);

		while($record = $recordset->fetch_assoc()){
			//print_r($record);
			$data["ID"] = htmlspecialchars(($record["ID"]));
			$data["Pizza"] = htmlspecialchars(($record["Pizza"]));
			$data["Z_ID"] = htmlspecialchars(($record["Z_ID"]));
			array_push($this->_result, $data);
		}
		$recordset->free();
		//print_r($this->_result);
		//exit();
	}
	protected function generateView() 
	{
		$this->getViewData();
		$this->generatePageHeader('Bäcker', 5);
		// to do: call generateView() for all members
		// to do: output view of this page

		echo <<<EOT
			<article class="pizza_status">
				<h1 class="title">Bäcker</h1>
				<form action="baecker.php" id="pizzen" method="POST">
					<table class="bestellung">
						<tr>
							<th></th>
							<th>bestellt</th>
							<th>im Ofen</th>
							<th>fertig</th>
						</tr>
EOT;

		for($i = 0; $i < count($this->_result); $i ++){
			$this->generateTableData($i);
   		}




		echo <<<EOT

					</table>
				</form>
			</article>

EOT;



		$this->generatePageFooter();
	}


	private function generateTableData($i){
		$lid = $this->_result[$i]["ID"];
		$pizza = $this->_result[$i]["Pizza"];
		$zustand = $this->_result[$i]["Z_ID"];

		$bestellt = "";
		$im_ofen = "";
		$fertig = "";
		switch($zustand){
			case 0:
				$bestellt = "checked";
				break;
			case 1:
				$im_ofen = "checked";
				break;
			case 2:
				$fertig = "checked";
				break;
		}

		echo <<<EOT

			<tr class="pizza">
				<td>$pizza</td>
				<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status[$lid]" value="0" $bestellt /></td>
				<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status[$lid]" value="1" $im_ofen /></td>
				<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status[$lid]" value="2" $fertig /></td>
			</tr>
EOT;



	}

	protected function processReceivedData() 
	{
		parent::processReceivedData();
		if(isset($_POST["status"])){
			//get max id in lieferung
			$sqlabfrage = "SELECT MAX(ID) as max FROM `lieferung`";
			$recordset = $this->_database->query($sqlabfrage);
			if(!$recordset)
				throw new Exception("Query failed ".$this->_database->error);
			while($record = $recordset->fetch_assoc()){
				$max = $record["max"];
			}
			$recordset->free();
			foreach ($_POST["status"] as $key => $value) {
				//echo "$key -> $value <br>";
				switch($value){//escape everything in value
					case 1:
						break;
					case 2:
						break;
					default:
						$value = 0;
				}
				//now escape all keys
				$keyvalue = -1;
				for($counter = 0; $counter <= $max; $counter ++){
					if($key == $counter){
						$keyvalue = $counter;
						break;
					}
				}
				if($keyvalue == -1){
					//injection found
					throw new Exception("Injection error");
				}
				$stmt = $this->_database->prepare("UPDATE `lieferung` SET `Z_ID` = ? WHERE `lieferung`.`ID` = ?");
				$stmt->bind_param('ii', $value, $keyvalue);
				if (!$stmt->execute()) {
					throw new Exception("Query failed ".$this->_database->error);
				}
			}
			header("Location: baecker.php");
			exit();
		}

	}



	public static function main() {
		try {
			$page = new Baecker();
			$page->processReceivedData();
			$page->generateView();
		}
		catch (Exception $e) {
			header("Content-type: text/plain; charset=UTF-8");
			echo $e->getMessage();
		}
	}


}
Baecker::main();






/*<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/pizza.css" />
		<title>Bäcker</title>
	</head>
	<body>
		<article class="pizza_status">
			<h1 class="title">Bäcker</h1>
			<form action="https://www.fbi.h-da.de/cgi-bin/Echo.pl" id="pizzen" method="POST">
				<table class="bestellung">
					<tr>
						<th></th>
						<th>bestellt</th>
						<th>im Ofen</th>
						<th>fertig</th>
					</tr>
					<tr class="pizza">
						<td>Margherita</td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_0" value="bestellt" checked /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_0" value="im_ofen" /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_0" value="fertig" /></td>
					</tr>
					<tr class="pizza">
						<td>Salami</td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_1" value="bestellt" checked /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_1" value="im_ofen" /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_1" value="fertig" /></td>
					</tr>
					<tr class="pizza">
						<td>Tonno</td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_2" value="bestellt" checked /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_2" value="im_ofen" /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_2" value="fertig" /></td>
					</tr>
					<tr class="pizza">
						<td>Hawaii</td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_3" value="bestellt" /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();"  name="status_3" value="im_ofen" /></td>
						<td><input class="status" type="radio" onclick="document.forms['pizzen'].submit();" name="status_3" value="fertig" checked /></td>
					</tr>
				</table>
			</form>
		</article>
	</body>
</html>*/