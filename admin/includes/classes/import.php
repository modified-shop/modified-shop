<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (product.php 1316 2005-10-21)

   -- over-worked by noRiddle
      * replaced functions with system functions
      * stripslashes (xtc_db_prepepare_input()) before xtc_db_perform() import
      * cast types for values before import
      * include admin group for access
      * include order description
      * fixed bug with CSV_SEPERATOR /t
      * newly formated code
   --

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
  *****************************************************************************/

    function __construct($filename) {
        $this->seperator = CSV_SEPERATOR;
        $this->TextSign = CSV_TEXTSIGN;
        if (trim(CSV_TEXTSIGN) == '') {
            $this->TextSign = '^';
        }
        if (CSV_SEPERATOR == '') {
            $this->seperator = "\t";
        }
        if (CSV_SEPERATOR == xtc_db_prepare_input('\t')) { //added stripslashes() since this is called in background in gID=20 (/admin/configuration.php)
            $this->seperator = "\t";
        }
        $this->filename = $filename;
        $this->ImportDir = DIR_FS_CATALOG.'import/';
        $this->catDepth = defined('CSV_CAT_DEPTH') ? CSV_CAT_DEPTH : 4;
        $this->languages = $this->get_lang();
        $this->counter = array ('prod_new' => 0,
                                'cat_new' => 0,
                                'prod_upd' => 0,
                                'cat_upd' => 0);
        $this->mfn = $this->get_mfn();
        $this->errorlog = array ();
        $this->time_start = time();
        $this->debug = false;
        $this->CatTree = array ('ID' => 0);
        // precaching categories in array ?
        $this->CatCache = true;
        $this->CatDefault = CSV_CATEGORY_DEFAULT;
        $this->FileSheme = array ();
        $this->Groups = xtc_get_customers_statuses();
        //BOC new var to use in for loops, noRiddle
        $this->count_groups = count($this->Groups);
        $this->sizeof_languages = sizeof($this->languages);
        //EOC new var to use in for loops, noRiddle
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
    *****************************************************************************/

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
          'p_man' => '', //products_manufacturers_model
          'p_disc' => '', // products discount
          'p_opttpl' => '', // options template
          'p_image' => '', // product image
          'p_vpe' => '', // products VPE
          'p_vpe_status' => '', // products VPE Status
          'p_vpe_value' => '', // products VPE value
          'p_shipping' => '' // product shipping_time
        );
        // group prices
        for ($i = 0; $i < $this->count_groups - 1; $i ++) {
            $file_layout = array_merge($file_layout, array ('p_priceNoTax.'.$this->Groups[$i +1]['id'] => ''));
        }
        // group permissions
        for ($i = 0; $i < $this->count_groups; $i ++) {
            $file_layout = array_merge($file_layout, array ('p_groupAcc.'.$this->Groups[$i]['id'] => ''));
        }
        // product images
        for ($i = 1; $i < MO_PICS + 1; $i ++) {
            $file_layout = array_merge($file_layout, array ('p_image.'.$i => ''));
        }
        // add lang fields
        for ($i = 0; $i < $this->sizeof_languages; $i ++) {
            $file_layout = array_merge($file_layout, array ('p_name.'.$this->languages[$i]['code'] => '',
                                                            'p_desc.'.$this->languages[$i]['code'] => '',
                                                            'p_shortdesc.'.$this->languages[$i]['code'] => '',
                                                            'p_orderdesc.'.$this->languages[$i]['code'] => '', // add order description
                                                            'p_meta_title.'.$this->languages[$i]['code'] => '',
                                                            'p_meta_desc.'.$this->languages[$i]['code'] => '',
                                                            'p_meta_key.'.$this->languages[$i]['code'] => '',
                                                            'p_keywords.'.$this->languages[$i]['code'] => '',
                                                            'p_url.'.$this->languages[$i]['code'] => '')
                                                            );
        }
        // add categorie fields
        for ($i = 0; $i < $this->catDepth; $i ++) {
            $file_layout = array_merge($file_layout, array ('p_cat.'.$i => ''));
        }
        
        foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/import/file_layout/','php') as $file) require ($file);
        
        return $file_layout;
    }

    /*****************************************************************************
    **
    *F map_file . . . . . . . . . . . . . . . . . . . . . . . . . . . .  map_file
    **
    **  generating mapping layout for importfile
    **  @param array $mapping standard fields
    **  @return array
    *****************************************************************************/

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
    *****************************************************************************/

    function get_lang() {
        $languages_query = xtc_db_query("SELECT languages_id,
                                                name,
                                                code,
                                                image,
                                                directory
                                           FROM ".TABLE_LANGUAGES."
                                       ORDER BY sort_order
                                      ");


        while ($languages = xtc_db_fetch_array($languages_query)) {
            $languages_array[] = array (
                'id' => $languages['languages_id'],
                'name' => $languages['name'],
                'code' => $languages['code']
            );
        }

        return $languages_array;
    }

    /*****************************************************************************
    **
    *F import . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .  import
    **
    *****************************************************************************/

    function import($mapping) {

        // open file
        $fp = fopen($this->ImportDir.$this->filename, 'r');

        // read the header line
        $header = fgetcsv($fp, 20000, $this->seperator, $this->TextSign);
        foreach($header as $key=>$name) {
            $mapping[$name] = $key;
        }

        $row = 1;  //set row to one to be able to count rows in while loop

        while ($line = fgetcsv($fp, 20000, $this->seperator, $this->TextSign)) {
            $row++; //increment row to get line number
            foreach($mapping as $name => $key) {
                $line_data[$name] = $line[$key];
            }

            if ($line_data['p_model'] != '') {
                if ($line_data['p_cat.0'] != '' || $this->FileSheme['p_cat.0'] != 'Y') { // if cat data field is empty or not existing
                    if ($this->FileSheme['p_cat.0'] != 'Y') { // if cat data field is not existing
                        if ($this->checkModel($line_data['p_model'])) { // if model no. already exists
                            $this->insertProduct($line_data, 'update');
                        } elseif ($this->CatDefault != '0') { // if model no. not existing and cat data field not exsisting and CatDefault is not TOP
                            $this->insertProduct($line_data,'insert');
                        }
                    } else { // if cat data field existing
                        if ($this->checkModel($line_data['p_model'])) {
                            $this->insertProduct($line_data, 'update',true);
                        } else {
                            $this->insertProduct($line_data,'insert',true);
                        }
                    }
                } else { // if cat data field existing but empty
                    if ($this->checkModel($line_data['p_model'])) { // if model no. already exists
                        $this->insertProduct($line_data, 'update');
                    } elseif ($this->CatDefault != '0') { // if model no. not existing and cat data field existing but empty and CatDefault is not TOP
                        $this->insertProduct($line_data,'insert');
                    } else { // cat data field existing but empty and no category choosen (i.e. TOP choosen)
                        $this->errorLog[] = '<b>ERROR:</b> no Categorie, line: '.$row.' dataset: empty field: p_cat.0' . 'and no categrory selected';
                    }
                }
            } else {
                $this->errorLog[] = '<b>ERROR:</b> no Modelnumber, line: '.$row.' dataset: empty field: p_model';
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
    *****************************************************************************/

    function checkModel($model) {
        $model_query = xtc_db_query("SELECT products_id
                                      FROM ".TABLE_PRODUCTS."
                                     WHERE products_model = '".xtc_db_input($model)."'
                                   ");

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
    ******************************************************************************/

    function checkImage($imgID,$pID) {
        $img_query = xtc_db_query("SELECT image_id
                                     FROM ".TABLE_PRODUCTS_IMAGES."
                                    WHERE products_id = '".$pID."'
                                      AND image_nr = '".$imgID."'
                                ");

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
        if(!empty($this->TextSign)) {
            if (substr($data, -1) == $this->TextSign)
                $data = substr($data, 1, strlen($data) - 2);

            return $data;
        }
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
    *****************************************************************************/

    function getMAN($manufacturer) {
        if ($manufacturer == '')
            return;

        if (isset ($this->mfn[$manufacturer]['id']))
            return $this->mfn[$manufacturer]['id'];


        $man_query = xtc_db_query("SELECT manufacturers_id
                                     FROM ".TABLE_MANUFACTURERS."
                                    WHERE manufacturers_name = '". xtc_db_input($manufacturer) ."'
                                ");

        if (!xtc_db_num_rows($man_query)) {
            $manufacturers_array = array ('manufacturers_name' => $manufacturer);
            xtc_db_perform(TABLE_MANUFACTURERS, $manufacturers_array);
            $this->mfn[$manufacturer]['id'] = xtc_db_insert_id();
            for ($i = 0; $i < $this->sizeof_languages; $i ++) {
              $insert_sql_data = array(
                'manufacturers_id' => $this->mfn[$manufacturer]['id'],
                'languages_id' => $this->languages[$i]['id']
              );
              xtc_db_perform(TABLE_MANUFACTURERS_INFO, $insert_sql_data);
            }
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
    *****************************************************************************/

    function insertProduct(& $dataArray, $mode = 'insert',$touchCat = false) {

        //BOC strip potential slashes and type cast inputs
        $products_array = array ('products_model' => xtc_db_prepare_input($dataArray['p_model']));
        if ($this->FileSheme['p_stock'] == 'Y')
            $products_array = array_merge($products_array, array ('products_quantity' => (int)$dataArray['p_stock']));
        if ($this->FileSheme['p_priceNoTax'] == 'Y')
            $products_array = array_merge($products_array, array ('products_price' => (float)$dataArray['p_priceNoTax']));
        if ($this->FileSheme['p_weight'] == 'Y')
            $products_array = array_merge($products_array, array ('products_weight' => (float)$dataArray['p_weight']));
        if ($this->FileSheme['p_status'] == 'Y')
            $products_array = array_merge($products_array, array ('products_status' => (int)$dataArray['p_status']));
        if ($this->FileSheme['p_image'] == 'Y')
            $products_array = array_merge($products_array, array ('products_image' => xtc_db_prepare_input($dataArray['p_image'])));
        if ($this->FileSheme['p_disc'] == 'Y')
            $products_array = array_merge($products_array, array ('products_discount_allowed' => (float)$dataArray['p_disc']));
        if ($this->FileSheme['p_ean'] == 'Y')
            $products_array = array_merge($products_array, array ('products_ean' => xtc_db_prepare_input($dataArray['p_ean'])));
        if ($this->FileSheme['p_man'] == 'Y')
            $products_array = array_merge($products_array, array ('products_manufacturers_model' => xtc_db_prepare_input($dataArray['p_man'])));
        if ($this->FileSheme['p_tax'] == 'Y')
            $products_array = array_merge($products_array, array ('products_tax_class_id' => (int)$dataArray['p_tax']));
        if ($this->FileSheme['p_opttpl'] == 'Y')
            $products_array = array_merge($products_array, array ('options_template' => xtc_db_prepare_input($dataArray['p_opttpl'])));
        if ($this->FileSheme['p_manufacturer'] == 'Y')
            $products_array = array_merge($products_array, array ('manufacturers_id' => $this->getMAN(xtc_db_prepare_input(trim($dataArray['p_manufacturer'])))));
        if ($this->FileSheme['p_fsk18'] == 'Y')
            $products_array = array_merge($products_array, array ('products_fsk18' => (int)$dataArray['p_fsk18']));
        if ($this->FileSheme['p_tpl'] == 'Y')
            $products_array = array_merge($products_array, array ('product_template' => xtc_db_prepare_input($dataArray['p_tpl'])));
        if ($this->FileSheme['p_vpe'] == 'Y')
            $products_array = array_merge($products_array, array ('products_vpe' => xtc_db_prepare_input($dataArray['p_vpe'])));
        if ($this->FileSheme['p_vpe_status'] == 'Y')
            $products_array = array_merge($products_array, array ('products_vpe_status' => (int)$dataArray['p_vpe_status']));
        if ($this->FileSheme['p_vpe_value'] == 'Y')
            $products_array = array_merge($products_array, array ('products_vpe_value' => (float)$dataArray['p_vpe_value']));
        if ($this->FileSheme['p_shipping'] == 'Y')
            $products_array = array_merge($products_array, array ('products_shippingtime' => (int)$dataArray['p_shipping']));
        if ($this->FileSheme['p_sorting'] == 'Y')
            $products_array = array_merge($products_array, array ('products_sort' => (int)$dataArray['p_sorting']));
        //EOC strip potential slashes and type cast inputs
        
        foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/import/insert_before/','php') as $file) require ($file);

        if ($mode == 'insert') {
            $products_array = array_merge($products_array, array ('products_date_added' => date("Y-m-d H:i:s")));
            $this->counter['prod_new']++;
            xtc_db_perform(TABLE_PRODUCTS, $products_array);
            $products_id = xtc_db_insert_id();
        } else {
            $products_array = array_merge($products_array, array ('products_last_modified' => date("Y-m-d H:i:s")));
            $this->counter['prod_upd']++;
            xtc_db_perform(TABLE_PRODUCTS, $products_array, 'update', 'products_model = \''.xtc_db_input($dataArray['p_model']).'\'');

            $prod_query = xtc_db_query("SELECT products_id
                                          FROM ".TABLE_PRODUCTS."
                                         WHERE products_model = '".xtc_db_input($dataArray['p_model'])."'
                                     ");

            $prod_data = xtc_db_fetch_array($prod_query);
            $products_id = $prod_data['products_id'];
        }

        // Insert group prices Qantity:Price::Quantity:Price
        for ($i = 0; $i < $this->count_groups - 1; $i ++) {
            // seperate string ::
            if (isset ($dataArray['p_priceNoTax.'.$this->Groups[$i +1]['id']]) && $dataArray['p_priceNoTax.'.$this->Groups[$i +1]['id']] != '') {

                $truncate_query = "DELETE FROM ".TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i +1]['id']."
                                         WHERE products_id = '".$products_id."'";

                xtc_db_query($truncate_query);
                $prices = $dataArray['p_priceNoTax.'.$this->Groups[$i +1]['id']];
                $prices = explode('::', $prices);
                for ($ii = 0, $cp = count($prices); $ii < $cp; $ii ++) {
                    $values = explode(':', $prices[$ii]);

                    $group_array = array (
                        'products_id' => $products_id,
                        'quantity' => $values[0],
                        'personal_offer' => $values[1]
                    );

                    xtc_db_perform(TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i +1]['id'], $group_array);
                }
            }
        }

        // Insert group permissions.
        for ($i = 0; $i < $this->count_groups; $i ++) {
            if (isset ($dataArray['p_groupAcc.'.$this->Groups[$i]['id']])) {
                $insert_array = array ('group_permission_'.$this->Groups[$i]['id'] => $dataArray['p_groupAcc.'.$this->Groups[$i]['id']]);
                xtc_db_perform(TABLE_PRODUCTS, $insert_array, 'update', 'products_id = \''.$products_id.'\'');
            }
        }

        // insert images
        for ($i = 1; $i < MO_PICS + 1; $i ++) {
            if (isset($dataArray['p_image.'.$i]) && $dataArray['p_image.'.$i]!="") {
                // check if entry exists
                if ($this->checkImage($i,$products_id)) {
                        $insert_array = array ('image_name' => $dataArray['p_image.'.$i]);
                        xtc_db_perform(TABLE_PRODUCTS_IMAGES, $insert_array, 'update', 'products_id = \''.$products_id.'\' AND image_nr=\''.$i.'\'');
                } else {
                    $insert_array = array (
                        'image_name' => $dataArray['p_image.'.$i],
                        'image_nr'=>$i,
                        'products_id'=>$products_id
                    );
                    xtc_db_perform(TABLE_PRODUCTS_IMAGES, $insert_array);
                }
            }
        }

        if ($touchCat) $this->insertCategory($dataArray, $mode, $products_id);

        for ($i_insert = 0; $i_insert < $this->sizeof_languages; $i_insert ++) {
            $prod_desc_array = array (
                'products_id' => $products_id,
                'language_id' => $this->languages[$i_insert]['id']
            );

            //BOC strip potential slashes
            if ($this->FileSheme['p_name.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_name' => xtc_db_prepare_input($dataArray['p_name.'.$this->languages[$i_insert]['code']])));
            if ($this->FileSheme['p_desc.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_description' => xtc_db_prepare_input($dataArray['p_desc.'.$this->languages[$i_insert]['code']])));
            if ($this->FileSheme['p_shortdesc.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_short_description' => xtc_db_prepare_input($dataArray['p_shortdesc.'.$this->languages[$i_insert]['code']])));
            //BOC include order description
            if ($this->FileSheme['p_orderdesc.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_order_description' => xtc_db_prepare_input($dataArray['p_orderdesc.'.$this->languages[$i_insert]['code']])));
            //EOC include order description
            if ($this->FileSheme['p_meta_title.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_meta_title' => xtc_db_prepare_input($dataArray['p_meta_title.'.$this->languages[$i_insert]['code']])));
            if ($this->FileSheme['p_meta_desc.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_meta_description' => xtc_db_prepare_input($dataArray['p_meta_desc.'.$this->languages[$i_insert]['code']])));
            if ($this->FileSheme['p_meta_key.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_meta_keywords' => xtc_db_prepare_input($dataArray['p_meta_key.'.$this->languages[$i_insert]['code']])));
            if ($this->FileSheme['p_keywords.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_keywords' => xtc_db_prepare_input($dataArray['p_keywords.'.$this->languages[$i_insert]['code']])));
            if ($this->FileSheme['p_url.'.$this->languages[$i_insert]['code']] == 'Y')
                $prod_desc_array = array_merge($prod_desc_array, array ('products_url' => xtc_db_prepare_input($dataArray['p_url.'.$this->languages[$i_insert]['code']])));
            //EOC strip potential slashes

            if ($mode == 'insert') {
                xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $prod_desc_array);
            } else {
                xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $prod_desc_array, 'update', 'products_id = \''.$products_id.'\' AND language_id=\''.$this->languages[$i_insert]['id'].'\'');
            }
        }
        // check for Category
        $categories_check_query = xtc_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$products_id."'");
        if (!xtc_db_num_rows($categories_check_query)) {
            $this->insertPtoCconnection($products_id, $this->CatDefault);
        }
    
        foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/import/insert_end/','php') as $file) require ($file);
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
    *****************************************************************************/

    function insertCategory(& $dataArray, $mode = 'insert', $pID) {
        if ($this->debug) {
            echo '<pre>';
            print_r($this->CatTree);
            echo '</pre>';
        }
        $cat = array ();
        $catTree = '';
        for ($i = 0; $i < $this->catDepth; $i ++) {
            if (trim($dataArray['p_cat.'.$i]) != '') {
                $cat[$i] = xtc_db_prepare_input(trim($dataArray['p_cat.'.$i]));
                $catTree .= '[\''.xtc_db_input($cat[$i]).'\']';
            }
        }

        $code = '$ID=$this->CatTree'.$catTree.'[\'ID\'];';
        //debug
        if ($this->debug) {echo '<pre>FIRST $CODE: ' . $code . '</pre>';}
        eval ($code);
        //debug
        if ($this->debug) echo '<pre>FIRST $ID: '.$ID.'</pre>';

        if (is_int($ID) || $ID == '0') {
            $this->insertPtoCconnection($pID, $ID);
        } else {

            $catTree = '';
            $parTree = '';
            $curr_ID = 0;
            for ($i = 0, $cc = count($cat); $i < $cc; $i ++) {
                $catTree .= '[\''.xtc_db_input($cat[$i]).'\']';
                $code = '$ID=$this->CatTree'.$catTree.'[\'ID\'];';
                //debug
                if ($this->debug) {echo '<pre>SECOND $CODE: ' . $code . '</pre>';}

                eval ($code);
                //debug
                if ($this->debug) {echo '<pre>SECOND $ID: ' . $ID . '</pre>';}
                if (is_int($ID) || $ID == '0') {
                    $curr_ID = $ID;
                } else {
                    $code = '$parent=$this->CatTree'.$parTree.'[\'ID\'];';
                   //debug
                   if ($this->debug) {echo '<pre>THIRD $CODE: ' . $code . '</pre>';}


                    eval ($code);
                   //debug
                   if ($this->debug) echo '<pre>$PARENT: '.$parent.'</pre>';

                    // check if categorie exists
                    $cat_query = xtc_db_query("SELECT c.categories_id
                                                 FROM ".TABLE_CATEGORIES." c
                                                 JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                                   ON cd.categories_id = c.categories_id
                                                WHERE cd.categories_name = '".xtc_db_input($cat[$i])."'
                                                  AND cd.language_id = '".$this->languages[0]['id']."'
                                                  AND c.parent_id = '".$parent."'");
                    if (!xtc_db_num_rows($cat_query)) { // insert categorie
                        $categorie_data = array (
                            'parent_id' => $parent,
                            'categories_status' => 1,
                            'date_added' => 'now()',
                            'last_modified' => 'now()'
                        );

                        xtc_db_perform(TABLE_CATEGORIES, $categorie_data);
                        $cat_id = xtc_db_insert_id();

                        $this->counter['cat_new']++;

                        $code = '$this->CatTree'.$parTree.'[\''.xtc_db_input($cat[$i]).'\'][\'ID\']='.$cat_id.';';
                        //debug
                        if ($this->debug) {echo '<pre>FOURTH $CODE: ' . $code . '</pre>';}

                        eval ($code);
                        //debug
                        if ($this->debug) echo '<pre>FIRST $CAT_ID: '.$cat_id.'</pre><hr />';

                        $parent = $cat_id;
                        for ($i_insert = 0; $i_insert < $this->sizeof_languages; $i_insert ++) {
                            $categorie_data = array (
                                'language_id' => $this->languages[$i_insert]['id'],
                                'categories_id' => $cat_id,
                                'categories_name' => $cat[$i]
                            );
                            xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION, $categorie_data);
                        }
                    } else {
                        $this->counter['cat_touched']++;
                        $cData = xtc_db_fetch_array($cat_query);
                        $cat_id = $cData['categories_id'];
                        $code = '$this->CatTree'.$parTree.'[\''.xtc_db_input($cat[$i]).'\'][\'ID\']='.$cat_id.';';
                        //debug
                        if ($this->debug) {echo '<pre>FIFTH $CODE: ' . $code . '</pre>';}

                        eval ($code);
                        //debug
                        if ($this->debug) echo '<pre>SECOND $CAT_ID: '.$cat_id.'</pre><hr />';
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
    *****************************************************************************/

    function insertPtoCconnection($pID, $cID) {
        $prod2cat_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                         WHERE categories_id = '".$cID."'
                                           AND products_id = '".$pID."'
                                      ");

        if (!xtc_db_num_rows($prod2cat_query)) {
            $insert_data = array (
                'products_id' => $pID,
                'categories_id' => $cID
            );

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
    *****************************************************************************/

    function get_line_content($line, $file_content, $max_lines) {
        // get first line
        $line_data = array ();
        $line_data['data'] = $file_content[$line];
        $lc = 1;
        // check if next line got ; in first 50 chars
        while (!strpos(substr($file_content[$line + $lc], 0, 6), 'XTSOL') && $line + $lc <= $max_lines) {
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
        $mfn_query = xtc_db_query("SELECT manufacturers_id,
                                          manufacturers_name
                                     FROM ".TABLE_MANUFACTURERS);


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
*******************************************************************************/

class xtcExport {

    /*****************************************************************************
    **
    *F xtcExport . . . . . . . . . . . . . . . . . . . . . . . . . . .  xtcExport
    **
    *****************************************************************************/

    function __construct($filename) {
        $this->catDepth = defined('CSV_CAT_DEPTH') ? CSV_CAT_DEPTH : 4;
        $this->languages = $this->get_lang();
        $this->filename = $filename;
        $this->CAT = array ();
        $this->PARENT = array ();
        $this->counter = array ('prod_exp' => 0);
        $this->time_start = time();
        $this->man = $this->getManufacturers();
        $this->TextSign = CSV_TEXTSIGN;
        //BOC set default text sign in export
        /*if (trim(CSV_TEXTSIGN) == '') {
            $this->TextSign = '"';
        }*/
        //EOC set default text sign in export
        $this->seperator = CSV_SEPERATOR;
        if (CSV_SEPERATOR == '')
            $this->seperator = "\t";
        if (CSV_SEPERATOR == xtc_db_prepare_input('\t')) //added stripslashes() since this is called in background in gID=20 (/admin/configuration.php)
            $this->seperator = "\t";

        $this->Groups = xtc_get_customers_statuses();
        //BOC new var to use in for loops, noRiddle
        $this->count_groups = count($this->Groups);
        $this->sizeof_languages = sizeof($this->languages);
        //EOC new var to use in for loops, noRiddle
    }

    /*****************************************************************************
    **
    *F get_lang . . . . . . . . . . . . . . . . . . . . . . . . . . . .  get_lang
    **
    ** Get installed languages
    **
    ** @return array
    **
    *****************************************************************************/

    function get_lang() {
        $languages_query = xtc_db_query('SELECT languages_id,
                                                name,
                                                code,
                                                image,
                                                directory
                                           FROM '.TABLE_LANGUAGES.'
                                       ORDER BY sort_order
                                       ');


        while ($languages = xtc_db_fetch_array($languages_query)) {
            $languages_array[] = array (
                'id' => $languages['languages_id'],
                'name' => $languages['name'],
                'code' => $languages['code']
            );
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
        //BOC set TextSign also when TextSign occurs within $data
        } elseif(strpos($data, $this->TextSign) != false) {
            $delim = true;
        }
        //EOC set TextSign also when TextSign occurs within $data
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
        $headings = array('XTSOL',
                  'p_model',
                  'p_stock',
                  'p_sorting',
                  'p_shipping',
                  'p_tpl',
                  'p_manufacturer',
                  'p_fsk18',
                  'p_priceNoTax');
        foreach($headings as $heading) {
            $line .= $this->encode($heading);
        }

        for ($i=1; $i < $this->count_groups; $i++) {
            $line .= $this->encode('p_priceNoTax.'.$this->Groups[$i]['id']);
        }
        if (GROUP_CHECK == 'true') {
            for ($i=0; $i < $this->count_groups; $i++) {
                $line .= $this->encode('p_groupAcc.'.$this->Groups[$i]['id']);
            }
        }

        $headings = array('p_tax',
                          'p_status',
                          'p_weight',
                          'p_ean',
                          'p_man',
                          'p_disc',
                          'p_opttpl',
                          'p_vpe',
                          'p_vpe_status',
                          'p_vpe_value');
        foreach($headings as $heading) {
            $line .= $this->encode($heading);
        }

        // product images
        for ($i = 1; $i < MO_PICS + 1; $i ++) {
            $line .= $this->encode('p_image.'.$i);
        }

        $line .= $this->encode('p_image');

        // add lang fields
        for ($i = 0; $i < $this->sizeof_languages; $i ++) {
            $line .= $this->encode('p_name.'.$this->languages[$i]['code']);
            $line .= $this->encode('p_desc.'.$this->languages[$i]['code']);
            $line .= $this->encode('p_shortdesc.'.$this->languages[$i]['code']);
            $line .= $this->encode('p_orderdesc.'.$this->languages[$i]['code']); //added order description
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
                                     SELECT *
                                       FROM '.TABLE_PRODUCTS
                                    );


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
            for ($i=1; $i < $this->count_groups; $i++) {
                $price_query = "SELECT *
                                  FROM ".TABLE_PERSONAL_OFFERS_BY.$this->Groups[$i]['id']."
                                 WHERE products_id = '".$export_data['products_id']."'
                              ORDER BY quantity";


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
                for ($i=0; $i < $this->count_groups; $i++) {
                    $line .= $this->encode($export_data['group_permission_'.$this->Groups[$i]['id']]);
                }
            }

            $line .= $this->encode($export_data['products_tax_class_id']);
            $line .= $this->encode($export_data['products_status']);
            $line .= $this->encode($export_data['products_weight']);
            $line .= $this->encode($export_data['products_ean']);
            $line .= $this->encode($export_data['products_manufacturers_model']);
            $line .= $this->encode($export_data['products_discount_allowed']);
            $line .= $this->encode($export_data['options_template']);
            $line .= $this->encode($export_data['products_vpe']);
            $line .= $this->encode($export_data['products_vpe_status']);
            $line .= $this->encode($export_data['products_vpe_value']);

            if (MO_PICS > 0) {
                $mo_query = "SELECT *
                               FROM ".TABLE_PRODUCTS_IMAGES."
                              WHERE products_id = '".$export_data['products_id']."'";

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

            for ($i = 0; $i < $this->sizeof_languages; $i ++) {
                $lang_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                             WHERE language_id = '".$this->languages[$i]['id']."'
                                               AND products_id = '".$export_data['products_id']."'
                                          ");

                $lang_data = xtc_db_fetch_array($lang_query);
                $lang_data['products_description'] = str_replace("\n", "", $lang_data['products_description']);
                $lang_data['products_short_description'] = str_replace("\n", "", $lang_data['products_short_description']);
                $lang_data['products_order_description'] = str_replace("\n", "", $lang_data['products_order_description']); //added order description
                $lang_data['products_description'] = str_replace("\r", "", $lang_data['products_description']);
                $lang_data['products_short_description'] = str_replace("\r", "", $lang_data['products_short_description']);
                $lang_data['products_order_description'] = str_replace("\r", "", $lang_data['products_order_description']); //added order description
                $lang_data['products_description'] = str_replace(chr(13), "", $lang_data['products_description']);
                $lang_data['products_short_description'] = str_replace(chr(13), "", $lang_data['products_short_description']);
                $lang_data['products_order_description'] = str_replace(chr(13), "", $lang_data['products_order_description']); //added order description
                $line .= $this->encode(stripslashes($lang_data['products_name']));
                $line .= $this->encode(stripslashes($lang_data['products_description']));
                $line .= $this->encode(stripslashes($lang_data['products_short_description']));
                $line .= $this->encode(stripslashes($lang_data['products_order_description'])); //added order description
                $line .= $this->encode(stripslashes($lang_data['products_meta_title']));
                $line .= $this->encode(stripslashes($lang_data['products_meta_description']));
                $line .= $this->encode(stripslashes($lang_data['products_meta_keywords']));
                $line .= $this->encode(stripslashes($lang_data['products_keywords']));
                $line .= $this->encode($lang_data['products_url']);
            }

            $cat_query = xtc_db_query("SELECT categories_id
                                         FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                        WHERE products_id = '".$export_data['products_id']."'
                                    ");



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

        return array (
            0 => $this->counter, 1 => '',
            2 => $this->calcElapsedTime($this->time_start)
         );
    }

    /*****************************************************************************
    **
    *F calcElapsedTime . . . . . . . . . . . . . . . . . . . . .  calcElapsedTime
    **
    ** Calculate Elapsed time from 2 given Timestamps
    **
    ** @param int $time old timestamp
    ** @return String elapsed time
    *****************************************************************************/

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
    *****************************************************************************/

    function buildCAT($catID) {
        if (!isset($this->CAT[$catID])) {
            $this->CAT[$catID]=array();
            $tmpID = $catID;

            while ($this->getParent($tmpID) != 0 || $tmpID != 0) {
                $sql = '-- admin/includes/classes/import export buildCat
                        SELECT categories_name
                          FROM '.TABLE_CATEGORIES_DESCRIPTION.'
                         WHERE categories_id = '.$tmpID.' and language_id = '.$this->languages[0]['id'];

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
    *****************************************************************************/

    function getTaxRates() { // must be optimazed (pre caching array)
        $tax = array ();
        $tax_query = xtc_db_query("SELECT tr.tax_class_id,
                                      tr.tax_rate,
                                      ztz.geo_zone_id
                                 FROM ".TABLE_TAX_RATES." tr
                                 JOIN ".TABLE_ZONES_TO_GEO_ZONES." ztz
                                      ON tr.tax_zone_id = ztz.geo_zone_id
                                WHERE ztz.zone_country_id='".STORE_COUNTRY."'");
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
    *****************************************************************************/

    function getManufacturers() {
        $man = array ();
        $man_query = xtc_db_query("SELECT manufacturers_name,
                                          manufacturers_id
                                     FROM ".TABLE_MANUFACTURERS);

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
    *****************************************************************************/

    function getParent($catID) {
        if (isset ($this->PARENT[$catID])) {
            return $this->PARENT[$catID];
        } else {
            $parent_query = xtc_db_query("SELECT parent_id
                                            FROM ".TABLE_CATEGORIES."
                                           WHERE categories_id = '".$catID."'
                                        ");

            $parent_data = xtc_db_fetch_array($parent_query);
            $this->PARENT[$catID] = $parent_data['parent_id'];
            return $parent_data['parent_id'];
        }
    }
}
