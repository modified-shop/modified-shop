<?php
/* -----------------------------------------------------------------------------------------
   $Id: banktransfer_validation.php 4434 2013-02-11 09:05:02Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banktransfer_validation.php,v 1.17 2003/02/18 18:33:15); www.oscommerce.com
   (c) 2003	 nextcommerce (banktransfer_validation.php,v 1.4 2003/08/1); www.nextcommerce.org
   (c) 2004 - 2006 fmce.de
   (c) 2004 - 2006 discus24.de
   (c) 2006 xt:Commerce
   (c) 2004 - 2010 Frank Maroke
   (c) 2010-2011 Nico Sommer
   (c) 2011-2012 Christian Rothe

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   OSC German Banktransfer v0.85a       	Autor:	Dominik Guder <osc@guder.org>
   Extensioncode: 							Marcel Bossert-Schwab <info@opensourcecommerce.de> (mbs)
   New methods 2005 - 2010: 				Frank Maroke (FrankM) <info@fmce.de>
   New methods and bug fixes 2010 - 2013:	Christian Rothe <buero@laufstar.de>

	 BLZ-Downloadseite der Deutschen Bundesbank:
	 http://www.bundesbank.de/Redaktion/DE/Standardartikel/Kerngeschaeftsfelder/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html

   Aktuelle Beschreibung der Pruefverfahren:
   http://www.bundesbank.de/Navigation/DE/Kerngeschaeftsfelder/Unbarer_Zahlungsverkehr/Pruefzifferberechnung/pruefzifferberechnung.html
   
   Stand dieses Klassen-Moduls: 9. Dezember 2013 
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


class AccountCheck {

/* Folgende Returncodes werden übergeben                                        */
/*                                                                              */
/* 0 -> Kontonummer & BLZ OK                                                    */
/* 1 -> Kontonummer & BLZ passen nicht                                          */
/* 2 -> Für diese Kontonummer ist kein Prüfziffernverfahren definiert           */
/* 3 -> Dieses Prüfziffernverfahren ist noch nicht implementiert                */
/* 4 -> Diese Kontonummer ist technisch nicht prüfbar                           */
/* 5 -> BLZ nicht gefunden                                                      */
/* 8 -> Keine BLZ übergeben                                                     */
/* 9 -> Keine Kontonummer übergeben                                             */
/* 10 -> Kein Kontoinhaber übergeben                                            */
/* 128 -> interner Fehler, der zeigt, dass eine Methode nicht implementiert ist */
/*                                                                              */

	function __construct() {
		$this->banktransfer_number = ''; // Enthält nach der Prüfung die geprüfte Kontonummer
		$this->banktransfer_blz = ''; // Enthält nach der Prüfung die geprüfte BLZ
		$this->Bankname = ''; // Enthält nach der Prüfung den Namen der Bank bei der Suche nach BLZ
		$this->PRZ = ''; //Enthält nach der Prüfung die Prüfziffer
  	$this->checkmode = 'classic'; // 
	}

var $Bankname; // Enthält den Namen der Bank bei der Suche nach BLZ
var $PRZ; //Enthält die Prüfziffer

////
// Diese Funktion gibt die Bankinformationen aus der csv-Datei zurück*/
  function csv_query($blz) {
    $cdata = -1;
    $fp = fopen(DIR_WS_INCLUDES . 'data/blz.csv', 'r');
    while ($data = fgetcsv($fp, 1024, ";")) {
      if ($data[0] == $blz){
        $cdata = array ('blz' => $data[0],
                        'bankname' => $data[1],
                        'prz' => $data[2]);
      }
    }
    return $cdata;
  }

////
// Diese Funktion gibt die Bankinformationen aus der Datenbank zurück*/
  function db_query($blz) {
    $blz_query = xtc_db_query("SELECT * from ".TABLE_BANKTRANSFER_BLZ." WHERE blz = '" . xtc_db_input($blz) . "'");
    if (xtc_db_num_rows($blz_query)){
      $data = xtc_db_fetch_array ($blz_query);
    }else
      $data = -1;
    return $data;
  }

////
// Diese Funktion gibt die Bankinformationen aus der Datenbank zurück*/
  function query($blz) {
    if (MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ == 'true' && defined(MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ))
      $data = $this->db_query($blz);
    else
      $data = $this->csv_query($blz);
    return $data;
  }

