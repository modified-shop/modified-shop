<?php
function correct_currency($pclass, $curr){
switch($pclass){
case 338:
return (0 == $curr);
case 337:
return (0 == $curr);
case 336:
return (0 == $curr);
case 351:
return (3 == $curr);
case 350:
return (3 == $curr);
case 145:
return (2 == $curr);
case 146:
return (2 == $curr);
case 320:
return (2 == $curr);
}
}

function get_months($pclass){
switch($pclass){
case 338:
return 24;
case 337:
return 24;
case 336:
return 6;
case 351:
return 6;
case 350:
return 24;
case 145:
return 3;
case 146:
return 6;
case 320:
return 24;
}
}

function get_month_fee($pclass){
switch($pclass){
case 338:
return 2900;
case 337:
return 2900;
case 336:
return 2900;
case 351:
return 3900;
case 350:
return 3900;
case 145:
return 395;
case 146:
return 395;
case 320:
return 395;
}
}

function get_start_fee($pclass){
switch($pclass){
case 338:
return 0;
case 337:
return 29500;
case 336:
return 19500;
case 351:
return 19500;
case 350:
return 0;
case 145:
return 950;
case 146:
return 1950;
case 320:
return 0;
}
}

function get_rate($pclass){
switch($pclass){
case 338:
return 1950;
case 337:
return 995;
case 336:
return 0;
case 351:
return 0;
case 350:
return 2200;
case 145:
return 0;
case 146:
return 0;
case 320:
return 2200;
}
}

function get_type($pclass){
switch($pclass){
case 338:
return 1;
case 337:
return 0;
case 336:
return 0;
case 351:
return 0;
case 350:
return 1;
case 145:
return 0;
case 146:
return 0;
case 320:
return 1;
}
}

function get_min_sum($pclass){
switch($pclass){
case 338:
return 20000;
case 337:
return 100000;
case 336:
return 100000;
case 351:
return 100000;
case 350:
return 20000;
case 145:
return 10000;
case 146:
return 10000;
case 320:
return 2000;
}
}

function get_pclass_ids(){
return array(338, 337, 336, 351, 350, 145, 146, 320);
}

?>
