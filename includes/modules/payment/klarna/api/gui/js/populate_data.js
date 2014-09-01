function verify_invno() {
    if (document.form.invno.value == '') {
        alert('Ett fakturanummer måste anges.'); 
        return false;
    }
    return true;
}

function verify_invno(invno) {
    if (invno == '') {
        alert('You have to enter an invoice number.');
        return false;
    }
    return true;
}


function verify_activate_part(activate_form) {
    if (activate_form.invno.value == '') {
        alert('You have to enter an invoice number.'); 
        return false;
    }

    if (!isInteger(activate_form.qty0.value) ||
        !isInteger(activate_form.qty1.value) ||
        !isInteger(activate_form.qty2.value)) {
        alert('Quantities must be positive.'); 
        return false;
    }

    if (activate_form.artno0.value == '' ||
        activate_form.artno1.value == '' ||
        activate_form.artno2.value == '') {
        alert('You have to enter an article number.'); 
        return false;
    }

    return true;
}

function isInteger(value) {
    var n = parseInt(value);
    return !isNaN(n) && n >= 0;
}

function randomString() {
    var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ";
    var len = 4;
    var string = '';

    for (var i = 0; i < len; i++) {
        var n = Math.floor(Math.random()*chars.length);
        string += chars.substring(n, n+1);
    }

    return string;
}

function setPetter(){
  setInvoiceForm("18106500157", "Petter", "Testmann", "Hundremeterskogen 100",
                 "0563", "Oslo", "norway", "nok", "norwegian1", "", "", "petter.testmann@klarna.com", "40 123 456", "40 123 456");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}

function setPetra(){
  setInvoiceForm("18106500076", "Petra", "Testdame", "Sofienberggate 56",
                 "0563", "Oslo", "norway", "nok", "norwegian1", "", "", "petra.testdame@klarna.com", "40 123 456", "40 123 456");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}

function setUnoVier(){
  setInvoiceForm("070719301", "Uno", "Vier", "Hellersbergstrasse",
                 "41460", "Neuss", "germany", "eur", "german", "14", "", "uno.vier@klarna.com", "015 2211 3356", "015 2211 3356");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '19');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '19');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '19');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '19');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '19'); 
}

function setUnoEins(){
  setInvoiceForm("070719601", "Uno", "Eins", "Hellersbergstrasse",
                 "41460", "Neuss", "germany", "eur", "german", "14", "", "uno.eins@klarna.com", "015 2211 3356", "015 2211 3356");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '19');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '19');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '19');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '19');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '19'); 
}

function setTest(){
  setInvoiceForm("010719701", "Test", "Persoon", "Neherkade",
                 "2521VA", "Gravenhage", "netherlands", "eur", "netherlands", "1", "", "test.persoon@klarna.com", "0612345678", "0612345678");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '19');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '19');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '19');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '19');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '19'); 
}

function setKarl(){
  setInvoiceForm("4304158399", "Karl", "Lidin", "Junibackg. 42", "23634",
                 "Hollviken", "sweden", "sek", "swedish", "", "", "karl.lidin@klarna.com", "076 526 00 00", "076 526 00 00");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}

function setMaud(){
  setInvoiceForm("5311096845", "Maud", "Johansson", "Köpmansgatan 7", "12149",
                 "Johanneshov", "sweden", "sek", "swedish", "", "", "maud.johansson@klarna.com", "076 526 00 00", "076 526 00 00");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}

function setKalleAnka(){
  setInvoiceForm("6020310139", "", "Kalle Anka AB", "Storgatan 1", "12345",
                 "Ankeborg", "sweden", "sek", "swedish", "", "", "karl.lidin@klarna.com", "076 526 00 00", "076 526 00 00");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}

function setBjornligan(){
  setInvoiceForm("6720217931", "", "Björnligan AB", "Fulgatan 1", "12345",
                 "Ankeborg", "sweden", "sek", "swedish", "", "", "maud.johansson@klarna.com", "076 526 00 00", "076 526 00 00");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}
