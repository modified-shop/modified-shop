<?php
if($klarna instanceof Klarna) {
    $country = 'dk';
    $klarna->setCountry($country);
    /* same as below
    $klarna->setCountry(KlarnaCountry::DA);
    $klarna->setLanguage(KlarnaLanguage::DK);
    $klarna->setCurrency(KlarnaCurrency::DKK);
    */

    //Customer info
    $pno = "0505610059";
    $gender = KlarnaFlags::MALE;
    $fname = "Rasmus Jens-Peter";
    $lname = "Lybert";
    $cellno = "20 123 456";
    $telno = "";
    $email = "rasmus.lybert@klarna.com";
    $ysalary = 1;

    //Address info
    $careof = "";
    $street = "Godthåbvej 8,-2";
    $zip = "3900";
    $city = "Godthåb";
    $houseNo = ""; //Only for NL and DE
    $houseExt = ""; //Only for NL

    $addr = new KlarnaAddr($email, $telno, $cellno, $fname, $lname, $careof, $street, $zip, $city, $country, $houseNo, $houseExt);

    $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    $klarna->setAddress(KlarnaFlags::IS_BILLING, $addr);
}

