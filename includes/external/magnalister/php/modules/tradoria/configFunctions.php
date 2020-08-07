<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function TradoriaShippingAddressConfig($args) {
	global $_MagnaSession;
	$sHtml = '<table>';
	$form = array();
	
	$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_amazon');
	foreach ($args['subfields'] as $item){
		$idkey = str_replace('.', '_', $item['key']);
		$configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'],'');
		$value = '';
		if(isset($configValue[$args['currentIndex']])) {
			$value = $configValue[$args['currentIndex']];
		}
		$item['key'] .= '][';
		if(isset($item['params'])){
			$item['params']['value'] = $value;
		}
		$sHtml .='<tr><td>'. $cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item,$value).'</td></tr>';
	}
	$sHtml .= '</table>';
	return $sHtml;
}

function TradoriaShippingAddressCountryConfig($args) {
	$aCountries = array(
            "AW" => "Aruba",
            "AF" => "Afghanistan",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AX" => "Åland Inseln",
            "AL" => "Albanien",
            "AD" => "Andorra",
            "AN" => "Niederl&auml;ndische Antillen",
            "AE" => "Vereinigte Arabische Emirate",
            "AR" => "Argentinien",
            "AM" => "Armenien",
            "AS" => "Amerikanisch Samoa",
            "AQ" => "Antarktis",
            "TF" => "Franz&ouml;sische S&uuml;dgebiete",
            "AG" => "Antigua Und Barbuda",
            "AU" => "Australien",
            "AT" => "Österreich",
            "AZ" => "Aserbaidschan",
            "BI" => "Burundi",
            "BE" => "Belgien",
            "BJ" => "Benin",
            "BF" => "Burkina Faso",
            "BD" => "Bangladesch",
            "BG" => "Bulgarien",
            "BH" => "Bahrain",
            "BS" => "Bahamas",
            "BA" => "Bosnien Und Herzegowina",
            "BY" => "Weißrussland",
            "BZ" => "Belize",
            "BM" => "Bermuda",
            "BO" => "Bolivien",
            "BR" => "Brasilien",
            "BB" => "Barbados",
            "BN" => "Brunei Darussalam",
            "BT" => "Bhutan",
            "BV" => "Bouvetinsel",
            "BW" => "Botsuana",
            "CF" => "Zentralafrikanische Republik",
            "CA" => "Kanada",
            "CC" => "Kokosinseln",
            "CH" => "Schweiz",
            "CL" => "Chile",
            "CN" => "China",
            "CI" => "Côte D´ivoire",
            "CM" => "Kamerun",
            "CD" => "Kongo, Dem. Rep.",
            "CG" => "Kongo",
            "CK" => "Cookinseln",
            "CO" => "Kolumbien",
            "KM" => "Komoren",
            "CV" => "Kap Verde",
            "CR" => "Costa Rica",
            "CU" => "Kuba",
            "CX" => "Weihnachtsinsel",
            "KY" => "Kaimaninseln",
            "CY" => "Zypern",
            "CZ" => "Tschechische Republik",
            "DE" => "Deutschland",
            "DJ" => "Republik Dschibuti",
            "DM" => "Dominica",
            "DK" => "D&auml;nemark",
            "DO" => "Dominikanische Republik",
            "DZ" => "Algerien",
            "EC" => "Ecuador",
            "EG" => "Ägypten",
            "ER" => "Eritrea",
            "EH" => "Westsahara",
            "ES" => "Spanien",
            "EE" => "Estland",
            "ET" => "Äthiopien",
            "FI" => "Finnland",
            "FJ" => "Fidschi",
            "FK" => "Falklandinseln",
            "FR" => "Frankreich",
            "FO" => "F&auml;r&ouml;er",
            "FM" => "Mikronesien, F&ouml;derierte Staaten Von",
            "GA" => "Gabun",
            "GB" => "Vereinigtes K&ouml;nigreich",
            "GE" => "Georgien",
            "GG" => "Guernsey",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GN" => "Guinea",
            "GP" => "Guadeloupe",
            "GM" => "Gambia",
            "GW" => "Guinea-bissau",
            "GQ" => "Äquatorialguinea",
            "GR" => "Griechenland",
            "GD" => "Grenada",
            "GL" => "Gr&ouml;nland",
            "GT" => "Guatemala",
            "GF" => "Franz&ouml;sisch Guiana",
            "GU" => "Guam",
            "GY" => "Guyana",
            "HK" => "Hong Kong",
            "HM" => "Heard Insel Und Mcdonald Inseln",
            "HN" => "Honduras",
            "HR" => "Kroatien",
            "HT" => "Haiti",
            "HU" => "Ungarn",
            "ID" => "Indonesien",
            "IM" => "Isle Of Man",
            "IN" => "Indien",
            "IO" => "Britische Territorien Im Indischen Ozean",
            "IE" => "Irland",
            "IR" => "Iran, Islam. Rep.",
            "IQ" => "Irak",
            "IS" => "Island",
            "IL" => "Israel",
            "IT" => "Italien",
            "JM" => "Jamaika",
            "JE" => "Jersey",
            "JO" => "Jordanien",
            "JP" => "Japan",
            "KZ" => "Kasachstan",
            "KE" => "Kenia",
            "KG" => "Kirgisistan",
            "KH" => "Kambodscha",
            "KI" => "Kiribati",
            "KN" => "St. Kitts Und Nevis",
            "KR" => "Korea, Rep.",
            "KW" => "Kuwait",
            "LA" => "Laos, Dem. Volksrep.",
            "LB" => "Libanon",
            "LR" => "Liberia",
            "LY" => "Libysch-arabische Dschamahirija",
            "LC" => "St. Lucia",
            "LI" => "Liechtenstein",
            "LK" => "Sri Lanka",
            "LS" => "Lesotho",
            "LT" => "Litauen",
            "LU" => "Luxemburg",
            "LV" => "Lettland",
            "MO" => "Macao",
            "MA" => "Marokko",
            "MC" => "Monaco",
            "MD" => "Moldau, Rep.",
            "MG" => "Madagaskar",
            "MV" => "Malediven",
            "MX" => "Mexiko",
            "MH" => "Marshallinseln",
            "MK" => "Mazedonien, Ehemalige Jugoslawische Republik",
            "ML" => "Mali",
            "MT" => "Malta",
            "MM" => "Myanmar",
            "ME" => "Montenegro",
            "MN" => "Mongolei",
            "MP" => "N&ouml;rdliche Marianen",
            "MZ" => "Mosambik",
            "MR" => "Mauretanien",
            "MS" => "Montserrat",
            "MQ" => "Martinique",
            "MU" => "Mauritius",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "YT" => "Mayotte",
            "NA" => "Namibia",
            "NC" => "Neukaledonien",
            "NE" => "Niger",
            "NF" => "Norfolk Insel",
            "NG" => "Nigeria",
            "NI" => "Nicaragua",
            "NU" => "Niue",
            "NL" => "Niederlande",
            "NO" => "Norwegen",
            "NP" => "Nepal",
            "NR" => "Nauru",
            "NZ" => "Neuseeland",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PA" => "Panama",
            "PN" => "Pitcairn",
            "PE" => "Peru",
            "PH" => "Philippinen",
            "PW" => "Palau",
            "PG" => "Papua-neuguinea",
            "PL" => "Polen",
            "PR" => "Puerto Rico",
            "KP" => "Korea, Dem. Volksrep.",
            "PT" => "Portugal",
            "PY" => "Paraguay",
            "PS" => "Pal&auml;stinische Gebiete",
            "PF" => "Franz&ouml;sisch Polynesien",
            "QA" => "Katar",
            "RE" => "Réunion",
            "RO" => "Rum&auml;nien",
            "RU" => "Russische F&ouml;deration",
            "RW" => "Ruanda",
            "SA" => "Saudi-arabien",
            "SD" => "Sudan",
            "SN" => "Senegal",
            "SG" => "Singapur",
            "GS" => "S&uuml;dgeorgien Und Die S&uuml;dlichen Sandwichinseln",
            "SH" => "Saint Helena",
            "SJ" => "Svalbard Und Jan Mayen",
            "SB" => "Salomonen",
            "SL" => "Sierra Leone",
            "SV" => "El Salvador",
            "SM" => "San Marino",
            "SO" => "Somalia",
            "PM" => "Saint Pierre Und Miquelon",
            "RS" => "Serbien",
            "ST" => "São Tomé Und Príncipe",
            "SR" => "Suriname",
            "SK" => "Slowakei",
            "SI" => "Slowenien",
            "SE" => "Schweden",
            "SZ" => "Swasiland",
            "SC" => "Seychellen",
            "SY" => "Syrien, Arab. Rep.",
            "TC" => "Turks- Und Caicosinseln",
            "TD" => "Tschad",
            "TG" => "Togo",
            "TH" => "Thailand",
            "TJ" => "Tadschikistan",
            "TK" => "Tokelau",
            "TM" => "Turkmenistan",
            "TL" => "Timor-leste",
            "TO" => "Tonga",
            "TT" => "Trinidad Und Tobago",
            "TN" => "Tunesien",
            "TR" => "T&uuml;rkei",
            "TV" => "Tuvalu",
            "TW" => "Taiwan",
            "TZ" => "Tansania, Vereinigte Rep.",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "UM" => "United States Minor Outlying Islands",
            "UY" => "Uruguay",
            "US" => "Vereinigte Staaten Von Amerika",
            "UZ" => "Usbekistan",
            "VA" => "Heiliger Stuhl",
            "VC" => "St. Vincent Und Die Grenadinen",
            "VE" => "Venezuela",
            "VG" => "Britische Jungferninseln",
            "VI" => "Amerikanische Jungferninseln",
            "VN" => "Vietnam",
            "VU" => "Vanuatu",
            "WF" => "Wallis Und Futuna",
            "WS" => "Samoa",
            "YE" => "Jemen",
            "ZA" => "S&uuml;dafrika",
            "ZM" => "Sambia",
            "ZW" => "Simbabwe"
	);
	$sHtml = '<select name="conf['.$args['key'].']">';
	foreach ($aCountries as $iso => $name){
		$sHtml .='<option '.($args['value'] == $name? 'selected=selected' : '' ).' value="'.$name.'">'.fixHTMLUTF8Entities($name).'</option>';
	}
	$sHtml .= '</select>';
	return $sHtml;
}

function TradoriaOrderstatus($args) {
	$aStats = array ();
	mlGetOrderStatus($aStats);
	$sHtml = '<select name="conf['.$args['key'].']">';
	foreach ($aStats['values'] as $no => $name){
		$sHtml .='<option '.($args['value'] == $no? 'selected=selected' : '' ).' value="'.$no.'">'.fixHTMLUTF8Entities($name).'</option>';
	}
	$sHtml .= '</select>';
	return $sHtml;
}

