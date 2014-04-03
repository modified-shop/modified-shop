<?php
/**
 * parse the raw post data which should contain a valid xml
 *
 * @return array value of the key xmlStatus indicates if we could parse the xml
 */
function parse_async_capture()
{
    // get the raw post data
    $postdata = file_get_contents("php://input");

    if (empty($postdata) === false) {

        $xml = ipl_core_load_xml($postdata);

        if (empty($xml) === false) {
            $data              = ipl_core_parse_async_capture_response($xml);
            $data['xmlStatus'] = true;
            $data['postdata']  = $postdata;

        } else {
            $data['xmlStatus'] = false;
            $data['postdata']  = $postdata;
        }
    } else {
        $data['xmlStatus'] = false;
        $data['postdata']  = $postdata;
    }
    return $data;
}
