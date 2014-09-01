<?php

/* --------------------------------------------------------------
   $Id: import.php 899 2009-09-13 07:44:47Z joerg $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   --------------------------------------------------------------
*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/*******************************************************************************
 **
 *C xtcImport . . . . . . . . . . . . . . . . . . . . . . . . . . . .  xtcImport
 **
 ******************************************************************************/
class xtcImport {

  /*****************************************************************************
   **
   *F xtcImport . . . . . . . . . . . . . . . . . . . . . . . . . . .  xtcImport
   **
	 ****************************************************************************/
	function xtcImport($filename) {
		$this->seperator = CSV_SEPERATOR;
		$this->TextSign = CSV_TEXTSIGN;
		//BOF - Dokuman - 2010-02-11 - set default textsign
		if (trim(CSV_TEXTSIGN) == '') {
			$this->TextSign = '"';
		}
    //EOF - Dokuman - 2010-02-11 - set default textsign
		if (CSV_SEPERATOR == '') {
			$this->seperator = "\t";
		}
		if (CSV_SEPERATOR == '\t') {
			$this->seperator = "\t";
		}
		$this->filename = $filename;
		$this->ImportDir = DIR_FS_CATALOG.'import/';
		$this->catDepth = 6;
		$this->languages = $this->get_lang();
		$this->counter = array ('prod_new' => 0, 'cat_new' => 0, 'prod_upd' => 0, 'cat_upd' => 0);
		$this->mfn = $this->get_mfn();
		$this->errorlog = array ();
		$this->time_start = time();
		$this->debug = false;
		$this->CatTree = array ('ID' => 0);
		// precaching categories in array ?
		$this->CatCache = true;
		$this->FileSheme = array ();
		$this->Groups = xtc_get_customers_statuses();
	}

  /*****************************************************************************
	 **
	 *F generate_map . . . . . . . . . . . . . . . . . . . . . . . .  generate_map
	 **
	 **   generating file layout
	 **
	 **   @param array $mapping standard fields
	 **   @return array
	 **
	 ****************************************************************************/
	function generate_map() {

		// lets define a standard fieldmapping array, with importable fields
		$file_layout = array (
			'p_model' => '', // products_model
			'p_stock' => '', // products_quantity
			'p_tpl' => '', // products_template
			'p_sorting' => '', // products_sorting
			'p_manufacturer' => '', // manufacturer
			'p_fsk18' => '', // FSK18
			'p_priceNoTax' => '', // Nettoprice
			'p_tax' => '', // taxrate in percent
			'p_status' => '', // products status
			'p_weight' => '', // products weight
			'p_ean' => '', // products ean
			'p_disc' => '', // products discount
			'p_opttpl' => '', // options template
			'p_image' => '', // product image
			'p_vpe' => '', // products VPE
			'p_vpe_status' => '', // products VPE Status
			'p_vpe_value' => '', // products VPE value
			'p_shipping' => '' // product shipping_time
		);
		
		// Group Prices
		for ($i = 0; $i < count($this->Groups) - 1; $i ++) {
			$file_layout = array_merge($file_layout, array ('p_priceNoTax.'.$this->Groups[$i +1]['id'] => ''));
		}

		// Group Permissions
		for ($i = 0; $i < count($this->Groups) - 1; $i ++) {
			$file_layout = array_merge($file_layout, array ('p_groupAcc.'.$this->Groups[$i +1]['id'] => ''));
		}

		// product images
		for ($i = 1; $i < MO_PICS + 1; $i ++) {
			$file_layout = array_merge($file_layout, array ('p_image.'.$i => ''));
		}

		// add lang fields
		for ($i = 0; $i < sizeof($this->languages); $i ++) {
			$file_layout = array_merge($file_layout, array ('p_name.'.$this->languages[$i]['code'] => '', 'p_desc.'.$this->languages[$i]['code'] => '', 'p_shortdesc.'.$this->languages[$i]['code'] => '', 'p_meta_title.'.$this->languages[$i]['code'] => '', 'p_meta_desc.'.$this->languages[$i]['code'] => '', 'p_meta_key.'.$this->languages[$i]['code'] => '','p_keywords.'.$this->languages[$i]['code'] => '', 'p_url.'.$this->languages[$i]['code'] => ''));
		}
		// add categorie fields
		for ($i = 0; $i < $this->catDepth; $i ++)
			$file_layout = array_merge($file_layout, array ('p_cat.'.$i => ''));

		return $file_layout;
	}

	/*****************************************************************************
	 **
	 *F map_file . . . . . . . . . . . . . . . . . . . . . . . . . . . .  map_file
	 **
	 **  generating mapping layout for importfile
	 **  @param array $mapping standard fields
	 **  @return array
	 ****************************************************************************/
	function map_file($mapping) {
		if (!file_exists($this->ImportDir.$this->filename)) {
			// error
			return 'error';
		} else {
			// file is ok, creating mapping
			$inhalt = array ();
			$inhalt = file($this->ImportDir.$this->filename);
			// get first line into array
			$content = explode($this->seperator, $inhalt[0]);

			foreach ($mapping as $key => $value) {
				// try to find our field in fieldlayout
				foreach ($content as $key_c => $value_c)
					if ($key == trim($this->RemoveTextNotes($content[$key_c]))) {
						$mapping[$key] = trim($this->RemoveTextNotes($key_c));
						$this->FileSheme[$key] = 'Y';
					}

			}
			return $mapping;
		}
	}

