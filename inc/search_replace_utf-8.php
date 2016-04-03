<?php
/*-----------------------------------------------------------------------
    $Id: search_replace_utf-8.php 2673 2012-02-23 13:06:49Z dokuman $

    Zeichenkodierung: UTF-8

   Version 1.06 rev.04 (c) by web28  - www.rpa-com.de
------------------------------------------------------------------------*/

function shopstat_getRegExps()
{

  $sr_array_default =
    array (
            "'\s&\s'"              => '-',        //--Kaufmännisches Und mit Blanks muss raus
            "'[\r\n\s]+'"          => '-',        // strip out white space
            "'&(quote|#34);'i"     => '-',        //--Anführungszeichen oben replace html entities
            "'&(amp|#38);'i"       => '-',        //--Ampersand-Zeichen => '', kaufmännisches Und
            "'&(lt|#60);'i"        => '-',        //--öffnende spitze Klammer
            "'&(gt|#62);'i"        => '-',        //--schließende spitze Klammer
            "'&(nbsp|#160);'i"     => '-',         //--Erzwungenes Leerzeichen
            "'&(iexcl|#161);|¡'i"  => '',         //umgekehrtes Ausrufezeichen
            "'&(cent|#162);|¢'i"   => 'ct',       //Cent-Zeichen
            "'&(pound|#163);|£'i"  => 'GBP',      //Pfund-Zeichen
            "'&(curren|#164);|¤'i" => '',         //Währungszeichen--currency
            "'&(yen|#165);|¥'i"    => 'Yen',      //Yen  wird zu Yen
            "'&(brvbar|#166);|¦'i" => '',         //durchbrochener Strich
            "'&(sect|#167);|§'i"   => '',         //Paragraph-Zeichen
            "'&(copy|#169);|©'i"   => '',         //Copyright-Zeichen
            "'&(shy|#173);'i"      => '',         //bedingter Trennstrich
            "'&(reg|#174);|®'i"    => '-R-',      //Eingetragene Marke wird zu -R-
            "'&(deg|#176);|°'i"    => '-GRAD-',   //Grad-Zeichen -- degree wird zu -Grad-
            "'&(plusmn|#177);|±'i" => '',         //Plusminus-Zeichen
            "'&(sup2|#178);|²'i"   => '',         //Hoch-2-Zeichen
            "'&(sup3|#179);|³'i"   => '',         //Hoch-3-Zeichen
            "'&(acute|#180);|´'i"  => '',         // Akut (accent aigu => '', acute) ### NICHT in iso-8859-15 enthalten ###
            "'&(micro|#181);|µ'i"  => '',         //Mikro-Zeichen
            "'&(trade|#8482);|™'i" => '-TM-',     //--Trademark wird zu -TM- ### NICHT in iso-8859-15 enthalten ###
            "'&(euro|#8364);|€'i"  => '-EUR-',    //--Eurozeichen wird zu EUR
            "'&(laquo|#171);|«'i"  => '',         //-- Left angle quotes Left Winkel Zitate
            "'&(raquo|#187);|»'i"  => '',         //--Right angle quotes Winkelgetriebe Zitate
            // Benannte Zeichen für Interpunktion
            "'&(ndash|#8211);|–'i" => '-',        //-- Gedankenstrich Breite n   ### NICHT in iso-8859-15 enthalten ###
            "'&(mdash|#8212);|—'i" => '-',        //-- Gedankenstrich Breite m   ### NICHT in iso-8859-15 enthalten ###
            "'&(lsquo|#8216);|‘'i" => '',         //-- einfaches Anführungszeichen links   ### NICHT in iso-8859-15 enthalten ###
            "'&(rsquo|#8217);|’'i" => '',         //-- einfaches Anführungszeichen rechts   ### NICHT in iso-8859-15 enthalten ###
            "'&(sbquo|#8218);|‚'i" => '',         //-- Einfaches => '', gekrümmtes Anführungszeichen unten ### NICHT in iso-8859-15 enthalten ###
            "'&(ldquo|#8220);|“'i" => '',         //-- doppeltes Anführungszeichen links ### NICHT in iso-8859-15 enthalten ###
            "'&(rdquo|#8221);|”'i" => '',         //-- doppeltes Anführungszeichen rechts ### NICHT in iso-8859-15 enthalten ###
            "'&(bdquo|#8222);|„'i" => '',         //-- Doppelte Anführungszeichen links unten ### NICHT in iso-8859-15 enthalten ###
            
            "'&#37;|%'i"           => '',         //--Prozent
            "/[\[\({]/"            => '',         //--öffnende Klammern nach Bindestriche
            "/[\)\]\}]/"           => '',         //--schliessende Klammern 
            "'&(szlig|#223);|ß'i"  => 'ss',       //--Umlaute etc.
            "'&(auml|#228);|ä'i"   => 'ae',       //--Umlaute etc.
            "'&(uuml|#252);|ü'i"   => 'ue',       //--Umlaute etc.
            "'&(ouml|#246);|ö'i"   => 'oe',       //--Umlaute etc.
            "'&(Auml|#196);|Ä'i"   => 'Ae',       //--Umlaute etc.
            "'&(Uuml|#220);|Ü'i"   => 'Ue',       //--Umlaute etc.
            "'&(Ouml|#214);|Ö'i"   => 'Oe',       //--Umlaute etc.
            "/'|\"|´|`/"           => '',         //--Anführungszeichen 
            "/[: => '',\.!?\*\+]/" => '-'         //--Doppelpunkte => '', Komma => '', Punkt etc. 
         );
         
  foreach ($sr_array_default as $sr => $rp ) {
      $search[] = $sr;
      $replace[] = $rp;
  }


  // Französisch
  if (SPECIAL_CHAR_FR) {
    $sr_array_fr = 
      array(  
            "'&(Agrave|#192);|À'i" => 'A',        // Capital A-grave Capital A-Grab
            "'&(agrave|#224);|à'i" => 'a',        //Lowercase a-grave Kleinbuchstaben a-Grab
            "'&(Acirc|#194);|Â'i"  => 'A',        //Capital A-circumflex Capital A-Zirkumflex
            "'&(acirc|#226);|â'i"  => 'a',        //Lowercase a-circumflex Kleinbuchstaben a-Zirkumflex
            "'&(AElig|#198);|Æ'i"  => 'AE',       //Capital AE Ligature Capital AE Ligature
            "'&(aelig|#230);|æ'i"  => 'ae',       //Lowercase AE Ligature Kleinbuchstabe ae
            "'&(Ccedil|#199);|Ç'i" => 'C',        //Capital C-cedilla Capital-C Cedille
            "'&(ccedil|#231);|ç'i" => 'c',        //Lowercase c-cedilla Kleinbuchstaben c-Cedille
            "'&(Egrave|#200);|È'i" => 'E',        //Capital E-grave Capital E-Grab
            "'&(egrave|#232);|è'i" => 'e',        //Lowercase e-grave Kleinbuchstaben e-Grab
            "'&(Eacute|#201);|É'i" => 'E',        //Capital E-acute E-Capital akuten
            "'&(eacute|#233);|é'i" => 'e',        //Lowercase e-acute Kleinbuchstaben e-acute
            "'&(Ecirc|#202);|Ê'i"  => 'E',        //Capital E-circumflex E-Capital circumflexa
            "'&(ecirc|#234);|ê'i"  => 'e',        //Lowercase e-circumflex Kleinbuchstaben e-Zirkumflex
            "'&(Euml|#203);|Ë'i"   => 'E',        //Capital E-umlaut Capital E-Umlaut
            "'&(euml|#235);|ë'i"   => 'e',        //Lowercase e-umlaut Kleinbuchstaben e-Umlaut
            "'&(Icirc|#206);|Î'i"  => 'I',        //Capital I-circumflex Capital I-Zirkumflex
            "'&(icirc|#238);|î'i"  => 'i',        //Lowercase i-circumflex Kleinbuchstaben i-Zirkumflex
            "'&(Iuml|#207);|Ï'i"   => 'I',        //Capital I-umlaut Capital I-Umlaut
            "'&(iuml|#239);|ï'i"   => 'i',        //Lowercase i-umlaut Kleinbuchstaben i-Umlaut
            "'&(Ocirc|#212);|Ô'i"  => 'O',        //Capital O-circumflex O-Capital circumflexa
            "'&(ocirc|#244);|ô'i"  => 'o',        //Lowercase o-circumflex Kleinbuchstabe o-Zirkumflex
            "'&(OElig|#338);|Œ'i"  => 'OE',       //Capital OE ligature Capital OE Ligatur
            "'&(oelig|#339);|œ'i"  => 'oe',       //Lowercase oe ligature Kleinbuchstaben oe Ligatur
            "'&(Ugrave|#217);|Ù'i" => 'U',        //Capital U-grave Capital U-Grab
            "'&(ugrave|#249);|ù'i" => 'u',        //Lowercase u-grave Kleinbuchstaben u-Grab
            "'&(Ucirc|#219);|Û'i"  => 'U',        //Capital U-circumflex Capital U-Zirkumflex
            "'&(ucirc|#251);|û'i"  => 'u',        //Lowercase U-circumflex Kleinbuchstaben U-Zirkumflex
            "'&(Yuml|#376);|Ÿ'i"   => 'Y',        //Großes Y mit Diaeresis
            "'&(yuml|#255);|ÿ'i"   => 'y'         //Kleines y mit Diaeresis
            );
    foreach ($sr_array_fr as $sr => $rp ) {
        $search[] = $sr;
        $replace[] = $rp;
    }
  }
  
  //Spanisch
  if (SPECIAL_CHAR_ES) {
    $sr_array_es =
      array(  
            // Spanisch
            "'&(Aacute|#193);|Á'i" => 'A',        //Großes A mit Akut
            "'&(aacute|#225);|á'i" => 'a',        //Kleines a mit Akut
            "'&(Iacute|#205);|Í'i" => 'I',        //Großes I mit Akut
            "'&(iacute|#227);|í'i" => 'i',        //Kleines i mit Akut
            "'&(Ntilde|#209);|Ñ'i" => 'N',        //Großes N mit Tilde
            "'&(ntilde|#241);|ñ'i" => 'n',        //Kleines n mit Tilde
            "'&(Oacute|#211);|Ó'i" => 'O',        //Großes O mit Akut
            "'&(oacute|#243);|ó'i" => 'o',        //Kleines o mit Akut
            "'&(Uacute|#218);|Ú'i" => 'U',        //Großes U mit Akut
            "'&(uacute|#250);|ú'i" => 'u',        //Kleines u mit Akut
            "'&(ordf|#170);|ª'i"   => '',         //Weibliche Ordnungszahl
            "'&(ordm|#186);|º'i"   => '',         //männliche Ordnungszahl
            "'&(iexcl|#161);|¡'i"  => '',         //umgekehrtes Ausrufungszeichen
            "'&(iquest|#191);|¿'i" => '',         //umgekehrtes Fragezeichen
            
            // Portugiesisch
            "'&(Atilde|#195);|Ã'i" => 'A',        //Großes A mit Tilde
            "'&(atilde|#227);|ã'i" => 'a',        //Kleines a mit Tilde
            "'&(Otilde|#213);|Õ'i" => 'O',        //Großes O mit Tilde
            "'&(otilde|#245);|õ'i" => 'o',        //Kleines o mit Tilde
            
            //Italienisch
            "'&(Igrave|#204);|Ì'i" => 'I',        //Großes I mit Grave
            "'&(igrave|#236);|ì'i" => 'i'         //Kleines i mit Grave
            );
    foreach ($sr_array_es as $sr => $rp ) {
        $search[] = $sr;
        $replace[] = $rp;
    }
  }
  //Weitere Sonderzeichen
  if (SPECIAL_CHAR_MORE) {
    $sr_array_mo = 
      array(  
            "'&(Ograve|#210);|Ò'i" => 'O',        //Großes O mit Grave
            "'&(ograve|#242);|ò'i" => 'o',        //Kleines o mit Grave
            "'&(Ograve|#210);|Ò'i" => 'O',        //Großes O mit Grave
            "'&(ograve|#242);|ò'i" => 'o',        //Kleines o mit Grave
            "'&(Oslash|#216);|Ø'i" => 'O',        //Großes O mit Schrägstrich
            "'&(oslash|#248);|ø'i" => 'o',        //Kleines o mit Schrägstrich
            "'&(Aring|#197);|Å'i"  => 'A',        //Großes A mit Ring (Krouzek)
            "'&(aring|#229);|å'i"  => 'a',        //Kleines a mit Ring (Krouzek)
            "'&(Scaron|#352);|Š'i" => 'S',        //Großes S mit Caron (Hatschek)
            "'&(scaron|#353);|š'i" => 's',        //Kleines s mit Caron (Hatschek)
            "'&(THORN|#222);|Þ'i"  => 'Th',       //Großes Thorn (isländischer Buchstabe)
            "'&(thorn|#254);|þ'i"  => 'th',       //Kleines thorn (isländischer Buchstabe)
            "'&(divide|#247);|÷'i" => '-',        //Divisions-Zeichen ("Geteilt durch ...")
            "'&(times|#215);|×'i"  => 'x',        //Multiplikationszeichen; "Multipliziert mit ..."
            "'&(ETH|#272;)|Ð'i"    => 'D',        //Großes D mit Querstrich (isländischer Buchstabe)
            "'&(eth|#273;)|ð'i"    => 'd',        //Kleines d mit Querstrich (isländischer Buchstabe)
            "'&(Yacute|#221;)|Ý'i" => 'Y',        //Großes Y mit Akut
            "'&(yacute|#253;)|ý'i" => 'y',        //Kleines y mit Akut
            "'&#381;|Ž'i"          => 'Z',        //--Großes Z mit Hatschek
            "'&#382;|ž'i"          => 'z',        //--Kleines z mit Hatschek

            //Benannte Zeichen für Pfeil-Symbole
            "'&(larr|#8592);|←'i"   => '-',       //--Pfeil links
            "'&(uarr|#8593);|↑'i"   => '-',       //--Pfeil oben
            "'&(rarr|#8594);|→'i"   => '-',       //--Pfeil rechts
            "'&(darr|#8595);|↓'i"   => '-',       //--Pfeil unten
            "'&(harr|#8596);|↔'i"   => '-',       //--Pfeil links/rechts
            "'&(crarr|#8629);'i"    => '-',       //--Pfeil links/rechts
            "'&(lAarr|#8656);'i"    => '-',       //--Pfeil links/rechts
            "'&(uAarr|#8657);'i"    => '-',       //--Pfeil links/rechts
            "'&(rArr|#8658);'i"     => '-',       //--Pfeil links/rechts
            "'&(dArr|#8659);'i"     => '-',       //--Pfeil links/rechts
            "'&(hArr|#8660);'i"     => '-'        //--Pfeil links/rechts
            );
    foreach ($sr_array_mo as $sr => $rp ) {
        $search[] = $sr;
        $replace[] = $rp;
    }
  }
  
  // Polnische Sonderzeichen
  if (SPECIAL_CHAR_PL) {
    $sr_array_pl = 
      array(
            "'&#260;|Ą'i"           => 'A',
            "'&#261;|ą'i"           => 'a',
            "'&#280;|Ę'i"           => 'E',
            "'&#281;|ę'i"           => 'e',
            //"'&(Oacute|#211);|Ó'i"  => 'O', 
            //"'&(oacute|#243);|ó'i"  => 'o',
            "'&#262;|Ć'i"           => 'C',
            "'&#263;|ć'i"           => 'c',
            "'&#321;|Ł'i"           => 'T',
            "'&#322;|ł'i"           => 't',
            "'&#323;|Ń'i"           => 'N',
            "'&#324;|ń'i"           => 'n',
            "'&#346;|Ś'i"           => 'S',
            "'&#347;|ś'i"           => 's',
            "'&#377;|Ź'i"           => 'Z',
            "'&#378;|ź'i"           => 'z',
            "'&#379;|Ż'i"           => 'Z',
            "'&#380;|ż'i"           => 'z'
           ); 
    foreach ($sr_array_pl as $sr => $rp ) {
        $search[] = $sr;
        $replace[] = $rp;
    }
  }
  
  // Tschechische  Sonderzeichen
  if (SPECIAL_CHAR_CZ) {
    $sr_array_cz = 
      array(
            "'&#268;|Č'i"           => 'C',     //Großes C mit Caron (Hatschek)
            "'&#269;|č'i"           => 'c',     //Kleines c mit Caron (Hatschek)
            "'&#270;|Ď'i"           => 'D',     //Großes D mit Caron (Hatschek)
            "'&#171;|ď'i"           => 'd',     //Kleines d mit Caron (Hatschek)
            "'&#282;|Ě'i"           => 'E',     //Großes E mit Caron (Hatschek)
            "'&#283;|ě'i"           => 'e',     //Kleines e mit Caron (Hatschek)
            "'&#327;|Ň'i"           => 'N',     //Großes N mit Caron (Hatschek)
            "'&#328;|ň'i"           => 'n',     //Kleines n mit Caron (Hatschek)
            "'&#344;|Ř'i"           => 'R',     //Großes R mit Caron (Hatschek)
            "'&#345;|ř'i"           => 'r',     //Kleines r mit Caron (Hatschek)
            "'&#346;|Ť'i"           => 'T',     //Großes T mit Caron (Hatschek)
            "'&#347;|ť'i"           => 't',     //Kleines t mit Caron (Hatschek)
            "'&#366;|Ů'i"           => 'U',     //Großes U mit Ring (Krouzek) darüber
            "'&#367;|ů'i"           => 'u',     //Kleines u mit Ring (Krouzek) darüber
           ); 
    foreach ($sr_array_cz as $sr => $rp ) {
        $search[] = $sr;
        $replace[] = $rp;
    }
  }
  
  return array($search, $replace);
}
?>