<?php 

$prev = isset($_GET['prev'])  && $_GET['prev'] == 1 ? true : false;
$email_preview = isset($_POST['email_preview']) && $_POST['email_preview'] == 1 ? true : false;
if ($email_preview) {
  $action ='update_order';
}

function email_preview_tabs()
{
    global $lang_charset;
    //header
    $email_div = '<head>'.PHP_EOL ;
    $email_div .= '<meta http-equiv="Content-Type" content="text/html; charset='.$lang_charset.'" /> '.PHP_EOL ;
    
    //CSS
    $email_div .=
    '<style type="text/css">
    #tab_html,#tab_txt {
    font-family: Verdana, Arial, sans-serif;
    font-size:13px;
    padding: 2px 5px;
    border: 1px solid #a3a3a3;
    float:left;
    cursor:pointer;
    margin-left:-1px;
    margin-bottom: 15px;
    }
    .active {
    background: #FF6165;
    }
    </style>'. PHP_EOL;
    

    
    //JAVASCRIPT
    $email_div .=
    '<script type="text/javascript">
    function change_class(newId,oldId) {
      //alert (newId);
      var newEnd = newId.split("_");
      document.getElementById("tab_"+newEnd[2]).className += " active";
      var newElem = document.getElementById(newId);
      newElem.style.display="block";
      var oldEnd = oldId.split("_");
      var cssClassStr = document.getElementById("tab_"+oldEnd[2]).className;
      document.getElementById("tab_"+oldEnd[2]).className = cssClassStr.replace(" active","");
      var oldElem = document.getElementById(oldId);            
      oldElem.style.display="none";
    }
    </script>'.PHP_EOL;
    
    $email_div .= '</head>'.PHP_EOL ;
    
    //TABS
    $email_div .= '<div id="tab_html" class="tab active" onclick="change_class(\'email_preview_html\',\'email_preview_txt\')">HTML</div>'.PHP_EOL;
    $email_div .= '<div id="tab_txt" class="tab" onclick="change_class(\'email_preview_txt\',\'email_preview_html\')">TEXT</div>'.PHP_EOL;
    $email_div .= '<div style="clear:both;"></div>'.PHP_EOL;
    
    return $email_div;
}