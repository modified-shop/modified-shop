<?php namespace kcfinder\cms;

/*---------------------------------
KCFinder Version 3.12 Integration Modul for modified shop

Version 1.00 by  web www.rpa-com.de
-----------------------------------*/

class ModifiedShop{
    protected static $authenticated = false;
    static function checkAuth() {
        $current_cwd = getcwd();
        if ( ! self::$authenticated) {
		    //Adminverzeichnis Shop
            $adminDir = '../../../'; 
            $current_cwd = getcwd();
            chdir($adminDir);
			//echo '$root_path; '.getcwd(); EXIT;
			define('_IS_FILEMANAGER',true);
			require_once('includes/application_top.php');
			chdir( $current_cwd);
			if (isset($_SESSION) && $_SESSION['customers_status']['customers_status_id'] == '0')	{
				$access_permission_query = xtc_db_query("SELECT * FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".$_SESSION['customer_id']."'");
				$access_permission = xtc_db_fetch_array($access_permission_query);
				if (!isset($access_permission['kcfinder']) || ($access_permission['kcfinder'] != '1')) {
				  //die('Direct Access to this location is not allowed.');
				}
				self::$authenticated = true;
				if (!isset($_SESSION['KCFINDER'])) {
					$_SESSION['KCFINDER'] = array();
				}
				if(!isset($_SESSION['KCFINDER']['disabled'])) {
					$_SESSION['KCFINDER']['disabled'] = false;
				}
				// Hauptverzeichnis Shop
        $shopDir = '../../../../';
				//$_SESSION['KCFINDER']['_check4htaccess'] = false; //Funktioniert nicht mehr bei kcfinder 3.12
				$_SESSION['KCFINDER']['uploadURL'] = $shopDir;
				//$_SESSION['KCFINDER']['uploadDir'] = BOLMER_BASE_PATH.'assets/';
				$_SESSION['KCFINDER']['thumbsDir'] = 'images/.thumbs'; // thumbsDir im images Verzeichnis anlegen
				$_SESSION['KCFINDER']['theme'] = 'default';
				
				//Image Processing
				$_SESSION['KCFINDER']['maxImageWidth'] = (defined('MODULE_KCFINDER_MAXIMAGEWIDTH') && MODULE_KCFINDER_MAXIMAGEWIDTH > 0 ? MODULE_KCFINDER_MAXIMAGEWIDTHH : 0);
				$_SESSION['KCFINDER']['maxImageHeight'] = (defined('MODULE_KCFINDER_MAXIMAGEHEIGHT') && MODULE_KCFINDER_MAXIMAGEHEIGHT > 0 ? MODULE_KCFINDER_MAXIMAGEHEIGHT : 0);
				//Thumbnailgröße
				$_SESSION['KCFINDER']['thumbWidth'] = (defined('MODULE_KCFINDER_THUMBSWIDTH') && MODULE_KCFINDER_THUMBSWIDTH > 0 ? MODULE_KCFINDER_THUMBSWIDTH : 100);
				$_SESSION['KCFINDER']['thumbHeight'] = (defined('MODULE_KCFINDER_THUMBSWIDTH') && MODULE_KCFINDER_THUMBSHEIGHT > 0 ? MODULE_KCFINDER_THUMBSHEIGHT : 100);
				//Speicherpfade nach type
				unset($_SESSION['KCFINDER']['types']); //Alle Standardeinstellungen löschen
				$_SESSION['KCFINDER']['types'] = array('images'  =>  "*img");

				if (isset($_GET['type'])) {
				  switch ($_GET['type']) {
					case 'images':
						$_SESSION['KCFINDER']['types'] = array('images'  =>  "*img");
						break;
					case 'flash':
						$_SESSION['KCFINDER']['types'] = array('media'  =>  "swf");
						break;
					case 'media':
						$_SESSION['KCFINDER']['types'] = array('media'  =>  "");
						break;
					case 'files':
						$_SESSION['KCFINDER']['types'] = array('media'  =>  "");
						break;
					default:
						$_SESSION['KCFINDER']['types'] = array('images'  =>  "*img");
				  }
				}
				
			} else {
			    die('Direct Access to this location is not allowed.');  
			}
        }
        return self::$authenticated;
    }
}
\kcfinder\cms\ModifiedShop::checkAuth();