	/*****************************************************************************
	 **
	 *F get_lang . . . . . . . . . . . . . . . . . . . . . . . . . . . .  get_lang
	 **
	 **   Get installed languages
	 **
	 **   @return array
	 ****************************************************************************/
	function get_lang() {

		$languages_query = xtc_db_query("select languages_id, name, code, image, directory from ".TABLE_LANGUAGES." order by sort_order");
		while ($languages = xtc_db_fetch_array($languages_query)) {
			$languages_array[] = array ('id' => $languages['languages_id'], 'name' => $languages['name'], 'code' => $languages['code']);
		}

		return $languages_array;
	}

	/*****************************************************************************
	 **
	 *F import . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  import
	 **
	 ****************************************************************************/
	function import($mapping) {

		// open file
		$fp = fopen($this->ImportDir.$this->filename, 'r');

		// read the header line
// BOF - Tomcraft - 2010-04-13 - Bugfix for PHP4 (Length cannot be NULL)
		//$header = fgetcsv($fp, NULL, $this->seperator, $this->TextSign);
		$header = fgetcsv($fp, 20000, $this->seperator, $this->TextSign);
// EOF - Tomcraft - 2010-04-13 - Bugfix for PHP4 (Length cannot be NULL)
		foreach($header as $key=>$name) {
				$mapping[$name] = $key;
		}

// BOF - Tomcraft - 2010-04-13 - Bugfix for PHP4 (Length cannot be NULL)
		//while ($line = fgetcsv($fp, NULL, $this->seperator, $this->TextSign)) {
		while ($line = fgetcsv($fp, 20000, $this->seperator, $this->TextSign)) {
// EOF - Tomcraft - 2010-04-13 - Bugfix for PHP4 (Length cannot be NULL)
			foreach($mapping as $name => $key) {
				$line_data[$name] = $line[$key];
			}

			if ($line_data['p_model'] != '') {
				if ($line_data['p_cat.0'] != '' || $this->FileSheme['p_cat.0'] != 'Y') {
					if ($this->FileSheme['p_cat.0'] != 'Y') {
						if ($this->checkModel($line_data['p_model'])) {
							$this->insertProduct($line_data, 'update');
						} else {
							$this->insertProduct($line_data,'insert');
						}
					} else {
						if ($this->checkModel($line_data['p_model'])) {
							$this->insertProduct($line_data, 'update',true);
						} else {
							$this->insertProduct($line_data,'insert',true);
						}
					}
				} else {
					$this->errorLog[] = '<b>ERROR:</b> no Categorie, line: '.$i.' dataset: '.$line_fetch['data'];
				}
			} else {
				$this->errorLog[] = '<b>ERROR:</b> no Modelnumber, line: '.$i.' dataset: '.$line_fetch['data'];
			}
		}
		return array ($this->counter, $this->errorLog, $this->calcElapsedTime($this->time_start));
	}

	/*****************************************************************************
	 **
	 *F checkModel . . . . . . . . . . . . . . . . . . . . . . . . . .  checkModel
	 **
	 ** Check if a product exists in database, query for model number
	 **
	 ** @param string $model products modelnumber
	 ** @return boolean
	 **
	 ****************************************************************************/
	function checkModel($model) {
		$model_query = xtc_db_query("SELECT products_id FROM ".TABLE_PRODUCTS." WHERE products_model='".addslashes($model)."'");
		if (!xtc_db_num_rows($model_query))
			return false;
		return true;
	}

	/*****************************************************************************
	 **
	 *F checkImage . . . . . . . . . . . . . . . . . . . . . . . . . .  checkImage
	 **
	 ** Check if a image exists
	 **
	 ** @param string $model products modelnumber
	 ** @return boolean
	 **
	*****************************************************************************/
	function checkImage($imgID,$pID) {
		$img_query = xtc_db_query("SELECT image_id FROM ".TABLE_PRODUCTS_IMAGES." WHERE products_id='".$pID."' and image_nr='".$imgID."'");
		if (!xtc_db_num_rows($img_query))
			return false;
		return true;
	}

	/*****************************************************************************
	 **
	 *F RemoveTextNotes . . . . . . . . . . . . . . . . . . . . .  RemoveTextNotes
	 **
	 ** removing textnotes from a dataset
	 **
	 ** @param String $data data
	 ** @return String cleaned data
	 **
	 ****************************************************************************/
	function RemoveTextNotes($data) {
		if (substr($data, -1) == $this->TextSign)
			$data = substr($data, 1, strlen($data) - 2);
		return $data;

	}

