var preis = [];
function addPizza(id, name, p) {
	"use strict";
	var warenkorb = document.getElementById("warenkorb");
	if(warenkorb.length == 0){
		document.getElementById("fehler").style.display = "none";
	}
	var pizza = document.createElement("option");
	pizza.text = name;
	pizza.value = id;
	pizza.tabindex = "127";
	warenkorb.appendChild(pizza);
	preis.push(p);
	makePreis();
}
function send(){
	"use strict";
	var warenkorb = document.getElementById("warenkorb");
	if(warenkorb.length == 0){
		document.getElementById("fehler").style.display = "block";
		return false;
	}
	var opts = warenkorb.options;
	for(var opt, j = 0; opt = opts[j]; j++) {
		opt.selected = true;
	}
	return true;
}
function deleteSelected(){
	"use strict";
	var warenkorb = document.getElementById("warenkorb");
	if(warenkorb.length == 0){
		return;
	}
	var opts = warenkorb.options;
	for(var index = 0; index < opts.length; index++) {
		if(opts[index].selected){
			opts.remove(index--);
			preis.splice(index + 1, 1);
		}
	}
	makePreis();
}
function deleteAll(){
	"use strict";
	var warenkorb = document.getElementById("warenkorb");
	var opts = warenkorb.options;
	for(var index = 0; index < opts.length; index++) {
		opts.remove(index--);
	}
	preis = [];
	makePreis();
}
function makePreis(){
	var p = 0.0;
	for(var i = 0; i < preis.length; i ++){
		p += preis[i];
	}
	var displayPreis = document.getElementById("preis");
	var str = p.toFixed(2) + "€";
	str = str.replace(".", ","); 
	displayPreis.innerHTML = str;
}