<?php

error_reporting(E_ALL);

require_once 'Page.php';

class Bestellung extends Page {

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
    	$sqlabfrage = "SELECT * FROM speisekarte";
    	$recordset = $this->_database->query($sqlabfrage);
    	if(!$recordset)
    		throw new Exception("Query failed ".$this->_database->error);

    	while($record = $recordset->fetch_assoc()){
    		$data["ID"] = htmlspecialchars(($record["ID"]));
    		$data["Name"] = htmlspecialchars(($record["Name"]));
    		$data["Preis"] = htmlspecialchars(($record["Preis"]));
    		$data["Bild"] = htmlspecialchars(($record["Bild"]));
    		array_push($this->_result, $data);
    	}
    	//print_r($this->_result);
    	$recordset->free();

    }

    protected function generateView() 
    {
        $this->getViewData();
        $this->generatePageHeader('Bestellung');
        echo <<<EOT
		<article>
			<h1 class="title">Bestellung</h1>
			<article class="auswahl">
				<table class="pizza_auswahl">
					<tr>
						<th class="auswahl_heading">Pizzabild</th>
						<th class="auswahl_heading">Pizzaname</th>
						<th class="auswahl_heading">Preis</th>
					</tr>



EOT;
        for($i = 0; $i < count($this->_result); $i ++){
        	$this->generateTableData($i);
   		}

   		echo <<<EOT

   		</table>
			</article>

			<article class="warenkorb">
				<form action="kunde.php" method="POST" onsubmit="return send()">
					<select class="selected_items" id="warenkorb" name="warenkorb[]" size="10" multiple tabindex="200">
					</select>
					<section class="fehler" id="fehler">
						Bitte eine Pizza auswählen.
					</section>
					<section class="preis">
						Preis
						<output name="preis" for="preis" id="preis" >0,00€</output>
					</section>
					<input class="name" type="text" value="" name="name" placeholder="Name" tabindex="300" required autofocus/>
					<input class="address" type="text" value="" name="adresse" placeholder="Adresse" tabindex="300" required/>
					<input class="button" type="button" value="Alles Löschen" onclick="deleteAll()"  tabindex="400"/>
					<input class="button" type="button" value="Auswahl Löschen" onclick="deleteSelected()"  tabindex="400"/>
					<input class="button" type="submit" value="Absenden" name="absenden"  tabindex="400"/>
				</form>
			</article>
		</article>

EOT;


        $this->generatePageFooter();
    }

    private function generateTableData($id) {
    	if(!$this->_result[$id]["ID"])
    		return;
    	$db_id = $this->_result[$id]["ID"];
    	$name = $this->_result[$id]["Name"];
    	$preis = $this->_result[$id]["Preis"]/100.0;
    	$bild = $this->_result[$id]["Bild"];
    	$preisEuro = number_format($preis , 2, ',', '.')."€";

    	echo <<<EOT
    		<tr class="pizza">
    			<td><img class="pizza_bild" src="imgs/$bild.png" alt="" onclick="addPizza($db_id, '$name', $preis )" tabindex="100"/></td>
    			<td class="pizza_name">$name</td>
    			<td class="preis_value">$preisEuro</td>
    		</tr>

EOT;

    }



    public static function main() {
        try {
            $page = new Bestellung();
            $page->processReceivedData();
            $page->generateView();
        }
        catch (Exception $e) {
            header("Content-type: text/plain; charset=UTF-8");
            echo $e->getMessage();
        }
    }


}
Bestellung::main();





/**
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/pizza.css" />
		<script src="scripts/bestellungen.js"></script>


		<title>Bestellung</title>
	</head>
	<body>
	<header>
		<noscript>
			<section class="noscript">
				<section>Um den vollen Funktionsumfang dieser Webseite zu erfahren, benötigen Sie JavaScript.</section>
				<section>Hier finden Sie die <a class="null" href="http://www.enable-javascript.com/de/" target="_blank">Anleitung wie Sie JavaScript in Ihrem Browser einschalten</a>.</section>
			</section>
		</noscript>
	</header>
		<article>
			<h1 class="title">Bestellung</h1>
			<article class="auswahl">
				<table class="pizza_auswahl">
					<tr>
						<th class="auswahl_heading">Pizzabild</th>
						<th class="auswahl_heading">Pizzaname</th>
						<th class="auswahl_heading">Preis</th>
					</tr>
					<tr class="pizza">
						<td><img class="pizza_bild" src="imgs/pizza.png" alt="" onclick="addPizza(1, 'Margherita', 3.99)" tabindex="100" autofocus/></td>
						<td class="pizza_name">Margherita</td>
						<td class="preis_value">3,99€</td>
					</tr>
					<tr class="pizza">
						<td><img class="pizza_bild" src="imgs/pizza.png" alt="" onclick="addPizza(2, 'Salami', 4.17)" tabindex="100"/></td>
						<td class="pizza_name">Salami</td>
						<td class="preis_value">4,17€</td>
					</tr>
					<tr class="pizza">
						<td><img class="pizza_bild" src="imgs/pizza.png" alt="" onclick="addPizza(3, 'Hawaii', 5.50)" tabindex="100"/></td>
						<td class="pizza_name">Hawaii</td>
						<td class="preis_value">5,50€</td>
					</tr>
					<tr class="pizza">
						<td><img class="pizza_bild" src="imgs/pizza.png" alt="" onclick="addPizza(4, 'Diavolo', 2.50)" tabindex="100"/></td>
						<td class="pizza_name">Diavolo</td>
						<td class="preis_value">2,50€</td>
					</tr>
					<tr class="pizza">
						<td><img class="pizza_bild" src="imgs/pizza.png" alt="" onclick="addPizza(42, 'Pizza Käse mit extra sehr viel Käse', 1.00)" tabindex="100"/></td>
						<td class="pizza_name">Pizza Käse mit extra sehr viel Käse</td>
						<td class="preis_value">1,00€</td>
					</tr>
					<tr class="pizza">
						<td><img class="pizza_bild" src="imgs/pizza.png" alt="" onclick="addPizza(1337, 'Schinken Diavolo', 0.01)" tabindex="100"/></td>
						<td class="pizza_name">Schinken Diavolo</td>
						<td class="preis_value">0,01€</td>
					</tr>
				</table>
			</article>

			<article class="warenkorb">
				<form action="https://www.fbi.h-da.de/cgi-bin/Echo.pl" method="POST" onsubmit="return send()">
					<select class="selected_items" id="warenkorb" name="warenkorb[]" size="10" multiple tabindex="200">
					</select>
					<section class="fehler" id="fehler">
						Bitte eine Pizza auswählen.
					</section>
					<section class="preis">
						Preis
						<output name="preis" for="preis" id="preis" >0,00€</output>
					</section>
					<input class="name" type="text" value="" name="name" placeholder="Name" tabindex="300" required/>
					<input class="address" type="text" value="" name="adresse" placeholder="Adresse" tabindex="300" required/>
					<input class="button" type="button" value="Alles Löschen" onclick="deleteAll()"  tabindex="400"/>
					<input class="button" type="button" value="Auswahl Löschen" onclick="deleteSelected()"  tabindex="400"/>
					<input class="button" type="submit" value="Absenden" name="absenden"  tabindex="400"/>
				</form>
			</article>
		</article>
	</body>
</html>
*/