	/*****************************************************************************
	 **
	 *F getMAN . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  getMAN
	 **
	 ** Get/create manufacturers ID for a given Name
	 **
	 ** @param String $manufacturer Manufacturers name
	 ** @return int manufacturers ID
	 **
	 ****************************************************************************/
	function getMAN($manufacturer) {
		if ($manufacturer == '')
			return;
		if (isset ($this->mfn[$manufacturer]['id']))
			return $this->mfn[$manufacturer]['id'];
		// BOF - vr - 18.04.2010 escape manufacturer
		// $man_query = xtc_db_query("SELECT manufacturers_id FROM ".TABLE_MANUFACTURERS." WHERE manufacturers_name = '".$manufacturer."'");
		$man_query = xtc_db_query("SELECT manufacturers_id FROM ".TABLE_MANUFACTURERS." WHERE manufacturers_name = '". mysql_real_escape_string($manufacturer) ."'");
		// EOF - vr - 18.04.2010 escape manufacturer
		if (!xtc_db_num_rows($man_query)) {
			$manufacturers_array = array ('manufacturers_name' => $manufacturer);
			xtc_db_perform(TABLE_MANUFACTURERS, $manufacturers_array);
			$this->mfn[$manufacturer]['id'] = mysql_insert_id();
		} else {
			$man_data = xtc_db_fetch_array($man_query);
			$this->mfn[$manufacturer]['id'] = $man_data['manufacturers_id'];

		}
		return $this->mfn[$manufacturer]['id'];
	}