function setSuvi(){
  setInvoiceForm("2302468989", "Suvi", "Aurinkoinen", "Planeettatie 2", "01450",
                 "Vantaa", "finland", "eur", "finnish", "", "", "suvi.aurinkoinen@klarna.com", "040 123 45 67", "040 123 45 67");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '23');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '23');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '23');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '23');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '23');  
}
function setMikael(){
  setInvoiceForm("010130887T", "Mikael", "Miehinen", "Tikkuritie 11", "01370",
                 "Vantaa", "finland", "eur", "finnish", "", "", "mikael.miehinen@klarna.com", "040 123 45 67", "040 123 45 67");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '23');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '23');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '23');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '23');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '23');                   
}
function setPorin(){
  setInvoiceForm("10891871", "", "Porin Mies-Laulu r.y.", "Vapaudenkatu 10", "28100",
                 "Pori", "finland", "eur", "finnish", "", "", "suvi.aurinkoinen@klarna.com", "040 123 45 67", "040 123 45 67");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '23');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '23');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '23');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '23');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '23');  
}
function setMankalan(){
  setInvoiceForm("07527622", "", "Mankalan Perhekodit Oy", "Porrassalmenkatu 19 B", "50100",
                 "Parikkala", "finland", "eur", "finnish", "", "", "mikael.miehinen@klarna.com", "040 123 45 67", "040 123 45 67");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '23');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '23');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '23');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '23');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '23');  
}
function setRasmus(){
  setInvoiceForm("0505610059", "Rasmus Jens-Peter", "Lybert", "Godthåbvej 8,-2", "3900",
                 "Godthåb", "denmark", "dkk", "danish", "", "", "rasmus.lybert@klarna.com", "20 123 456", "20 123 456");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25');  
}
function setOnbase(){
  setInvoiceForm("27968880", "", "Onbase ApS", "Centrumgaden 37", "2750",
                 "Ballerup", "denmark", "dkk", "danish", "", "", "rasmus.lybert@klarna.com", "20 123 456", "20 123 456");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}
function setLarsenOlsen(){
  setInvoiceForm("99999993", "", "Larsen & Olsen Contracters Aps", "Glarmestervej 2", "8600",
                 "Silkeborg", "denmark", "dkk", "danish", "", "", "rasmus.lybert@klarna.com", "20 123 456", "20 123 456");
  setInvoiceGoods('0', 1, '10002345', 'Logitech MX 518', '19', 1, 'product', 1, '25');
  setInvoiceGoods('1', 1, '78642453', 'Macbook Air', '17', 1, 'product', 1, '25');
  setInvoiceGoods('2', 1, '77745691', 'Sandisk Memory stick 32GB', '12', 1, 'product', 1, '25');
  setInvoiceGoods('3', 1, 'S1', 'Shipment fee', '15', 1, 'shipment', 1, '25');
  setInvoiceGoods('4', 1, 'H1', 'Handling fee', '15', 1, 'handling', 1, '25'); 
}
function setInvoiceForm(pno, fname, lname, street, postno, city, country,
                        currency, language, housenum, houseext, email, telno, cellno){
  document.add_invoice_form.pno.value = pno;
  document.add_invoice_form.fname.value = fname;
  document.add_invoice_form.lname.value = lname;
  document.add_invoice_form.street.value = street;
  document.add_invoice_form.postno.value = postno;
  document.add_invoice_form.city.value = city;
  document.add_invoice_form.country.value = country;
  document.add_invoice_form.currency.value = currency;
  document.add_invoice_form.language.value = language;
    
  if(typeof(housenum) != 'undefined')
	document.add_invoice_form.housenum.value = housenum;
	else
  document.add_invoice_form.housenum.value = "";

  if(typeof(houseext) != 'undefined')
	document.add_invoice_form.housenum.value = housenum;
	else
  document.add_invoice_form.houseext.value = "";
  
  document.add_invoice_form.email.value = email;
  document.add_invoice_form.telno.value = telno;
  document.add_invoice_form.cellno.value = cellno;
}

