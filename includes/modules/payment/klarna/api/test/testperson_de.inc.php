<?php
if($klarna instanceof Klarna) {
    $country = 'de';
    $klarna->setCountry($country);
    /* same as below
    $klarna->setCountry(KlarnaCountry::DE);
    $klarna->setLanguage(KlarnaLanguage::DE);
    $klarna->setCurrency(KlarnaCurrency::EUR);
    */

    //Customer info
    /*
    07071960
    Uno Eins
    Hellersbergstrasse 14
    41460 Neuss
     */
    $pno = "07071960";
    $gender = KlarnaFlags::MALE;
    $fname = "Uno";
    $lname = "Eins";
    $cellno = "015 2211 3356";
    $telno = "";
    $email = "uno.eins@deutschland.de";
    $ysalary = 0;

    //Address info
    $careof = "";
    $street = "Hellersbergstrasse";
    $zip = "41460";
    $city = "Neuss";
    $houseNo = "14"; //Only for NL and DE
    $houseExt = ""; //Only for NL

    $addr = new KlarnaAddr($email, $telno, $cellno, $fname, $lname, $careof, $street, $zip, $city, $country, $houseNo, $houseExt);
    
    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    $klarna->setAddress(KlarnaFlags::IS_BILLING, $addr);
}