////
  // Diese Funktion gibt die Einer einer Zahl zwischen 0 und 99 zurueck.
  private function OnlyOne($Digit) {
    return $Digit = $Digit % 10;
  }  /* End of OnlyOne */

  // Diese Funktion berechnet die Quersumme einer Zahl zwischen 0 und 99.
  private function CrossSum($Digit) {
    $CrossSum = $Digit;
    if ($Digit > 9) {
      $Help1 = substr($Digit,0,1);
      $Help2 = substr($Digit,1,1);
      $CrossSum = (int) $Help1 + (int) $Help2;
    }
    return $CrossSum;
  }  /* End of CrossSum */

  // Auffüllen der Konto-Nr. mit '0' auf 10 Stellen.
  public function ExpandAccount($AccountNo) {
    $AccountNo = str_pad($AccountNo, 10, "0", STR_PAD_LEFT);

    while (strlen($AccountNo) > 10) {
      $AccountNo = substr($AccountNo, 1);
    }
    return $AccountNo;
  }  /* End of ExpandAccount */

  // Erweiterte ExpandAccount fuer Methode C5:
  // Entfernt die führenden Nullen einer Kontonummer
  // und gibt den Integer zusammen mit der Laenge zurueck.
  private function ExpandAccountExtended($AccountNo) {
    $AccountNoLong        = $this->ExpandAccount($AccountNo);
    $AccountNoShort       = ltrim($AccountNoLong, "0");
    $AccountNoShortLen    = strlen($AccountNoShort);
    $aAccountNo = array(
      'AccountNoLong'     => $AccountNoLong,
      'AccountNoShort'    => $AccountNoShort,
      'AccountNoShortLen' => $AccountNoShortLen,
    );
    return $aAccountNo;
  }  /* End of ExpandAccountExtended */


  /* --- Changed FrankM 20061206, 20070822, 20080717, 20100602 --- */
  /* --- Changed Christian Rothe 20110606 --- */
  private function Method00($AccountNo, $Significance, $Checkpoint, $Modulator=10, $LeaveCheckpoint=0, $DoNotExpand=0, $ChecksumCalcMethod = '') {
    $Help = 0;
    $Method00 = 1;

    // Methoden der Bundesbank C6 und D1 uebergeben die finale Kontonummer, $DoNotExpand = 1.
    if ($DoNotExpand == 0) {
      $AccountNo = $this->ExpandAccount($AccountNo);
    }

    // Pruefziffer ermitteln..
    $PNumber = substr($AccountNo, $Checkpoint-1, 1);

    // Sonderfall Methoden der Bundesbank C6 und D1, zur Pruefung letzte Stelle entfernen.
    if ($Checkpoint == 16) {
      $AccountNo = substr($AccountNo, 0, -1);
    }

    if ($LeaveCheckpoint == 0) {
      for ($Run = 0; $Run < strlen($Significance); $Run++) {
        $Help += $this->CrossSum(substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
      }
    // Sonderfall fuer Methode 57
    }else{
      $HelpFirst = substr($AccountNo,0,2);
      $HelpCheckP = substr($AccountNo,($Checkpoint - 1),1);
      $HelpSecond = substr($AccountNo,3,7);
      $AccountNo = $HelpFirst . $HelpSecond . $HelpCheckP;
      for ($Run = 0; $Run < strlen($Significance); $Run++) {
        $Help += $this->CrossSum(substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
      }
    }

    // Sonderfall fuer Methode E0
    if ($ChecksumCalcMethod == 'E0') {
      $Help += 7;
    }

    $Help = $Help % $Modulator;

    if ($ChecksumCalcMethod == 'D7') {
      $Checksum = $Help;
    } else {
      $Checksum = $Modulator - $Help;
    }

    if ($Checksum == $Modulator) {
      $Checksum = 0;
    }

    if ($Checksum == $PNumber) {
      $Method00 = 0;
    }

    return $Method00;
  }  /* End of Method00 */


  private function Method01($AccountNo, $Significance) {
    $Help = 0;
     $Method01 = 1;
     $AccountNo = $this->ExpandAccount($AccountNo);
     for ($Run = 0; $Run < strlen($Significance); $Run++) {
       $Help += substr($AccountNo,$Run,1) * substr($Significance,$Run,1);
     }
     $Help = $this->OnlyOne($Help);
     $Checksum = 10 - $Help;

     if ($Checksum == 10) {
       $Checksum = 0;
     }
     if ($Checksum == substr($AccountNo,-1)) {
       $Method01 = 0;
     }
     return $Method01;
  }  /* End of Method01 */


  private function Method02($AccountNo , $Significance, $Modified) {
    $Help = 0;
     $Method02 = 1;
     $AccountNo = $this->ExpandAccount($AccountNo);
     switch ($Modified) {
       case FALSE :
        for ($Run = 0;$Run < strlen($Significance);$Run++) {
          $Help += substr($AccountNo,$Run,1) * substr($Significance,$Run,1);
        }
        break;
      case TRUE :
        for ($Run = 0;$Run < strlen($Significance);$Run++) {
          $Help += substr($AccountNo,$Run,1) * HexDec(substr($Significance,$Run,1));
        }
        break;
    }
    $Help = $Help % 11;
    if ($Help == 0) {
      $Help = 11;
    }
    if ($Help <> 1) {
      $Checksum = 11 - $Help;
      if ($Checksum == substr($AccountNo,-1)) {
        $Method02 = 0;
      }
    }
     return $Method02;
  }  /* End of Method02 */

  /* --- Hotfix FrankM 20081208 --- */
  private function Method06($AccountNo, $Significance, $Modified, $Checkpoint, $Modulator) {
    $Help = 0;
    $Method06 = 1;
     $AccountNo = $this->ExpandAccount($AccountNo);
     switch ($Modified) {
       case FALSE :
        for ($Run = 0; $Run < strlen($Significance);$Run++) {
          $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
        }
        break;
      case TRUE  :
        for ($Run = 0; $Run < strlen($Significance);$Run++) {
          $Help += (substr($AccountNo,$Run,1) * HexDec(substr($Significance,$Run,1)));
        }
        break;
    }
    $Help = $Help % $Modulator;
    $Checksum = $Modulator - $Help;
    // Bedingung bei Modulator 7
    /* --- Fix by Christian Rothe 20110327 --- */
    if ($Help == 0 && $Modulator == 7) {
      $Checksum = 0;
    }
    // Bedingung bei Modulator 11
    if ($Help < 2 && $Modulator == 11) {
      $Checksum = 0;
    }
    if ($Checksum == substr($AccountNo,$Checkpoint-1,1)) {
      $Method06 = 0;
    }
    return $Method06;
  }  /* End of Method06 */

  private function Method16($AccountNo , $Significance, $Checkpoint) {
    $Help = 0;
    $Method16 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % 11;
    $Checksum = 11 - $Help;
    if ($Help == 0) {
      $Checksum = 0;
    }
    if ($Checksum == substr($AccountNo,$Checkpoint-1,1)) {
       $Method16 = 0;
     }
     if ($Help == 1) {
       if ($Checksum == substr($AccountNo,Checkpoint - 2,1)) {
         $Method16 = 0;
       }
     }
     return $Method16;
   }  /* End of Method16 */

  private function Method90($AccountNo , $Significance ,$Checkpoint, $Modulator) {
    $Help = 0;
    $Method90 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % $Modulator;
    $Checksum = $Modulator - $Help;
    if ($Help == 0) {
      $Checksum = 0;
    }
    if ($Checksum == substr($AccountNo,$Checkpoint -1, 1)) {
      $Method90 = 0;
    }
    return $Method90;
  } /* End of Method90 */


  /* ----- Endgueltige Funktionen der einzelnen Berechnungsmethoden. ---- */

  private function Mark00($AccountNo) {
    $Mark00 = $this->Method00($AccountNo, '212121212', 10);
    return $Mark00;
  }  /* End of Mark00 */

  private function Mark01($AccountNo) {
    $Mark01 = $this->Method01($AccountNo, '173173173');
    return $Mark01;
  }  /* End of Mark01 */

  private function Mark02($AccountNo) {
    $Mark02 = $this->Method02($AccountNo, '298765432', FALSE);
    return $Mark02;
  }  /* End of Mark02 */

  private function Mark03($AccountNo) {
    $Mark03 = $this->Method01($AccountNo, '212121212');
    return $Mark03;
  }  /* End of Mark03 */

  private function Mark04($AccountNo) {
    $Mark04 = $this->Method02($AccountNo, '432765432', FALSE);
    return $Mark04;
  }  /* End of Mark04 */

  private function Mark05($AccountNo) {
    $Mark05 = $this->Method01($AccountNo, '137137137');
    return $Mark05;
  }  /* End of Mark05 */

  private function Mark06($AccountNo) {
    $Mark06 = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);
    return $Mark06;
  }  /* End of Mark06 */

  private function Mark07($AccountNo) {
    $Mark07 = $this->Method02($AccountNo, 'A98765432', TRUE);
    return $Mark07;
  }  /* End of Mark07 */

  private function Mark08($AccountNo) {
    $Mark08 = 1;
    if ($AccountNo > 60000) {
      $Mark08 = $this->Method00($AccountNo, '212121212', 10);
    }
    return $Mark08;
  }  /* End of Mark08 */

  // Kein Pruefziffernverfahren vorhanden.
  // Kontonummer ist aktuell bei der Implementierung immer als RICHTIG zu beurteilen.
  private function Mark09($AccountNo) {
    $Mark09 = 2;
    return $Mark09;
  }  /* End of Mark09 */

  private function Mark10($AccountNo) {
    $Mark10 = $this->Method06($AccountNo, 'A98765432', TRUE, 10, 11);
    return $Mark10;
  }  /* End of Mark10 */

  private function Mark11($AccountNo) {
    $Significance = 'A98765432';
    $Help = 0;
    $Mark11 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * HexDec(substr($Significance,$Run,1)));
    }
    $Help = $Help % 11;
    $Checksum = 11 - $Help;
    if ($Help == 0) {
      $Checksum = 0;
    }
    if ($Help == 1) {
      $Checksum = 9;
    }
    if ($Checksum == substr($AccountNo,-1)) {
      $Mark11 = 0;
    }
    return $Mark11;
  }  /* End of Mark11 */

  private function Mark12($AccountNo) {
    $Mark12 = $this->Method01($AccountNo, '731731731');
    return $Mark12;
  }  /* End of Mark12 */

  private function Mark13($AccountNo) {
    $Help = $this->Method00($AccountNo, '0121212', 8);
    if ($Help == 1) {
      if (Substr($AccountNo,-2) <> '00') {
        $Help = $this->Method00(substr($this->ExpandAccount($AccountNo), 2) . '00', '0121212', 8);
      }
    }
    $Mark13 = $Help;
    return $Mark13;
  }  /* End of Mark13 */

  private function Mark14($AccountNo) {
    $Mark14 = $this->Method02($AccountNo, '000765432', FALSE);
    return $Mark14;
  }  /* End of Mark14 */

  private function Mark15($AccountNo) {
    $Mark15 = $this->Method06($AccountNo, '000005432', FALSE, 10, 11);
    return $Mark15;
  }  /* End of Mark15 */

  private function Mark16($AccountNo) {
    $Mark16 = $this->Method16($AccountNo, '432765432', 10);
    return $Mark16;
  }  /* End of Mark16 */

  private function Mark17($AccountNo) {
    $Significance = '0121212';
    $Help = 0;
    $Help2 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += ($this->CrossSum(substr($AccountNo,$Run,1) * substr($Significance,$Run,1)));
    }
    $Help = $Help -1;
    $Checksum = $Help % 11;
    $Checksum = 10 - $Checksum;
    if ($Checksum == 10) {
      $Checksum = 0;
    }
    if ($Checksum == substr($AccountNo,7,1)) {
      $Help2 = 0;
    }
    $Mark17 = $Help2;
    return $Mark17;
  }  /* End of Mark17 */

  private function Mark18($AccountNo) {
    $Mark18 = $this->Method01($AccountNo, '317931793');
    return $Mark18;
  }  /* End of Mark18 */

  private function Mark19($AccountNo) {
    $Mark19 = $this->Method06($AccountNo, '198765432', FALSE, 10, 11);
    return $Mark19;
  }  /* End of Mark19 */

  private function Mark20($AccountNo) {
    $Mark20 = $this->Method06($AccountNo, '398765432', FALSE, 10, 11);

    return $Mark20;
  }  /* End of Mark20 */

  // --- Fix FrankM 20080717 ---
  private function Mark21($AccountNo) {
    // Initialisierung
    $Significance = '212121212';
    $Help = 0;
    $Mark21 = 1;
    // Kontonummer auf zehn Stellen auffuellen.
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Quersumme aus Produkten bilden.
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += $this->CrossSum(substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    // Daraus erneut Quersumme bilden bis diese einstellig ist.
    while (strlen($Help) > 1){
      $Help = $this->CrossSum($Help);
    }
    // Quersumme von 10 subtrahieren = Pruefziffer
    $Checksum = 10 - $Help;
    if ($Checksum == substr($AccountNo,-1)) {
      $Mark21 = 0;
    }
    return $Mark21;
  }  /* End of Mark21 */

  private function Mark22($AccountNo) {
    $Significance = '313131313';
    $Help = 0;
    $Mark22 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Zwischenwert = (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
       $Help += $this->OnlyOne($Zwischenwert);
     }
     $Checksum = ceil($Help/10)*10 - $Help;
     if ($Checksum == substr($AccountNo,-1)) {
      $Mark22 = 0;
    }
    return $Mark22;
  }  /* End of Mark22 */

  private function Mark23($AccountNo) {
    $Mark23 = $this->Method16($AccountNo, '765432', 7);
    return $Mark23;
  }  /* End of Mark23 */

  private function Mark24($AccountNo) {
    $Significance = '123123123';
    $Help = 0;
    $Mark24 = 1;
    switch (substr($AccountNo,0,1)) {
      case 3 :
      case 4 :
      case 5 :
      case 6 :
        // deaktiviert, da die Postbank diese Definition nicht einhaelt.
        //$AccountNo = Substr($AccountNo,1);
        break;
      case 9 :
      //  $AccountNo = SubStr($AccountNo,3);
        break;
    }
    while (substr($AccountNo,0,1)==0){
      $AccountNo = substr($AccountNo,1);
    }

    for ($Run = 0;$Run < strlen($AccountNo)-1;$Run++ ) {
      $ZwischenHilf = substr($AccountNo,$Run,1) * substr($Significance,$Run,1) + substr($Significance,$Run,1);
      $Help += $ZwischenHilf % 11;
    }

    $Checksum = $this->OnlyOne($Help);

    if ($Checksum == substr($AccountNo,-1)) {
      $Mark24 = 0;
    }
    return $Mark24;
  }  /* End of Mark24 */

  private function Mark25($AccountNo) {
    $Significance = '098765432';
    $Falsch = FALSE;
    $Help = 0;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % 11;
    $Checksum = 11 - $Help;
    if ($Checksum == 11) {
      $Checksum = 0;
    }
    if ($Checksum == 10) {
      $Checksum = 0;
      if ((substr($AccountNo,1,1) <> '8') and (substr($AccountNo,1,1) <> '9')) {
        $Mark25 = 1;
        $Falsch = TRUE;
      }
    }
    if ($Falsch == FALSE) {
      if ($Checksum == substr($AccountNo,-1)) {
        $Mark25 = 0;
      } else {
        $Mark25 = 1;
      }
    }
    return $Mark25;
  }  /* End of Mark25 */

  private function Mark26($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (Substr($AccountNo,-2) == '00') {
      $AccountNo = Substr($AccountNo,2) . '00';
    }
    $Mark26 = $this->Method06($AccountNo,'2345672',FALSE,8,11);
    return $Mark26;
  }  /* End of Mark26 */

  private function Mark27($AccountNo) {
    if ((int)$AccountNo <= 999999999.) {
      $Mark27 = $this->Method00($AccountNo, '212121212', 10);
    } else {
      $Mark27 = $this->Mark29($AccountNo);
    }
    return $Mark27;
  }  /* End of Mark27 */

  private function Mark28($AccountNo) {
    $Mark28 = $this->Method06($AccountNo, '8765432', FALSE, 8, 11);
    return $Mark28;
  }  /* End of Mark28 */

  /* --- Hotfix FrankM 20081208 --- */
  /* --- Fix Christian Rothe 20110606 --- */
  private function Mark29($AccountNo) {
    $Transform = '143214321';
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Help = 0;
    for ($Run = 0;$Run < strlen($Transform);$Run++) {
      $ToAdd = 0;
      switch (substr($Transform,$Run,1)) {
        case '1' :
          switch (substr($AccountNo,$Run,1)) {
            Case '0' :
              $ToAdd = 0;
              break;
            Case '1' :
              $ToAdd = 1;
              break;
            Case '2' :
              $ToAdd = 5;
              break;
            Case '3' :
              $ToAdd = 9;
              break;
            Case '4' :
              $ToAdd = 3;
              break;
            Case '5' :
              $ToAdd = 7;
              break;
            Case '6' :
              $ToAdd = 4;
              break;
            Case '7' :
              $ToAdd = 8;
              break;
            Case '8' :
              $ToAdd = 2;
              break;
            Case '9' :
              $ToAdd = 6;
              break;
          }
          break;
        case '2' :
          switch (substr($AccountNo,$Run,1)) {
            Case '0' :
              $ToAdd = 0;
              break;
            Case '1' :
              $ToAdd = 1;
              break;
            Case '2' :
              $ToAdd = 7;
              break;
            Case '3' :
              $ToAdd = 6;
              break;
            Case '4' :
              $ToAdd = 9;
              break;
            Case '5' :
              $ToAdd = 8;
              break;
            Case '6' :
              $ToAdd = 3;
              break;
            Case '7' :
              $ToAdd = 2;
              break;
            Case '8' :
              $ToAdd = 5;
              break;
            Case '9' :
              $ToAdd = 4;
              break;
          }
          break;
        case '3' :
          switch (substr($AccountNo,$Run,1)) {
            Case '0' :
              $ToAdd = 0;
              break;
            Case '1' :
              $ToAdd = 1;
              break;
            Case '2' :
              $ToAdd = 8;
              break;
            Case '3' :
              $ToAdd = 4;
              break;
            Case '4' :
              $ToAdd = 6;
              break;
            Case '5' :
              $ToAdd = 2;
              break;
            Case '6' :
              $ToAdd = 9;
              break;
            Case '7' :
              $ToAdd = 5;
              break;
            Case '8' :
              $ToAdd = 7;
              break;
            Case '9' :
              $ToAdd = 3;
              break;
          }
          break;
        case '4' :
          switch (substr($AccountNo,$Run,1)) {
            Case '0' :
              $ToAdd = 0;
              break;
            Case '1' :
              $ToAdd = 1;
              break;
            Case '2' :
              $ToAdd = 2;
              break;
            Case '3' :
              $ToAdd = 3;
              break;
            Case '4' :
              $ToAdd = 4;
              break;
            Case '5' :
              $ToAdd = 5;
              break;
            Case '6' :
              $ToAdd = 6;
              break;
            Case '7' :
              $ToAdd = 7;
              break;
            Case '8' :
              $ToAdd = 8;
              break;
            Case '9' :
              $ToAdd = 9;
              break;
          }
          break;
      }
      $Help += $ToAdd;
    }
    $Help = $this->OnlyOne($Help);
    $Checksum = 10 - $Help;
    // Fix by Christian Rothe 20110606: Nur 1 Stelle beruecksichtigen
    // Ist die Pruefsumme = 10, ist die Pruefziffer = 0
    $Checksum = $this->OnlyOne($Checksum);
    if ($Checksum == substr($AccountNo,-1)) {
      $Mark29 = 0;
    } else {
      $Mark29 = 1;
    }
    return $Mark29;
  }

  private function Mark30($AccountNo) {
    $Significance = '200001212';
    $Help = 0;
    $Mark30 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++){
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $this->OnlyOne($Help);
    $Checksum = 10 - $Help;
    if ($Checksum == 10) {
      $Checksum = 0;
    }
    if ($Checksum == substr($AccountNo,-1)) {
      $Mark30 = 0;
    }
    return $Mark30;
  }  /* End of Mark30 */

  private function Mark31($AccountNo) {
    $Significance = '123456789';
    $Help = 0;
    $Mark31 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++){
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Checksum = $Help % 11;
    if($Checksum == substr($AccountNo,-1)) {
      $Mark31 = 0;
    }
    return $Mark31;
  }  /* End of Mark31 */

  private function Mark32($AccountNo) {
    $Mark32 = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
    return $Mark32;
  }  /* End of Mark32 */

  private function Mark33($AccountNo) {
    $Mark33 = $this->Method06($AccountNo, '000065432', FALSE, 10, 11);
    return $Mark33;
  }  /* End of Mark33 */

  private function Mark34($AccountNo) {
    $Mark34 = $this->Method06($AccountNo, '79A5842', TRUE, 8, 11);
    return $Mark34;
  }  /* End of Mark34 */

  private function Mark35($AccountNo) {
    $Mark35 = 3;
    return $Mark35;
  }  /* End of Mark35 */

  private function Mark36($AccountNo) {
    $Mark36 = $this->Method06($AccountNo, '000005842', FALSE, 10, 11);
    return $Mark36;
  }  /* End of Mark36 */

  private function Mark37($AccountNo) {
    $Mark37 = $this->Method06($AccountNo, '0000A5842', TRUE, 10, 11);
    return $Mark37;
  }  /* End of Mark37 */

  private function Mark38($AccountNo) {
    $Mark38 = $this->Method06($AccountNo, '0009A5842', TRUE, 10, 11);
    return $Mark38;
  }  /* End of Mark38 */

  private function Mark39($AccountNo) {
    $Mark39 = $this->Method06($AccountNo, '0079A5842', TRUE, 10, 11);
    return $Mark39;
  }  /* End of Mark39 */

  private function Mark40($AccountNo) {
    $Mark40 = $this->Method06($AccountNo, '6379A5842', TRUE, 10, 11);
    return $Mark40;
  }  /* End of Mark40 */

  private function Mark41($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,3,1) == '9') {
      $AccountNo = '000'. substr($AccountNo,3);
    }
    $Mark41 = $this->Method00($AccountNo,'212121212',10);
    return $Mark41;
  }  /* End of Mark41 */

  private function Mark42($AccountNo) {
    $Mark42 = $this->Method06($AccountNo, '098765432', FALSE, 10, 11);
    return $Mark42;
  }  /* End of Mark42 */

  private function Mark43($AccountNo) {
    $Significance = '987654321';
    $Help = 0;
    $Mark43 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % 10;
    $Checksum = 10 - $Help;
    if ($Checksum == 10) {
      $Checksum = 0;
    }
    if ($Checksum == substr($AccountNo,-1)) {
      $Mark43 = 0;
    }
    return $Mark43;
  }  /* End of Mark43 */

  private function Mark44($AccountNo) {
    $Mark44 = $this->Method06($AccountNo, '0000A5842', TRUE, 10, 11);
    return $Mark44;
  }  /* End of Mark44 */

  private function Mark45($AccountNo) {
    if (substr($AccountNo,0,1)=='0' or substr($AccountNo,4,1)=='1'){
      $Mark45 = 2;
    } else {
      $Mark45 = $this->Method00($AccountNo, '212121212', 10);
    }
    return $Mark45;
  }  /* End of Mark45 */

  private function Mark46($AccountNo) {
    $Mark46 = $this->Method06($AccountNo, '0065432', FALSE, 8, 11);
    return $Mark46;
  }  /* End of Mark46 */

  private function Mark47($AccountNo) {
    $Mark47 = $this->Method06($AccountNo, '00065432', FALSE, 9, 11);
    return $Mark47;
  }  /* End of Mark47 */

  private function Mark48($AccountNo) {
    $Mark48 = $this->Method06($AccountNo, '00765432', FALSE, 9, 11);
    return $Mark48;
  }  /* End of Mark48 */

  private function Mark49($AccountNo) {
    $Mark49=$this->Mark00($AccountNo);
    if ($Mark49 == 0)
      return $Mark49;
    $Mark49=$this->Mark01($AccountNo);
    return $Mark49;
  }  /* End of Mark49 */

  private function Mark50($AccountNo) {
    $Help = $this->Method06($AccountNo, '765432', FALSE, 7, 11);
    if ($Help == 1) {
      if (strlen($AccountNo) < 7) {
        $Help = $this->Method06($AccountNo . '000', '765432', FALSE, 7, 11);
      }
    }
    $Mark50 = $Help;
    return $Mark50;
  }  /* End of Mark50 */


  /* --- Hotfix FrankM 20081208 --- */
  /* --- Changed Christian Rothe 20130603 --- */
  private function Mark51($AccountNo) {
      $AccountNo = $this->ExpandAccount($AccountNo);
      // Normale Berechnung, keine Sachkonten
      if (substr($AccountNo, 2, 1) != '9') {
        // Methode A: Modulus 11, Gewichtung 2, 3, 4, 5, 6, 7
        $Help = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
          if ($Help == 1) {
            // Methode B: Modulus 11, Gewichtung 2, 3, 4, 5, 6 (= Mark33)
            $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 11);
            if ($Help == 1) {
            	// Methode C: Modulus 10, Gewichtung 2, 1, 2, 1, 2, 1
            	$Help = $this->Method00($AccountNo,'000121212',10);
	            if ($Help == 1) {
	              // 10. Stelle 7, 8 oder 9 = ungueltig
	              switch (substr($AccountNo, -1)) {
	                case '7' :
	                case '8' :
	                case '9' :
	                  $Help = 1;
	                  break;
	                default :
	                  // Methode D: Modulus 7, Gewichtung 2, 3, 4, 5, 6
	                  $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 7);
	                  break;
	              }
	           }
	         }
        }
      } else {
        // Ausnahme fuer Sachkonten, 3. Stelle der Kontonummer = 9
        // Variante 1 zur Ausnahme, Modulus 11, Gewichtung 2, 3, 4, 5, 6, 7, 8
        $Help = $this->Method06($AccountNo, '008765432', FALSE, 10, 11);
        if ($Help == 1 ){
          // Variante 2 zur Ausnahme, Modulus 11, Gewichtung 2, 3, 4, 5, 6, 7, 8, 9, 10
          $Help = $this->Method06($AccountNo, 'A98765432', TRUE, 10, 11);
        }
      }
      return $Help;
    }  /* End of Mark51 */

  private function Mark52($AccountNo,$BLZ ) {
    $Significance = '4216379A5842';
    if ((strlen($AccountNo) == 10) && (substr($AccountNo,0,1) == '9')){
        $Correct = $this->Mark20($AccountNo);

    } else {
      $Help = 0;
      $Rest = 0;
      $AltKonto = substr($BLZ,-4).substr($AccountNo,0,2);

      $AccountNo = Substr($AccountNo,2);
      while (substr($AccountNo,0,1) == '0') {
        $AccountNo = Substr($AccountNo,1);
      }
      $AltKonto = $AltKonto . $AccountNo;

      $Checksum = substr($AltKonto,5,1);

      $AltKonto = substr($AltKonto,0,5).'0'.substr($AltKonto,6);

      $Laenge = strlen($AltKonto);

      $Significance=substr($Significance,(12 - $Laenge));
      for ($Run = 0; $Run < $Laenge;$Run++) {
        $Help += substr($AltKonto,$Run,1) * HexDec(substr($Significance,$Run,1));
      }
      $Rest = $Help % 11;
      $Gewicht = HexDec(substr($Significance,5,1));

      $PZ = -1;
      while ($Help2 % 11 <>10) {  //franky_n
        $PZ++;
        $Help2 = $Rest + ($PZ * $Gewicht);
        if ($PZ == 9) {
            break;
        }
      } //franky_n
      if ($Help2 % 11 == 10) {
         if ($PZ == $Checksum) {
           $Correct = 0;
         } else {
           $Correct = 1;
         }
       } else {
         $Correct = 1;
       }
     }
    return $Correct;
  }  /* End of Mark52 */

  private function Mark53($AccountNo,$BLZ ) {
    $Significance = '4216379A5842';
    if (strlen($AccountNo) == 10) {
      if (substr($AccountNo,0,1) == '9') {
        $Correct = $this->Mark20($AccountNo);
      }
    } else {
      $Help = 0;
      $Rest = 0;

      $AltKonto = substr($BLZ,-4,2) . substr($AccountNo,1,1) . substr($BLZ,-1). substr($AccountNo,0,1) . substr($AccountNo,2,1);

      $AccountNo = Substr($AccountNo,3);

      while (substr($AccountNo,0,1) == '0') {
        $AccountNo = Substr($AccountNo,1);
      }

      $AltKonto = $AltKonto . $AccountNo;

      while (strlen($AltKonto) < 12){
        $AltKonto = "0" . $AltKonto;
      }

      $Checksum = substr($AltKonto, 5, 1);
      $AltKonto = substr($AltKonto, 0, 5) . '0' . substr($AltKonto, 6);
      $Laenge = strlen($AltKonto);

      for ($Run = 0; $Run < $Laenge;$Run++) {
        $Help += substr($AltKonto, $Run, 1) * HexDec(substr($Significance, $Run, 1));
      }

      $Rest = $Help % 11;

      $Gewicht = HexDec(substr($Significance,5, 1));
      $PZ = -1;
      while ($Help2 % 11 <> 10 or $PZ > 9) { //franky_n
        $PZ++;
        $Help2 = $Rest + ($PZ * $Gewicht);
      } //franky_n

      if ($Help2 % 11 == 10) {
        if ($PZ == $Checksum) {
          $Correct = 0;
        } else {
          $Correct = 1;
        }
      } else {
        $Correct = 1;
      }
    }
    return $Correct;
  }  /* End of Mark53 */

  private function Mark54($AccountNo) {
    $Mark54 = 3;
    return $Mark54;
  }  /* End of Mark54 */

  private function Mark55($AccountNo) {
    $Mark55 = $this->Method06($AccountNo, '878765432', FALSE, 10, 11);
    return $Mark55;
  }  /* End of Mark55 */

  private function Mark56($AccountNo) {
    $Significance = '432765432';
    $Mark56 = 1;
    $Help = 0;
    $Correct = 0;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % 11;
    $Help = 11 - $Help;
    $Checksum = $Help;
    switch (substr($AccountNo,1,1)) {
      case '9' :
        if ($Help == 11) {
          $Checksum = 8;
        }
        if ($Help == 10) {
          $Checksum = 7;
        }
      default :
        if ($Help == 11) {
          $Correct = 1;
        }
        if ($Help == 10) {
          $Correct = 1;
        }
    }
    if ($Correct == 0) {
      if ($Checksum == substr($AccountNo,-1)) {
        $Mark56 = 0;
      }
    }
    return $Mark56;
  }  /* End of Mark56 */

  /* Alte Methode 57 - ungueltig.
  private function Mark57($AccountNo) {
    $Correct = 1;

    $AccountNo = $this->ExpandAccount($AccountNo);

    $help = substr($AccountNo,0,2);

    switch (true){
      case ($help <= 50):
      case ($help == 91):
      case ($help >= 96 && $help <= 99):
        return 0;
        break;
      default:
    }

    if (preg_match("/[87]{6}/", $AccountNo)) {
      return 0;
    }

    $Mark57 = $this->Method00($AccountNo, '121212121', 10);
    return $Mark57;

  }
  */

  /* --- Neue Methode 57 --- */
  /* --- Changed FrankM 20061206, 20070822 --- */
  /* --- Changed Christian Rothe 20130909 --- */
  private function Mark57($AccountNo) {

    // Auffuellen mit Nullen auf 10 Stellen.
    $AccountNo = $this->ExpandAccount($AccountNo);

    // Pruefstellen zum ermitteln der Varianten.
    $help01 = substr($AccountNo,0,2);

    // Genutzte Berechnungsvariante ermitteln.
    switch (true){
      case ($help01 == 51):
      case ($help01 == 55):
      case ($help01 == 61):
      case ($help01 >= 64 && $help01 <= 66):
      case ($help01 == 70):
      case ($help01 >= 73 && $help01 <= 82):
      case ($help01 == 88):
      case ($help01 >= 94 && $help01 <= 95):
        // Variante 1: Modulus 10, Gewichtung 1,2,1,2,1,2,1,2,1, Pruefziffer Stelle 10.
        $PResult = $this->Method00($AccountNo, '121212121', 10);
        // Ausnahme: Wenn die ersten 6 Stellen 777777 oder 888888 dann richtig.
        $help02 = substr($AccountNo,0,6);
        if ($help02 == 777777) {
            $PResult = 0;
          }
        if ($help02 == 888888) {
            $PResult = 0;
          }
        break;
      case ($help01 >= 32 && $help01 <= 39):
      case ($help01 >= 41 && $help01 <= 49):
      case ($help01 >= 52 && $help01 <= 54):
      case ($help01 >= 56 && $help01 <= 60):
      case ($help01 >= 62 && $help01 <= 63):
      case ($help01 >= 67 && $help01 <= 69):
      case ($help01 >= 71 && $help01 <= 72):
      case ($help01 >= 83 && $help01 <= 87):
      case ($help01 >= 89 && $help01 <= 90):
      case ($help01 >= 92 && $help01 <= 93):
      case ($help01 >= 96 && $help01 <= 98):
        // Variante 2: Modulus 10, Gewichtung 1,2,1,2,1,2,1,2,1, Pruefziffer Stelle 3,
        // Pruefziffer bei der Berechnung auslassen.
        $PResult = $this->Method00($AccountNo, '121212121', 3, 10, -1);
        break;
      case ($help01 == 40):
      case ($help01 == 50):
      case ($help01 == 91):
      case ($help01 == 99):
        // Variante 3: Methode 09 (Keine Berechnung).
        $PResult = $this->Mark09($AccountNo);;
        break;
      case ($help01 >= 01 && $help <= 31):
        // Variante 4: Dritte und vierte Stelle zwischen 01 und 12
        // -UND- siebte bis neunte Stelle kleiner 500.
        $help03 = substr($AccountNo,2,2);
        $help04 = substr($AccountNo,6,3);
        $PResult = 1;
        if ($help03 >= 01 && $help03 <= 12) {
          if ($help04 < 500) {
            $PResult = 0;
          }
        }
        // Ausnahme: Diese Kontonummer ist als richtig zu bewerten.
        if ($AccountNo == 0185125434) {
          $PResult = 0;
        }
        break;
      default:
        // Kontonummern die mit 00 beginnen sind falsch.
        $PResult = 1;
        break;
    }

    // Der Ordnung halber...
    $Mark57 = $PResult;
    return $Mark57;

  }  /* End of Mark57 */

  private function Mark58($AccountNo) {
    $Mark58 = $this->Method02($AccountNo, '000065432', FALSE);
    return $Mark58;
  }  /* End of Mark58 */

  private function Mark59($AccountNo) {
    $Mark59 = 1;
      if (strlen($AccountNo) > 8) {
        $Mark59 = $this->Method00($AccountNo, '212121212', 10);
      }
      return $Mark59;
    }  /* End of Mark59 */

  private function Mark60($AccountNo) {
    $Mark60 = $this->Method00($AccountNo, '002121212', 10);
    return $Mark60;
  }  /* End of Mark60 */

  private function Mark61($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,8,1) == '8') {
      $Mark61 = $this->Method00($AccountNo, '2121212012', 8);
    } else {
      $Mark61 = $this->Method00($AccountNo, '2121212', 8);
    }
    return $Mark61;
  }  /* End of Mark61 */

  private function Mark62($AccountNo) {
    $Mark62 = $this->Method00($AccountNo,'0021212',8);
    return $Mark62;
  }  /* End of Mark62 */

  private function Mark63($AccountNo) {
    $Help = $this->Method00($AccountNo,'0121212',8);
    if ($Help == 1) {
      $Help = $this->Method00($AccountNo,'000121212',10);
    }
    $Mark63 = $Help;
    return $Mark63;
  }  /* End of Mark63 */

  private function Mark64($AccountNo) {
    $Mark64 = $this->Method06($AccountNo, '9A5842', TRUE, 7, 11);
    return $Mark64;
  }  /* End of Mark64 */

  private function Mark65($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,8,1) == '9') {
      $Mark65 = $this->Method00($AccountNo, '2121212012', 8);
    } else {
      $Mark65 = $this->Method00($AccountNo, '2121212', 8);
    }
    return $Mark65;
  }  /* End of Mark65 */

  /* --- Changed Christian Rothe 20140303 --- */
  private function Mark66($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,1,1) == '9') {
      $Mark66 = $this->Mark09($AccountNo);
    } else {
	    $Significance = '070065432';
	    $Help = 0;
	    $Mark66 = 1;    
	    for ($Run = 0;$Run < strlen($Significance);$Run++) {
	      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
	    }
	    $Help = $Help % 11;
	    $Checksum = 11 - $Help;
	    if ($Help == 0) {
	      $Checksum = 1;
	    }
	    if ($Help == 1) {
	      $Checksum = 0;
	    }
	    if ($Checksum == substr($AccountNo,-1)) {
	      $Mark66 = 0;
	    }
	  }
    return $Mark66;
  }  /* End of Mark66 */

  private function Mark67($AccountNo) {
    $Mark67 = $this->Method00($AccountNo, '2121212', 8);
    return $Mark67;
  }  /* End of Mark67 */

  private function Mark68($AccountNo) {
    $Correct = 0;
    $Significance = '212121212';
    if (strlen($AccountNo) == 9) {
      if (substr($AccountNo,1,1) == '4') {
        $Correct = 4;
      }
    }
    if (strlen($AccountNo) == 10) {
      $Significance = '000121212';
    }
    if ($Correct == 0) {
      $Correct = $this->Method00($AccountNo,$Significance,10);
      if ($Correct == 1) {
        $Correct = $this->Method00($AccountNo,'210021212',10);
      }
    }
    $Mark68 = $Correct;
    return $Mark68;
  }  /* End of Mark68 */

  private function Mark69($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Correct = 0;
    if (Substr($AccountNo,0,2) == '93') {
      $Correct = 2;
    }
    if ($Correct == 0) {
      $Correct = $this->Mark28($AccountNo);
      if ($Correct == 1) {
        $Correct = $this->Mark29($AccountNo);
      } elseif (Substr($AccountNo,0,2) == '97'){
         $Correct = $this->Mark29($AccountNo);
      }
    }
    $Mark69 = $Correct;
    return $Mark69;
  }  /* End of Mark69 */

  private function Mark70($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,3,1) == '5'){
      $Mark70 = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
    } elseif (Substr($AccountNo,3,2) == '69') {
      $Mark70 = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
    } else {
      $Mark70 = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);
   }
    return $Mark70;
  }  /* End of Mark70 */

  private function Mark71($AccountNo) {
    $Significance='0654321';
    $Help = 0;
    $Mark71 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % 11;
    $Checksum = 11 - $Help;
    if ($Help == 0) {
      $Checksum = 0;
    }
    if ($Help == 1) {
      $Checksum = 1;
    }
    if ($Checksum == substr($AccountNo,-1)) {
      $Mark71 = 0;
    }
    return $Mark71;
  }  /* End of Mark71 */

  private function Mark72($AccountNo) {
    $Mark72 = $this->Method00($AccountNo, '000121212', 10);
    return $Mark72;
  }   /* End of Mark72 */