function setInvoiceGoods(nr, qty, artno, desc, price, precision, type, inclvat, vat)
{
	/* Text fields */
	$('input[name="qty'+nr+'"]').val(qty);
	$('input[name="artno'+nr+'"]').val(artno);
	$('input[name="desc'+nr+'"]').val(desc);
	$('input[name="price'+nr+'"]').val(price);
	$('input[name="vat'+nr+'"]').val(vat);
	
	/* Selects */
	$('select[name="precision'+nr+'"] option').attr('selected', '');
	$('select[name="precision'+nr+'"] option[value="'+precision+'"]').attr('selected', 'selected');
	
	$('select[name="goods_type'+nr+'"] option').attr('selected', '');
	$('select[name="goods_type'+nr+'"] option[value="'+type+'"]').attr('selected', 'selected');
	
	
	/* Checkbox */
	if(inclvat)
		$('input[name|=inclvat"'+nr+'"]').attr('checked', 'checked');
}

function setrPetter(){
  setReservationForm("18106500157", "Petter", "Testmann", "Hundremeterskogen 100",
                 "0563", "Oslo", "no", "164", "1", "97", "3", "", "", "petter.testmann@klarna.com", "40 123 456", "40 123 456");
}

function setrPetra(){
  setReservationForm("18106500076", "Petra", "Testdame", "Sofienberggate 56",
                 "0563", "Oslo", "no", "164", "1", "97", "3", "", "", "petra.testdame@klarna.com", "40 123 456", "40 123 456");
}

function setrKarl(){
  setReservationForm("4304158399", "Karl", "Lidin", "Junibacksg 42", "23634",
                 "Hollviken", "se", "209", "0", "138", "2", "", "", "karl.lidin@klarna.com", "076 526 00 00", "076 526 00 00");
}

function setrUnoVier(){
  setReservationForm("070719301", "Uno", "Vier", "Hellersbergstrasse",
                 "41460", "Neuss", "de", "81", "2", "28", "6", "14", "", "uno.vier@klarna.com", "015 2211 3356", "015 2211 3356");
}

function setrUnoEins(){
  setReservationForm("070719301", "Uno", "Eins", "Hellersbergstrasse",
                 "41460", "Neuss", "de", "81", "2", "28", "6", "14", "", "uno.eins@klarna.com", "015 2211 3356", "015 2211 3356");
}

function setrTest(){
  setReservationForm("010719701", "Test", "Persoon", "Neherkade", "2521VA",
                 "Gravenhage", "nl", "154", "2", "101", "7", "1", "", "test.persoon@klarna.com", "0612345678", "0612345678");
}


function setrMaud(){
  setReservationForm("5311096845", "Maud", "Johansson", "Köpmansg 7", "12149",
                 "Johanneshov", "se", "209", "0", "138", "2", "", "", "maud.johansson@klarna.com", "076 526 00 00", "076 526 00 00");
}

function setrKalleAnka(){
  setReservationForm("6020310139", "", "Kalle Anka AB", "Storgatan 1", "12345",
                 "Ankeborg", "se", "209", "0", "138", "2", "", "", "karl.lidin@klarna.com", "076 526 00 00", "076 526 00 00");
}

function setrBjornligan(){
  setReservationForm("6720217931", "", "Björnligan AB", "Fulgatan 1", "12345",
                 "Ankeborg", "se", "209", "0", "138", "2", "", "", "maud.johansson@klarna.com", "076 526 00 00", "076 526 00 00");
}
function setrSuvi(){
  setReservationForm("2302468989", "Suvi", "Aurinkoinen", "Planeettatie 2", "01450",
                 "Vantaa", "fi", "73", "2", "37", "4", "", "", "suvi.aurinkoinen@klarna.com", "040 123 45 67", "040 123 45 67");
}
function setrMikael(){
  setReservationForm("010130887T", "Mikael", "Miehinen", "Tikkuritie 11", "01370",
                 "Vantaa", "fi", "73", "2", "37", "4", "", "", "mikael.miehinen@klarna.com", "040 123 45 67", "040 123 45 67");
}
function setrPorin(){
  setReservationForm("10891871", "", "Porin Mies-Laulu r.y.", "Vapaudenkatu 10", "28100",
                 "Pori", "fi", "73", "2", "37", "4", "", "", "suvi.aurinkoinen@klarna.com", "040 123 45 67", "040 123 45 67");
}
function setrMankalan(){
  setReservationForm("07527622", "", "Mankalan Perhekodit Oy", "Porrassalmenkatu 19 B", "50100",
                 "Parikkala", "fi", "73", "2", "37", "4", "", "", "mikael.miehinen@klarna.com", "040 123 45 67", "040 123 45 67");
}
function setrRasmus(){
  setReservationForm("0505610059", "Rasmus Jens-Peter", "Lybert", "Godthåbvej 8,-2", "3900",
                 "Godthåb", "dk", "59", "3", "27", "5", "", "", "rasmus.lybert@klarna.com", "20 123 456", "20 123 456");
}
function setrOnbase(){
  setReservationForm("27968880", "", "Onbase ApS", "Centrumgaden 37", "2750",
                 "Ballerup", "dk", "59", "3", "27", "5", "", "", "rasmus.lybert@klarna.com", "20 123 456", "20 123 456");
}
function setrLarsenOlsen(){
  setReservationForm("99999993", "", "Larsen & Olsen Contracters Aps", "Glarmestervej 2", "8600",
                 "Silkeborg", "denmark", "59", "3", "27", "5", "", "", "rasmus.lybert@klarna.com", "20 123 456", "20 123 456");
}

