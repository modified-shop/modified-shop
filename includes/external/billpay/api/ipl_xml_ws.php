<?php
    require_once('ipl_xml_api.php');
    function parse_async_capture($postdata = null){
		if (empty($postdata)) {
            $postdata = file_get_contents("php://input");
        }
		if(!empty($postdata)){
			$xml = ipl_core_load_xml($postdata);
			if (!empty($xml)){
				$data = ipl_core_parse_async_capture_response($xml);
				$data['postdata'] = $postdata;
				$data['xmlStatus'] = true;
			}else{
				$data['xmlStatus'] = false;
				$data['postdata'] = $postdata;
			}			
		}else{
			$data['xmlStatus'] = false;
			$data['postdata'] = $postdata;
		}		
		return $data; 
	}
	

