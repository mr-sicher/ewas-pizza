<?php

error_reporting(E_ALL);

require_once 'Page.php';

class Fahrer extends Page {

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
    	$sqlabfrage = "SELECT A.ID, A.B_ID AS Bestellnummer, A.Name, Adresse, speisekarte.Name AS Pizza, Preis, Zustand FROM (SELECT lieferung.ID, lieferung.B_ID, lieferung.S_ID, lieferung.Z_ID as Zustand, bestellung.Name, bestellung.Adresse FROM bestellung JOIN lieferung ON bestellung.ID = lieferung.B_ID) AS A JOIN speisekarte on A.S_ID = speisekarte.ID ORDER BY A.B_ID ";
    	$recordset = $this->_database->query($sqlabfrage);
    	if(!$recordset)
    		throw new Exception("Query failed ".$this->_database->error);

    	while($record = $recordset->fetch_assoc()){
    		//print_r($record);
    		$bestellung = htmlspecialchars(($record["Bestellnummer"]));
    		if(!isset($this->_result[$bestellung])){
    			$this->_result[$bestellung] = array();
    		}
    		$data["ID"] = htmlspecialchars($record["ID"]);
    		$data["Name"] = htmlspecialchars($record["Name"]);
    		$data["Adresse"] = htmlspecialchars($record["Adresse"]);
    		$data["Pizza"] = htmlspecialchars($record["Pizza"]);
			$data["Preis"] = htmlspecialchars($record["Preis"]);
			$data["Zustand"] = htmlspecialchars($record["Zustand"]);
    		array_push($this->_result[$bestellung], $data);
    	}
    	$recordset->free();
    	/*echo "<pre>";
    	print_r($this->_result);
    	echo "</pre>";//*/
    }

    protected function generateView() 
    {
        $this->getViewData();
        $this->generatePageHeader('Fahrer', 5);
        echo <<<EOT

		<article>
			<h1 class="title">Fahrer</h1>
			<form id="pizzen" action="" method="POST">
EOT;
		
		foreach ($this->_result as $key => $value) {
			$this->generateTableData($key);
		}

   		echo <<<EOT

   		</form>
		</article>

EOT;


        $this->generatePageFooter();
    }

    private function generateTableData($bestnr) {
		$data = $this->_result[$bestnr];
		$zustand = 100;
		$preis = 0;
		$pizzen = "";
		for($i = 0; $i < count($data); $i ++){
			if($zustand > $data[$i]["Zustand"]){
				$zustand = $data[$i]["Zustand"];
			}
			$preis += $data[$i]["Preis"];
			$pizzen .= $data[$i]["Pizza"].", ";
		}

		$pizzen = substr( $pizzen , 0 , strlen($pizzen)-2);
		$preisEuro = number_format($preis/100.0 , 2, ',', '.')."€";
		$name = $data[0]["Name"];
		$adresse = $data[0]["Adresse"];
		
		$status_1 = "";
		$status_2 = "";
		$status_3 = "";
		
		//echo $zustand;
		if($zustand < 2){
			return;
		}else if($zustand == 2){
			$status_1 = "checked";
		}else if($zustand == 3){
			$status_2 = "checked";
		}else if($zustand == 4){
			$status_3 = "checked";
			return;
		}

    	echo <<<EOT
    		<section class="lieferung">
					<h2 class="kundenname">$name, $adresse</h2>
					<section class="pizzen">$pizzen</section>
					<section class="preis">Preis: <output name="preis">$preisEuro</output></section>
					<section class="zustand">
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status[$bestnr] " value="2" $status_1 />gebacken
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status[$bestnr] " value="3" $status_2 />unterwegs
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status[$bestnr] " value="4" $status_3 />ausgeliefert
					</section>
				</section>

EOT;



    }



    protected function processReceivedData() 
	{
		parent::processReceivedData();
		if(isset($_POST["status"])){
			//print_r($_POST["status"]);
			//get max id from lieferung
			$sqlabfrage = "SELECT MAX(B_ID) as max FROM `lieferung`";
			$recordset = $this->_database->query($sqlabfrage);
			if(!$recordset)
				throw new Exception("Query failed ".$this->_database->error);
			while($record = $recordset->fetch_assoc()){
				$max = $record["max"];
			}
			$recordset->free();
			foreach ($_POST["status"] as $key => $value) {
				//echo "$key -> $value <br>";
				switch($value){//allow only values 2, 3, 4
					case 2:
						break;
					case 3:
						break;
					case 4:
						break;
					default:
						$value = 2;
						break;
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
				$stmt = $this->_database->prepare("UPDATE `lieferung` SET `Z_ID` = ? WHERE `lieferung`.`B_ID` = ?");
				$stmt->bind_param('ii', $value, $keyvalue);
				if (!$stmt->execute()) {
					throw new Exception("Query failed ".$this->_database->error);
				}
			}
			header("Location: fahrer.php");//*/
			exit();
		}

	}

    public static function main() {
        try {
            $page = new Fahrer();
            $page->processReceivedData();
            $page->generateView();
        }
        catch (Exception $e) {
            header("Content-type: text/plain; charset=UTF-8");
            echo $e->getMessage();
        }
    }


}
Fahrer::main();





/*
<!--http://www.w3schools.com/php/php_ajax_php.asp-->

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/pizza.css" />
		<title>Fahrer</title>
	</head>
	<body>
		<article>
			<h1 class="title">Fahrer</h1>
			<form id="pizzen" action="https://www.fbi.h-da.de/cgi-bin/Echo.pl" method="POST">
				<section class="lieferung">
					<h2 class="kundenname">Müller, Freßgasse 11, 65000 Frankfurt</h2>
					<section class="pizzen">Tonno, Calzone, Margherita, Hawaii, Tonno</section>
					<section class="preis">Preis: <output name="preis" for="rechnung">13,00€</output></section>
					<section class="zustand">
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_0" value="gebacken" checked/>gebacken
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_0" value="unterwegs"/>unterwegs
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_0" value="ausgeliefert" />ausgeliefert
					</section>
				</section>
				<section class="lieferung">
					<h2 class="kundenname">Maier, Hauptstr. 5</h2>
					<section class="pizzen">Tonno, Tonno, Margherita, Tonno, Tonno, Margherita, Tonno, Tonno, Margherita, Tonno, Tonno, Margherita</section>
					<section class="preis">Preis: <output name="preis" for="rechnung" id="rechnung">10,50€</output></section>
					<section class="zustand">
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_1" value="gebacken" checked/>gebacken
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_1" value="unterwegs"/>unterwegs
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_1" value="ausgeliefert" />ausgeliefert
					</section>
				</section>
				<section class="lieferung">
					<h2 class="kundenname">Egnal, Musterstraße. 5</h2>
					<section class="pizzen">Margherita</section>
					<section class="preis">Preis: <output name="preis" for="rechnung" id="rechnung">1,50€</output></section>
					<section class="zustand">
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_2" value="gebacken" checked/>gebacken
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_2" value="unterwegs"/>unterwegs
						<input type="radio" onclick="document.forms['pizzen'].submit();" name="status_2" value="ausgeliefert" />ausgeliefert
					</section>
				</section>
			</form>
		</article>
	</body>
</html>
*/