	/*****************************************************************************
	 **
	 *F insertProduct . . . . . . . . . . . . . . . . . . . . . . .  insertProduct
	 **
	 ** Insert a new product into Database
	 **
	 ** @param array $dataArray Linedata
	 ** @param string $mode insert or update flag
	 **
	 ****************************************************************************/
	function insertProduct(& $dataArray, $mode = 'insert',$touchCat = false) {

		$products_array = array ('products_model' => $dataArray['p_model']);
		if ($this->FileSheme['p_stock'] == 'Y')
			$products_array = array_merge($products_array, array ('products_quantity' => $dataArray['p_stock']));
		if ($this->FileSheme['p_priceNoTax'] == 'Y')
			$products_array = array_merge($products_array, array ('products_price' => $dataArray['p_priceNoTax']));
		if ($this->FileSheme['p_weight'] == 'Y')
			$products_array = array_merge($products_array, array ('products_weight' => $dataArray['p_weight']));
		if ($this->FileSheme['p_status'] == 'Y')
			$products_array = array_merge($products_array, array ('products_status' => $dataArray['p_status']));
		if ($this->FileSheme['p_image'] == 'Y')
			$products_array = array_merge($products_array, array ('products_image' => $dataArray['p_image']));
		if ($this->FileSheme['p_disc'] == 'Y')
			$products_array = array_merge($products_array, array ('products_discount_allowed' => $dataArray['p_disc']));
		if ($this->FileSheme['p_ean'] == 'Y')
			$products_array = array_merge($products_array, array ('products_ean' => $dataArray['p_ean']));
		if ($this->FileSheme['p_tax'] == 'Y')
			$products_array = array_merge($products_array, array ('products_tax_class_id' => $dataArray['p_tax']));
		if ($this->FileSheme['p_opttpl'] == 'Y')
			$products_array = array_merge($products_array, array ('options_template' => $dataArray['p_opttpl']));
		if ($this->FileSheme['p_manufacturer'] == 'Y')
			$products_array = array_merge($products_array, array ('manufacturers_id' => $this->getMAN(trim($dataArray['p_manufacturer']))));
		if ($this->FileSheme['p_fsk18'] == 'Y')
			$products_array = array_merge($products_array, array ('products_fsk18' => $dataArray['p_fsk18']));
		if ($this->FileSheme['p_tpl'] == 'Y')
			$products_array = array_merge($products_array, array ('product_template' => $dataArray['p_tpl']));
		if ($this->FileSheme['p_vpe'] == 'Y')
			$products_array = array_merge($products_array, array ('products_vpe' => $dataArray['p_vpe']));
		if ($this->FileSheme['p_vpe_status'] == 'Y')
			$products_array = array_merge($products_array, array ('products_vpe_status' => $dataArray['p_vpe_status']));
		if ($this->FileSheme['p_vpe_value'] == 'Y')
			$products_array = array_merge($products_array, array ('products_vpe_value' => $dataArray['p_vpe_value']));
		if ($this->FileSheme['p_shipping'] == 'Y')
			$products_array = array_merge($products_array, array ('products_shippingtime' => $dataArray['p_shipping']));
		if ($this->FileSheme['p_sorting'] == 'Y')
			$products_array = array_merge($products_array, array ('products_sort' => $dataArray['p_sorting']));
		$products_array = array_merge($products_array, array ('products_date_added' => 'now()'));

		if ($mode == 'insert') {
			$this->counter['prod_new']++;
			xtc_db_perform(TABLE_PRODUCTS, $products_array);
			$products_id = mysql_insert_id();
		} else {
			$this->counter['prod_upd']++;
			xtc_db_perform(TABLE_PRODUCTS, $products_array, 'update', 'products_model = \''.addslashes($dataArray['p_model']).'\'');
			$prod_query = xtc_db_query("SELECT products_id FROM ".TABLE_PRODUCTS." WHERE products_model='".addslashes($dataArray['p_model'])."'");
			$prod_data = xtc_db_fetch_array($prod_query);
			$products_id = $prod_data['products_id'];

		}

		// Insert Group Prices.
		for ($i = 0; $i < count($this->Groups) - 1; $i ++) {
			// seperate string ::
			if (isset ($dataArray['p_priceNoTax.'.$this->Groups[$i +1]['id']])) {
			// BOF - vr - 2010-03-16 use $products_id instead
				// $truncate_query = "DELETE FROM ".TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i +1]['id']." WHERE products_id='".$prod_data['products_id']."'";
				$truncate_query = "DELETE FROM ".TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i +1]['id']." WHERE products_id='".$products_id."'";
			// EOF - vr - 2010-03-16 use $products_id instead
				xtc_db_query($truncate_query);
				$prices = $dataArray['p_priceNoTax.'.$this->Groups[$i +1]['id']];
				$prices = explode('::', $prices);
				for ($ii = 0; $ii < count($prices); $ii ++) {
					$values = explode(':', $prices[$ii]);
					// BOF - vr - 2010-03-16 use $products_id instead
					// $group_array = array ('products_id' => $prod_data['products_id'], 'quantity' => $values[0], 'personal_offer' => $values[1]);
					$group_array = array ('products_id' => $products_id, 'quantity' => $values[0], 'personal_offer' => $values[1]);
					// EOF - vr - 2010-03-16 use $products_id instead

					xtc_db_perform(TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i +1]['id'], $group_array);
				}
			}
		}

		// Insert Group Permissions.
		for ($i = 0; $i < count($this->Groups) - 1; $i ++) {
			// seperate string ::
			if (isset ($dataArray['p_groupAcc.'.$this->Groups[$i +1]['id']])) {
				$insert_array = array ('group_permission_'.$this->Groups[$i +1]['id'] => $dataArray['p_groupAcc.'.$this->Groups[$i +1]['id']]);
				xtc_db_perform(TABLE_PRODUCTS, $insert_array, 'update', 'products_id = \''.$products_id.'\'');
			}
		}

		// insert images
		for ($i = 1; $i < MO_PICS + 1; $i ++) {
			if (isset($dataArray['p_image.'.$i]) && $dataArray['p_image.'.$i]!="") {
			// check if entry exists
			if ($this->checkImage($i,$products_id)) {
				$insert_array = array ('image_name' => $dataArray['p_image.'.$i]);
				xtc_db_perform(TABLE_PRODUCTS_IMAGES, $insert_array, 'update', 'products_id = \''.$products_id.'\' and image_nr=\''.$i.'\'');
			} else {
				$insert_array = array ('image_name' => $dataArray['p_image.'.$i],'image_nr'=>$i,'products_id'=>$products_id);
				xtc_db_perform(TABLE_PRODUCTS_IMAGES, $insert_array);
			}
		}
		}

		if ($touchCat) $this->insertCategory($dataArray, $mode, $products_id);
		for ($i_insert = 0; $i_insert < sizeof($this->languages); $i_insert ++) {
			$prod_desc_array = array ('products_id' => $products_id, 'language_id' => $this->languages[$i_insert]['id']);

			if ($this->FileSheme['p_name.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_name' => addslashes($dataArray['p_name.'.$this->languages[$i_insert]['code']])));
			if ($this->FileSheme['p_desc.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_description' => addslashes($dataArray['p_desc.'.$this->languages[$i_insert]['code']])));
			if ($this->FileSheme['p_shortdesc.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_short_description' => addslashes($dataArray['p_shortdesc.'.$this->languages[$i_insert]['code']])));
			if ($this->FileSheme['p_meta_title.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_meta_title' => $dataArray['p_meta_title.'.$this->languages[$i_insert]['code']]));
			if ($this->FileSheme['p_meta_desc.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_meta_description' => $dataArray['p_meta_desc.'.$this->languages[$i_insert]['code']]));
			if ($this->FileSheme['p_meta_key.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_meta_keywords' => $dataArray['p_meta_key.'.$this->languages[$i_insert]['code']]));
			if ($this->FileSheme['p_keywords.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_keywords' => $dataArray['p_keywords.'.$this->languages[$i_insert]['code']]));
			if ($this->FileSheme['p_url.'.$this->languages[$i_insert]['code']] == 'Y')
				$prod_desc_array = array_merge($prod_desc_array, array ('products_url' => $dataArray['p_url.'.$this->languages[$i_insert]['code']]));

			if ($mode == 'insert') {
				xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $prod_desc_array);
			} else {
				xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $prod_desc_array, 'update', 'products_id = \''.$products_id.'\' and language_id=\''.$this->languages[$i_insert]['id'].'\'');
			}
		}
	}

	/*****************************************************************************
	 **
	 *F insertCategory . . . . . . . . . . . . . . . . . . . . . .  insertCategory
	 **
	 ** Match and insert Categories
	 **
	 ** @param array $dataArray data array
	 ** @param string $mode insert mode
	 ** @param int $pID  products ID
	 ****************************************************************************/
	function insertCategory(& $dataArray, $mode = 'insert', $pID) {
		if ($this->debug) {
			echo '<pre>';
			//print_ r($this->CatTree);
			echo '</pre>';
		}
		$cat = array ();
		$catTree = '';
		for ($i = 0; $i < $this->catDepth; $i ++)
			if (trim($dataArray['p_cat.'.$i]) != '') {
				$cat[$i] = trim($dataArray['p_cat.'.$i]);
				$catTree .= '[\''.addslashes($cat[$i]).'\']';
			}
		$code = '$ID=$this->CatTree'.$catTree.'[\'ID\'];';
		if ($this->debug)
			echo $code;
		eval ($code);

		if (is_int($ID) || $ID == '0') {
			$this->insertPtoCconnection($pID, $ID);
		} else {

			$catTree = '';
			$parTree = '';
			$curr_ID = 0;
			for ($i = 0; $i < count($cat); $i ++) {

				$catTree .= '[\''.addslashes($cat[$i]).'\']';

				$code = '$ID=$this->CatTree'.$catTree.'[\'ID\'];';
				eval ($code);
				if (is_int($ID) || $ID == '0') {
					$curr_ID = $ID;
				} else {

					$code = '$parent=$this->CatTree'.$parTree.'[\'ID\'];';
					eval ($code);
					// check if categorie exists
					$cat_query = xtc_db_query("SELECT c.categories_id FROM ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd
																									                                            WHERE
																									                                            cd.categories_name='".addslashes($cat[$i])."'
																									                                            and cd.language_id='".$this->languages[0]['id']."'
																									                                            and cd.categories_id=c.categories_id
																									                                            and parent_id='".$parent."'");

					if (!xtc_db_num_rows($cat_query)) { // insert categorie
						$categorie_data = array ('parent_id' => $parent, 'categories_status' => 1, 'date_added' => 'now()', 'last_modified' => 'now()');

						xtc_db_perform(TABLE_CATEGORIES, $categorie_data);
						$cat_id = mysql_insert_id();
						$this->counter['cat_new']++;
						$code = '$this->CatTree'.$parTree.'[\''.addslashes($cat[$i]).'\'][\'ID\']='.$cat_id.';';
						eval ($code);
						$parent = $cat_id;
						for ($i_insert = 0; $i_insert < sizeof($this->languages); $i_insert ++) {
							$categorie_data = array ('language_id' => $this->languages[$i_insert]['id'], 'categories_id' => $cat_id, 'categories_name' => $cat[$i]);
							xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION, $categorie_data);

						}
					} else {
						$this->counter['cat_touched']++;
						$cData = xtc_db_fetch_array($cat_query);
						$cat_id = $cData['categories_id'];
						$code = '$this->CatTree'.$parTree.'[\''.addslashes($cat[$i]).'\'][\'ID\']='.$cat_id.';';
						eval ($code);
					}

				}
				$parTree = $catTree;
			}
			$this->insertPtoCconnection($pID, $cat_id);
		}

	}

	/*****************************************************************************
	 **
	 *F insertPtoCconnection . . . . . . . . . . . . . . . .  insertPtoCconnection
	 **
	 ** Insert products to categories connection
	 **
	 ** @param int $pID products ID
	 ** @param int $cID categories ID
	 **
	 ****************************************************************************/
	function insertPtoCconnection($pID, $cID) {
		$prod2cat_query = xtc_db_query("SELECT *
										                                    FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
										                                    WHERE
										                                    categories_id='".$cID."'
										                                    and products_id='".$pID."'");

		if (!xtc_db_num_rows($prod2cat_query)) {
			$insert_data = array ('products_id' => $pID, 'categories_id' => $cID);

			xtc_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, $insert_data);
		}
	}

	/*****************************************************************************
	 **
	 *F get_line_content . . . . . . . . . . . . . . . . . . . .  get_line_content
	 **
	 ** Parse Inputfile until next line
	 **
	 ** @param int $line taxrate in percent
	 ** @param string $file_content taxrate in percent
	 ** @param int $max_lines taxrate in percent
	 ** @return array
	 ****************************************************************************/
	function get_line_content($line, $file_content, $max_lines) {
		// get first line
		$line_data = array ();
		$line_data['data'] = $file_content[$line];
		$lc = 1;
		// check if next line got ; in first 50 chars
		while (!strstr(substr($file_content[$line + $lc], 0, 6), 'XTSOL') && $line + $lc <= $max_lines) {
			$line_data['data'] .= $file_content[$line + $lc];
			$lc ++;
		}
		$line_data['skip'] = $lc -1;
		return $line_data;
	}

	/*****************************************************************************
	 **
	 *F calcElapsedTime . . . . . . . . . . . . . . . . . . . . .  calcElapsedTime
	 **
	 ** Calculate Elapsed time from 2 given Timestamps
	 ** @param int $time old timestamp
	 ** @return String elapsed time
	 **
	 ****************************************************************************/
	function calcElapsedTime($time) {

		// calculate elapsed time (in seconds!)
		$diff = time() - $time;
		$daysDiff = 0;
		$hrsDiff = 0;
		$minsDiff = 0;
		$secsDiff = 0;

		$sec_in_a_day = 60 * 60 * 24;
		while ($diff >= $sec_in_a_day) {
			$daysDiff ++;
			$diff -= $sec_in_a_day;
		}
		$sec_in_an_hour = 60 * 60;
		while ($diff >= $sec_in_an_hour) {
			$hrsDiff ++;
			$diff -= $sec_in_an_hour;
		}
		$sec_in_a_min = 60;
		while ($diff >= $sec_in_a_min) {
			$minsDiff ++;
			$diff -= $sec_in_a_min;
		}
		$secsDiff = $diff;

		return ('(elapsed time '.$hrsDiff.'h '.$minsDiff.'m '.$secsDiff.'s)');

	}

	/*****************************************************************************
	 **
	 *F get_mfn . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  get_mfn
	 **
	 ** Get manufacturers
	 **
	 ** @return array
	 **
	 ****************************************************************************/
	function get_mfn() {
		$mfn_query = xtc_db_query("select manufacturers_id, manufacturers_name from ".TABLE_MANUFACTURERS);
		while ($mfn = xtc_db_fetch_array($mfn_query)) {
			$mfn_array[$mfn['manufacturers_name']] = array ('id' => $mfn['manufacturers_id']);
		}
		return $mfn_array;
	}

}