/*
  private function Mark73($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,2,1) == '9') {
      $Mark73 = $this->Method06($AccountNo, 'A98765432', TRUE, 10, 11);
    } else {
      $Mark73 = $this->Method00($AccountNo, '000121212', 10);
    }
    return $Mark73;
  }
 */


  private function Mark73($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,2,1) != '9') {
      $Mark73 = $this->Method00($AccountNo, '000121212', 10); //Variante 1
      if ($Mark73 != 0) {
        $Mark73 = $this->Method00($AccountNo, '000021212', 10); //Variante 2
        if ($Mark73 != 0) {
          $Mark73 = $this->Method00($AccountNo, '000021212', 10, 7); //Variante 3
        }
      }
    } else {
//      $Mark73 = $this->Method06($AccountNo, 'A98765432', TRUE, 10, 11);
      $Mark73 = $this->Mark51($AccountNo);
    }
    return $Mark73;
  } /* End of Mark73 */


  // Letzte Ueberpruefung: 22.06.2007 FrankM
  private function Mark74($AccountNo) {
    $Help = 0;
    $V2 = 0;
    // Wenn Kontonummer sechstellig, Variante 2 beachten!
    if (strlen($AccountNo) == 6) {
      $V2 = 1;
    }
    $Correct = $this->Method00($AccountNo, '212121212', 10);
    if ($Correct == 1) {
      // Wenn Variante 2...
      if ($V2 == 1) {
        $Significance = '212121212';
        $Correct = 1;
        $AccountNo = $this->ExpandAccount($AccountNo);
        for ($Run = 0; $Run < strlen($Significance); $Run++) {
          $Help += $this->CrossSum(substr($AccountNo, $Run,1) * substr($Significance, $Run,1));
        }
        $Help = $this->OnlyOne($Help);
        $Help = 5 - $Help;
        if ($Help < 0) {
          $Help = 10 + $Help;
        }
        //echo "HELP = " . $Help . "<br>";
        //echo "PRUE = " . substr($AccountNo,-1) . "<br>";
        $Checksum = $Help;
        // Wenn Checksumme = Pruefziffer, dann richtig
        if ($Checksum == substr($AccountNo,-1)) {
          $Correct = 0;
        }
      } // Ende Variante 2
    }
    //echo "ERG = " . $Correct . "<br>";
    return $Correct;
  }  /* End of Mark74 */


  /* --- Fixed FrankM 20070822 --- */
  private function Mark75($AccountNo) {
    $Help = 1;
    switch (strlen($AccountNo)) {
      case 6 :
      case 7 :
        $Help = $this->Method00($AccountNo,'000021212',10);
        break;
      case 9 :
        if (substr($AccountNo,0,1) == '9') {
          $Help = $this->Method00($AccountNo,'0021212',8);
        } else {
          $Help = $this->Method00($AccountNo,'021212',7);
        }
        break;
      case 10 :
        $Help = $this->Method00($AccountNo,'021212',7);
        break;
    }
    return $Help;
  }  /* End of Mark75 */

  private function Mark76($AccountNo) {
    $Help = 0;
    $Correct = 1;
    $Significance = '0765432';
    $AccountNo = $this->ExpandAccount($AccountNo);
    for ($Run = 0;$Run < strlen($Significance);$Run++) {
      $Help += substr($AccountNo,$Run,1) * substr($Significance,$Run,1);
    }
    $Help = $Help % 11;
    if ($Help == 10) {
      $Correct = 4;
    } else {
      if ($Help == substr($AccountNo,-3,1)) {
        $Correct = 0;
      } else {
        $Help=0;
        $Significance = '000765432';
        for ($Run = 0;$Run < strlen($Significance);$Run++) {
          $Help += substr($AccountNo,$Run,1) * substr($Significance,$Run,1);
        }
        $Help = $Help % 11;
        if ($Help == 10) {
          $Correct = 4;
        } else {
          if ($Help == substr($AccountNo,-1)) {
            $Correct = 0;
          } else {
            $Correct = 1;
          }
        }
      }
    }
    return $Correct;
  }  /* End of Mark76 */

  private function Mark77($AccountNo) {
    $Help = 0;
    $Mark77 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Significance = '54321';
    for ($Run = 4;$Run == 9;$Run++) {
      $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
    }
    $Help = $Help % 11;
    if ($Help <> 0) {
      $Help = 0;
      $Significance = '54345';
      for ($Run = 4;$Run < 10;$Run++) {
        $Help += (substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
      }
      $Help = $Help % 11;
      if ($Help = 0) {
        $Mark77 = 0;
      }
    }
    return $Mark77;
  }  /* End of Mark77 */

  private function Mark78($AccountNo) {
    if (strlen($AccountNo) == 8) {
      $Mark78 = 4;
    } else {
      $Mark78 = $this->Method00($AccountNo, '212121212', 10);
    }
    return $Mark78;
  }  /* End of Mark78 */

  private function Mark79($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    switch (substr($AccountNo,0,1)) {
      case '0' :
      case '7' :
      case '8' :
        $Mark79 = 1;
        break;
      case '1' :
      case '2' :
      case '9' :
        $Mark79 = $this->Method00($AccountNo, '12121212', 9);
        break;
      case '3' :
      case '4' :
      case '5' :
      case '6' :
        $Mark79 = $this->Method00($AccountNo, '212121212', 10);
      default :
        $Mark79 = 1;
    }
    return $Mark79;
  }  /* End of Mark79 */

  private function Mark80($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);

    if (substr($AccountNo,2,1) == '9') {
      $Mark80 = $this->Mark51($AccountNo);
    } else {
      $Mark80 = $this->Method00($AccountNo, '000021212', 10);
    }
    if ($Mark80 != 0){
      $Significance='000021212';
      $Help = 0;
       $Mark80 = 1;

       for ($Run = 0; $Run < strlen($Significance); $Run++) {
         $Help += $this->CrossSum(substr($AccountNo,$Run,1) * substr($Significance,$Run,1));
       }
      $Help = $Help % 7;
       $Checksum = 7 - $Help;

       if ($Checksum == 10) {
         $Checksum = 0;
       }
       if ($Checksum == substr($AccountNo,9,1)) {
         $Mark80 = 0;
      }
    }
    return $Mark80;
  }  /* End of Mark80 */

  private function Mark81($AccountNo) {
        $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,2,1) == '9') {
      $Mark81 = $this->Mark10($AccountNo);
    } else {
      $Mark81 = $this->Mark51($AccountNo);
    }
    return $Mark81;
  }  /* End of Mark81 */

  private function Mark82($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
     if (substr($AccountNo,2, 2) == '99') {
       $Mark82 = $this->Mark10($AccountNo);
     } else {
       $Mark82 = $this->Mark33($AccountNo);
     }
     return $Mark82;
   }  /* End of Mark82 */

  // --- Fix FrankM 20081208 ---
  private function Mark83($AccountNo) {
    // Methode A
    $Help = $this->Mark32($AccountNo);
    if ($Help == 1) {
      // Methode B
      $Help = $this->Mark33($AccountNo);
      if ($Help == 1) {
        // 10. Stelle 7, 8 oder 9 = ungueltig
        switch (substr($AccountNo, -1)) {
          case '7' :
          case '8' :
          case '9' :
            $Help = 1;
            break;
          default :
            // Methode C: Modulus 7, Gewichtung 2, 3, 4, 5, 6
            $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 7);
            break;
        }
      }
    }
    $Mark83 = $Help;
    return $Mark83;
  }  /* End of Mark83 */

  /* --- Changed Christian Rothe 20130603 --- */
  private function Mark84($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo, 2, 1) != '9') {
      // Methode A: Modulus 11, Gewichtung 2, 3, 4, 5, 6
      $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 11);
      // Methode B: Modulus 7, Gewichtung 2, 3, 4, 5, 6
      if ($Help == 1) {
        $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 7);
      }
      // Methode C: Modulus 10, Gewichtung 2, 1, 2, 1, 2
      if ($Help == 1) {
        $Help = $this->Method06($AccountNo, '000021212', FALSE, 10, 10);
      }
    } else {
        $Help = $this->Mark51($AccountNo);
    }
    return $Help;
  }  /* End of Mark84 */

  // --- Fix FrankM 20080717 ---
  private function Mark85($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Zuerst Typ A = Modifizierte Methode 6.
    $Help = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
    if ($Help == 0) {
      return $Help;
    } else {
    // Wenn falsch, dann Typ B = Methode 33 (Modifizierte Methode 6).
      $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 11);
    }
    if ($Help == 0) {
      return $Help;
    } else {
      // Wenn falsch, dann Typ C.
      // Wenn zehnte Stelle = 7, 8, oder 9 dann Kontonummer ungueltig.
      if ($AccountNo[9] == '7' or $AccountNo[9] == '8' or $AccountNo[9] == '9') {
        return $Help;
      } else {
        // Methode 33 (Modifizierte Methode 6 mit Divisor 7).
        if (substr($AccountNo, 2, 2) != '99') {
          $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 7);
        // Wenn 3. und 4. Stelle = 99, dann Modifizierte Methode 2.
        } else {
          $Help = $this->Method02($AccountNo, '008765432', FALSE);
        }
      }
    }
    return $Help;
  }  /* End of Mark85 */

  private function Mark86($AccountNo) {
    $Help = $this->Method00($AccountNo, '000121212', 10);
    if ($Help == 1) {
      if (substr($AccountNo,2,1) == '9') {
        $Help = $this->Method06($AccountNo, 'A98765432', TRUE, 10, 11);
      } else {
        $Help = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
      }
    }
    $Mark86 = $Help;
    return $Mark86;
  }  /* End of Mark86 */

  private function Mark87($AccountNo) {
    $Tab1[0] = 0;
    $Tab1[1] = 4;
    $Tab1[2] = 3;
    $Tab1[3] = 2;
    $Tab1[4] = 6;

    $Tab2[0] = 7;
    $Tab2[1] = 1;
    $Tab2[2] = 5;
    $Tab2[3] = 9;
    $Tab2[4] = 8;

    $Result = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,2,1) == '9') {
      $Result = $this->Mark10($AccountNo);
    } else {
      for ($Run = 0; $Run < strlen($AccountNo); $Run++) {
        // $AccountNoTemp[$Run + 1] = (int) substr($AccountNo,$Run,1);
        $AccountNoTemp[$Run] = (int) substr($AccountNo,$Run,1);
      }

      $i = 4;
      while ($AccountNoTemp[$i] == 0) {
        $i++;
      }

      $C2 = $i % 2;
      $D2 = 0;
      $A5 = 0;
      while ($i < 10) {
        switch ($AccountNoTemp[$i]) {
          case 0 :
            $AccountNoTemp[$i] = 5;
            break;
          case 1 :
            $AccountNoTemp[$i] = 6;
            break;
          case 5:
            $AccountNoTemp[$i] = 10;
            break;
          case 6:
            $AccountNoTemp[$i] = 1;
            break;
        }
        if ($C2 == $D2) {
          if ($AccountNoTemp[$i] > 5) {
            if(($C2 == 0) AND ($D2 == 0)) {
              $C2 = 1;
              $D2 = 1;
              $A5 = $A5 + 6 - ($AccountNoTemp[$i] - 6);
            } else {
              $C2 = 0;
              $D2 = 0;
              $A5 = $A5 + $AccountNoTemp[$i];
            } //end if(($C2 == 0) AND ($D2 == 0))
          } else {
            if (($C2 == 0) AND ($D2 == 0)) {
              $C2 = 1;
              $A5 = $A5 + $AccountNoTemp[$i];
            } else {
              $C2 = 0;
              $A5 = $A5 + $AccountNoTemp[$i];
            }
          }
        } else {
          if ($AccountNoTemp[$i] > 5) {
            if ($C2 == 0) {
              $C2 = 1;
              $D2 = 0;
              $A5 = $A5 - 6 + ($AccountNoTemp[$i] - 6);
            } else {
              $C2 = 0;
              $D2 = 1;
              $A5 = $A5 - $AccountNoTemp[$i];
            }
          } else {
            if ($C2 == 0) {
              $C2 = 1;
              $A5 = $A5 - $AccountNoTemp[$i];
            } else {
              $C2 = 0;
              $A5 = $A5 - $AccountNoTemp[$i];
            }
          }
        }
        $i++;
      }
      while (($A5 < 0) OR ($A5 > 4)) {
        if ($A5 > 4) {
          $A5 = $A5 - 5;
        } else {
          $A5 = $A5 + 5;
        }
      }
      if ($D2 == 0) {
        $P = $TAB1[$A5];
      } else {
        $P = $TAB2[$A5];
      }
      if ($P == $AccountNoTemp[10]) {
        $Result = 0;
      } else {
        if ($AccountNoTemp[4] == 0) {
          if ($P > 4) {
            $P = $P - 5;
          } else {
            $P = $P + 5;
          }
          if ($P == $AccountNoTemp[10] ) {
            $Result = 0;
          }
        }
      }
      if ($Result <> 0 ) {
        $Result = $this->Mark33($AccountNo);
        if ($Result <> 0 ) {
          $Result = $this->Method06($AccountNo,'000065432',FALSE,10,7);
        }
      }
    }
    return $Result;
  }  /* End of Mark87 */

  private function Mark88($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,2,1) == '9') {
      $Mark88 = $this->Method06($AccountNo, '008765432', FALSE, 10, 11);
    } else {
      $Mark88 = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
    }
    return $Mark88;
  }  /* End of Mark88 */

  private function Mark89($AccountNo) {
    $Correct = 1;
    switch (strlen($AccountNo)) {
      case 1 :
      case 2 :
      case 3 :
      case 4 :
      case 5 :
      case 6 :
      case 10 :
        $Correct = 4;
        break;
      case 7 :
      case 9 :
        $AccountNo = $this->ExpandAccount($AccountNo);
        $Correct = $this->Method06($AccountNo,'098765432',FALSE,10,11);
        break;
      default :
        if ((((int)$AccountNo > 32000005) and ((int)$AccountNo < 38999995)) or (((int)$AccountNo >1999999999) AND ((int)$AccountNo <400000000))) {
          $Correct = $this->Mark10($AccountNo);
        }
    }
    return $Correct;
  }  /* End of Mark89 */

  private function Mark90($AccountNo) {
    $Help = $this->Method06($AccountNo, '000765432', FALSE, 10, 11); // Methode A
      if ($Help != 0) {
        $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 11); // Methode B
        if ($Help != 0) {
          switch (substr($AccountNo,-1)) {
            case '7' :
            case '8' :
            case '9' :
              $Help = 4;
              break;
            default :
              $Help = $this->Method06($AccountNo, '000065432', FALSE, 10, 7); //Methode C
              break;
          }
        }
        if ($Help != 0) {
          $Help = $this->Method06($AccountNo, '000065432',FALSE, 10, 9);  //Methode D
        }
        if ($Help != 0) {
              $Help = $this->Method06($AccountNo, '000021212',FALSE, 10, 10); //Methode E
        }
      }
    return $Help;
  }  /* End of Mark90 */

  private function Mark91($AccountNo) {
    $Help = $this->Method06($AccountNo, '765432', FALSE, 7, 11);
    if ($Help == 1) {
      $Help = $this->Method06($AccountNo, '234567', FALSE, 7, 11);
      if ($Help == 1) {
        $Help = $this->Method06($AccountNo, 'A987650432', TRUE, 7, 11);
        if ($Help == 1) {
          $Help = $this->Method06($AccountNo, '9A5842', TRUE, 7, 11);
        }
      }
    }
    return $Help;;
  }  /* End of Mark91 */

  private function Mark92($AccountNo) {
    $Mark92 = $this->Method01($AccountNo, '000173173');
    return $Mark92;
  }  /* End of Mark92 */

  private function Mark93($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Correct = 1;
      if (substr($AccountNo,0,4) == '0000') {
        $Correct = $this->Method06($AccountNo,'000065432',FALSE,10,11);
      } else {
        $Correct = $this->Method06($AccountNo,'65432',FALSE,10,11);
    }
      if ($Correct == 1) {
        if (substr($AccountNo,0,4) == '0000') {
          $Correct = $this->Method06($AccountNo,'000065432',FALSE,10,7);
        } else {
          $Correct = $this->Method06($AccountNo,'65432',FALSE,10,7);
      }
    }
    $Mark93 = $Correct;
    return $Mark93;
  }  /* End of Mark93 */

  private function Mark94($AccountNo) {
    $Mark94 = $this->Method00($AccountNo, '121212121', 10);
    return $Mark94;
  }  /* End of Mark94 */

  /* --- Changed Christian Rothe 20130909 --- */
  private function Mark95($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);

    switch($AccountNo) {
      case ($AccountNo >= '0000000001' && $AccountNo <= '0001999999'):
      case ($AccountNo >= '0009000000' && $AccountNo <= '0025999999'):
      case ($AccountNo >= '0396000000' && $AccountNo <= '0499999999'):
      case ($AccountNo >= '0700000000' && $AccountNo <= '0799999999'):
      case ($AccountNo >= '0910000000' && $AccountNo <= '0989999999'):
        $Mark95 = 4;
        break;
      default: 
	     $Mark95 = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);	
    }

    return $Mark95;
  }  /* End of Mark95 */

  private function Mark96($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Help = $this->Mark19($AccountNo);
      if ($Help == 1) {
        $Help = $this->Method00($AccountNo, '212121212', 10);
        if ($Help == 1) {
          if ((int) $AccountNo >1299999) {
            if ((int) $AccountNo < 99400000) {
              $Help = 0;
            }
          }
        }
      }
     return $Help;
    }  /* End of Mark96 */

  private function Mark97($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Help = (int) substr($AccountNo, 0, 9) % 11;
      if ($Help == 10) {
        $Help = 0;
      }
      if (substr($AccountNo,-1) == $Help) {
        $Mark97 = 0;
      } else {
        $Mark97 = 1;
      }
      return $Mark97;
    }  /* End of Mark97 */

  private function Mark98($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Correct = $this->Method01($AccountNo,'003713713');
    if ($Correct == 1) {
      $Correct = $this->Mark32($AccountNo);
    }
    $Mark98 = $Correct;
    return $Mark98;
  }  /* End of Mark98 */

  private function Mark99($AccountNo) {
    $Mark99 = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);
    if ((int) $AccountNo >= 396000000 && (int) $AccountNo <= 499999999) {
      $Mark99 = 4;
     }
    return $Mark99;
  }  /* End of Mark99 */

  private function MarkA1($AccountNo) {
    if (strlen($AccountNo) == 8 OR strlen($AccountNo)==10){
      $AccountNo = $this->ExpandAccount($AccountNo);
      $MarkA1 = $this->Method00($AccountNo, '002121212', 10);
    } else {
      $MarkA1 = 1;
    }
    return $MarkA1;
  }  /* End of MarkA1 */

  private function MarkA2($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $MarkA2 = $this->Method00($AccountNo, '212121212', 10);
    if ($MarkA2 != 0){
      $MarkA2 = $this->Mark04($AccountNo);
    }
    return $MarkA2;
  }  /* End of MarkA2 */

  private function MarkA3($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Method00($AccountNo, '212121212', 10);
    if ($RetVal != 0){
      $RetVal = $this->Mark10($AccountNo);
    }
    return $RetVal;
  }  /* End of MarkA3 */

  private function MarkA4($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if ((int) substr($AccountNo, 2,2) != 99){
      /* Variante 1 */
      $MarkA4 = $this->Method06($AccountNo,'000765432',FALSE, 10,11);
      if ($MarkA4 !=0){
        /* Variante 2 */
        $Significance='000765432';
        $MarkA4 = 1;
        $Help = 0;
        for ($Run = 0; $Run < strlen($Significance); $Run++) {
          $Help += substr($AccountNo,$Run,1) * substr($Significance,$Run,1);
        }
        $Help = $Help % 7;
        $Checksum = 7 - $Help;

        if ($Help == 0) {
          $Checksum = 0;
        }
        if ($Checksum == substr($AccountNo,-1)) {
         $MarkA4 = 0;
        }
      }
      if ($MarkA4 != 0){
        /* Variante 4 */
        $MarkA4 = $this->Mark93($AccountNo);
      }
    } else {
      /* Variante 3 */
      $MarkA4 = $this->Method06($AccountNo,'000065432',FALSE,10,11);
      if ($MarkA4 != 0){
        /* Variante 4 */
       $MarkA4 = $this->Mark93($AccountNo);
      }
    }
    return $MarkA4;
  }  /* End of MarkA4 */

  private function MarkA5($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $MarkA5 = $this->Method00($AccountNo, '212121212', 10);
    if ($MarkA5 != 0){
      if (substr($AccountNo,1,1) != "9"){
        $MarkA5 = $this->Mark10($AccountNo);
      } else {
        $MarkA5 = 1;
      }
    }
    return $MarkA5;
  }  /* End of MarkA5 */

  private function MarkA6($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,1,1) != "8"){
      $RetVal = $this->Method01($AccountNo, '173173173');
    } else {
      $RetVal = $this->Method00($AccountNo, '212121212', 10);
    }
    return $RetVal;
  }  /* End of MarkA6 */

  private function MarkA7($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Method00($AccountNo, '212121212', 10);
    if ($RetVal != 0){
      $RetVal = $this->Mark03($AccountNo);
    }
    return $RetVal;
  }  /* End of MarkA7 */

  private function MarkA8($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Mark81($AccountNo);
    if ($RetVal != 0){
      if (substr($AccountNo,2,1) != "9"){
        $RetVal = $this->Mark73($AccountNo);
      } else {
        $RetVal = 1;
      }
    }
    return $RetVal;
  }  /* End of MarkA8 */

  /* --- Fixed FrankM 20050408 --- */
  private function MarkA9($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Method01($AccountNo, '173173173');
    if ($RetVal != 0){
      $RetVal = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);
    }
    return $RetVal;
  }  /* End of MarkA9 */


  /* --- Added FrankM 20050408 --- */
  /* --- Wird aktuell von keiner Bank benutzt (09/2007 - 12/2007) ---*/
  private function MarkB0($AccountNo) {
    if (strlen($AccountNo) != 10 OR substr($AccountNo,0,1) == "8"){
      $RetVal = 1;
    } else {
      if (substr($AccountNo,9,1) == "1" OR substr($AccountNo,9,1) == "2" OR substr($AccountNo,9,1) == "3" OR substr($AccountNo,9,1) == "6"){
        $RetVal = 0;
      } else {
        $RetVal = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);
      }
    }
    return $RetVal;
  }  /* End of MarkB0 */

  /* --- Added FrankM 20050413 --- */
  private function MarkB1($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Method01($AccountNo, '137137137');
    if ($RetVal != 0){
      $RetVal = $this->Method01($AccountNo, '173173173');
    }
    return $RetVal;
  }  /* End of MarkB1 */

  /* --- Added FrankM 20050415 --- */
  private function MarkB2($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,0,1) <= "7"){
      $RetVal = $this->Method02($AccountNo, '298765432', FALSE);
    } else {
      $RetVal = $this->Method00($AccountNo, '212121212', 10);
    }
    return $RetVal;
  }  /* End of MarkB2 */

  /* --- Added FrankM 20050415 --- */
  private function MarkB3($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,0,1) <= "8"){
      $RetVal = $this->Method06($AccountNo, '000765432', FALSE, 10, 11);
    } else {
      $RetVal = $this->Method06($AccountNo, '432765432', FALSE, 10, 11);
    }
    return $RetVal;
  }  /* End of MarkB3 */

  /* --- Added FrankM 20050415 --- */
  private function MarkB4($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,0,1) == "9"){
      $RetVal = $this->Method00($AccountNo, '212121212', 10);
    } else {
      $RetVal = $this->Method02($AccountNo, '298765432', FALSE);
    }
    return $RetVal;
  }  /* End of MarkB4 */

  /* --- Added FrankM 20050727 --- */
  private function MarkB5($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Method01($AccountNo, '137137137');
    if ($RetVal != 0){
      if ((substr($AccountNo,0,1) == '8') Or (substr($AccountNo,0,1) == '9')) {
        return $RetVal;
      }
      $RetVal = $this->Method00($AccountNo, '212121212', 10);
    }
    return $RetVal;
  }  /* End of MarkB5 */

  /* --- Added FrankM 20060112 ---
     --- Hotfix 20070717 ---
     --- Hotfix FrankM 20080110 --- */
  /* --- Changed Christian Rothe 20110901 --- */

  private function MarkB6($AccountNo, $BLZ) {
    // Wenn Laenge = 9 dann Kontonummer fuer Methode 53 merken, Hotfix 20080110.
    if (strlen($AccountNo) == 9) {
       $AccountNoShort = $AccountNo;
    }
    // Mit fuehrenden Nullen auf 10 erweitern.
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Kontonummern, die an der 1. Stelle den Wert 1-9 beinhalten, nach Methode 20 pruefen.
    // Ebenso Kontonummern, die an den Stellen 1–5 die Werte 02691-02699 beinhalten, nach Methode 20 pruefen.
    if (((int)(substr($AccountNo,0 ,1)) <= 9 && (int)(substr($AccountNo,0 ,1)) > 0 ) || (substr($AccountNo,0 ,5) <= '02699' && substr($AccountNo,0 ,5) >= '02691' )){ //franky_n
      $RetVal = $this->Mark20($AccountNo);
    } else {
      // Fuer Methode 53 muss die Laenge der Kontonummer = 9 sein
      if (strlen($AccountNoShort) == 9) {
        $RetVal = $this->Mark53($AccountNoShort, $BLZ);
      } else {
        $RetVal = 1;
      }
    }
    return $RetVal;
  }  /* End of MarkB6 */

  /* --- Added FrankM 20060112 --- */
  private function MarkB7($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $AccountFloat = doubleval($AccountNo);
    if (($AccountFloat >= 1000000) And ($AccountFloat <= 5999999)) {
      $RetVal = $this->Method01($AccountNo, '173173173');
    } elseif (($AccountFloat >= 700000000) And ($AccountFloat <= 899999999)) {
      $RetVal = $this->Method01($AccountNo, '173173173');
    } else {
      $RetVal = 2;
    }
    return $RetVal;
  }  /* End of MarkB7 */

  /* --- Added FrankM 20060112 --- */
  /* --- Changed Christian Rothe 20110606 --- */
  /* --- 20110606: Aenderung des Verfahrens durch die Bundesbank --- */
  private function MarkB8($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = $this->Mark20($AccountNo);
    if ($RetVal != 0){
      $RetVal = $this->Mark29($AccountNo);
    }
    if ($RetVal != 0){
      if (($AccountNo >= 5100000000) And ($AccountNo <=  5999999999)) {
        $RetVal = $this->Mark09($AccountNo);
      } elseif (($AccountNo >= 9010000000) And ($AccountNo <=  9109999999)) {
        $RetVal = $this->Mark09($AccountNo);
      }
    }
    return $RetVal;
  }  /* End of MarkB8 */

  /* --- Added FrankM 20060124 --- */
  /* --- Benutzt von Hanseatic Bank, Hamburg --- */
  private function MarkB9($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $RetVal = 1;

    // Variante 1 - Zwei führende Nullen
    if ((substr($AccountNo,0,2) == "00")And (substr($AccountNo,2,1) != "0")){
      $Significance = '1231231';
      for ($Run = 0;$Run < strlen($Significance);$Run++) {
        $Step1 = (substr($AccountNo,$Run + 2,1) * substr($Significance,$Run,1));
        $Step2 = $Step1 + substr($Significance,$Run,1);
        $Step3 += $Step2 % 11;
      }
      $Checksum = $Step3 % 10;
      if ($Checksum == substr($AccountNo,-1)) {
        $RetVal = 0;
      } else {
        $Checksum = $Checksum + 5;
        if ($Checksum > 10) {
          $Checksum = $Checksum - 10;
        }
        if ($Checksum == substr($AccountNo,-1)) {
          $RetVal = 0;
        }
      }

    // Variante 2 - Drei führende Nullen
    } elseif ((substr($AccountNo,0,3) == "000")And (substr($AccountNo,3,1) != "0")){
      $Significance = '654321';
      for ($Run = 0;$Run < strlen($Significance);$Run++) {
        $Step1 += (substr($AccountNo,$Run + 3,1) * substr($Significance,$Run,1));
      }
      $Checksum = $Step1 % 11;
      if ($Checksum == substr($AccountNo,-1)) {
        $RetVal = 0;
      } else {
        $Checksum = $Checksum + 5;
        if ($Checksum > 10) {
          $Checksum = $Checksum - 10;
        }
        if ($Checksum == substr($AccountNo,-1)) {
          $RetVal = 0;
        }
      }
    }
    return $RetVal;
  }  /* End of MarkB9 */

  /* --- Added FrankM 20060112 ---
     --- Fix FrankM 20061103 ---
     --- Hotfix FrankM 20080110 --- */
  private function MarkC0($AccountNo, $BLZ) {
    // Wenn Laenge = 8 dann Kontonummer fuer Methode 52 merken, Hotfix 20080110.
    if (strlen($AccountNo) == 8) {
       $AccountNoShort = $AccountNo;
    }
    // Mit fuehrenden Nullen auf 10 erweitern.
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Pruefen nach Methode 52 (achtstellig)
    if ((substr($AccountNo,0,2) == "00") And (substr($AccountNo,0,3)!= "000")) {
      // Fuer Methode 52 muss die Laenge der Kontonummer = 8 sein.
      if (strlen($AccountNoShort) == 8) {
        $RetVal = $this->Mark52($AccountNoShort, $BLZ);
      } else {
        $RetVal = 1;
      }
      // Wenn falsch, dann Methode 20
      if ($RetVal != 0){
        $RetVal = $this->Mark20($AccountNo);
      }
    // Alles andere nach Methode 20
    } else {
      $RetVal = $this->Mark20($AccountNo);
    }
    return $RetVal;
  }  /* End of MarkC0 */

  /* --- Added 20060703 --- */
  private function MarkC1($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $markC1 = 1;
    if($AccountNo{0} != '5') { // Variante 1
      // Methode 17, Modulus 11, Gewichtung 1, 2, 1, 2, 1, 2
      $markC1 = $this->Mark17($AccountNo);
    } else { // Variante 2
      $weights = '121212121';
      $sum = 0;
      for($i = 0; $i < 9; $i++) {
        $sum += $this->CrossSum($AccountNo{$i} * $weights{$i});
      }
      $sum--;
      $prz = $sum % 11;
      if(0 < $prz) {
        $prz = 10 - $prz;
      }
      if($prz == $AccountNo{9}) { // 10. Stelle ist PRZ
        $markC1 = 0;
      }
    }
    return $markC1;
  }

  /* --- Added 20060703 --- */
  private function MarkC2($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $markC2 = $this->Mark22($AccountNo);
    if($markC2 != 0) {
      $markC2 = $this->Mark00($AccountNo);
    }
  return $markC2;
  }

  /* --- Added FrankM 20070305 --- */
  private function MarkC3($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,0,1) != "9") {
      $markC3 = $this->Mark00($AccountNo);
    } else {
      $markC3 = $this->Mark58($AccountNo);
    }
    return $markC3;
  }

  /* --- Added FrankM 20070305 --- */
  private function MarkC4($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo,0,1) != "9") {
      $markC4 = $this->Mark15($AccountNo);
    } else {
      $markC4 = $this->Mark58($AccountNo);
    }
    return $markC4;
  }

  /* --- Added FrankM 20070822 --- */
  private function MarkC5($AccountNo) {
    $aAccountNo = $this->ExpandAccountExtended($AccountNo);
    $AccountNo = $aAccountNo['AccountNoLong'];

    // Berechnungsvariante nach Kontonummernlaenge.
    switch ($aAccountNo['AccountNoShortLen']) {
      case 6:
        // Variante 1, sechsstellige Kontonummer
        if ((int)(substr($AccountNo,4 ,1)) <= 8 && (int)(substr($AccountNo,4 ,1)) >= 1 ){ //franky_n
          $markC5 = $this->Mark75($aAccountNo['AccountNoShort']);
        } else {
          $markC5 = 1;
        }
        break;
      case 8:
        // Variante 4, achtstellige Kontonummer.
        if ((substr($AccountNo,2,1) == "3") or (substr($AccountNo,2,1) == "4") or (substr($AccountNo,2,1) == "5")) {
          $markC5 = $this->Mark09($AccountNo);
        } else {
          $markC5 = 1;
        }
        break;
      case 9:
        // Variante 1, neunstellige Kontonummer
        if (intval(substr($AccountNo,1 ,1)) <= 8 && intval(substr($AccountNo,1 ,1)) >= 1 ){
          $markC5 = $this->Mark75($aAccountNo['AccountNoShort']);
        } else {
          $markC5 = 1;
        }
        break;
      case 10:
        // Variante 4, zehnstellige Kontonummer.
        if ((substr($AccountNo,0,2) == "70") or (substr($AccountNo,0,2) == "85")) {
          $markC5 = $this->Mark09($AccountNo);
          return $markC5;
        // Variante 2, zehnstellige Kontonummer.
        } elseif ((substr($AccountNo,0,1) == "1") or (substr($AccountNo,0,1) == "4") or (substr($AccountNo,0,1) == "5") or (substr($AccountNo,0,1) == "6") or (substr($AccountNo,0,1) == "9")) {
          $markC5 = $this->Mark29($AccountNo);
        // Variante 3, zehnstellige Kontonummer.
        } elseif ((substr($AccountNo,0,1) == "3")) {
          $markC5 = $this->Mark00($AccountNo);
        } else {
          $markC5 = 1;
        }
        break;
      default:
        $markC5 = 1;
    } // End switch.
    return $markC5;
  }

  /* --- Added FrankM 20070822 ---
     --- Changed FrankM 20090206, 20100602 --- */
  /* --- Changed Christian Rothe 20110606 --- */
  /* --- Changed Christian Rothe 20130309 --- */
  /* --- 20110606: Aenderung des Verfahrens durch die Bundesbank --- */
  private function MarkC6($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $FirstLeftDigit = substr($AccountNo, 0, 1);

    // Je nach vorhandener erster Stelle von links die Konstante zuordnen.
    switch ($FirstLeftDigit) {
      case 0:
        $Help = '4451970';
        break;
      case 1:
        $Help = '4451981';
        break;
      case 2:
        $Help = '4451992';
        break;
      case 3:
        $Help = '4451993';
        break;
      case 4:
        $Help = '4344992';
        break;
      case 5:
        $Help = '4344990';
        break;
      case 6:
        $Help = '4344991';
        break;
      case 7:
        $Help = '5499570';
        break;
      case 8:
        $Help = '4451994';
        break;
      case 9:
        $Help = '5499579';
        break;
      }  /* end switch */
      // Fuer Berechnung der Pruefziffer die Konstante
      // zur Kontonummer hinzu fuegen.
      $Help .= substr($AccountNo, 1);

      // Methode 00, 16. Stelle Pruefziffer, Modulator 10,
      // Pruefziffer NICHT verschieben, ExpandAccount NICHT anwenden.
      $markC6 = $this->Method00($Help, '212121212121212', 16, 10, 0, 1);

    return $markC6;
  }

  /* --- Added FrankM 20071009 --- */
  private function MarkC7($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Zuerst mit Methode 63 pruefen.
    $markC7 = $this->Mark63($AccountNo);
    // Wenn Pruefzifferfehler, dann Methode 06
    if ($markC7 == 1) {
      $markC7 = $this->Mark06($AccountNo);
    }
    return $markC7;
  }

  /* --- Added FrankM 20080519 --- */
  private function MarkC8($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Zuerst mit Methode 00 pruefen.
    $markC8 = $this->Mark00($AccountNo);
    // Wenn Pruefzifferfehler, dann Methode 04
    if ($markC8 == 1) {
      $markC8 = $this->Mark04($AccountNo);
      // Wenn Pruefzifferfehler, dann Methode 07
      if ($markC8 == 1) {
        $markC8 = $this->Mark07($AccountNo);
      }
    }
    return $markC8;
  }

  /* --- Added FrankM 20080519 --- */
  private function MarkC9($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Zuerst mit Methode 00 pruefen.
    $markC9 = $this->Mark00($AccountNo);
    // Wenn Pruefzifferfehler, dann Methode 07
    if ($markC9 == 1) {
      $markC9 = $this->Mark07($AccountNo);
    }
    return $markC9;
  }

  /* --- Added FrankM 20080717 --- */
  private function MarkD0($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Wenn die erste und zweite Stelle = 57, dann keine Pruefung.
    if (substr($AccountNo, 0, 2) == "57") {
      $markD0 = $this->Mark09($AccountNo);
    // Andernfalls Methode 20 (modifizierte Methode 06).
    } else {
      $markD0 = $this->Method06($AccountNo, '398765432', FALSE, 10, 11);
    }
    return $markD0;
  }

  /* --- Added FrankM 20080717 ---
     --- Changed FrankM 20100602 ---
     --- Changed Nico Sommer 20110120 ---
     --- Changed Christian Rothe 20110901 ---
     --- Changed Christian Rothe 20130309 --- */

  private function MarkD1($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $FirstLeftDigit = substr($AccountNo, 0, 1);
    $AccountSequence = substr($AccountNo, 1, 9);
    if (substr($AccountNo, 0, 1) == "8") {
      $markD1 = 1;
    } else {
      // Je nach vorhandener erster Stelle von links die Konstante zuordnen.
      switch ($FirstLeftDigit) {
        case 0:
         $Help = '4363380' . $AccountSequence;
         break;
        case 1:
         $Help = '4363381' . $AccountSequence;
         break;
        case 2:
         $Help = '4363382' . $AccountSequence;
         break;
        case 3:
         $Help = '4363383' . $AccountSequence;
         break;
        case 4:
         $Help = '4363384' . $AccountSequence;
         break;
        case 5:
         $Help = '4363385' . $AccountSequence;
         break;
        case 6:
         $Help = '4363386' . $AccountSequence;
         break;
        case 7:
         $Help = '4363387' . $AccountSequence;
         break;
        case 9:
         $Help = '4363389' . $AccountSequence;
         break;
      }  /* end switch */
      // Methode 00, 16. Stelle Pruefziffer, Modulator 10,
      // Pruefziffer NICHT verschieben, ExpandAccount NICHT anwenden.
      $markD1 = $this->Method00($Help, '212121212121212', 16, 10, 0, 1);
    }
    return $markD1;
  }

  /* --- Added FrankM 20081208 --- */
  private function MarkD2($AccountNo) {
    // Zuerst mit Methode 95 pruefen.
    $markD2 = $this->Mark95($AccountNo);
    // Wenn Pruefzifferfehler, dann mit Methode 00 pruefen.
    if ($markD2 == 1) {
      $markD2 = $this->Mark00($AccountNo);
      // Wenn Pruefzifferfehler, dann mit Methode 68 pruefen.
      if ($markD2 == 1) {
        $markD2 = $this->Mark68($AccountNo);
      }
    }
    return $markD2;
  }

  /* --- Added FrankM 20081208 --- */
  private function MarkD3($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $markD3 = $this->Mark00($AccountNo);
    // Wenn Pruefzifferfehler, dann mit Methode 27 pruefen.
    if ($markD3 == 1) {
      $markD3 = $this->Mark27($AccountNo);
    }
    return $markD3;
  }

  /* --- Added FrankM 20100602 --- */
  /* --- Changed Christian Rothe 20110606 --- */
  /* --- 20110606: Aenderung des Verfahrens durch die Bundesbank --- */
  private function MarkD4($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if ((substr($AccountNo, 0, 1) == "0")) {
      $markD4 = 1;
    } else {
      $Help = '428259' . $AccountNo;

      // Methode 00, 16. Stelle Pruefziffer, Modulator 10,
      // Pruefziffer NICHT verschieben, ExpandAccount NICHT anwenden.
      $markD4 = $this->Method00($Help, '212121212121212', 16, 10, 0, 1);
    }
    return $markD4;
  }

  /* --- Added Christian Rothe 20101206 --- */
  private function MarkD5($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    if (substr($AccountNo, 2, 2) == "99") {
      // Kontonummern mit '99' an 3. und 4. Stelle mit dieser Verfahrensvariante pruefen
      $markD5 = $this->Method06($AccountNo, '008765432', TRUE, 10, 11);
    } else {
      $markD5 = $this->Method06($AccountNo, '000765432', TRUE, 10, 11);
      // Wenn Pruefzifferfehler, dann weiter pruefen mit Modulus 7.
       if ($markD5) {
        $markD5 = $this->Method90($AccountNo, '000765432', 10, 7);
       }
      // Wenn Pruefzifferfehler, dann weiter pruefen mit Modulus 10.
       if ($markD5) {
        $markD5 = $this->Method90($AccountNo, '000765432', 10, 10);
       }
    }
    return $markD5;
  }

  /* --- Added Christian Rothe 20110327 --- */
  private function MarkD6($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Zunaechst pruefen mit Methode 07
    $markD6 = $this->Mark07($AccountNo);
    // Wenn Pruefzifferfehler, dann weiter pruefen mit Methode 03.
     if ($markD6) {
       $markD6 = $this->Mark03($AccountNo);
     }
    // Wenn Pruefzifferfehler, dann weiter pruefen mit Methode 00.
     if ($markD6) {
       $markD6 = $this->Mark00($AccountNo);
     }
    return $markD6;
  }

  /* --- Added Christian Rothe 20110606 --- */
  private function MarkD7($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
     $markD7 = $this->Method00($AccountNo, '212121212', 10, 10, 0, 0, 'D7');
    return $markD7;
  }

  /* --- Added Christian Rothe 20110606 --- */
  private function MarkD8($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    // Kontonummern aus dem Nummernkreis 1000000000 bis 9999999999 mit Methode 00 pruefen
     $markD8 = 1;
     if (($AccountNo >= 1000000000) And ($AccountNo <=  9999999999)) {
       $markD8 = $this->Mark00($AccountNo);
     } elseif (($AccountNo >= '0010000000') And ($AccountNo <=  '0099999999')) {
      // Fuer Kontonummernkreis 0010000000 bis 0099999999 mit Methode 09 pruefen
       $markD8 = $this->Mark09($AccountNo);
     }
    return $markD8;
  }

  /* --- Added Christian Rothe 20120606 --- */
  private function MarkD9($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $markD9 = $this->Method00($AccountNo, '212121212', 10, 10, 0, 0, 'D9');
    // Wenn Pruefzifferfehler, dann weiter pruefen mit Methode 10.
    if ($markD9) {
      $markD9 = $this->Mark10($AccountNo);
    }
    // Wenn Pruefzifferfehler, dann weiter pruefen mit Methode 18.
    if ($markD9) {
      $markD9 = $this->Mark18($AccountNo);
    }
    return $markD9;
  }

  /* --- Added Christian Rothe 20130309 --- */
  private function MarkE0($AccountNo) {
    $AccountNo = $this->ExpandAccount($AccountNo);
    $markE0 = $this->Method00($AccountNo, '212121212', strlen($AccountNo), 10, 0, 0, 'E0');
    return $markE0;
  }

  /* --- Added Christian Rothe 20131209 --- */
  private function MarkE1($AccountNo) {
  	$markE1 = 1;
    $AccountNo = $this->ExpandAccount($AccountNo);
    $Help = 0;
    $Significance = array(9, 10, 11, 6, 5, 4, 3, 2, 1);
    // Rechnen mit ASCII-Repräsentation der Zahlen
    for ($Run = 0; $Run < sizeof($Significance);$Run++) {
      $Help += ((substr($AccountNo,$Run,1) + 48) * $Significance[$Run]);
    }
		$Checksum = $Help % 11; 
    if ($Checksum < 10 && $Checksum == substr($AccountNo,-1)) {
        $markE1 = 0;
    }    
    return $markE1;
  }

