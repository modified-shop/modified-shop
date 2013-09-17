<?php
	/*  File:       findologic_export.php
	 *  Version:    4.0 (120)
	 *  Date:       20.Apr.2011
	 *
	 *  FINDOLOGIC GmbH
	 */
	
	require ('includes/application_top.php');
  require_once (DIR_FS_EXTERNAL.'findologic/findologic_config.inc.php');

	if ((!array_key_exists("shop", $_GET)) || ($_GET["shop"] != FL_SHOP_ID)) {
		die('Unauthorized access!');
	}

	/* ensure that strings are not utf8-encoded twice */
	function ensure_encoding($string) {

		if (!is_string($string)) {
			return $string;
		}

		$is_unicode = (mb_detect_encoding($string, array('UTF-8'), true) == 'UTF-8');

		if ($is_unicode) {
			return $string;
		} else {
			return utf8_encode($string);
		}
	}

	$lng = new language();
	$lng_chosen = $lng->catalog_languages[FL_LANG];
	define("FL_LANG_ID", $lng_chosen['id']);

	echo 'Exporting prices for currency ' . CURRENCY . ' and customer group ' . CUSTOMER_GROUP . "\n";

	$xtcPrice = new xtcPrice(CURRENCY, CUSTOMER_GROUP);

	set_time_limit(3000);

	function get_output_filename() {
		return DIR_FS_DOCUMENT_ROOT.'export/findologic.csv';
	}
	
	function get_image($image) {
	if (!empty($image)) {
		$bild = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_ORIGINAL_IMAGES . $image;
	} else {	
		$bild = null;
	}
		return $bild;
	}

	function get_taxzone() {
		return 1;
	}
		
	function get_instead($row) {
		global $xtcPrice;
		$pPrice = $xtcPrice->getPprice($row['products_id']);
		$pPrice = $xtcPrice->xtcAddTax($pPrice, get_taxrate($row));
		return $pPrice;
	}

	function get_maxprice($row) {
		$basePrice = $row['specials_new_products_price'];
		if ($basePrice == 0) {
			$basePrice = $row['products_price'];
		}
		$basePrice += $row['max_add_on_price'];
		return round(add_tax($basePrice, $row),2);
	}

	function add_tax($basePrice, $row) {
		if (FL_NET_PRICE) return round($basePrice, 2);
		else return round($basePrice * (100.0 + get_taxrate($row)) / 100.0, 2);
	}
	  
	function get_taxrate($row) {
		global $xtcPrice;

		$taxClassId = $row['products_tax_class_id'];

		if (!isset($xtcPrice->TAX[$taxClassId])) {
			$taxrate = 0;
		} else {
			$taxrate = $xtcPrice->TAX[$taxClassId];
		}

		return $taxrate;
	}
	
	function get_description($model, $description) {
		return (extract_text("Artikelnummer: " . str_pad($model, 7 ,'0', STR_PAD_LEFT) . " " .$description));
	}
	
	function get_columns() {
		return array(
			"id",
			"ordernumber",
			"name",
			"summary",
			"description",
			"price",
			"instead",
			"maxprice",
			"taxrate",
			"url",
			"image",
			"attributes",
			"keywords",
			"groups",
			"bonus",
			"shipping",
		);
	}

	function get_column_delimiter() {
		return "\t";
	}

	function get_category_delimiter() {
		return "_";
	}

	
	$useKeywords = true;
	$debug = false;

	/* print out database information about a certain product by passing ...&debug=<product_id> */
	if (isset($_GET['debug']) && is_numeric($_GET['debug'])) {
		$debug = true;
		$debugId = $_GET['debug'];
		$sql = "SELECT DISTINCT pr.products_id AS id, pc.categories_id, c.categories_status FROM (products pr)
			LEFT OUTER JOIN products_to_categories pc
				ON pr.products_id = pc.products_id
			LEFT JOIN categories c
				ON pc.categories_id = c.categories_id
			WHERE pr.products_id = $debugId
			ORDER BY id";
		$result = xtc_db_query($sql);
	} else {
		$filename = get_output_filename();
		if (!is_writeable($filename)) {
			die('File "' . $filename . '" is not writeable!');
		}
		if (isset($_GET['first']) && is_numeric($_GET['first']) && isset($_GET['count']) && is_numeric($_GET['count'])) {
			$incremental = true;
			$first = (int) $_GET['first'];
			$count = (int) $_GET['count'];
		} else {
			$incremental = false;
			$first = null;
			$count = null;
		}


		if (!$incremental || $first === 0) {
			$fp = fopen($filename , "w");
			$header = implode(get_columns(), get_column_delimiter());
			fwrite($fp , $header."\n");
		} else {
			$fp = fopen($filename , "a");
		}

		global $fp;

		$sql = "SELECT COUNT(DISTINCT pr.products_id) AS productCount FROM (products pr)
			LEFT OUTER JOIN products_to_categories pc
				ON pr.products_id = pc.products_id
			LEFT JOIN categories c
				ON pc.categories_id = c.categories_id
			WHERE (pc.categories_id = 0 OR c.categories_status = 1) 
				AND products_status = 1";
		$result = xtc_db_query($sql);
		if (xtc_db_num_rows($result) and $row = xtc_db_fetch_array($result)) {
			$productCount = $row["productCount"];
		} else {
			$productCount = 0;
		}
		echo "Found $productCount products...\r\n<br>";

		$sql = "SELECT DISTINCT pr.products_id AS id FROM (products pr)
			LEFT OUTER JOIN products_to_categories pc
				ON pr.products_id = pc.products_id
			LEFT JOIN categories c
				ON pc.categories_id = c.categories_id
			WHERE (pc.categories_id = 0 OR c.categories_status = 1) 
				AND products_status = 1
				ORDER BY id";
		if ($incremental) {
			$sql .= " LIMIT $count OFFSET $first";
		}

		$result = xtc_db_query($sql);
	}

	$products = 0;
	$n = 0;
	echo "\r\n";
	if(xtc_db_num_rows($result)) 
	{
		while ($row = xtc_db_fetch_array($result)) {

			if ($debug) {
				output_row($row);
			}

			if (select_product($row['id'], $useKeywords, $debug)) $products++;
			$n++;
			if ($n % 500 == 0) {
				echo "$n of $productCount products processed.\r\n";
			}
		}
	}
	echo "\r\n";

	echo $products." products exported successfully.\r\n<br />";
	if ($products < $n) {
		echo ($n - $products)." products failed!\r\n<br />";
	}

	if ($incremental && (($n + $first) < $productCount)) {
		echo ($productCount - $n) . " products remaining!\r\n<br />";
	}

	fclose($fp);

	function for_url_rewrite($string) {
		return preg_replace('[^0-9A-Za-z+]', '-', $string);
	}

	function extract_text($string) {
		$string = preg_replace('/<[^<>]*>/', ' ', $string);
		$string = str_replace("\n", " ",$string);
		$string = str_replace("  ", " ",$string);
		$string = str_replace(" ", " ", $string);
		$string = str_replace("\r", "", $string);
		$string = str_replace("\t", "", $string);
		return $string;
	}

	function get_encoded_text($text) {
		$text = str_replace("&nbsp;","",$text);
		return $text;
	}
	
	function select_product($product_nr, $useKeywords, $debug = false) {
		global $fp, $xtcPrice;

		$keywordsQuery = $useKeywords ? "pd.products_keywords" : "'' AS products_keywords";

		$sql=
			"SELECT
				pr.products_id,
				pr.products_model,
				pd.products_name,
				pd.products_short_description,
				pd.products_description,
				sp.specials_new_products_price,
				pr.products_price,
				pr.products_discount_allowed,
				MAX(pa.options_values_price) AS max_add_on_price,
				pr.products_image,
				pr.products_ordered,				
				pr.products_tax_class_id,
				tx.tax_rate,
				tx.tax_description,
				$keywordsQuery,
				mn.manufacturers_name,
				ss.shipping_status_name
			FROM
				products pr
				LEFT OUTER JOIN manufacturers mn
					ON pr.manufacturers_id = mn.manufacturers_id
				LEFT OUTER JOIN products_description pd
					ON (pr.products_id = pd.products_id AND pd.language_id = " . FL_LANG_ID . " AND length(trim(pd.products_name)) > 0)
				LEFT OUTER JOIN specials sp
					ON (pr.products_id = sp.products_id AND sp.expires_date > now())
				LEFT OUTER JOIN products_attributes pa
					ON (pr.products_id = pa.products_id)
				LEFT OUTER JOIN products_options po
					ON (pa.options_id = po.products_options_id AND po.language_id = " . FL_LANG_ID . ")
				LEFT OUTER JOIN products_options_values pov
					ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = " . FL_LANG_ID . ")
				LEFT OUTER JOIN tax_rates tx
					ON (pr.products_tax_class_id = tx.tax_class_id)
				LEFT OUTER JOIN products_to_categories pc
					ON pr.products_id = pc.products_id
				LEFT JOIN categories c
					ON pc.categories_id = c.categories_id
				LEFT JOIN shipping_status ss
					ON (pr.products_shippingtime = ss.shipping_status_id AND ss.language_id = " . FL_LANG_ID . ")
			WHERE
				pr.products_id = $product_nr
			GROUP BY
				pr.products_id";

		$result = xtc_db_query($sql);

		if (xtc_db_num_rows($result) and $row = xtc_db_fetch_array($result)) {

			if ($debug) {
				output_row($row);
			}
		
			if(isset($row['manufacturers_name']) && !empty($row['manufacturers_name'])) {			
				$attributes = array("vendor" => $row['manufacturers_name']);
			}
			else {
				$attributes = array();
			}
			
			$all_cat = get_all_product_category_names($row['products_id'], $debug);
			if(isset($all_cat) && !empty($all_cat)) {
				$attributes['cat'] = $all_cat;
			}

			$sql =
				"SELECT po.products_options_name AS pon,
					pov.products_options_values_name AS pov
				FROM products_attributes pa
				LEFT OUTER JOIN products_options po
					ON (pa.options_id = po.products_options_id AND po.language_id = " . FL_LANG_ID . ")
				LEFT OUTER JOIN products_options_values pov
					ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = " . FL_LANG_ID . ")
				WHERE pa.products_id = $product_nr";
			$result_fla = xtc_db_query($sql);

			while ($row_fla = xtc_db_fetch_array($result_fla)) {

				if ($debug) {
					output_row($row_fla);
				}

				if(!isset($attributes[$row_fla['pon']]))	{
					$attributes[$row_fla['pon']] = array($row_fla['pov']);
				}
				else {
					array_push($attributes[$row_fla['pon']], $row_fla['pov']);
				}
			}			

			$attributes_enc = null;
			foreach($attributes as $key => $value) {
				if(!is_array($value)) {
					if(!empty($value)) $attributes_enc = $attributes_enc . "&" . urlencode(ensure_encoding($key)) . "[]=" . urlencode(ensure_encoding($value));
				}
				else {
					foreach($value as $skey => $svalue) {
						if(!empty($svalue)) $attributes_enc = $attributes_enc . "&" . urlencode(ensure_encoding($key)) . "[]=" . urlencode(ensure_encoding($svalue));
					}
				}
			}

			if($attributes_enc[0] == '&') {
				$attributes_enc = substr($attributes_enc, 1);
			}

			$product = array(
				"id" => $row['products_id'],
				"ordernumber" => $row['products_model'],
				"name" => $row['products_name'],
				"summary" => extract_text($row['products_short_description']),
				"description" => get_description($row['products_model'], $row['products_short_description']),
				"price" => $xtcPrice->xtcGetPrice($row['products_id'], false, 1, $row['products_tax_class_id'], 0, 0, 0),
				"instead" => get_instead($row),
				"maxprice" => get_maxprice($row),
				"taxrate" => get_taxrate($row),
				"url" => xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$row['products_id']),
				"image" => get_image($row['products_image']),
				"attributes" => $attributes_enc,
				"keywords" => $row['products_keywords'],
				"groups" => '',
				"bonus" => '',
				"shipping" => extract_text($row['shipping_status_name']),
			);

			$values = array();
			foreach (get_columns() as $property) {
				array_push(
					$values,
					$product[$property]
				);
			}
			$text = get_encoded_text(implode($values, get_column_delimiter()));

			fwrite($fp , $text."\n");
			return true;
		}
		return false;
	}

	function get_all_product_category_names($productId, $debug = false) {
		$categories = array();
		$sql = "SELECT pc.categories_id AS cat FROM products_to_categories pc WHERE pc.products_id = ".$productId;
		$result = xtc_db_query($sql);
		if (xtc_db_num_rows($result)) {
			while ($row = xtc_db_fetch_array($result)) {

				if ($debug) {
					output_row($row);
				}

				array_push($categories,
					get_category_and_parent_category_names($row['cat'], $debug)
				);
			}
		}
		return $categories;
	}

	function get_category_and_parent_category_names($cat, $debug = false) {
		$catid = $cat;
		$depthLimit = 100;

		$categories = array();
		$depthLevel = 0;
		while ($catid != 0 && $depthLevel < $depthLimit)
		{
			$sql =
				"SELECT
					c.parent_id AS parent,
					cd.categories_name AS name
				FROM
					categories c
					LEFT OUTER JOIN categories_description cd
						ON (c.categories_id = cd.categories_id AND cd.language_id = " . FL_LANG_ID . ")
				WHERE
					c.categories_id = ".$catid.";";

			$result = xtc_db_query($sql);

			if (xtc_db_num_rows($result) && ($row = xtc_db_fetch_array($result))) {

				if ($debug) {
					output_row($row);
				}

				$newcatid = $row['parent'];
				$name = strip_tags($row['name']);
				$name = str_replace("/", "/&shy;", $name);
				/* push the parent category on the category stack */
				array_push($categories, $name);
				if ($newcatid == $catid) break;
				$catid = $newcatid;
				$depthLevel++;
			} else {
				break;
			}
		}

		/* higher categories are further back in the category stack, reverse it */
		$categories = array_reverse($categories);

		if ($depthLevel < $depthLimit) {
			return implode(get_category_delimiter(), $categories);
		} else {
			return $name;
		}
	}

	function output_row($row) {
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array_map('extract_text', array_keys($row)), get_column_delimiter());
		fputcsv($fp, array_map('extract_text', array_values($row)), get_column_delimiter());
		fclose($fp);
	}
?>