function setReservationForm(pno, fname, lname, street, postno, city, flcountry, country,
                        currency, language, pnoencoding, housenum, houseext, email, telno, cellno){
  document.reservation_form.pno.value = pno;
  document.reservation_form.lfname.value = fname;
  document.reservation_form.llname.value = lname;
  document.reservation_form.lstreet.value = street;
  document.reservation_form.lpostno.value = postno;
  document.reservation_form.lcity.value = city;
  document.reservation_form.lcountry.value = flcountry;
  document.reservation_form.ffname.value = fname;
  document.reservation_form.flname.value = lname;
  document.reservation_form.fstreet.value = street;
  document.reservation_form.fpostno.value = postno;
  document.reservation_form.fcity.value = city;
  document.reservation_form.fcountry.value = flcountry;
  document.reservation_form.country.value = country;
  document.reservation_form.currency.value = currency;
  document.reservation_form.language.value = language;
  document.reservation_form.pnoencoding.value = pnoencoding;
  document.reservation_form2.pno.value = pno;
  document.reservation_form2.lfname.value = fname;
  document.reservation_form2.llname.value = lname;
  document.reservation_form2.lstreet.value = street;
  document.reservation_form2.lpostno.value = postno;
  document.reservation_form2.lcity.value = city;
  document.reservation_form2.lcountry.value = flcountry;
  document.reservation_form2.ffname.value = fname;
  document.reservation_form2.flname.value = lname;
  document.reservation_form2.fstreet.value = street;
  document.reservation_form2.fpostno.value = postno;
  document.reservation_form2.fcity.value = city;
  document.reservation_form2.fcountry.value = flcountry;
  document.reservation_form2.country.value = country;
  document.reservation_form2.currency.value = currency;
  document.reservation_form2.language.value = language;
  document.reservation_form2.pnoencoding.value = pnoencoding;
  
  if(typeof(housenum) != 'undefined') {
  document.reservation_form.lhousenum.value = housenum;
  document.reservation_form.fhousenum.value = housenum;
    document.reservation_form2.lhousenum.value = housenum;
  document.reservation_form2.fhousenum.value = housenum;
	}
	else {
  document.reservation_form.lhousenum.value = "";
  document.reservation_form.fhousenum.value = "";
    document.reservation_form2.lhousenum.value = housenum;
  document.reservation_form2.fhousenum.value = housenum;
}
  if(typeof(houseext) != 'undefined') {
	document.reservation_form.lhouseext.value = houseext;
  document.reservation_form.fhouseext.value = houseext;
  	document.reservation_form2.lhouseext.value = houseext;
  document.reservation_form2.fhouseext.value = houseext;
	}
	else {
	document.reservation_form.lhouseext.value = "";
  document.reservation_form.fhouseext.value = "";
  	document.reservation_form2.lhouseext.value = "";
  document.reservation_form2.fhouseext.value = "";
	}
	
  document.reservation_form.email.value = email;
  document.reservation_form.phone.value = telno;
  document.reservation_form.cell.value = cellno;	
  document.reservation_form2.email.value = email;
  document.reservation_form2.phone.value = telno;
  document.reservation_form2.cell.value = cellno;  
}
