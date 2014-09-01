<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/



class idealo_csv_shipping{
	public $shipping = array(	'DE'	=>	array(	'country'	=> 'DE',
													'active'	=> '0',
													'costs'		=> '',
													'free'		=> '',
													'type'		=> ''
													),
								'AT'	=>	array(	'country'	=> 'AT',
													'active'	=> '0',
													'costs'		=> '',
													'free'		=> '',
													'type'		=> ''
													)
								);
								
	 public function __construct() {}

}
	
?>
