<?php
/* --------------------------------------------------------------
   configuration_get_conf.inc.php 2008-08-06 gambio
   Gambio OHG
   http://www.gambio.de
   Copyright (c) 2008 Gambio OHG
   Released under the GNU General Public License
   --------------------------------------------------------------
*/
?><?php

	
	/*
		-> function to get shop_configuration values
	*/
	
	function xtc_get_shop_conf($configuration_key, $result_type = 'ASSOC') {

		$configuration_values = false;
	
		if($result_type == 'ASSOC' || $result_type == 'NUMERIC'){

			if(is_array($configuration_key)){
				foreach($configuration_key as $key){
					$configuration_query = xtc_db_query("
											SELECT
												configuration_value
											FROM
												shop_configuration
											WHERE
												configuration_key = '" . $key . "'
												LIMIT 1
											");
					if(xtc_db_num_rows($configuration_query) == 1){
						if($configuration_values == false) $configuration_values = array();
						$configuration_row = xtc_db_fetch_array($configuration_query);
						if($result_type == 'ASSOC') {
							$configuration_values[$key] = $configuration_row['configuration_value'];
						} else {
							$configuration_values[] = $configuration_row['configuration_value'];				
						}					
					}
				}
			}
			else{
				$configuration_query = xtc_db_query("
										SELECT
											configuration_value
										FROM
											shop_configuration
										WHERE
											configuration_key = '" . $configuration_key . "'
											LIMIT 1
										");

				if(xtc_db_num_rows($configuration_query) == 1){
					if($configuration_values == false) $configuration_values = '';
					$configuration_row = xtc_db_fetch_array($configuration_query);
					$configuration_values = $configuration_row['configuration_value'];
				}
			}
		}
		return $configuration_values;
	}
?>