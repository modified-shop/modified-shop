<?php
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
    6020310139
    Kalle Anka AB
    Storgatan 1
    12345 Ankeborg
     */
    $pno = '6020310139';
    $gender = null; //Not used for Sweden
    $fname = "Karl";
    $lname = "Lidin";
    $telno = "";
    $cellno = "076 526 00 00";
    $email = "karl.lidin@klarna.com";
    $ysalary = 0;

    //Address info (address 1)
    $careof = "";
    $street = "Storgatan 1";
    $zip = "12345";
    $city = "Ankeborg";
    $houseNo = ""; //Only for NL and DE
    $houseExt = ""; //Only for NL

    $addr = new KlarnaAddr($email, $telno, $cellno, $fname, $lname, $careof, $street, $zip, $city, $country, $houseNo, $houseExt);
    $addr->setCompanyName('Kalle Anka AB');
    $addr->isCompany = true; //This might not be used.
    
    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    $klarna->setAddress(KlarnaFlags::IS_BILLING, $addr);
}

