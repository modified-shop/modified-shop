<?php
if($klarna instanceof Klarna) {
    $country = 'no';
    $klarna->setCountry($country);
    /* same as below
    $klarna->setCountry(KlarnaCountry::NO);
    $klarna->setLanguage(KlarnaCountry::NB);
    $klarna->setCurrency(KlarnaCountry::NOK);
    */

    //Customer info
    /*
    18106500157
    Petter Testmann
    Hundremeterskogen 100
    0563 Oslo
    40 123 456
     */
    $pno = "18106500157";
    $gender = null; //We do not need to send gender for Norway
    $fname = "Petter";
    $lname = "Testmann";
    $telno = "";
    $cellno = "40 123 456";
    $email = "petter.testmann@klarna.com";
    $ysalary = 0;

    //Address info
    $careof = "";
    $street = "Hundremeterskogen 100";
    $zip = "0563";
    $city = "Oslo";
    $houseNo = ""; //Only for NL/DE
    $houseExt = ""; //Only for NL
    
    $addr = new KlarnaAddr($email, $telno, $cellno, $fname, $lname, $careof, $street, $zip, $city, $country, $houseNo, $houseExt);

    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    $klarna->setAddress(KlarnaFlags::IS_BILLING, $addr);
}