/* ----- Ende Endgueltige Funktionen der einzelnen Berechnungsmethoden. ---- */

/* -------- Dies ist die wichtigste function ---------- */
  function CheckAccount($banktransfer_number, $banktransfer_blz) {
    $KontoNR = preg_replace('/[^0-9]/', '', $banktransfer_number); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
    $BLZ = preg_replace('/[^0-9]/', '', $banktransfer_blz); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3

    $Result = 0;
    if ((int)$BLZ == 0 || strlen($BLZ) < 8) {
      return 8;  /* Keine BLZ übergeben */
    }
    if ((int) $KontoNR == 0) {
      return 9;  /* Keine Kontonummer übergeben */
    }

/*     Beginn Implementierung */
    $adata = $this->query($BLZ);
    if ($adata == -1) {
      $Result = 5; // BLZ nicht gefunden;
      $PRZ = -1;
      $this->PRZ = $PRZ;
      $this->banktransfer_number=ltrim($banktransfer_number,"0");
      $this->banktransfer_blz=$banktransfer_blz;
    } else {
      $this->Bankname = $adata['bankname'];
      $this->PRZ = str_pad ($adata['prz'], 2, "0", STR_PAD_LEFT);
      $this->banktransfer_number=ltrim($banktransfer_number,"0");
      //$this->banktransfer_number=$this->ExpandAccount($banktransfer_number);
      $this->banktransfer_blz=$banktransfer_blz;

      $PRZ = $adata['prz'];

      switch ($PRZ) {
        case "52" :
          $Result = $this->Mark52($KontoNR, $BLZ);
          break;
        case "53" :
          $Result = $this->Mark53($KontoNR, $BLZ);
          break;
        /* --- Added FrankM 20060112 --- */
        case "B6" :
          $Result = $this->MarkB6($KontoNR, $BLZ);
          break;
        case "C0" :
          $Result = $this->MarkC0($KontoNR, $BLZ);
          break;
        default:
          $MethodName = "Mark$PRZ";
          if (method_exists($this, $MethodName)){
            $Result = call_user_func (array($this, $MethodName), $KontoNR);
          } else {
            $Result = 3;
          }
        }  /* end switch */

      } /* end if num_rows */

			if ($Result > 0 && $this->checkmode == 'iban') {
				$Result += 2000;
			}

      return $Result;
    }  /* End of CheckAccount */

		/* -------- 
			Diese Funktion liefert eine Beurteilung, ob eine Kontonummer grundsätzlich 
			angenommen und für Zahlungen verwendet werden kann 
		---------- */

		function account_acceptable($check_result) {
				$result = false;
				if (	$check_result == 0 || 
							$check_result == 2 ||
							$check_result == 3 ||
							$check_result == 4 ||
							$check_result == 2002 ||
							$check_result == 2003 ||
							$check_result == 2004) {
					$result = true;
				}
				return $result;
		}

		/*
		// Funktionen zu Testen einzelner Methoden mit von Bundesbank angegebenen Testkontonummern
		// Für Produktivbetrieb auskommentieren

		function devTest($KontoNR, $PRZ) {

			$MethodName = "Mark$PRZ";
      $Result =  call_user_func (array($this, $MethodName), $KontoNR);

      return $Result;
			
		}
		*/

  }  /* End Class AccountCheck */



