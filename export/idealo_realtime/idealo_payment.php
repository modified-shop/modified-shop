<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/




class idealo_payment{
	public $payment = array(	'PREPAID'   				=> array('active' 		=> '0',
																	 'title' 		=> 'Vorkasse',
																	 'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'PREPAID',
																	 'country'		=> ''),
								 'COD'	 					=> array('active' 		=> '0',
																	 'title' 		=> 'Nachnahme',
																	 'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'COD',
																	 'country'		=> ''),
								 'INVOICE' 					=> array('active' 		=> '0',
																	 'title' 		=> 'Rechnung',
																	  'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'INVOICE',
																	 'country'		=> ''),
								 'CREDITCARD' 						=> array('active' 		=> '0',
																	 'title' 		=> 'Kreditkarte',
																	  'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'CREDITCARD',
																	 'country'		=> ''),
								 'DIRECTDEBIT' 			=> array('active' 		=> '0',
																	 'title' 		=> 'Lastschrift',
																	 'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'DIRECTDEBIT',
																	 'country'		=> ''),
								 'PAYPAL' 					=> array('active' 		=> '0',
																	 'title' 		=> 'PayPal',
																	 'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'PAYPAL',
																	 'country'		=> ''),
								'SOFORTUEBERWEISUNG' 		=> array('active' 		=> '0',
																	 'title' 		=> 'Sofortueberweisung',
																	 'percent' 		=> '',
																	 'fix' 			=> '',
																	 'shipping'		=> '0',
																	 'db'			=> 'SOFORTUEBERWEISUNG',
																	 'country'		=> ''),
								);
								
}	
 
?>
