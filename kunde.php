<?php

//httpd.exe -f ./conf/myconfig.conf

error_reporting(E_ALL);

require_once 'Page.php';

class Kunde extends Page {

	private $_bestellungsid = null;
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
		try{
			//select from bestellid
			//echo "SELECT speisekarte.Name as Pizza, Preis, A.Z_ID  as Zustand FROM (SELECT lieferung.ID, lieferung.B_ID, lieferung.S_ID, lieferung.Z_ID, bestellung.Name, bestellung.Adresse FROM bestellung JOIN lieferung ON bestellung.ID = lieferung.B_ID WHERE bestellung.ID = ".$this->_bestellungsid.") AS A JOIN speisekarte on A.S_ID = speisekarte.ID<br>";
			$recordset = $this->_database->query("SELECT speisekarte.Name as Pizza, Preis, A.Z_ID  as Zustand FROM (SELECT lieferung.ID, lieferung.B_ID, lieferung.S_ID, lieferung.Z_ID, bestellung.Name, bestellung.Adresse FROM bestellung JOIN lieferung ON bestellung.ID = lieferung.B_ID WHERE bestellung.ID = ".$this->_bestellungsid.") AS A JOIN speisekarte on A.S_ID = speisekarte.ID");
			//$selectBestellungIdStmt->bind_param('i', $this->_bestellungsid);
			//$selectBestellungIdStmt->execute();
			//$recordset = $selectBestellungIdStmt->get_result();
			if(!$recordset)
				throw new Exception("Query failed ".$this->_database->error);
			//print_r($selectBestellungIdStmt->__toString());
			$id = -1;
			while($record = $recordset->fetch_assoc()){
				$data["Pizza"] = htmlspecialchars(($record["Pizza"]));
				$data["Preis"] = htmlspecialchars(($record["Preis"]));
				$data["Zustand"] = htmlspecialchars(($record["Zustand"]));
				array_push($this->_result, $data);
			}
			$recordset->free();
			if(count($this->_result) == 0){
				$this->error("417 No food found");
				//header("Location: bestellung.php");
			}
		}catch(Exception $e){
			//echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
			$this->error("500 SQL Error");
			//exit();
		}

	}
	protected function generateView() 
	{
		$this->getViewData();
		$this->generatePageHeader('Kunde', 5);
		// to do: call generateView() for all members
		// to do: output view of this page

		echo <<<EOT
			<article>
				<h1 class="title">Kunde</h1>
				<section>
					<form>
						<table class ="bestellung">
							<tr>
								<th></th>
								<th>bestellt</th>
								<th>im Ofen</th>
								<th>fertig</th>
								<th>unterwegs</th>
							</tr>
EOT;
		for($i = 0; $i < count($this->_result); $i ++){
			$this->generateTableData($i);
   		}
   		echo <<<EOT

						</table>
					</form>
				</section>
			</article>
			<ul>
				<li class="neu"><a href="bestellung.php" type="text/html" target="_blank">Neue Bestellung</a></li>
			</ul>

EOT;


		$this->generatePageFooter();
	}

	private function generateTableData($id) {
    	$pizza = $this->_result[$id]["Pizza"];
    	$zustand = $this->_result[$id]["Zustand"];

    	echo '<tr class="pizza">
				<td>'.$pizza.'</td>
				<td><input class="status" type="radio" name="status_'.$id.'" value="bestellt" disabled ';
				if($zustand == 0)
					echo 'checked';
				echo '/></td><td><input class="status" type="radio" name="status_'.$id.'" value="im_ofen" disabled ';
				if($zustand == 1)
					echo 'checked';
				echo '/></td><td><input class="status" type="radio" name="status_'.$id.'" value="fertig" disabled ';
				if($zustand == 2)
					echo 'checked';
				echo '/></td>
				<td><input class="status" type="radio" name="status_'.$id.'" value="unterwegs" disabled ';
				if($zustand == 3)
					echo 'checked';
				echo '/></td></tr>';



    }


	protected function processReceivedData() {
		parent::processReceivedData();
		if(isset($_POST["absenden"]) && isset($_POST["adresse"]) && isset($_POST["name"]) && isset($_POST["warenkorb"])){
			//exit();
			$this->doPost();
			return;

		}else if(isset($_GET["bestellung"])){
			$this->_bestellungsid = base64_decode($_GET["bestellung"]);
			if(strlen($_GET["bestellung"]) < 1){
				header('Location: bestellung.php');
			}
			if(base64_encode($this->_bestellungsid) === $_GET["bestellung"]){}else{
				$this->error('403 Forbidden');
				//header('Location: bestellung.php');
			}
		}else{
			//throw new Exception("no data received");
			header('Location: bestellung.php');
		}
		if($this->_bestellungsid === "" || !ctype_digit($this->_bestellungsid)){
			//echo " - $this->_bestellungsid -";
			//exit();
			$this->error('421 Misdirected Request');
		}
		
	}
	private function error($message = ""){
		if($message != ""){
			header("HTTP/1.1 ".$message);
		}
		require_once('error.php');
		exit();
	}
	
	private function doPost(){
			//kein escape benötigt
			$name = /*$this->_database->real_escape_string*/($_POST["name"]);
			//$name = trim($name);
			//$name = htmlspecialchars($name);
			//$name = stripslashes($name);
			$adresse = /*$this->_database->real_escape_string*/($_POST["adresse"]);
			//$adresse = trim($adresse);
			//$adresse = htmlspecialchars($adresse);
			//$adresse = stripslashes($adresse);
			$warenkorb = $_POST["warenkorb"];
			//print_r($warenkorb);
			$insertBestellungStmt = $this->_database->prepare("INSERT INTO `bestellung` (`Name`, `Adresse`) VALUES (?, ?)");
			$insertBestellungStmt->bind_param('ss', $name, $adresse);
			$insertBestellungStmt->execute();
			$insertBestellungStmt->close();

			$selectBestellungIdStmt = $this->_database->prepare("SELECT MAX(`ID`) FROM `bestellung` WHERE Name = ? AND Adresse=?");
			//echo "ECHO $name $adresse";
			$selectBestellungIdStmt->bind_param('ss', $name, $adresse);
			$selectBestellungIdStmt->execute();
			$recordset = $selectBestellungIdStmt->get_result();
			if(!$recordset)
				throw new Exception("Query failed ".$this->_database->error);
			//print_r($selectBestellungIdStmt->__toString());
			$id = -1;
			while($record = $recordset->fetch_assoc()){
				$id = $record["MAX(`ID`)"];
			}
			$recordset->free();
			$selectBestellungIdStmt->close();

			$sqlabfrage = "SELECT MAX(ID) as max FROM `speisekarte`";
			$recordset = $this->_database->query($sqlabfrage);
			if(!$recordset)
				throw new Exception("Query failed ".$this->_database->error);
			while($record = $recordset->fetch_assoc()){
				$max = $record["max"];
			}
			$recordset->free();
			
			
			for($i = 0; $i < count($warenkorb); $i ++){
				$value = -1;
				//allow only values <= max id in speisekarte
				$insertBestellungStmt = $this->_database->prepare("INSERT INTO `lieferung` (`B_ID`, `S_ID`) VALUES (?, ?)");
				for($counter = 0; $counter <= $max; $counter ++){
					if($counter ==  $warenkorb[$i]){
						$value = $counter;
						break;
					}
				}
				//echo "GEDONSE $id $warenkorb[$i]";
				if($value != -1){ 
					$insertBestellungStmt->bind_param('ii', $id, $value);
					$insertBestellungStmt->execute();
				}
				$insertBestellungStmt->close();
			}
			echo "1234411213 ".$id." ".'header("Location: kunde.php?bestellung=".'.base64_encode($id).')';
			header("Location: kunde.php?bestellung=".base64_encode($id)."");
			exit();
	}

	public static function main() {
		try {
			$page = new Kunde();
			$page->processReceivedData();
			$page->generateView();
		}
		catch (Exception $e) {
			header("Content-type: text/plain; charset=UTF-8");
			echo $e->getMessage();
		}
	}


}
Kunde::main();



