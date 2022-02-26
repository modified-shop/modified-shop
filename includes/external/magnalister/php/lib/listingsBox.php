<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function generateListingsBox() {
	global $magnaConfig;
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetUsedListingsCountForDateRange',
			'SUBSYSTEM' => 'Core',
			'BEGIN' => date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), 1, date('Y'))),
			'END' => date("Y-m-d H:i:s"),
		));
		$usedListings = (int)$result['DATA']['UsedListings'];
	} catch (MagnaException $e) {
		$usedListings = -1;
	}

	$listings = array (
		'used' => $usedListings + (isset($_GET['l']) ? (int)$_GET['l'] : 0),
		'available' => $magnaConfig['maranon']['IncludedListings']+(isset($_GET['a']) ? ($_GET['a']+1) : 0)
	);
	
	$define = 'ML_RATE_'.strtoupper($magnaConfig['maranon']['Tariff']);
	$currentRate = defined($define) ? constant($define) : ML_LABEL_LISTINGSBASED;
	#echo print_m($magnaConfig['maranon'], 'maranon');
	
	if ($magnaConfig['maranon']['Tariff'] == 'FreeTrial') {
		$contractends = $magnaConfig['maranon']['TestEnds'];
	} else {
		$contractends = $magnaConfig['maranon']['CancellationDate'];
	}
	$contractends = strtotime($contractends);
	if ($contractends > 0) {
		$contractends = date('d.m.Y', $contractends);
	} else {
		$contractends = 0;
	}

    if (
            isset($magnaConfig['maranon']['TestEnds'])
            && new DateTime() > new DateTime($magnaConfig['maranon']['TestEnds'])
    ) {// last tariff was test he is continuing with another tariff
            $tarif = sprintf(ML_RATE_SWITCH_TRIAL, $magnaConfig['maranon']['TestEnds'], constant('ML_RATE_'.strtoupper($magnaConfig['maranon']['WishTariff'])));
    } elseif (
            ! isset($magnaConfig['maranon']['TestEnds'])
            &&
            (
                $magnaConfig['maranon']['Tariff'] == $magnaConfig['maranon']['WishTariff']
                || ($magnaConfig['maranon']['TariffChangeDate'] == '0000-00-00')
            )
            && ($magnaConfig['maranon']['CancellationDate'] == '0000-00-00')
        ) {//he has specific tariff(not test)
            $tarif = sprintf(ML_RATE_CONTINUE, $currentRate, $contractends);
    } else if (
            ($magnaConfig['maranon']['WishTariff'] != $magnaConfig['maranon']['Tariff'])
            && ($magnaConfig['maranon']['CancellationDate'] == '0000-00-00')
            && ($magnaConfig['maranon']['TariffChangeDate'] != '0000-00-00')
            || (
                isset($magnaConfig['maranon']['TestEnds'])
                && new DateTime() < new DateTime($magnaConfig['maranon']['TestEnds'])
            )
    ) {//it is still in test period
            $tarif = sprintf(ML_RATE_SWITCH, $currentRate,
                ($contractends === 0)
                ? date('d.m.Y', strtotime($magnaConfig['maranon']['TariffChangeDate']))
                : $contractends, constant('ML_RATE_'.strtoupper($magnaConfig['maranon']['WishTariff'])));
    } else {//he canceled contract
        $tarif = sprintf(ML_RATE_END, $currentRate, $contractends);
    }

    $isGambioCloudFreePlan = false;
    if (defined('SHOPSYSTEM_GAMBIO_CLOUD') && SHOPSYSTEM_GAMBIO_CLOUD === true) {
        if ((int)$usedListings < 20) {
            $listings['available'] = 20;
            $isGambioCloudFreePlan = true;
            $tarif = 'Gambio Cloud';
        } else {
            $tarif = 'Gambio Cloud '.$tarif;
        }
    }

	$tarif ='
		<tr>
			<th>'.ML_LABEL_RATE.':</th>
			<td>'.$tarif.'</td>
		</tr>';
 
	$listingsStatus = '';
	$upgrade = '';
		
	if ($listings['used'] < 0) {
		$listingsStatus = '
			<tr>
				<th class="nowrap">'.ML_LABEL_LISTINGS_USED_THIS_MONTH.':</th>
				<td class="fullWidth">'.ML_ERROR_LISTINGS_USED_UNKOWN.'</td>
			</tr>';
	} else if ($listings['available'] < 0) {
		$listingsStatus = '
			<tr>
				<th class="nowrap">'.ML_LABEL_LISTINGS_USED_THIS_MONTH.':</th>
				<td class="fullWidth">'.$listings['used'].'</td>
			</tr>';
	} else {
		$percent = min(100.0, round($listings['used']/$listings['available'] * 100, 2));
		$listingsStatus = '
			<tr>
				<th class="nowrap">'.ML_LABEL_LISTINGS_USED_THIS_MONTH.':</th>
				<td class="fullWidth">
					<div id="listingsBar">
						<img src="'.DIR_MAGNALISTER_WS_IMAGES.'listingsbar.png" alt="'.$listings['used'].' / '.$listings['available'].'"/>
						<div class="bar" style="width:'.(100 - $percent).'%"></div>
						<div class="bar_sep" style="width:'.$percent.'%"></div>
						<div class="percent" title="'.$listings['used'].' / '.$listings['available'].'">'.$percent.'%</div>
					</div>
				</td>
			</tr>';
		if ($listings['used'] > $listings['available']) {
			$upgrade = '
				<tr><th>'.ML_LABEL_LISTINGS_UPGRADE_HEADLINE.'</th><td>
					'.sprintf(ML_TEXT_LISTING_EXCEEDED, ($listings['used'] - $listings['available']), $magnaConfig['maranon']['ShopID']).'
				</td></tr>';
		
		} else if (($percent >= 80) && ($magnaConfig['maranon']['Tariff'] != 'FreeTrial') && $isGambioCloudFreePlan === false) {
			$upgrade = '
				<tr><th>'.ML_LABEL_LISTINGS_UPGRADE_HEADLINE.'</th><td>
					'.sprintf(ML_TEXT_LISTING_ALMOST_EMPTY, 
						(100 - $percent),
						$magnaConfig['maranon']['ShopID']
					).'
				</td></tr>';
		}
	}

	return '
		<table class="magnaframe"><tbody><tr><td>
			<table class="fullWidth"><tbody>'.$listingsStatus.'</tbody></table>
			<table class="valigntop normaltext"><tbody>'.$tarif.$upgrade.'</tbody></table>
		</td></tr></tbody></table>
	';
}