/*******************************************************************************
 **
 *C xtcExport . . . . . . . . . . . . . . . . . . . . . . . . . . . .  xtcExport
 **
 ******************************************************************************/
class xtcExport {

	/*****************************************************************************
	 **
	 *F xtcExport . . . . . . . . . . . . . . . . . . . . . . . . . . .  xtcExport
	 **
	 ****************************************************************************/
	function xtcExport($filename) {
		$this->catDepth = 6;
		$this->languages = $this->get_lang();
		$this->filename = $filename;
		$this->CAT = array ();
		$this->PARENT = array ();
		$this->counter = array ('prod_exp' => 0);
		$this->time_start = time();
		$this->man = $this->getManufacturers();
		$this->TextSign = CSV_TEXTSIGN;
		$this->seperator = CSV_SEPERATOR;
		if (CSV_SEPERATOR == '')
			$this->seperator = "\t";
		if (CSV_SEPERATOR == '\t')
			$this->seperator = "\t";
		$this->Groups = xtc_get_customers_statuses();
	}

	/*****************************************************************************
	 **
	 *F get_lang . . . . . . . . . . . . . . . . . . . . . . . . . . . .  get_lang
	 **
	 ** Get installed languages
	 **
	 ** @return array
	 **
	 ****************************************************************************/
	function get_lang() {

		$languages_query = xtc_db_query('select languages_id, name, code, image, directory from '.TABLE_LANGUAGES.' order by sort_order');
		while ($languages = xtc_db_fetch_array($languages_query)) {
			$languages_array[] = array ('id' => $languages['languages_id'], 'name' => $languages['name'], 'code' => $languages['code']);
		}

		return $languages_array;
	}