class IbanAccountCheck extends AccountCheck { 

/*
   -----------------------------------------------------------------------------------------
   Copyright (c) 2013 Christian Rothe
   -----------------------------------------------------------------------------------------
*/

/* Folgende Returncodes werden übergeben                                         */
/*                                                                               */
/* 0 -> IBAN ist okay                                                            */
/* 1000 -> Länderkennung ist unbekannt                                           */
/* 1010 -> Länge der IBAN ist falsch: Zu viele Stellen                           */
/* 1020 -> Länge der IBAN ist falsch: Zu wenige Stellen                          */
/* 1030 -> IBAN entspricht nicht dem für das Land festgelegten Format            */
/* 1040 -> Prüfsumme der IBAN ist nicht korrekt -> Tippfehler                    */
/* 1050 -> BIC ist ungültig                                                      */
/* 1060 -> Länge des BIC ist falsch: Zu viele Stellen                            */
/* 1070 -> Länge des BIC ist falsch: Zu wenige Stellen                           */
/* 1080 -> Länge des BIC ist ungültig                                            */

/* Zusätzlich werden für Deutsche Konten folgende Returncodes übergeben          */
/* 2001 -> Deutsche Kontonummer & deutsche BLZ passen nicht                      */
/* 2002 -> Für diese Kontonummer kein deutsches Prüfziffernverfahren definiert   */
/*         Da die IBAN-Prüfung klappte, ist die Nr. wahrscheinlich trotzdem okay */
/* 2003 -> Dieses Deutsche Prüfziffernverfahren ist noch nicht implementiert     */
/*         Da die IBAN-Prüfung klappte, ist die Nr. wahrscheinlich trotzdem okay */
/* 2004 -> Diese Kontonummer ist mit deutschen Methoden im Detail nicht prüfbar  */
/*         Da die IBAN-Prüfung klappte, ist die Nr. wahrscheinlich trotzdem okay */
/* 2005 -> Deutsche BLZ nicht gefunden                                           */
/* 2008 -> Keine deutsche BLZ übergeben                                          */
/* 2009 -> Keine deutsche Kontonummer übergeben                                  */
/* 2010 -> Kein Kontoinhaber übergeben                                           */
/* 2020 -> BIC ist ungültig                                                      */
/* 2128 -> interner Fehler, der zeigt, dass eine Methode nicht implementiert ist */
/*                                                                               */

