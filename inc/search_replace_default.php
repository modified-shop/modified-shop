<?php
/*-----------------------------------------------------------------------
    $Id$
	
    Zeichenkodierung: ISO-8859-1
   
   Version 1.06 rev.04 (c) by web28  - www.rpa-com.de   
------------------------------------------------------------------------*/
function shopstat_getRegExps(&$search, &$replace)
{
    $search     = array(
                        "'\s&\s'",          //--Kaufmännisches Und mit Blanks muss raus
                        "'[\r\n\s]+'",      // strip out white space
                        "'&(quote|#34);'i", // replace html entities
                        "'&(amp|#38);'i",   //--Ampersand-Zeichen, kaufmännisches Und
                        "'&(lt|#60);|<'i",  //--öffnende spitze Klammer
                        "'&(gt|#62);|>'i",  //--schließende spitze Klammer
                        "'&(nbsp|#160);'i", //--Erzwungenes Leerzeichen          
                        "'&(iexcl|#161);|¡'i", //umgekehrtes Ausrufezeichen
                        "'&(cent|#162);|¢'i",  //Cent-Zeichen
                        "'&(pound|#163);|£'i", //Pfund-Zeichen
                        "'&(copy|#169);|©'i",  //Copyright-Zeichen                        
                        "'%'",              //--Prozent muss weg
                        "/[\[\({]/",        //--öffnende Klammern nach Bindestriche
                        "/[\)\]\}]/",       //--schliessende Klammern weg
                        "/ß/",              //--Umlaute etc.
                        "/ä/",              //--Umlaute etc.
                        "/ü/",              //--Umlaute etc.
                        "/ö/",              //--Umlaute etc.
                        "/Ä/",              //--Umlaute etc.
                        "/Ü/",              //--Umlaute etc.
                        "/Ö/",              //--Umlaute etc.
                        "/'|\"|´|`/",       //--Anführungszeichen weg.
                        "/[:,\.!?\*\+]/",   //--Doppelpunkte, Komma, Punkt etc. weg.
                        );
    $replace    = array(
                        "-",    //--Kaufmännisches Und mit Blanks muss raus
                        "-",    // strip out white space
                        "",     // replace html entities
                        "-",    //--Ampersand-Zeichen, kaufmännisches Und
                        "-",    //--öffnende spitze Klammer
                        "-",    //--schließende spitze Klammer
                        "-",    //--Erzwungenes Leerzeichen 
                        "",     //chr(161), //umgekehrtes Ausrufezeichen
                        "ct",   //chr(162), //Cent-Zeichen
                        "GBP",  //chr(163), //Pfund-Zeichen
                        "",     //chr(169),Copyright-Zeichen                        
                        "",     //--Prozent muss weg
                        "-",    //--öffnende Klammern nach Bindestriche
                        "",     //--schliessende Klammern weg
                        "ss",   //--Umlaute etc.
                        "ae",   //--Umlaute etc.
                        "ue",   //--Umlaute etc.
                        "oe",   //--Umlaute etc.
                        "Ae",   //--Umlaute etc.
                        "Ue",   //--Umlaute etc.
                        "Oe",   //--Umlaute etc.
                        "",     //--Anführungszeichen weg.
                        ""      //--Doppelpunkte, Komma, Punkt etc. weg.
                        );

}
?>