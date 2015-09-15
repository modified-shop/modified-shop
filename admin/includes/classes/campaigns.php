<?php

/* -----------------------------------------------------------------------------------------
   $Id: campaigns.php 1180 2007-04-23 11:13:00Z Hetfield $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   (c) 2007 Hits for weekly, monthly and yearly for campaigns-report by Hetfield

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class campaigns {

	function __construct(& $get_array) {
		global $currencies;

    $required = array('startD', 'startM', 'startY', 'endD', 'endM', 'endY', 'status', 'campaign', 'report');
    if (count(array_intersect_key(array_flip($required), $get_array)) === count($required)) {
        
			$this->startD = $get_array['startD'];
			$this->startM = $get_array['startM'];
			$this->startY = $get_array['startY'];
			$this->startDate = mktime(0, 0, 0, $this->startM, $this->startD, $this->startY);
			$this->endD = $get_array['endD'];
			$this->endM = $get_array['endM'];
			$this->endY = $get_array['endY'];
			$this->endDate = mktime(0, 0, 0, $this->endM, $this->endD, $this->endY);
			$this->status = $get_array['status'];
			$this->campaign = $get_array['campaign'];
			$this->campaigns = $this->getCampaigns();

			if ($get_array['campaign'] == "0") {
				$this->SelectArray = $this->campaigns;

			} else {
				$this->SelectArray = $this->getSelectedCampaign();
			}
			$this->type = $get_array['report'];

			$this->result = array ();
			$this->total = array ();

			// query data

			$this->counter = 0;
			$this->counterCMP = 0;
			// tŠglich

			$this->getTotalLeads();
			$this->getTotalSells();

			for ($n = 0; $n < count($this->SelectArray); $n ++) {

				$this->campaign = $this->SelectArray[$n]['id'];
				$this->result[$this->counterCMP]['id'] = $this->campaign;
				$this->result[$this->counterCMP]['text'] = $this->camp[$this->campaign];

				switch ($this->type) {

					// yearly
					case 1 :
						$start = $this->startDate;

						while ($start <= $this->endDate) {

							$end = mktime(0, 0, 0, date("m", $start), date("d", $start), date("Y", $start) + 1);
							// get Leads
							$this->getLeads($start, $end, $this->type);
							// get Sells
							$this->getSells($start, $end, $this->type);
							
							$this->getHits($start, $end, $this->type);

							$start = $end;
							$this->counter++;

						}
						break;

						// monthly
					case 2 :
						$start = $this->startDate;

						while ($start <= $this->endDate) {

							$end = mktime(0, 0, 0, date("m", $start) + 1, date("d", $start), date("Y", $start));
							// get Leads
							$this->getLeads($start, $end, $this->type);
							// get Sells
							$this->getSells($start, $end, $this->type);
							
							$this->getHits($start, $end, $this->type);

							$start = $end;
							$this->counter++;

						}

						break;

						// weekly
					case 3 :
						$start = $this->startDate;

						while ($start <= $this->endDate) {

							$end = mktime(0, 0, 0, date("m", $start), date("d", $start) + 7, date("Y", $start));
							// get Leads
							$this->getLeads($start, $end, $this->type);
							// get Sells
							$this->getSells($start, $end, $this->type);
							
							$this->getHits($start, $end, $this->type);

							$start = $end;
							$this->counter++;

						}

						break;

						// daily
					case 4 :
						$start = $this->startDate;

						while ($start <= $this->endDate) {

							$end = mktime(0, 0, 0, date("m", $start), date("d", $start) + 1, date("Y", $start));
							// get Leads
							$this->getLeads($start, '', $this->type);
							// get Sells
							$this->getSells($start, '', $this->type);
							
							$this->getHits($start, '', $this->type);

							$start = $end;
							$this->counter++;

						}
						break;

				}
				$this->counter = 0;
				$this->counterCMP++;
			}
			//			$this->printResult();
			$this->total['sum_plain'] = $this->total['sum'];
			$this->total['sum'] = $currencies->format($this->total['sum']);
			
		}

	}

	function getCampaigns() {

		$campaign = array ();
		$campaign_query = "SELECT * FROM ".TABLE_CAMPAIGNS." ORDER BY campaigns_name";
		$campaign_query = xtc_db_query($campaign_query);
		while ($campaign_data = xtc_db_fetch_array($campaign_query)) {
			$campaign[] = array ('id' => $campaign_data['campaigns_refID'], 'text' => $campaign_data['campaigns_name']);
			$this->camp[$campaign_data['campaigns_refID']] = $campaign_data['campaigns_name'];
		}
		return $campaign;
	}

	function getSelectedCampaign() {

		$campaign = array ();
		$campaign_query = "SELECT * FROM ".TABLE_CAMPAIGNS." WHERE campaigns_refID='".$this->campaign."'";
		$campaign_query = xtc_db_query($campaign_query);
		while ($campaign_data = xtc_db_fetch_array($campaign_query)) {
			$campaign[] = array ('id' => $campaign_data['campaigns_refID'], 'text' => $campaign_data['campaigns_name']);

		}
		return $campaign;
	}

	function getTotalLeads() {
		$end = mktime(0, 0, 0, date("m", $this->endDate), date("d", $this->endDate) + 1, date("Y", $this->endDate));
		$selection = " and ci.customers_info_date_account_created>'".xtc_db_input(date("Y-m-d", $this->startDate))."'"." and ci.customers_info_date_account_created<'".xtc_db_input(date("Y-m-d", $end))."'";

		$lead_query = "SELECT count(*) as leads FROM ".TABLE_CUSTOMERS." c, ".TABLE_CUSTOMERS_INFO." ci WHERE c.customers_id=ci.customers_info_id".$selection;
		$lead_query = xtc_db_query($lead_query);
		$lead_data = xtc_db_fetch_array($lead_query);

		$this->total['leads'] = $lead_data['leads'];

	}

	function getTotalSells() {
		$end = mktime(0, 0, 0, date("m", $this->endDate), date("d", $this->endDate) + 1, date("Y", $this->endDate));
		$selection = " and o.date_purchased>'".xtc_db_input(date("Y-m-d", $this->startDate))."'"." and o.date_purchased<'".xtc_db_input(date("Y-m-d", $end))."'";
		$status = "";
		if ($this->status > 0)
			$status = " and o.orders_status='".$this->status."'";
		$sale_query = "SELECT count(*) as sells, SUM(ot.value/o.currency_value) as Summe FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_TOTAL." ot WHERE o.orders_id=ot.orders_id and ot.class='ot_total'".$selection.$status;
		$sale_query = xtc_db_query($sale_query);
		$sale_data = xtc_db_fetch_array($sale_query);

		$this->total['sells'] = $sale_data['sells'];
		$this->total['sum'] = $sale_data['Summe'];
	}

	function getSells($date_start, $date_end = '', $type) {
		global $currencies;

		switch ($type) {

			case 1 :
			case 2 :
			case 3 :
				$selection = " and o.date_purchased>'".xtc_db_input(date("Y-m-d", $date_start))."'"." and o.date_purchased<'".xtc_db_input(date("Y-m-d", $date_end))."'";

				break;

				// daily
			case 4 :
				$end = mktime(0, 0, 0, date("m", $date_start), date("d", $date_start) + 1, date("Y", $date_start));
				$selection = " and o.date_purchased>'".xtc_db_input(date("Y-m-d", $date_start))."'"." and o.date_purchased<'".xtc_db_input(date("Y-m-d", $end))."'";
				break;

		}

		$status = "";
		if ($this->status > 0)
			$status = " and o.orders_status='".$this->status."'";
		$sell_query = "SELECT count(*) as sells, SUM(ot.value/o.currency_value) as Summe FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_TOTAL." ot WHERE o.orders_id=ot.orders_id and ot.class='ot_total' and o.conversion_type='1' and o.refferers_id='".$this->campaign."'".$selection.$status;
		$sell_query = xtc_db_query($sell_query);
		$sell_data = xtc_db_fetch_array($sell_query);

		$late_sell_query = "SELECT count(*) as sells, SUM(ot.value/o.currency_value) as Summe FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_TOTAL." ot WHERE o.orders_id=ot.orders_id and ot.class='ot_total' and o.conversion_type='2' and o.refferers_id='".$this->campaign."'".$selection.$status;
		$late_sell_query = xtc_db_query($late_sell_query);
		$late_sell_data = xtc_db_fetch_array($late_sell_query);


		$this->result[$this->counterCMP]['result'][$this->counter]['sells'] = $sell_data['sells'];
		$this->result[$this->counterCMP]['result'][$this->counter]['sum'] =  $currencies->format(($sell_data['Summe']+$late_sell_data['Summe']));
		$this->result[$this->counterCMP]['sells_s'] += $sell_data['sells'];
		$this->result[$this->counterCMP]['sum_s'] += ($sell_data['Summe']+$late_sell_data['Summe']);
		if ($this->total['sells'] == 0) {
			$this->result[$this->counterCMP]['result'][$this->counter]['sells_p'] = 0;
			$this->result[$this->counterCMP]['result'][$this->counter]['late_sells_p'] = 0;
			$this->result[$this->counterCMP]['result'][$this->counter]['sum_p'] = 0;
		} else {
			$this->result[$this->counterCMP]['result'][$this->counter]['sells_p'] = $sell_data['sells'] / $this->total['sells'] * 100;
			$this->result[$this->counterCMP]['result'][$this->counter]['late_sells_p'] = round($late_sell_data['sells'] / $this->total['sells'] * 100,2);
			$this->result[$this->counterCMP]['result'][$this->counter]['sum_p'] = round(($sell_data['Summe']+$late_sell_data['Summe'])/$this->total['sum']*100,2);
		}
		$this->result[$this->counterCMP]['result'][$this->counter]['late_sells'] = $late_sell_data['sells'];
		$this->result[$this->counterCMP]['late_sells_s'] += $late_sell_data['sells'];

	}

	function getLeads($date_start, $date_end = '', $type) {

		switch ($type) {

			case 1 :
			case 2 :
			case 3 :
				$selection = " and ci.customers_info_date_account_created>'".xtc_db_input(date("Y-m-d", $date_start))."'"." and ci.customers_info_date_account_created<'".xtc_db_input(date("Y-m-d", $date_end))."'";

				break;

			case 4 :
				$end = mktime(0, 0, 0, date("m", $date_start), date("d", $date_start) + 1, date("Y", $date_start));
				$selection = " and ci.customers_info_date_account_created>'".xtc_db_input(date("Y-m-d", $date_start))."'"." and ci.customers_info_date_account_created<'".xtc_db_input(date("Y-m-d", $end))."'";

				break;

		}

		// select leads
		$lead_query = "SELECT count(*) as leads FROM ".TABLE_CUSTOMERS." c, ".TABLE_CUSTOMERS_INFO." ci WHERE c.customers_id=ci.customers_info_id AND c.refferers_id='".$this->campaign."'".$selection;
		$lead_query = xtc_db_query($lead_query);
		$lead_data = xtc_db_fetch_array($lead_query);

		$this->result[$this->counterCMP]['result'][$this->counter]['range'] = $this->getDateFormat($date_start, $date_end);
		$this->result[$this->counterCMP]['result'][$this->counter]['leads'] = $lead_data['leads'];
		$this->result[$this->counterCMP]['leads_s'] += $lead_data['leads'];
		if ($this->total['leads'] == 0) {
			$this->result[$this->counterCMP]['result'][$this->counter]['leads_p'] = 0;
		} else {
			$this->result[$this->counterCMP]['result'][$this->counter]['leads_p'] = $lead_data['leads'] / $this->total['leads'] * 100;
		}
	}
	
	function getHits($date_start, $date_end = '', $type) {

		switch ($type) {

			case 1 :
			case 2 :
			case 3 :
				$selection = " and time>'".xtc_db_input(date("Y-m-d", $date_start))."'"." and time <'".xtc_db_input(date("Y-m-d", $date_end))."'";

				break;

			case 4 :
				$end = mktime(0, 0, 0, date("m", $date_start), date("d", $date_start) + 1, date("Y", $date_start));
				$selection = " and time>'".xtc_db_input(date("Y-m-d", $date_start))."'"." and time<'".xtc_db_input(date("Y-m-d", $end))."'";

				break;

		}

		// select leads
		$hits_query = "SELECT count(*) as hits FROM ".TABLE_CAMPAIGNS_IP."  WHERE campaign='".$this->campaign."'".$selection;
		$hits_query = xtc_db_query($hits_query);
		$hits_data = xtc_db_fetch_array($hits_query);

		$this->result[$this->counterCMP]['result'][$this->counter]['hits'] = $hits_data['hits'];
		$this->result[$this->counterCMP]['hits_s'] += $hits_data['hits'];
		if ($this->total['hits'] == 0) {
			$this->result[$this->counterCMP]['result'][$this->counter]['hits_s'] = 0;
		} else {
			$this->result[$this->counterCMP]['result'][$this->counter]['hits_s'] = $hits_data['hits'] / $this->total['hits'] * 100;
		}
	}

	function getDateFormat($date_from, $date_to) {

		if ($date_from != $date_to && $date_to != '') {
			return date(DATE_FORMAT, $date_from).'-'.date(DATE_FORMAT, $date_to);
		} else {
			return date(DATE_FORMAT, $date_from);
		}

	}

	function printResult() {
		echo '<pre>';
		print_r($this->result);

		print_r($this->total);

		echo '</pre>';
	}

}
?>