		function __construct() {

			parent::__construct();

			$this->banktransfer_iban = ''; // Enthält nach der Prüfung die geprüfte IBAN
			$this->banktransfer_bic = '';  // Enthält nach der Prüfung den geprüften BIC
			$this->IBAN_country = '';  // Enthält nach der Prüfung das Land der geprüften IBAN

			// Basis-Informationen / Definition zu IBAN-Kontonummern der einzelnen Länder einlesen 
    	$this->init();
    	$this->checkmode = 'iban';
		}

		function IbanCheckAccount($iban, $bic = null) {

			$result = $this->is_valid_iban($iban);

			// Keine BIC-Überprüfung / keine BIC-Fehlermeldungen bei deutschen Shops, 
			// wenn IBAN eine deutsche IBAN ist und BIC nicht eingegebn
			if ($this->get_store_ctryiso2() == 'DE' && $this->iban_get_ctryiso2($iban) == 'DE' && $bic == '') {
				$bic = null;
			}
			
			if ($result == 0 && isset($bic)) {
				$result = $this->is_valid_bic($bic,$iban);		
			}
			
			// IBAN und BIC für spätere Verwendung nach außen verfügbar machen
			$this->banktransfer_iban = $this->iban_trim($iban);
			$this->banktransfer_bic = $this->bic_trim($bic);
			$this->IBAN_country = $this->iban_get_ctryiso2($iban);
			 
			return $result;

		}
    
		function is_valid_iban($iban, $use_german_check = true) {

			$result = 0;
			
			if (!$this->iban_check_country($iban)) {
				$result = 1000;				
			}
			
			// Länge der IBAN prüfen		
			if ($result == 0) {		
				$temp_result = $this->iban_check_length($iban);
				if ($temp_result) {
					$result = $temp_result;
				}
			}

			// Format der IBAN anhand von regulären Ausdrücken prüfen
			if ($result == 0 && !$this->iban_check_format($iban)) {
				$result = 1030;
			}

			// IBAN-Fehler über Prüfsumme aufdecken 
			if ($result == 0 && !$this->iban_check_checksum($iban)) {
				$result = 1040;
			}
			
			// Detail-Check für deutsche IBAN-Nummern
			if ($result == 0 && $this->iban_get_ctryiso2($iban) == 'DE' && $use_german_check) {
				$result = $this->iban_check_german_account($iban);
			}
					
			return $result;
			
		}

