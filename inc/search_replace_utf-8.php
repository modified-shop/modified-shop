<?php
/*-----------------------------------------------------------------------
    $Id: search_replace_utf-8.php 2673 2012-02-23 13:06:49Z dokuman $

    Zeichenkodierung: UTF-8

   Version 1.06 rev.04 (c) by web28  - www.rpa-com.de
------------------------------------------------------------------------*/

function shopstat_getRegExps(&$search, &$replace)
{
    $search     = array(
            "'\s&\s'",                  //--Kaufmännisches Und mit Blanks muss raus
            "'[\r\n\s]+'",              // strip out white space
            "'&(quote|#34);'i",          //--Anführungszeichen oben replace html entities
            "'&(amp|#38);'i",          //--Ampersand-Zeichen, kaufmännisches Und
            "'&(lt|#60);'i",         //--öffnende spitze Klammer
            "'&(gt|#62);'i",         //--schließende spitze Klammer
            "'&(nbsp|#160);'i",          //--Erzwungenes Leerzeichen
            //BOF - web28 - 2010-04-16 - UTF-8 kompatibel +  Eingetragene Marke, Trademark, Eurozeichen
            "'&(iexcl|#161);|¡'i",     //umgekehrtes Ausrufezeichen
            "'&(cent|#162);|¢'i",     //Cent-Zeichen
            "'&(pound|#163);|£'i",     //Pfund-Zeichen
            "'&(curren|#164);|€'i",     //Währungszeichen--currency
            "'&(yen|#165);|¥'i",       //Yen  wird zu Yen
            "'&(brvbar|#166);|Š'i",    //durchbrochener Strich
            "'&(sect|#167);|§'i",    //Paragraph-Zeichen
            "'&(copy|#169);|©'i",    //Copyright-Zeichen
            "'&(reg|#174);|®'i",    //Eingetragene Marke wird zu -R-
            "'&(deg|#176);|°'i",    //Grad-Zeichen -- degree wird zu -Grad-
            "'&(plusmn|#177);|±'i",    //Plusminus-Zeichen
            "'&(sup2|#178);|²'i",      //Hoch-2-Zeichen
            "'&(sup3|#179);|³'i",     //Hoch-3-Zeichen
            "'&(acute|#180);|´'i",     // Akut (accent aigu, acute) ### NICHT in iso-8859-15 enthalten ###
            "'&(micro|#181);|µ'i",    //Mikro-Zeichen
            "'&(trade|#8482);|™'i",     //--Trademark wird zu -TM- ### NICHT in iso-8859-15 enthalten ###
            "'&(euro|#8364);|€'i",     //--Eurozeichen wird zu EUR
            "'&(laquo|#171);|«'i",      //-- Left angle quotes Left Winkel Zitate
            "'&(raquo|#187);|»'i",     //--Right angle quotes Winkelgetriebe Zitate
            //BOF - web28 - 2010-05-13 - Benannte Zeichen für Interpunktion
            "'&(ndash|#8211);|–'i",   //-- Gedankenstrich Breite n   ### NICHT in iso-8859-15 enthalten ###
            "'&(mdash|#8212);|—'i",   //-- Gedankenstrich Breite m   ### NICHT in iso-8859-15 enthalten ###
            "'&(lsquo|#8216);|‘'i",   //-- einfaches Anführungszeichen links   ### NICHT in iso-8859-15 enthalten ###
            "'&(rsquo|#8217);|’'i",   //-- einfaches Anführungszeichen rechts   ### NICHT in iso-8859-15 enthalten ###
            "'&(sbquo|#8218);|‚'i",   //-- Einfaches, gekrümmtes Anführungszeichen unten ### NICHT in iso-8859-15 enthalten ###
            "'&(ldquo|#8220);|“'i",   //-- doppeltes Anführungszeichen links ### NICHT in iso-8859-15 enthalten ###
            "'&(rdquo|#8221);|”'i",   //-- doppeltes Anführungszeichen rechts ### NICHT in iso-8859-15 enthalten ###
            "'&(bdquo|#8222);|„'i",   //-- Doppelte Anführungszeichen links unten ### NICHT in iso-8859-15 enthalten ###
            //EOF - web28 - 2010-05-13 - Benannte Zeichen für Interpunktion
            //EOF - web28 - 2010-04-16 - UTF-8 kompatibel +  Eingetragene Marke, Trademark, Eurozeichen
            "'&'",                     //--Kaufmännisches Und
            "'%'",                     //--Prozent muss weg
            "/[\[\({]/",              //--öffnende Klammern nach Bindestriche
            "/[\)\]\}]/",             //--schliessende Klammern weg
            "/ß/",                    //--Umlaute etc.
            "/ä/",                    //--Umlaute etc.
            "/ü/",                    //--Umlaute etc.
            "/ö/",                    //--Umlaute etc.
            "/Ä/",                    //--Umlaute etc.
            "/Ü/",                    //--Umlaute etc.
            "/Ö/",                    //--Umlaute etc.
            "/'|\"|`/",                 //--Anführungszeichen weg.
            "/[:,\.!?\*\+]/"           //--Doppelpunkte, Komma, Punkt etc. weg.
                        );


  if (SPECIAL_CHAR_FR) {
  $search2 = array(  //BOF  - web28 - 2010-05-12 - Französisch
            "'&(Agrave|#192);|À'i",    // Capital A-grave Capital A-Grab
            "'&(agrave|#224);|à'i",    //Lowercase a-grave Kleinbuchstaben a-Grab
            "'&(Acirc|#194);|Â'i",     //Capital A-circumflex Capital A-Zirkumflex
            "'&(acirc|#226);|â'i",     //Lowercase a-circumflex Kleinbuchstaben a-Zirkumflex
            "'&(AElig|#198);|Æ'i",     //Capital AE Ligature Capital AE Ligature
            "'&(aelig|#230);|æ'i",     //Lowercase AE Ligature Kleinbuchstabe ae
            "'&(Ccedil|#199);|Ç'i",    //Capital C-cedilla Capital-C Cedille
            "'&(ccedil|#231);|ç'i",    //Lowercase c-cedilla Kleinbuchstaben c-Cedille
            "'&(Egrave|#200);|È'i",    //Capital E-grave Capital E-Grab
            "'&(egrave|#232);|è'i",    //Lowercase e-grave Kleinbuchstaben e-Grab
            "'&(Eacute|#201);|É'i",    //Capital E-acute E-Capital akuten
            "'&(eacute|#233);|é'i",    //Lowercase e-acute Kleinbuchstaben e-acute
            "'&(Ecirc|#202);|Ê'i",     //Capital E-circumflex E-Capital circumflexa
            "'&(ecirc|#234);|ê'i",     //Lowercase e-circumflex Kleinbuchstaben e-Zirkumflex
            "'&(Euml|#203);|Ë'i",      //Capital E-umlaut Capital E-Umlaut
            "'&(euml|#235);|ë'i",      //Lowercase e-umlaut Kleinbuchstaben e-Umlaut
            "'&(Icirc|#206);|Î'i",     //Capital I-circumflex Capital I-Zirkumflex
            "'&(icirc|#238);|î'i",     //Lowercase i-circumflex Kleinbuchstaben i-Zirkumflex
            "'&(Iuml|#207);|Ï'i",      //Capital I-umlaut Capital I-Umlaut
            "'&(iuml|#239);|ï'i",      //Lowercase i-umlaut Kleinbuchstaben i-Umlaut
            "'&(Ocirc|#212);|Ô'i",     //Capital O-circumflex O-Capital circumflexa
            "'&(ocirc|#244);|ô'i",     //Lowercase o-circumflex Kleinbuchstabe o-Zirkumflex
            "'&(OElig|#338);|Œ'i",     //Capital OE ligature Capital OE Ligatur
            "'&(oelig|#339);|œ'i",     //Lowercase oe ligature Kleinbuchstaben oe Ligatur
            "'&(Ugrave|#217);|Ù'i",    //Capital U-grave Capital U-Grab
            "'&(ugrave|#249);|ù'i",    //Lowercase u-grave Kleinbuchstaben u-Grab
            "'&(Ucirc|#219);|Û'i",     //Capital U-circumflex Capital U-Zirkumflex
            "'&(ucirc|#251);|û'i",     //Lowercase U-circumflex Kleinbuchstaben U-Zirkumflex
            "'&(Yuml|#376);|Ÿ'i",      //Großes Y mit Diaeresis
            "'&(yuml|#255);|ÿ'i"       //Kleines y mit Diaeresis
            //EOF - web28 - 2010-05-12 - Französisch
            );

  $search = array_merge($search,$search2);
  //echo print_r($search);
  }

  if (SPECIAL_CHAR_ES) {
  $search3 = array(  //BOF - web28 - 2010-08-13 - Spanisch
            "'&(Aacute|#193);|Á'i",    //Großes A mit Akut
            "'&(aacute|#225);|á'i",    //Kleines a mit Akut
            "'&(Iacute|#205);|Í'i",    //Großes I mit Akut
            "'&(iacute|#227);|í'i",    //Kleines i mit Akut
            "'&(Ntilde|#209);|Ñ'i",    //Großes N mit Tilde
            "'&(ntilde|#241);|ñ'i",    //Kleines n mit Tilde
            "'&(Oacute|#211);|Ó'i",    //Großes O mit Akut
            "'&(oacute|#243);|ó'i",    //Kleines o mit Akut
            "'&(Uacute|#218);|Ú'i",    //Großes U mit Akut
            "'&(uacute|#250);|ú'i",    //Kleines u mit Akut
            "'&(ordf|#170);|ª'i",      //Weibliche Ordnungszahl
            "'&(ordm|#186);|º'i",      //männliche Ordnungszahl
            "'&(iexcl|#161);|¡'i",     //umgekehrtes Ausrufungszeichen
            "'&(iquest|#191);|¿'i",    //umgekehrtes Fragezeichen
            //EOF - web28 - 2010-08-13 - Spanisch
            //EOF - web28 - 2010-05-12 - Portugiesisch
            "'&(Atilde|#195);|Ã'i",    //Großes A mit Tilde
            "'&(atilde|#227);|ã'i",    //Kleines a mit Tilde
            "'&(Otilde|#213);|Õ'i",    //Großes O mit Tilde
            "'&(otilde|#245);|õ'i",    //Kleines o mit Tilde
            //BOF - web28 - 2010-05-12 - Portugiesisch
            //BOF - web28 - 2010-05-12 - Italienisch
            "'&(Igrave|#204);|Ì'i",    //Großes I mit Grave
            "'&(igrave|#236);|ì'i"     //Kleines i mit Grave
            //EOF - web28 - 2010-05-12 - Italienisch
            );

  $search = array_merge($search,$search3);
  }

    if (SPECIAL_CHAR_MORE) {
  $search4 = array(  //BOF - web28 - 2010-05-12 - Weitere Sonderzeichen
            "'&(Ograve|#210);|Ò'i",    //Großes O mit Grave
            "'&(ograve|#242);|ò'i",    //Kleines o mit Grave
            "'&(Ograve|#210);|Ò'i",    //Großes O mit Grave
            "'&(ograve|#242);|ò'i",    //Kleines o mit Grave
            "'&(Oslash|#216);|Ø'i",    //Großes O mit Schrägstrich
            "'&(oslash|#248);|ø'i",    //Kleines o mit Schrägstrich
            "'&(Aring|#197);|Å'i",     //Großes A mit Ring (Krouzek)
            "'&(aring|#229);|å'i",     //Kleines a mit Ring (Krouzek)
            "'&(Scaron|#352);|Š'i",    //Großes S mit Caron (Hatschek)
            "'&(scaron|#353);|š'i",    //Kleines s mit Caron (Hatschek)
            "'&(THORN|#222);|Þ'i",     //Großes Thorn (isländischer Buchstabe)
            "'&(thorn|#254);|þ'i",     //Kleines thorn (isländischer Buchstabe)
            "'&(divide|#247);|÷'i",    //Divisions-Zeichen ("Geteilt durch ...")
            "'&(times|#215);|×'i",     //Multiplikationszeichen; "Multipliziert mit ..."
            "'&(ETH|#272;)|Ð'i",       //Großes D mit Querstrich (isländischer Buchstabe)
            "'&(eth|#273;)|ð'i",       //Kleines d mit Querstrich (isländischer Buchstabe)
            "'&(Yacute|#221;)|Ý'i",    //Großes Y mit Akut
            "'&(yacute|#253;)|ý'i",    //Kleines y mit Akut
            "/Ž/",                     //--Großes Z mit Hatschek
            "/ž/"                      //--Kleines z mit Hatschek
            //EOF - web28 - 2010-05-12 - Weitere Sonderzeichen
            );

  $search = array_merge($search,$search4);
  //echo print_r($search);
  }

//*****************************************************************

  $replace    = array(
            "-",    //--Kaufmännisches Und mit Blanks
            "-",    // strip out white space
            "-",    //--Anführungszeichen oben
            "-",    //--Ampersand-Zeichen, kaufmännisches Und
            "-",    //--öffnende spitze Klammer
            "-",    //--schließende spitze Klammer
            "",      //--Erzwungenes Leerzeichen
            //BOF - web28 - 2010-04-16 - UTF-8 kompatibel +  Eingetragene Marke, Trademark, Eurozeichen
            "",     //chr(161), //umgekehrtes Ausrufezeichen
            "ct",     //chr(162), //Cent-Zeichen
            "GBP",     //chr(163), //Pfund-Zeichen
            "",     //chr(164), //Währungszeichen--currency
            "Yen",     //chr(165), //Yen-Zeichen
            "",      //chr(166),durchbrochener Strich
            "",      //chr(167),Paragraph-Zeichen
            "",      //chr(169),Copyright-Zeichen
            "",     //chr(174), //Eingetragene Marke
            "-GRAD-",   //chr(176), //Grad-Zeichen
            "",      //chr(177) Plusminus-Zeichen
            "",     //chr(178) Hoch-2-Zeichen
            "",     //chr(179) Hoch-3-Zeichen
            "",      //chr(180) Akut (accent aigu, acute) NICHT in iso-8859-15 enthalten
            "",     //chr(181) Mikro-Zeichen
            "-TM-",    //--Trademark wird zu -TM-
            "-EUR-",    //--Eurozeichen wird zu EUR
            "",      //chr(171) -- Left angle quotes Left Winkel Zitate
            "",      //chr(187) -- Right angle quotes Right Winkel Zitate
            //BOF - web28 - 2010-05-13 - Benannte Zeichen für Interpunktion
            "-",     //-- Gedankenstrich Breite n
            "-",     //-- Gedankenstrich Breite m
            "",     //-- einfaches Anführungszeichen links
            "",     //-- einfaches Anführungszeichen rechts
            "",     //-- einfaches low-9-Zeichen
            "",     //-- doppeltes Anführungszeichen links
            "",     //-- doppeltes Anführungszeichen rechts
            "",     //-- doppeltes low-9-Zeichen rechts
            //EOF - web28 - 2010-05-13 - Benannte Zeichen für Interpunktion
            //EOF - web28 - 2010-04-16 - UTF-8 kompatibel +  Eingetragene Marke, Trademark, Eurozeichen
            "-",    //--Kaufmännisches Und
            "-",    //--Prozent
                  "-",    //--öffnende Klammern
                  "",      //--schliessende Klammern
                  "ss",    //--Umlaute etc.
                  "ae",    //--Umlaute etc.
                  "ue",    //--Umlaute etc.
                  "oe",    //--Umlaute etc.
                  "Ae",    //--Umlaute etc.
                  "Ue",    //--Umlaute etc.
                  "Oe",    //--Umlaute etc.
            "",      //--Anführungszeichen
            "-"      //--Doppelpunkte, Komma, Punkt etc.
                        );

  if (SPECIAL_CHAR_FR) {
  $replace2 = array(  //BOF - web28 - 2010-05-12 - Französisch
            "A",    // Capital A-grave Capital A-Grab
            "a",    //Lowercase a-grave Kleinbuchstaben a-Grab
            "A",    //Capital A-circumflex Capital A-Zirkumflex
            "a",    //Lowercase a-circumflex Kleinbuchstaben a-Zirkumflex
            "AE",    //Capital AE Ligature Capital AE Ligature
            "ae",    //Lowercase AE Ligature Kleinbuchstabe ae
            "C",    //Capital C-cedilla Capital-C Cedille
            "c",    //Lowercase c-cedilla Kleinbuchstaben c-Cedille
            "E",    //Capital E-grave Capital E-Grab
            "e",    //Lowercase e-grave Kleinbuchstaben e-Grab
            "E",    //Capital E-acute E-Capital akuten
            "e",    //Lowercase e-acute Kleinbuchstaben e-acute
            "E",    //Capital E-circumflex E-Capital circumflexa
            "e",    //Lowercase e-circumflex Kleinbuchstaben e-Zirkumflex
            "E",    //Capital E-umlaut Capital E-Umlaut
            "e",    //Lowercase e-umlaut Kleinbuchstaben e-Umlaut
            "I",    //Capital I-circumflex Capital I-Zirkumflex
            "i",    //Lowercase i-circumflex Kleinbuchstaben i-Zirkumflex
            "I",    //Capital I-umlaut Capital I-Umlaut
            "i",    //Lowercase i-umlaut Kleinbuchstaben i-Umlaut
            "O",    //Capital O-circumflex O-Capital circumflexa
            "o",    //Lowercase o-circumflex Kleinbuchstabe o-Zirkumflex
            "OE",    //Capital OE ligature Capital OE Ligatur
            "oe",    //Lowercase oe ligature Kleinbuchstaben oe Ligatur
            "U",    //Capital U-grave Capital U-Grab
            "u",    //Lowercase u-grave Kleinbuchstaben u-Grab
            "U",    //Capital U-circumflex Capital U-Zirkumflex
            "u",    //Lowercase U-circumflex Kleinbuchstaben U-Zirkumflex
            "Y",    //Großes Y mit Diaeresis
            "y"      //Kleines y mit Diaeresis
            //EOF - web28 - 2010-05-12 - Französisch
            );

  $replace = array_merge($replace,$replace2);
  }

  if (SPECIAL_CHAR_ES) {
  $replace3 = array(  //BOF - web28 - 2010-08-13 - Spanisch
            "A",    //Großes A mit Akut
            "a",    //Kleines a mit Akut
            "I",    //Großes I mit Akut
            "i",    //Kleines i mit Akut
            "N",    //Großes N mit Tilde
            "n",    //Kleines n mit Tilde
            "O",    //Großes O mit Akut
            "o",    //Kleines o mit Akut
            "U",    //Großes U mit Akut
            "u",    //Kleines u mit Akut
            "",      //Weibliche Ordnungszahl
            "",      //männliche Ordnungszahl
            "",      //umgekehrtes Ausrufungszeichen
            "",      //umgekehrtes Fragezeichen
            //EOF - web28 - 2010-08-13 - Spanisch
            //EOF - web28 - 2010-08-13 - Portugiesisch
            "A",    //Großes A mit Tilde
            "a",    //Kleines a mit Tilde
            "O",    //Großes O mit Tilde
            "o",    //Kleines o mit Tilde
            //BOF - web28 - 2010-08-13 - Portugiesisch
            //BOF - web28 - 2010-08-13 - Italienisch
            "I",    //Großes I mit Grave
            "i"      //Kleines i mit Grave
            //EOF - web28 - 2010-08-13 - Italienisch
            );

  $replace = array_merge($replace,$replace3);
  }

    if (SPECIAL_CHAR_MORE) {
  $replace4 = array(  //BOF -web28 - 2010-09-16 - Weitere Sonderzeichen
            "O",    //Großes O mit Grave
            "o",    //Kleines o mit Grave
            "O",    //Großes O mit Grave
            "o",    //Kleines o mit Grave
            "O",    //Großes O mit Schrägstrich
            "o",    //Kleines o mit Schrägstrich
            "A",    //Großes A mit Ring (Krouzek)
            "a",    //Kleines a mit Ring (Krouzek)
            "S",    //Großes S mit Caron (Hatschek)
            "s",    //Kleines s mit Caron (Hatschek)
            "Th",    //Großes Thorn (isländischer Buchstabe)
            "th",    //Kleines thorn (isländischer Buchstabe)
            "-",    //Divisions-Zeichen ("Geteilt durch ...")
            "x",    //Multiplikationszeichen; "Multipliziert mit ..."
            "D",    //Großes D mit Querstrich (isländischer Buchstabe)
            "d",    //Kleines d mit Querstrich (isländischer Buchstabe)
            "Y",    //Großes Y mit Akut
            "y",    //Kleines y mit Akut
            "Z",      //--Großes Z mit Hatschek
            "z"        //--Kleines z mit Hatschek
            //EOF - web28 - 2010-09-16 - Weitere Sonderzeichen
            );

  $replace = array_merge($replace,$replace4);
  }

}
?>