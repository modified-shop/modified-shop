<?php

try {
    if (!isset($config)) {
        $config = include 'config/config.php';
    }

    include 'include/utils.php';

    if ($_SESSION['RF']["verify"] != "RESPONSIVEfilemanager") {
        response(trans('forbidden') . AddErrorLocation(), 403)->send();
        exit;
    }

    if (!rfm_check_token()) {
        response(trans('forbidden') . AddErrorLocation(), 403)->send();
        exit;
    }

    include 'include/mime_type_lib.php';

    $ftp = ftp_con($config);

    if ($ftp) {
        $source_base = $config['ftp_base_folder'] . $config['upload_dir'];
        $thumb_base = $config['ftp_base_folder'] . $config['ftp_thumbs_dir'];
    } else {
        $source_base = $config['current_path'];
        $thumb_base = $config['thumbs_base_path'];
    }

    if (!isset($_POST["fldr"])) {
        return;
    }

    $_POST['fldr'] = str_replace('undefined', '', $_POST['fldr']);

    $fldr = rawurldecode(trim(strip_tags($_POST['fldr']), "/") . "/");

    // the target paths are only built once the folder passed the check
    if (!checkRelativePath($fldr)) {
        response(trans('wrong path') . AddErrorLocation())->send();
        exit;
    }

    $storeFolder = $source_base . $_POST["fldr"];
    $storeFolderThumb = $thumb_base . $_POST["fldr"];

    $path = $storeFolder;
    $cycle = true;
    $max_cycles = 50;
    $i = 0;
    //GET config
    while ($cycle && $i < $max_cycles) {
        $i++;
        if ($path == $config['current_path']) {
            $cycle = false;
        }
        if (file_exists($path . "config.php")) {
            $configTemp = include $path . 'config.php';
            $config = array_merge($config, $configTemp);
            //TODO switch to array
            $cycle = false;
        }
        $path = fix_dirname($path) . '/';
    }

    require('UploadHandler.php');
    $messages = null;
    if (trans("Upload_error_messages") !== "Upload_error_messages") {
        $messages = trans("Upload_error_messages");
    }

    // make sure the length is limited to avoid DOS attacks
    if (isset($_POST['url']) && strlen($_POST['url']) < 2000) {
        $url = $_POST['url'];
        $urlPattern = '/^(https?:\/\/)?([\da-z\.-]+\.[a-z\.]{2,6}|[\d\.]+)([\/?=&#]{1}[\da-z\.-]+)*[\/\?]?$/i';

        if (preg_match($urlPattern, $url)) {
            // the server does the fetching, so keep internal addresses out of reach
            $url_parts = parse_url((strpos($url, '://') === false ? 'http://' . $url : $url));
            $url_host = (isset($url_parts['host'])) ? $url_parts['host'] : '';
            // the scheme keeps the case it was written in, and CURLOPT_RESOLVE
            // only binds the host and port pair it is given
            $url_scheme = strtolower((isset($url_parts['scheme'])) ? $url_parts['scheme'] : 'http');
            $url_port = (isset($url_parts['port'])) ? (int)$url_parts['port'] : (($url_scheme == 'https') ? 443 : 80);
            $url_ip = ($url_host != '') ? gethostbyname($url_host) : '';

            $url_ip_flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
            if (defined('FILTER_FLAG_GLOBAL_RANGE')) {
                $url_ip_flags |= FILTER_FLAG_GLOBAL_RANGE;
            }

            if (filter_var($url_ip, FILTER_VALIDATE_IP, $url_ip_flags) === false) {
                throw new Exception('Is not a valid URL.');
            }

            // the blocks above leave these through, and FILTER_FLAG_GLOBAL_RANGE only
            // arrived in PHP 8.2 while the shop still starts at 8.0, so they are listed
            $url_ip_long = ip2long($url_ip);
            $url_ip_reserved = array(
              '100.64.0.0/10',    // carrier grade NAT
              '192.0.0.0/24',     // IETF protocol assignments
              '192.0.2.0/24',     // documentation
              '192.88.99.0/24',   // 6to4 relay anycast
              '198.18.0.0/15',    // benchmarking
              '198.51.100.0/24',  // documentation
              '203.0.113.0/24',   // documentation
              '224.0.0.0/4',      // multicast
            );
            foreach ($url_ip_reserved as $url_ip_range) {
              list($url_range_net, $url_range_bits) = explode('/', $url_ip_range);
              $url_range_mask = (0xFFFFFFFF << (32 - (int)$url_range_bits)) & 0xFFFFFFFF;
              if (($url_ip_long & $url_range_mask) === (ip2long($url_range_net) & $url_range_mask)) {
                throw new Exception('Is not a valid URL.');
              }
            }

            $temp = tempnam('/tmp','RF');

            $ch = curl_init($url);
            $fp = fopen($temp, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            // pin the transfer to the address that was checked, so it is not resolved a second time
            curl_setopt($ch, CURLOPT_RESOLVE, array($url_host . ':' . $url_port . ':' . $url_ip));
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception('Invalid URL');
            }
            fclose($fp);

            $_FILES['files'] = array(
                'name' => array(basename($_POST['url'])),
                'tmp_name' => array($temp),
                'size' => array(filesize($temp)),
                'type' => null
            );
        } else {
            throw new Exception('Is not a valid URL.');
        }
    }


    if ($config['mime_extension_rename']) {
        $info = pathinfo($_FILES['files']['name'][0]);
        $mime_type = $_FILES['files']['type'][0];
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($_FILES['files']['tmp_name'][0]);
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['files']['tmp_name'][0]);
        } else {
            $mime_type = get_file_mime_type($_FILES['files']['tmp_name'][0]);
        }
        $extension = get_extension_from_mime($mime_type);

        if ($extension == 'so' || $extension == '' || $mime_type == "text/troff") {
            $extension = $info['extension'];
        }
        $filename = $info['filename'] . "." . $extension;
    } else {
        $filename = $_FILES['files']['name'][0];
    }
    $_FILES['files']['name'][0] = fix_filename($filename, $config);

    if(!$_FILES['files']['type'][0]){
        $_FILES['files']['type'][0] = $mime_type;

    }
    // LowerCase
    if ($config['lower_case']) {
        $_FILES['files']['name'][0] = fix_strtolower($_FILES['files']['name'][0]);
    }
    if (!checkresultingsize($_FILES['files']['size'][0])) {
    	if ( !isset($upload_handler->response['files'][0]) ) {
            // Avoid " Warning: Creating default object from empty value ... "
            $upload_handler->response['files'][0] = new stdClass();
        }
        $upload_handler->response['files'][0]->error = sprintf(trans('max_size_reached'), $config['MaxSizeTotal']) . AddErrorLocation();
        echo json_encode($upload_handler->response);
        exit();
    }

    $uploadConfig = array(
        'config' => $config,
        'storeFolder' => $storeFolder,
        'storeFolderThumb' => $storeFolderThumb,
        'ftp' => $ftp,
        'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $storeFolder,
        'upload_url' => $config['base_url'] . $config['upload_dir'] . $_POST['fldr'],
        'mkdir_mode' => $config['folderPermission'],
        'max_file_size' => $config['MaxSizeUpload'] * 1024 * 1024,
        'correct_image_extensions' => true,
        'print_response' => false
    );

    if (!$config['ext_blacklist']) {
        $uploadConfig['accept_file_types'] = '/\.(' . implode('|', $config['ext']) . ')$/i';

        if ($config['files_without_extension']) {
            $uploadConfig['accept_file_types'] = '/((\.(' . implode('|', $config['ext']) . ')$)|(^[^.]+$))$/i';
        }
    } else {
        $uploadConfig['accept_file_types'] = '/\.(?!' . implode('|', $config['ext_blacklist']) . '$)/i';

        if ($config['files_without_extension']) {
            $uploadConfig['accept_file_types'] = '/((\.(?!' . implode('|', $config['ext_blacklist']) . '$))|(^[^.]+$))/i';
        }
    }

    if ($ftp) {
        if (!is_dir($config['ftp_temp_folder'])) {
            mkdir($config['ftp_temp_folder'], $config['folderPermission'], true);
        }

        if (!is_dir($config['ftp_temp_folder'] . "thumbs")) {
            mkdir($config['ftp_temp_folder'] . "thumbs", $config['folderPermission'], true);
        }

        $uploadConfig['upload_dir'] = $config['ftp_temp_folder'];
    }

    //print_r($_FILES);die();
    $upload_handler = new UploadHandler($uploadConfig, true, $messages);
} catch (Exception $e) {
    $return = array();

    if ($_FILES['files']) {
        foreach ($_FILES['files']['name'] as $i => $name) {
            $return[] = array(
                'name' => $name,
                'error' => $e->getMessage(),
                'size' => $_FILES['files']['size'][$i],
                'type' => $_FILES['files']['type'][$i]
            );
        }

        echo json_encode(array("files" => $return));
        return;
    }

    echo json_encode(array("error" => $e->getMessage()));
}
