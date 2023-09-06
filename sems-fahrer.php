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
    	$sqlabfrage = "SELECT B_ID AS Bestellungsnummer, A.Name, Adresse, speisekarte.Name as Pizza, Preis, A.Zustand FROM (SELECT lieferung.ID, lieferung.B_ID, lieferung.S_ID, lieferung.Z_ID as Zustand, bestellung.Name, bestellung.Adresse FROM bestellung JOIN lieferung ON bestellung.ID = lieferung.B_ID) AS A JOIN speisekarte on A.S_ID = speisekarte.ID ORDER BY B_ID";
    	$recordset = $this->_database->query($sqlabfrage);
    	if(!$recordset)
    		throw new Exception("Query failed ".$this->_database->error);

    	while($record = $recordset->fetch_assoc()){
    		$data["Bestellungsnummer"] = htmlspecialchars(($record["Bestellungsnummer"]));
    		$data["Name"] = htmlspecialchars(($record["Name"]));
    		$data["Adresse"] = htmlspecialchars(($record["Adresse"]));
    		$data["Pizza"] = htmlspecialchars(($record["Pizza"]));
			$data["Preis"] = htmlspecialchars(($record["Preis"]));
			$data["Zustand"] = htmlspecialchars(($record["Zustand"]));
    		array_push($this->_result, $data);
    	}
    	//print_r($this->_result);
    	$recordset->free();

    }

    protected function generateView() 
    {
        $this->getViewData();
        $this->generatePageHeader('Fahrer');
        echo <<<EOT

		<article>
			<h1 class="title">Fahrer</h1>
			<form id="pizzen" action="fahrer.php" method="POST">



EOT;

		$current_bestnr = 0;
		$zustand = 0;
		
		if($this->_result[0]["Zustand"] > 1){
			$current_bestnr = $this->_result[0]["Bestellungsnummer"];
			$zustand = $this->_result[0]["Zustand"];
			$this->generateTableData($current_bestnr);
		}
		
        for($i = 0; $i < count($this->_result); $i ++){
        	if($this->_result[$i]["Bestellungsnummer"] > $current_bestnr && $this->_result[$i]["Zustand"] > 1 && $this->_result[$i]["Zustand"] < 4){
				$current_bestnr = $this->_result[$i]["Bestellungsnummer"];
				$zustand = $this->_result[$i]["Zustand"];
				$this->generateTableData($current_bestnr);
			}
   		}

   		echo <<<EOT

   		</form>
		</article>

EOT;


        $this->generatePageFooter();
    }

    private function generateTableData($bestnr) {
		
		$preis = 0;
		$pizzen = "";
		$pizza = "";
		$name = "";
		$adresse = "";
		$platzhalter = ", ";
		$finalzustand = -1;
		
		for($i = 0; $i < count($this->_result); $i ++){
        	if($this->_result[$i]["Bestellungsnummer"] == $bestnr){
				$preis += $this->_result[$i]["Preis"];
				$pizza = $this->_result[$i]["Pizza"];
				$pizzen = $pizzen.$platzhalter.$pizza;
				$zustand = $this->_result[$i]["Zustand"];
				if($zustand < $finalzustand || $finalzustand == -1){
					$finalzustand = $this->_result[$i]["Zustand"];
				} 
			}
   		}
		
		if($finalzustand < 2){
			return;
		}
				
		for($i = 0; $i < count($this->_result); $i ++){
        	if($this->_result[$i]["Bestellungsnummer"] == $bestnr){
				$name = $this->_result[$i]["Name"];
				$adresse = $this->_result[$i]["Adresse"];
				break;
			}
   		}		
		
		$preis = $preis/100.0;
		$preisEuro = number_format($preis , 2, ',', '.')."€";

    	$pizzen = ltrim($pizzen, ",");
		$pizzen = ltrim($pizzen);
		
		$status_1 = "";
		$status_2 = "";
		$status_3 = "";
		
		//echo $zustand;
		if($zustand == 2){
			$status_1 = "checked";
		}else if($zustand == 3){
			$status_2 = "checked";
		}else if($zustand == 4){
			$status_3 = "checked";
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
			print_r($_POST["status"]);
			/*foreach ($_POST["status"] as $key => $value) {
				//echo "$key -> $value <br>";
				$stmt = $this->_database->prepare("UPDATE `lieferung` SET `Z_ID` = ? WHERE `lieferung`.`ID` = ?");
				$stmt->bind_param('ii', $value, $key);
				if (!$stmt->execute()) {
					throw new Exception("Query failed ".$this->_database->error);
				}
			}
			header("Location: baecker.php");//*/
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