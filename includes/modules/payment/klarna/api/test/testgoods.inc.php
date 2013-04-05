<?php

if($klarna instanceof Klarna) {
    $testgoods = array();
    $testgoods[] = array("qty" => 1,
                         "artNo" => "1023",
                         "artTitle" => "a clock",
                         "price" => 5.95,
                         "vat" => 25,
                         "discount" => 0,
                         "flags" => KlarnaFlags::INC_VAT);
    
    $testgoods[] = array("qty" => 2,
                         "artNo" => "1024",
                         "artTitle" => "a pair of boots",
                         "price" => 3.50,
                         "vat" => 25,
                         "discount" => 0,
                         "flags" => KlarnaFlags::INC_VAT);

    $testgoods[] = array("qty" => 1,
                         "artNo" => "shipping",
                         "artTitle" => "shipping",
                         "price" => 1.99,
                         "vat" => 25,
                         "discount" => 0,
                         "flags" => KlarnaFlags::INC_VAT + KlarnaFlags::IS_SHIPMENT);
    
    foreach($testgoods as $goods) {
        $klarna->addArticle($goods['qty'], $goods['artNo'], $goods['artTitle'], $goods['price'], $goods['vat'], $goods['discount'], $goods['flags']);
    }
}