		function is_valid_bic($bic, $iban = null) {
			
			$result = 0;

			$temp_result = $this->bic_check_length($bic);
			if ($temp_result) {
				$result = $temp_result;
			}
		
			if ($result == 0 && !$this->bic_validate($bic)) {
				 $result = 1050;
			}

			// Detail-Check für deutsche Konto: Existiert BIC?
			if ($result == 0 && isset($iban) && $this->iban_get_ctryiso2($iban) == 'DE') {
				$result = $this->bic_check_german_account($bic, $iban);
			}
			
			return $result;
		}
    
    function iban_check_country($iban) {
    	
			// Länge der IBAN prüfen
			$iban = $this->iban_trim($iban);
			$ctry_iso_2 = $this->iban_get_ctryiso2($iban);
   	    	
    	if (isset($this->ibanstructure[$ctry_iso_2])) {
    		return true;
    	}  
    	
    	return false;
    	
    }

    function iban_check_length($iban) {
    	
			// Länge der IBAN prüfen
			$iban = $this->iban_trim($iban);
			$ctry_iso_2 = $this->iban_get_ctryiso2($iban);
   	    	
    	if (strlen($iban) > $this->ibanstructure[$ctry_iso_2]['length']) {
    		return 1010;
    	} elseif (strlen($iban) < $this->ibanstructure[$ctry_iso_2]['length']) {
     		return 1020;   	
			}  
    	
    	return false;
    	
    }

    function iban_check_format($iban) {

			// Format der IBAN anhand von regulären Ausdrücken prüfen
			$result = true;
			
			$iban = $this->iban_trim($iban);
			$ctry_iso_2 = $this->iban_get_ctryiso2($iban);
			
			if (isset($this->ibanstructure[$ctry_iso_2]['regex'])) {
	 			if (!preg_match('/^' . $this->ibanstructure[$ctry_iso_2]['regex'] . '$/', $iban)) {
	            $result = false;
	      }
	    }
	    
	    return $result;    	

		}		

		function iban_get_ctryiso2($iban) {

			// Land aus IBAN extrahieren

			$iban = $this->iban_trim($iban);
			$ctry_iso_2 = substr($iban,0,2);			

			return $ctry_iso_2;
		}

		function iban_check_checksum($iban) {
			
			// IBAN-Fehler über Prüfsumme aufdecken
			 
			$result = false;
			
			$iban = $this->iban_trim($iban);

			$iban1 = substr($iban, 4) . substr($iban, 0, 4);
			$iban1 = str_replace(
					array('A',  'B',  'C',  'D',  'E',  'F',  'G',  'H',  'I',  'J',  'K',  'L',  'M',
								'N',  'O',  'P',  'Q',  'R',  'S',  'T',  'U',  'V',  'W',  'X',  'Y',  'Z'),
					array('10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22',
								'23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35'),
			    $iban1);

			$help = intval(substr($iban1, 0, 1));
			$len  = strlen($iban1);
			for ($x = 1; $x < $len; ++$x) {
			    $help *= 10;
			    $help += intval(substr($iban1, $x, 1));
			    $help %= 97;
			}

			if ($help == 1) {
			    $result = true;
			}

			return $result;
			
		}    

		function iban_check_german_account($iban) {
		
			// Überprüfe deutsche Kontonummern mit den Prüfziffern-Methoden der Deutschen Bundesbank
			
			$iban = $this->iban_trim($iban);
			$kontonr = substr($iban, 12, 10);
			$blz = substr($iban, 4, 8);
			
			$result = $this->CheckAccount($kontonr,$blz);
			
			return $result;
			
		}

		function is_sepa_country($ctry_iso2) {
			
			$result = false;
			
			if ($this->ibanstructure[$ctry_iso2]['sepa_ctry']) {
				$result = true;
			}
			
			return $result;
			
		}

		function iban_trim($iban) {
			
			// IBAN auf einheitliches Format mit Großbuchstaben und ohne Leerstellen bringen
			// Swift (BIC) können damit ebenfalls formatiert werden
			$iban = trim($iban); 
			$iban = strtoupper($iban);
			$iban = preg_replace('/[^A-Z0-9]/','',$iban);
			
			return $iban;
		
		}
		
		function iban_format_human($iban) {

			// IBAN in durch Leerzeichen getrennte Vierergruppen formatieren

			$iban = $this->iban_trim($iban);

			$quadrupel_count = ceil(strlen($iban)/4);
			$iban_formatted = substr($iban,0,4);
			
			for ($i = 1, $n = $quadrupel_count; $i < $n; $i++) {
				$iban_formatted .= ' '.substr($iban,($i*4),4);
			}

			return $iban_formatted;

		}

		function bic_trim($bic) {
			
			$bic = $this->iban_trim($bic);
			
			return $bic;
		
		}
		    