	/*****************************************************************************
	 **
	 *F encode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  encode
	 **
	 ****************************************************************************/
  function encode($data) {
    $result = $data;
    $delim = false;
    if (strpos($data, $this->seperator) !== false) {
      $delim = true;
    } elseif(substr($data,0,1)==$this->TextSign) {
      $delim = true;
    }
    if($delim) {
      $result = $this->TextSign.str_replace($this->TextSign, str_repeat($this->TextSign,2), $data).$this->TextSign;
    }
    return $result.$this->seperator;
  }

	/*****************************************************************************
	 **
	 *F exportProdFile . . . . . . . . . . . . . . . . . . . . . .  exportProdFile
	 **
	 ****************************************************************************/
	function exportProdFile() {

		$fp = fopen(DIR_FS_CATALOG.'export/'.$this->filename, "w+");
    $line = '';
    $headings = array('XTSOL', 'p_model', 'p_stock', 'p_sorting', 'p_shipping',
                  'p_tpl', 'p_manufacturer', 'p_fsk18', 'p_priceNoTax');
    foreach($headings as $heading) {
      $line .= $this->encode($heading);
    }

    for ($i=1; $i<count($this->Groups); $i++) {
      $line .= $this->encode('p_priceNoTax.'.$this->Groups[$i]['id']);
    }
    if (GROUP_CHECK == 'true') {
      for ($i=1; $i<count($this->Groups); $i++) {
        $line .= $this->encode('p_groupAcc.'.$this->Groups[$i]['id']);
      }
    }

    $headings = array('p_tax', 'p_status', 'p_weight', 'p_ean', 'p_disc',
                      'p_opttpl', 'p_vpe', 'p_vpe_status', 'p_vpe_value');
    foreach($headings as $heading) {
      $line .= $this->encode($heading);
    }

    // product images
		for ($i = 1; $i < MO_PICS + 1; $i ++) {
			$line .= $this->encode('p_image.'.$i);
		}
    
    $line .= $this->encode('p_image');

		// add lang fields
		for ($i = 0; $i < sizeof($this->languages); $i ++) {
			$line .= $this->encode('p_name.'.$this->languages[$i]['code']);
			$line .= $this->encode('p_desc.'.$this->languages[$i]['code']);
			$line .= $this->encode('p_shortdesc.'.$this->languages[$i]['code']);
      $line .= $this->encode('p_meta_title.'.$this->languages[$i]['code']);
      $line .= $this->encode('p_meta_desc.'.$this->languages[$i]['code']);
			$line .= $this->encode('p_meta_key.'.$this->languages[$i]['code']);
			$line .= $this->encode('p_keywords.'.$this->languages[$i]['code']);
      $line .= $this->encode('p_url.'.$this->languages[$i]['code']);
		}
		// add categorie fields
		for ($i = 0; $i < $this->catDepth; $i ++) {
			$line .= $this->encode('p_cat.'.$i);
    }
		fputs($fp, $line."\n");

		// content
		$export_query = xtc_db_query('-- admin/includes/classes/import.php export
      select *
      from '.TABLE_PRODUCTS);
		while ($export_data = xtc_db_fetch_array($export_query)) {
			$this->counter['prod_exp']++;
			$line = $this->encode('XTSOL');
			$line .= $this->encode($export_data['products_model']);
			$line .= $this->encode($export_data['products_quantity']);
      $line .= $this->encode($export_data['products_sort']);
			$line .= $this->encode($export_data['products_shippingtime']);
			$line .= $this->encode($export_data['product_template']);
			$line .= $this->encode($this->man[$export_data['manufacturers_id']]);
			$line .= $this->encode($export_data['products_fsk18']);
      $line .= $this->encode($export_data['products_price']);

			// group prices  Qantity:Price::Quantity:Price
			for ($i=1; $i<count($this->Groups); $i++) {
				$price_query = "SELECT * FROM ".TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i]['id']." WHERE products_id = '".$export_data['products_id']."'ORDER BY quantity";
				$price_query = xtc_db_query($price_query);
				$groupPrice = '';
				while ($price_data = xtc_db_fetch_array($price_query)) {
					if ($price_data['personal_offer'] > 0) {
						$groupPrice .= $price_data['quantity'].':'.$price_data['personal_offer'].'::';
					}
				}
				$groupPrice .= ':';
				$groupPrice = str_replace(':::', '', $groupPrice);
				if ($groupPrice == ':')
					$groupPrice = "";
				$line .= $this->encode($groupPrice);

			}

			// group permissions
			if (GROUP_CHECK == 'true') {
				for ($i=1; $i<count($this->Groups); $i++) {
					$line .= $this->encode($export_data['group_permission_'.$this->Groups[$i]['id']]);
				}
			}

			$line .= $this->encode($export_data['products_tax_class_id']);
			$line .= $this->encode($export_data['products_status']);
			$line .= $this->encode($export_data['products_weight']);
      $line .= $this->encode($export_data['products_ean']);
			$line .= $this->encode($export_data['products_discount_allowed']);
      $line .= $this->encode($export_data['options_template']);
      $line .= $this->encode($export_data['products_vpe']);
      $line .= $this->encode($export_data['products_vpe_status']);
			$line .= $this->encode($export_data['products_vpe_value']);

			if (MO_PICS > 0) {
				$mo_query = "SELECT * FROM ".TABLE_PRODUCTS_IMAGES." WHERE products_id='".$export_data['products_id']."'";
				$mo_query = xtc_db_query($mo_query);
				$img = array ();
				while ($mo_data = xtc_db_fetch_array($mo_query)) {
					$img[$mo_data['image_nr']] = $mo_data['image_name'];
				}

			}

			// product images
			for ($i = 1; $i < MO_PICS + 1; $i ++) {
				if (isset ($img[$i])) {
					$line .= $this->encode($img[$i]);
				} else {
					$line .= $this->encode('');
				}
			}

			$line .= $this->encode($export_data['products_image']);

			for ($i = 0; $i < sizeof($this->languages); $i ++) {
				$lang_query = xtc_db_query("SELECT * FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE language_id='".$this->languages[$i]['id']."' and products_id='".$export_data['products_id']."'");
				$lang_data = xtc_db_fetch_array($lang_query);
				$lang_data['products_description'] = str_replace("\n", "", $lang_data['products_description']);
				$lang_data['products_short_description'] = str_replace("\n", "", $lang_data['products_short_description']);
				$lang_data['products_description'] = str_replace("\r", "", $lang_data['products_description']);
				$lang_data['products_short_description'] = str_replace("\r", "", $lang_data['products_short_description']);
				$lang_data['products_description'] = str_replace(chr(13), "", $lang_data['products_description']);
				$lang_data['products_short_description'] = str_replace(chr(13), "", $lang_data['products_short_description']);
				$line .= $this->encode(stripslashes($lang_data['products_name']));
				$line .= $this->encode(stripslashes($lang_data['products_description']));
        $line .= $this->encode(stripslashes($lang_data['products_short_description']));
				$line .= $this->encode(stripslashes($lang_data['products_meta_title']));
				$line .= $this->encode(stripslashes($lang_data['products_meta_description']));
				$line .= $this->encode(stripslashes($lang_data['products_meta_keywords']));
        $line .= $this->encode(stripslashes($lang_data['products_keywords']));
				$line .= $this->encode($lang_data['products_url']);;

			}
			$cat_query = xtc_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$export_data['products_id']."'");
			$cat_data = xtc_db_fetch_array($cat_query);

			$line .= $this->buildCAT($cat_data['categories_id']);
			fputs($fp, $line."\n");
		}

