<?php
$addr = null;
if($klarna instanceof Klarna) {
    $country = 'se';
    $klarna->setCountry($country);
    /* same as below
    $klarna->setCountry(KlarnaCountry::SE);
    $klarna->setLanguage(KlarnaLanguage::SE);
    $klarna->setCurrency(KlarnaCurrency::SEK);
    */

    //Customer info
    /*
    430415-8399
    Karl Lidin
    Junibacksg 42
    23634 Hollviken
     */
    $pno = "430415-8399";
    $gender = null; //For Sweden we do not need to send gender.
    $fname = "Karl";
    $lname = "Lidin";
    $cellno = "076 526 00 00";
    $telno = "";
    $email = "karl.lidin@klarna.com";
    $ysalary = 0;

    //Address info
    $careof = "";
    $street = "Junibacksg 42";
    $zip = "23634";
    $city = "Hollviken";
    $houseNo = ""; //Only for NL and DE
    $houseExt = ""; //Only for NL

    $addr = new KlarnaAddr($email, $telno, $cellno, $fname, $lname, $careof, $street, $zip, $city, $country, $houseNo, $houseExt);
    
    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    $klarna->setAddress(KlarnaFlags::IS_BILLING, $addr);
}