    function init() {
    			
				/*
				Dabei bedeutet:
				AD, BE, ... Länderkennzeichen
				pp zweistellige Prüfsumme
				b Stelle der Bankleitzahl
				d Kontotyp
				k Stelle der Kontonummer
				K Kontrollziffern
				r Regionalcode
				s Stelle der Filialnummer (Branch Code / code guichet)
				X sonstige Funktionen
    		*/
   	
				$this->ibanstructure = array(
															// Albanien
																'AL' => array(	'scheme' => 'ALpp bbbs sssK kkkk kkkk kkkk kkkk',
																								'regex' => 'AL[0-9]{2}[0-9]{8}[A-Z0-9]{16}',
																								'length' => 28,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5, 
																								'bank_length' => 8),
															// Andorra	
																'AD' => array(	'scheme' => 'ADpp bbbb ssss kkkk kkkk kkkk', 
																								'regex' => 'AD[0-9]{2}[0-9]{4}[0-9]{4}[A-Z0-9]{12}', 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5, 
																								'bank_length' => 8),
															// Aserbaidschan
																'AZ' => array(	'scheme' => 'AZpp bbbb kkkk kkkk kkkk kkkk kkkk', 
																								'regex' => 'AZ[0-9]{2}[A-Z]{4}[A-Z0-9]{20}', 
																								'length' => 28,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5, 
																								'bank_length' => 5),
															// Bahrain
																'BH' => array(	'scheme' =>	'BHpp bbbb kkkk kkkk kkkk kk',
																								'regex' => 'BH[0-9]{2}[A-Z]{4}[A-Z0-9]{14}', 
																								'length' => 22,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5, 
																								'bank_length' => 4),													
															// Belgien 
																'BE' => array(	'scheme' => 'BEpp bbbk kkkk kkKK',
																								'regex' => 'BE[0-9]{2}[0-9]{3}[0-9]{7}[0-9]{2}', 
																								'length' => 16,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Bosnien und Herzegowina
																'BA' => array(	'scheme' => 'BApp bbbs sskk kkkk kkKK', 
																								'regex' => 'BA[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{8}[0-9]{2}',
																								'length' => 20,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 7),
															// Brasilien
																'BR' => array(	'scheme' => 'BRpp bbbb bbbb ssss skkk kkkk kkkk k',
																								'regex' => 'BR[0-9]{2}[0-9]{8}[0-9]{5}[0-9]{10}[A-Z][A-Z0-9]', 
																								'length' => 29,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 13),
															// Bulgarien
																'BG' => array(	'scheme' => 'BGpp bbbb ssss ddkk kkkk kk',
																								'regex' => 'BG[0-9]{2}[A-Z]{4}[0-9]{4}[0-9]{2}[A-Z0-9]{8}', 
																								'length' => 22,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
															// Costa Rica
																'CR' => array(	'scheme' => 'CRpp bbbk kkkk kkkk kkkk k',
																								'regex' => 'CR[0-9]{2}[0-9]{3}[0-9]{14}', 
																								'length' => 21,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),															
															// Dänemark
																'DK' => array(	'scheme' => 'DKpp bbbb kkkk kkkk kK',
																								'regex' => 'DK[0-9]{2}[0-9]{14}', 
																								'length' => 18,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Deutschland
																'DE' => array(	'scheme' => 'DEpp bbbb bbbb kkkk kkkk kk',
																								'regex' => 'DE[0-9]{2}[0-9]{8}[0-9]{10}', 
																								'length' => 22,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
															// Dominikanische Republik
																'DO' => array(	'scheme' => 'DOpp bbbb kkkk kkkk kkkk kkkk kkkk',
																								'regex' => 'DO[0-9]{2}[A-Z0-9]{4}[0-9]{20}', 
																								'length' => 28,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Estland
																'EE' => array(	'scheme' => 'EEpp bbkk kkkk kkkk kkkK',
																								'regex' => 'EE[0-9]{2}[0-9]{2}[0-9]{2}[0-9]{11}[0-9]{1}', 
																								'length' => 20,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Faröer Insel 
																'FO' => array(	'scheme' => 'FOpp bbbb kkkk kkkk kK',
																								'regex' => 'FO[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}', 
																								'length' => 18,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Finnland
																'FI' => array(	'scheme' => 'FIpp bbbb bbkk kkkk kK',
																								'regex' => 'FI[0-9]{2}[0-9]{6}[0-9]{7}[0-9]{1}', 
																								'length' => 18,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 6),
															// Frankreich
																'FR' => array(	'scheme' => 'FRpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => 'FR[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}', 
																								'length' => 27,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Georgien
																'GE' => array(	'scheme' => 'GEpp bbkk kkkk kkkk kkkk kk',
																								'regex' => 'GE[0-9]{2}[A-Z]{2}[0-9]{16}', 
																								'length' => 22,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 2),
															// Gibraltar
																'GI' => array(	'scheme' => 'GIpp bbbb kkkk kkkk kkkk kkk',
																								'regex' => 'GI[0-9]{2}[A-Z]{4}[A-Z0-9]{15}', 
																								'length' => 23,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Griechenland
																'GR' => array(	'scheme' => 'GRpp bbbs sssk kkkk kkkk kkkk kkk',
																								'regex' => 'GR[0-9]{2}[0-9]{3}[0-9]{4}[A-Z0-9]{16}', 
																								'length' => 27,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 7),
															// Grönland
																'GL' => array(	'scheme' => 'GLpp bbbb kkkk kkkk kK',
																								'regex' => 'GL[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}', 
																								'length' => 18,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Großbritannien
																'GB' => array(	'scheme' => 'GBpp bbbb ssss sskk kkkk kk',
																								'regex' => 'GB[0-9]{2}[A-Z]{4}[0-9]{6}[0-9]{8}', 
																								'length' => 22,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Guadeloupe
																'GP' => array(	'scheme' => 'GPpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => null, 
																								'length' => 27,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Guatemala
																'GT' => array(	'scheme' => 'GTpp bbbb kkkk kkkk kkkk kkkk kkkk',
																								'regex' => 'GT[0-9]{2}[A-Z0-9]{4}[A-Z0-9]{20}', 
																								'length' => 28,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Hongkong 
																'HK' => array(	'scheme' => 'HKpp bbbb kkkk kkkk',
																								'regex' => null, 
																								'length' => 16,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Irland
																'IE' => array(	'scheme' => 'IEpp bbbb ssss sskk kkkk kk',
																								'regex' => 'IE[0-9]{2}[A-Z]{4}[0-9]{6}[0-9]{8}', 
																								'length' => 22,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Island
																'IS' => array(	'scheme' => 'ISpp bbbb sskk kkkk XXXX XXXX XX',
																								'regex' => 'IS[0-9]{2}[0-9]{4}[0-9]{2}[0-9]{6}[0-9]{10}', 
																								'length' => 26,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 6),
															// Israel 
																'IL' => array(	'scheme' => 'ILpp bbbs sskk kkkk kkkk kkk',
																								'regex' => 'IL[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{13}', 
																								'length' => 23,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 6),
															// Italien 
																'IT' => array(	'scheme' => 'ITpp Kbbb bbss sssk kkkk kkkk kkk',
																								'regex' => 'IT[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}', 
																								'length' => 27,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 6,
																								'bank_length' => 10),
															// Jordanien 
																'JO' => array(	'scheme' => 'JOpp bbbb ssss kkkk kkkk kkkk kkkk kk',
																								'regex' => null, 
																								'length' => 30,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
															// Jungferninseln
																'VG' => array(	'scheme' => 'VGpp bbbb kkkk kkkk kkkk kkkk',
																								'regex' => 'VG[0-9]{2}[A-Z]{4}[0-9]{16}', 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),																								
															// Kasachstan
																'KZ' => array(	'scheme' => 'KZpp bbbk kkkk kkkk kkkk',
																								'regex' => 'KZ[0-9]{2}[0-9]{3}[A-Z0-9]{13}', 
																								'length' => 20,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Katar
																'QA' => array(	'scheme' => 'QApp bbbb kkkk kkkk kkkk kkkk kkkk k',
																								'regex' => null, 
																								'length' => 29,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Kroatien
																'HR' => array(	'scheme' => 'HRpp bbbb bbbk kkkk kkkk k',
																								'regex' => 'HR[0-9]{2}[0-9]{7}[0-9]{10}', 
																								'length' => 21,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 7),
															// Kuwait
																'KW' => array(	'scheme' => 'KWpp bbbb kkkk kkkk kkkk kkkk kkkk kk',
																								'regex' => 'KW[0-9]{2}[A-Z]{4}[0-9]{22}', 
																								'length' => 30,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Lettland
																'LV' => array(	'scheme' => 'LVpp bbbb kkkk kkkk kkkk k',
																								'regex' => 'LV[0-9]{2}[A-Z]{4}[A-Z0-9]{13}', 
																								'length' => 21,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Libanon
																'LB' => array(	'scheme' => 'LBpp bbbb kkkk kkkk kkkk kkkk kkkk',
																								'regex' => 'LB[0-9]{2}[0-9]{4}[A-Z0-9]{20}', 
																								'length' => 28,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Liechtenstein
																'LI' => array(	'scheme' => 'LIpp bbbb bkkk kkkk kkkk k',
																								'regex' => 'LI[0-9]{2}[0-9]{5}[A-Z0-9]{12}', 
																								'length' => 21,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 5),
															// Litauen
																'LT' => array(	'scheme' => 'LTpp bbbb bkkk kkkk kkkk',
																								'regex' => 'LT[0-9]{2}[0-9]{5}[0-9]{11}', 
																								'length' => 20,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 5),
															// Luxemburg
																'LU' => array(	'scheme' => 'LUpp bbbk kkkk kkkk kkkk',
																								'regex' => 'LU[0-9]{2}[0-9]{3}[A-Z0-9]{13}', 
																								'length' => 20,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Malta 
																'MT' => array(	'scheme' => 'MTpp bbbb ssss skkk kkkk kkkk kkkk kkk',
																								'regex' => 'MT[0-9]{2}[A-Z]{4}[0-9]{5}[A-Z0-9]{18}', 
																								'length' => 31,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 9),
															// Marokko
																'MT' => array(	'scheme' => 'MApp bbba aakk kkkk kkkk kkKK',
																								'regex' => null, 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Martinique
																'MQ' => array(	'scheme' => 'MQpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => null, 
																								'length' => 27,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Mauretanien
																'MR' => array(	'scheme' => 'MRpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => 'MR13[0-9]{5}[0-9]{5}[0-9]{11}[0-9]{2}', 
																								'length' => 27,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),																														
															// Mauritius
																'MU' => array(	'scheme' => 'MUpp bbbb bbss kkkk kkkk kkkk kkkK KK',
																								'regex' => 'MU[0-9]{2}[A-Z]{4}[0-9]{2}[0-9]{2}[0-9]{12}[0-9]{3}[A-Z]{3}', 
																								'length' => 30,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 7),
															// Mazedonien
																'MK' => array(	'scheme' => 'MKpp bbbk kkkk kkkk kKK',
																								'regex' => 'MK[0-9]{2}[0-9]{3}[A-Z0-9]{10}[0-9]{2}', 
																								'length' => 19,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Moldawien
																'MD' => array(	'scheme' => 'MDpp bbkk kkkk kkkk kkkk kkkk',
																								'regex' => 'MD[0-9]{2}[A-Z0-9]{20}', 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 2),
															// Monaco
																'MC' => array(	'scheme' => 'MCpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => 'MC[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}', 
																								'length' => 27,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Montenegro
																'ME' => array(	'scheme' => 'MEpp bbbk kkkk kkkk kkkk KK',
																								'regex' => 'ME[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}', 
																								'length' => 20,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Neukaledonien													
																'NC' => array(	'scheme' => 'NCpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => null, 
																								'length' => 30,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Niederlande
																'NL' => array(	'scheme' => 'NLpp bbbb kkkk kkkk kk',
																								'regex' => 'NL[0-9]{2}[A-Z]{4}[0-9]{10}', 
																								'length' => 18,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Norwegen
																'NO' => array(	'scheme' => 'NOpp bbbb kkkk kkK',
																								'regex' => 'NO[0-9]{2}[0-9]{4}[0-9]{6}[0-9]{1}', 
																								'length' => 15,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Österreich
																'AT' => array(	'scheme' => 'ATpp bbbb bkkk kkkk kkkk',
																								'regex' => 'AT[0-9]{2}[0-9]{5}[0-9]{11}', 
																								'length' => 20,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 5),
															// Pakistan
																'PK' => array(	'scheme' => 'PKpp bbbb rrkk kkkk kkkk kkkk',
																								'regex' => 'PK[0-9]{2}[A-Z]{4}[A-Z0-9]{16}', 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 6),
															// Polen
																'PL' => array(	'scheme' => 'PLpp bbbs sssK kkkk kkkk kkkk kkkk',
																								'regex' => 'PL[0-9]{2}[0-9]{8}[0-9]{16}', 
																								'length' => 28,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
															// Palästinensische Autonomiegebiete
																'PS' => array(	'scheme' => 'PSpp bbbb rrrr rrrr rkkk kkkk kkkk k',
																								'regex' => 'PS[0-9]{2}[A-Z]{4}[A-Z0-9]{21}', 
																								'length' => 29,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 13),															
															// Portugal
																'PT' => array(	'scheme' => 'PTpp bbbb ssss kkkk kkkk kkkK K',
																								'regex' => 'PT[0-9]{2}[0-9]{4}[0-9]{4}[0-9]{11}[0-9]{2}', 
																								'length' => 25,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
															// Réunion
																'RE' => array(	'scheme' => 'REpp bbbb bsss sskk kkkk kkkk kKK',
																								'regex' => null, 
																								'length' => 27,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 10),
															// Rumänien*	
																'RO' => array(	'scheme' => 'ROpp bbbb kkkk kkkk kkkk kkkk',
																								'regex' => 'RO[0-9]{2}[A-Z]{4}[A-Z0-9]{16}', 
																								'length' => 24,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// San Marino
																'SM' => array(	'scheme' => 'SMpp Kbbb bbss sssk kkkk kkkk kkk',
																								'regex' => 'SM[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}', 
																								'length' => 27,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 6,
																								'bank_length' => 10),
															// Saudi-Arabien
																'SA' => array(	'scheme' => 'SApp bbkk kkkk kkkk kkkk kkkk',
																								'regex' => 'SA[0-9]{2}[0-9]{2}[A-Z0-9]{18}', 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 2),
															// Schweden
																'SE' => array(	'scheme' => 'SEpp bbbk kkkk kkkk kkkk kkkK',
																								'regex' => 'SE[0-9]{2}[0-9]{3}[0-9]{16}[0-9]{1}', 
																								'length' => 24,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Schweiz
																'CH' => array(	'scheme' => 'CHpp bbbb bkkk kkkk kkkk k',
																								'regex' => 'CH[0-9]{2}[0-9]{5}[A-Z0-9]{12}', 
																								'length' => 21,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 5),
															// Serbien
																'RS' => array(	'scheme' => 'RSpp bbbk kkkk kkkk kkkk KK',
																								'regex' => 'RS[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}', 
																								'length' => 22,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),
															// Slowakei 
																'SK' => array(	'scheme' => 'SKpp bbbb ssss sskk kkkk kkkk',
																								'regex' => 'SK[0-9]{2}[0-9]{4}[0-9]{6}[0-9]{10}', 
																								'length' => 24,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Slowenien
																'SI' => array(	'scheme' => 'SIpp bbss skkk kkkk kKK',
																								'regex' => 'SI[0-9]{2}[0-9]{5}[0-9]{8}[0-9]{2}', 
																								'length' => 19,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 5),
															// Spanien
																'ES' => array(	'scheme' => 'ESpp bbbb ssss KKkk kkkk kkkk',
																								'regex' => 'ES[0-9]{2}[0-9]{4}[0-9]{4}[0-9]{1}[0-9]{1}[0-9]{10}', 
																								'length' => 24,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
															// Tschechien 
																'CZ' => array(	'scheme' => 'CZpp bbbb kkkk kkkk kkkk kkkk',
																								'regex' => 'CZ[0-9]{2}[0-9]{20}', 
																								'length' => 24,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 4),
															// Türkei
																'TR' => array(	'scheme' => 'TRpp bbbb brkk kkkk kkkk kkkk kk',
																								'regex' => 'TR[0-9]{2}[0-9]{5}[A-Z0-9]{1}[A-Z0-9]{16}', 
																								'length' => 26,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 6),
															// Tunesien
																'TN' => array(	'scheme' => 'TNpp bbss skkk kkkk kkkk kkKK',
																								'regex' => 'TN59[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}', 
																								'length' => 24,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 5),
															// Ungarn
																'HU' => array(	'scheme' => 'HUpp bbbs sssK kkkk kkkk kkkk kkkK',
																								'regex' => 'HU[0-9]{2}[0-9]{3}[0-9]{4}[0-9]{1}[0-9]{15}[0-9]{1}', 
																								'length' => 28,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 7),
															// Vereinigte Arabische Emirate
																'AE' => array(	'scheme' => 'AEpp bbbk kkkk kkkk kkkk kkk',
																								'regex' => 'AE[0-9]{2}[0-9]{3}[0-9]{16}', 
																								'length' => 23,
																								'sepa_ctry' => false,
																								'bank_start_pos' => 5,
																								'bank_length' => 3),															
															// Zypern
																'CY' => array(	'scheme' => 'CYpp bbbs ssss kkkk kkkk kkkk kkkk',
																								'regex' => 'CY[0-9]{2}[0-9]{3}[0-9]{5}[A-Z0-9]{16}', 
																								'length' => 28,
																								'sepa_ctry' => true,
																								'bank_start_pos' => 5,
																								'bank_length' => 8),
													);    	




 
    }


    function bic_check_length($bic) {
    	
			// Länge des BIC prüfen
			if (strlen($bic) > 11) {
				return 1060;
			} elseif (strlen($bic) < 8) {
				return 1070;
			} elseif (strlen($bic) != 8 && strlen($bic) != 11) {
				return 1080;
			};
    	
    	return false;
    	
    }
    
    
		function bic_validate($bic) {

			/*
					Der BIC oder SWIFT-Code hat eine Länge von 8 oder 11 alphanumerischen Zeichen und folgenden Aufbau: BBBBCCLLbbb	
					Bedeutung
					BBBB:	nur Buchstaben 		4-stelliger Bankcode, vom Geldinstitut frei wählbar
					CC:	nur Buchstaben			2-stelliger Ländercode nach ISO 3166-1
					LL:	Buchstaben/Ziffern 	2-stellige Codierung des Ortes in zwei Zeichen. 
																	Das erste Zeichen darf nicht die Ziffer "0" oder "1" sein. 
																	Wenn das zweite Zeichen kein Buchstabe, sondern eine Ziffer ist, so bedeutet dies:
																	0 – es handelt sich um eine Test-BIC
																	1 – es handelt sich um einen passiven SWIFT-Teilnehmer
																	2 – der Empfänger zahlt die Transaktionskosten
																	Der Buchstabe 'O' ist als zweites Zeichen nicht gestattet.
					bbb: Buchstaben/Ziffern	3-stellige Kennzeichnung (Branch-Code) der Filiale oder Abteilung (optional)
																	Ein 8-stelliger BIC kann um "XXX" auf einen 11-stelligen ergänzt werden, entsprechend kann "XXX" auch weggelassen werden, 
																	andere Kennzeichen nicht. Der Branch-Code darf nicht mit "X" anfangen, es sei denn, es ist "XXX".
			*/
		
			$bic = $this->bic_trim($bic);
		
			$result = false;
			if(preg_match('/^([A-Z]){4}([A-Z]){2}([0-9A-Z]){2}([0-9A-Z]{3})?$/', $bic)) {
				$result = true;
			}
			// Stellen 7 und 8: 2-stellige Codierung des Ortes in zwei Zeichen. Das erste Zeichen davon darf nicht die Ziffer "0" oder "1" sein. Der Buchstabe 'O' ist als zweites Zeichen nicht gestattet.
			if (substr($bic,6,1) == '0' || substr($bic,6,1) == '1') {
				$result = false;
			}
			if (substr($bic,7,1) == 'O') {
				$result = false;
			}
			// Der Branch-Code darf nicht mit "X" anfangen, es sei denn, es ist "XXX".
			if (substr($bic,8,1) == 'X' && substr($bic,8,3) != 'XXX') {
				$result = false;
			}
			
			return $result;
		
		}   

		function get_bic($blz) {
			$result = false;
			$check_blz = $this->query($blz);
			if ($check_blz != -1 && isset($check_blz['bic'])) {
				$result = $check_blz['bic'];
			} 
			return $result;
		}

		function get_bankname($blz) {
			$result = false;
			$check_blz = $this->query($blz);
			if ($check_blz != -1 && isset($check_blz['bankname'])) {
				$result = $check_blz['bankname'];
			} 
			return $result;
		}

		function bic_check_german_account($bic, $iban) {
		
			// Überprüfe, ob BIC in BLZ-Daten der Deutschen Bundesbank vorhanden ist.

			$result = 0;

	    if (MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ == 'true' && defined(MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ)) {
				// Zunächst prüfen, ob BIC-Spalte in Bankleitzahlen-Tabelle enthalten
				$table_query = xtc_db_query("SHOW COLUMNS from ".TABLE_BANKTRANSFER_BLZ." where Field = 'bic'"); 
				if (xtc_db_num_rows($table_query) > 0) {
	
					// Okay: BIC-Spalte existiert
					// Wir prüfen also den BIC
					$result = 2020;
	
					$bic = $this->bic_trim($bic);
					$bic_padded = str_pad($bic, 11, "X", STR_PAD_RIGHT);
		
					$german_bank_query = xtc_db_query("select * from ".TABLE_BANKTRANSFER_BLZ." where bic = '".xtc_db_input($bic)."' or bic = '".xtc_db_input($bic_padded)."' limit 1");	
					
					if ($german_bank = xtc_db_fetch_array($german_bank_query)) {
						$result = 0;
					}

				} 	
			}
		
			return $result;
			
		}

		function bban_to_iban($bban, $ctry_iso2) {
			
			// Leerzeichen entfernen und Buchstaben in Großbuchstaben verwandeln
			$ctry_iso2 = $this->iban_trim($ctry_iso2);
			$bban = $this->iban_trim($bban);

			$iban_tmp = $bban . $ctry_iso2.'00'; 

			$iban_tmp = str_replace(
					array('A',  'B',  'C',  'D',  'E',  'F',  'G',  'H',  'I',  'J',  'K',  'L',  'M',
								'N',  'O',  'P',  'Q',  'R',  'S',  'T',  'U',  'V',  'W',  'X',  'Y',  'Z'),
					array('10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22',
								'23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35'),
			    $iban_tmp);


			$modulo = (bcmod($iban_tmp,"97"));
			$pruefziffer = str_pad ( 98 - $modulo, 2, "0",STR_PAD_LEFT);
			$iban = $ctry_iso2 . $pruefziffer . $bban;

			// Ergebnis überprüfen
			$checkresult = $this->is_valid_iban($iban, false);
			if ($checkresult > 0) {
				$iban = false;
			}
	
			return $iban;
	
		}


		function message($error_code) {

				/* Folgende Error-Codes werden berücksichtigt                                    */
				/*                                                                               */

				/* 0 -> Bankverbindung ist okay                                                  */

				/* 1 -> Kontonummer & BLZ passen nicht                                           */
				/* 2 -> Für diese Kontonummer ist kein Prüfziffernverfahren definiert            */
				/* 3 -> Dieses Prüfziffernverfahren ist noch nicht implementiert                 */
				/* 4 -> Diese Kontonummer ist technisch nicht prüfbar                            */
				/* 5 -> BLZ nicht gefunden                                                       */
				/* 8 -> Keine BLZ übergeben                                                      */
				/* 9 -> Keine Kontonummer übergeben                                              */
				/* 10 -> Kein Kontoinhaber übergeben                                             */
				/* 128 -> interner Fehler, der zeigt, dass eine Methode nicht implementiert ist  */

				/* 1000 -> Länderkennung ist unbekannt                                           */
				/* 1010 -> Länge der IBAN ist falsch: Zu viele Stellen                           */
				/* 1020 -> Länge der IBAN ist falsch: Zu wenige Stellen                          */
				/* 1030 -> IBAN entspricht nicht dem für das Land festgelegten Format            */
				/* 1040 -> Prüfsumme der IBAN ist nicht korrekt -> Tippfehler                    */
				/* 1050 -> BIC ist ungültig                                                      */
				/* 1060 -> Länge des BIC ist falsch: Zu viele Stellen                            */
				/* 1070 -> Länge des BIC ist falsch: Zu wenige Stellen                           */
				/* 1080 -> Länge des BIC ist ungültig                                            */
				
				/* 2001 -> Deutsche Kontonummer & deutsche BLZ passen nicht                      */
				/* 2002 -> Für diese Kontonummer kein deutsches Prüfziffernverfahren definiert   */
				/*         Da die IBAN-Prüfung klappte, ist die Nr. wahrscheinlich trotzdem okay */
				/* 2003 -> Dieses Deutsche Prüfziffernverfahren ist noch nicht implementiert     */
				/*         Da die IBAN-Prüfung klappte, ist die Nr. wahrscheinlich trotzdem okay */
				/* 2004 -> Diese Kontonummer ist mit deutschen Methoden im Detail nicht prüfbar  */
				/*         Da die IBAN-Prüfung klappte, ist die Nr. wahrscheinlich trotzdem okay */
				/* 2005 -> Deutsche BLZ nicht gefunden                                           */
				/* 2008 -> Keine deutsche BLZ übergeben                                          */
				/* 2009 -> Keine deutsche Kontonummer übergeben                                  */
				/* 2010 -> Kein Kontoinhaber übergeben                                           */
				/* 2020 -> BIC ist ungültig                                                      */
				/* 2128 -> interner Fehler, der zeigt, dass eine Methode nicht implementiert ist */
				/*                                                                               */


				if (defined('BANKACCOUNT_CHECK_TEXT_BANK_ERROR_'.(int)$error_code)) {
					$result = constant('BANKACCOUNT_CHECK_TEXT_BANK_ERROR_'.(int)$error_code);
				} else {
					$result = BANKACCOUNT_CHECK_TEXT_BANK_ERROR_UNKNOWN;
				}
				
				return $result;
			
		}		
 
 		function get_store_ctryiso2() {
 			
 			$country_query = xtc_db_query("select countries_iso_code_2 from ".TABLE_COUNTRIES." where countries_id = '".(int)STORE_COUNTRY."'");
 			$country = xtc_db_fetch_array($country_query);
 			
 			return $country['countries_iso_code_2'];
 			
 		}
 
    
}  /* End Class IbanAccountCheck */

 
?>