		fclose($fp);
		/*
		if (COMPRESS_EXPORT=='true') {
			$backup_file = DIR_FS_CATALOG.'export/' . $this->filename;
			exec(LOCAL_EXE_ZIP . ' -j ' . $backup_file . '.zip ' . $backup_file);
		   unlink($backup_file);
		}
		*/
		return array (0 => $this->counter, 1 => '', 2 => $this->calcElapsedTime($this->time_start));
	}

	/*****************************************************************************
	 **
	 *F calcElapsedTime . . . . . . . . . . . . . . . . . . . . .  calcElapsedTime
	 **
	 ** Calculate Elapsed time from 2 given Timestamps
	 **
	 ** @param int $time old timestamp
	 ** @return String elapsed time
	 ****************************************************************************/
	function calcElapsedTime($time) {

		$diff = time() - $time;
		$daysDiff = 0;
		$hrsDiff = 0;
		$minsDiff = 0;
		$secsDiff = 0;

		$sec_in_a_day = 60 * 60 * 24;
		while ($diff >= $sec_in_a_day) {
			$daysDiff ++;
			$diff -= $sec_in_a_day;
		}
		$sec_in_an_hour = 60 * 60;
		while ($diff >= $sec_in_an_hour) {
			$hrsDiff ++;
			$diff -= $sec_in_an_hour;
		}
		$sec_in_a_min = 60;
		while ($diff >= $sec_in_a_min) {
			$minsDiff ++;
			$diff -= $sec_in_a_min;
		}
		$secsDiff = $diff;

		return ('(elapsed time '.$hrsDiff.'h '.$minsDiff.'m '.$secsDiff.'s)');

	}

	/*****************************************************************************
	 **
	 *F buildCAT . . . . . . . . . . . . . . . . . . . . . . . . . . . .  buildCAT
	 **
	 ****************************************************************************/
	function buildCAT($catID) {
		if (!isset($this->CAT[$catID])) {
			$this->CAT[$catID]=array();
			$tmpID = $catID;

			while ($this->getParent($tmpID) != 0 || $tmpID != 0) {
        $sql = '-- admin/includes/classes/import export buildCat
          select categories_name
          from '.TABLE_CATEGORIES_DESCRIPTION.'
          where categories_id='.$tmpID.' and language_id='.$this->languages[0]['id'];
        $query = xtc_db_query($sql);
				$cat_data = xtc_db_fetch_array($query);
				$tmpID = $this->getParent($tmpID);
				array_unshift($this->CAT[$catID], $this->encode($cat_data['categories_name']));
			}
      for ($i=$this->catDepth - count($this->CAT[$catID]); $i>0; $i--) {
        $this->CAT[$catID][] = $this->encode('');
      }
		}
    return implode('', $this->CAT[$catID]);
	}

	/*****************************************************************************
	 **
	 *F getTaxRates . . . . . . . . . . . . . . . . . . . . . . . . .  getTaxRates
	 **
	 ** Get the tax_class_id to a given %rate
	 **
	 ** @return array
	 **
	 ****************************************************************************/
	function getTaxRates() { // must be optimazed (pre caching array)
		$tax = array ();
		$tax_query = xtc_db_query("Select
										                                      tr.tax_class_id,
										                                      tr.tax_rate,
										                                      ztz.geo_zone_id
										                                      FROM
										                                      ".TABLE_TAX_RATES." tr,
										                                      ".TABLE_ZONES_TO_GEO_ZONES." ztz
										                                      WHERE
										                                      ztz.zone_country_id='".STORE_COUNTRY."'
										                                      and tr.tax_zone_id=ztz.geo_zone_id
										                                      ");
		while ($tax_data = xtc_db_fetch_array($tax_query)) {

			$tax[$tax_data['tax_class_id']] = $tax_data['tax_rate'];

		}
		return $tax;
	}

	/*****************************************************************************
	 **
	 *F getManufacturers . . . . . . . . . . . . . . . . . . . .  getManufacturers
	 **
	 ** Prefetch Manufactrers
	 **
	 ** @return array
	 **
	 ****************************************************************************/
	function getManufacturers() {
		$man = array ();
		$man_query = xtc_db_query("SELECT
										                                manufacturers_name,manufacturers_id
										                                FROM
										                                ".TABLE_MANUFACTURERS);
		while ($man_data = xtc_db_fetch_array($man_query)) {
			$man[$man_data['manufacturers_id']] = $man_data['manufacturers_name'];
		}
		return $man;
	}

	/*****************************************************************************
	 **
	 *F getParent . . . . . . . . . . . . . . . . . . . . . . . . . . .  getParent
	 **
	 ** Return Parent ID for a given categories id
	 **
	 ** @return int
	 **
	 ****************************************************************************/
	function getParent($catID) {
		if (isset ($this->PARENT[$catID])) {
			return $this->PARENT[$catID];
		} else {
			$parent_query = xtc_db_query("SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id='".$catID."'");
			$parent_data = xtc_db_fetch_array($parent_query);
			$this->PARENT[$catID] = $parent_data['parent_id'];
			return $parent_data['parent_id'];
		}
	}
}