/**




<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/pizza.css" />
		<title>Kunde</title>
	</head>
	<body>
		<article>
			<h1 class="title">Kunde</h1>
			<section>
				<form>
					<table class ="bestellung">
						<tr>
							<th></th>
							<th>bestellt</th>
							<th>im Ofen</th>
							<th>fertig</th>
							<th>unterwegs</th>
						</tr>
						<tr class="pizza">
							<td>Margherita</td>
							<td><input class="status" type="radio" name="status_0" value="bestellt" disabled checked /></td>
							<td><input class="status" type="radio" name="status_0" value="im_ofen" disabled /></td>
							<td><input class="status" type="radio" name="status_0" value="fertig" disabled /></td>
							<td><input class="status" type="radio" name="status_0" value="unterwegs" disabled /></td>
						</tr>
						<tr class="pizza">
							<td>Salami</td>
							<td><input class="status" type="radio" name="status_1" value="bestellt" disabled checked /></td>
							<td><input class="status" type="radio" name="status_1" value="im_ofen" disabled /></td>
							<td><input class="status" type="radio" name="status_1" value="fertig" disabled /></td>
							<td><input class="status" type="radio" name="status_1" value="unterwegs" disabled /></td>
						</tr>
						<tr class="pizza">
							<td>Tonno</td>
							<td><input class="status" type="radio" name="status_2" value="bestellt" disabled checked /></td>
							<td><input class="status" type="radio" name="status_2" value="im_ofen" disabled /></td>
							<td><input class="status" type="radio" name="status_2" value="fertig" disabled /></td>
							<td><input class="status" type="radio" name="status_2" value="unterwegs" disabled /></td>
						</tr>
						<tr class="pizza">
							<td>Hawaii</td>
							<td><input class="status" type="radio" name="status_3" value="bestellt" disabled /></td>
							<td><input class="status" type="radio" name="status_3" value="im_ofen" disabled /></td>
							<td><input class="status" type="radio" name="status_3" value="fertig" disabled checked /></td>
							<td><input class="status" type="radio" name="status_3" value="unterwegs" disabled /></td>
						</tr>
					</table>
				</form>
			</section>
		</article>
		<ul>
			<li class="neu"><a href="bestellung.html" type="text/html" target="_blank">Neue Bestellung</a></li>
		</ul>
	</body>
</html>


*/