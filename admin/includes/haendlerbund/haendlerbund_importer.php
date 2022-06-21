<?php

class haendlerbund_importer
{

    #Klassenvariablen
    var $version = '1.03';
    var $api = false;
    var $api_config = false;
    var $data = array();

    #Konstruktor
    function __construct()
    {
    }

    #Process Methode: Verarbeitet die Formulardaten, validiert sie und importiert die Daten in die DB.
    function process($api_config)
    {
        $agb_service = array(
            "haendlerbund_impressum|Impressum" => "1293C20B491",
            "haendlerbund_agb|AGB" => "12766C46A8A",
            "haendlerbund_widerruf|Widerrufsbelehrung" => "12766C53647",
            "haendlerbund_versandinfo|Zahlung und Versand" => "12766C58F26",
            "haendlerbund_datenschutz|Datenschutzerkl&auml;rung" => "160DEDA9674",
            "haendlerbund_batteriegesetz|Hinweise zur Batterieentsorgung" => "134CBB4D101"
        );
        if ($_POST["config_save"] == 1) {
            $this->saveConfiguration($data);
        }

        if ($_POST['agb_import'] == 1 || $api_config == 1) {
            if ($api_config == 1) {
                $apikey = $_GET["api_key"];
            } else {
                $apikey = $this->getConfigurationValue("haendlerbund_key");
            }

            $check_array = array();
            $error = "";
            $_SESSION["import_message"] = "";

            foreach ($agb_service as $agb_array => $key) {
                $agb_array = explode("|", $agb_array);
                $agb = $agb_array[0];
                $agb_name = $agb_array[1];

                $url = "https://legaltext-cache.haendlerbund.de/cache/?APIkey=1IqJF0ap6GdDNF7HKzhFyciibdml8t4v&did={$key}&AccessToken={$apikey}&mode=classes";

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_USERAGENT, "INPUT DATA SCRIPT");
                curl_setopt($curl, CURLOPT_URL, $url);
                $result = curl_exec($curl);
                curl_close($curl);

                $result = str_replace("€", "&euro;", $result);
                $result = strip_tags($result, "<p><a><br /><br>\n<strong><b>");

                if ($api_config == 1) {
                    if (!preg_match("/DOCUMENT_NOT_AVAILABLE/i", $result) && !preg_match(
                            "/SHOP_NOT_FOUND/i",
                            $result
                        )) {
                        $check_array[] = $agb;
                        echo $this->getContentIDSelect($agb_name, $agb, $agb);
                        xtc_db_query(
                            "UPDATE configuration SET configuration_value='1' WHERE configuration_key='" . $agb . "_hbon'"
                        );
                    } else {
                        xtc_db_query(
                            "UPDATE configuration SET configuration_value='0' WHERE configuration_key='" . $agb . "_hbon'"
                        );
                    }
                }

                if ($_POST['agb_import'] == 1 && !preg_match("/DOCUMENT_NOT_AVAILABLE/i", $result) && !preg_match(
                        "/SHOP_NOT_FOUND/i",
                        $result
                    )) {
                    if ($this->updateContent(
                            $this->getConfigurationValue($agb),
                            utf8_decode(
                                str_replace(
                                    'style="font-size: medium;"',
                                    "",
                                    str_replace('style="font-size: 14px;"', "", $result)
                                )
                            )
                        ) == "1") {
                        $_SESSION["import_message"] = "Import erfolgreich abgeschlossen!";
                    }
                }
            }

            if (count($check_array) == 0 && $api_config == 1) {
                echo "<br /><p align='center' style='color:red'>Es konnten keine Rechtstexte gefunden werden. <br />Bitte &uuml;berpr&uuml;fen Sie Ihren API-Key oder wenden Sie sich an den Support.<input type='hidden' name='api_check_ok' id='api_check_ok' value='0' /></p>";
            } else {
                echo "<input type='hidden' name='api_check_ok' id='api_check_ok' value='1' />";
            }

            if (strtolower($_SESSION['language_charset']) != "utf-8") {
                $return = utf8_encode($return);
            }

            return $return;
        }
    }


    #checkErrors Methode: Validiert die Formulardaten
    function checkErrors($arRequired)
    {
        foreach ($arRequired as $key => $element) {
            if (empty($_POST[$element])) {
                return true;
            }
        }
        return false;
    }


    #getFormData Methoda: Entschaerft die Formulardaten fuer eine sichere Verwendung der Variablen in einer MySQL-Query
    function getFormData($array)
    {
        $data = array();
        foreach ($array as $key => $dataname) {
            $data[$dataname] = xtc_db_input($_POST[$dataname]);
        }
        return $data;
    }


    #saveConfiguration Methode: Speichert alle im Parameter uebergebenen Konfigurationen in der DB.
    function saveConfiguration()
    {
        foreach ($_POST as $configuration_key => $configuration_value) {
            if (stripos($configuration_key, "haendlerbund") !== false) {
                $sql = xtc_db_query(
                    "SELECT * FROM configuration WHERE configuration_key='" . $configuration_key . "' LIMIT 1"
                );
                if (xtc_db_num_rows($sql)) {
                    xtc_db_query(
                        "UPDATE configuration SET configuration_value='" . $configuration_value . "' WHERE configuration_key='" . $configuration_key . "' LIMIT 1"
                    );
                }
            }
        }
    }

    #getConfigurationValue Methode: Holt einen Konfigurations-Wert aus der DB, passend zum via Parameter uebergebenen Schluessel
    function getConfigurationValue($key)
    {
        $value = '';
        $sqlConfiguration = xtc_db_query(
            "SELECT * FROM configuration WHERE configuration_key='" . strtoupper($key) . "' LIMIT 1"
        );
        if (xtc_db_num_rows($sqlConfiguration) > 0) {
            $dataConfiguration = xtc_db_fetch_array($sqlConfiguration);
            $value = $dataConfiguration['configuration_value'];
        }
        return $value;
    }


    #updateContent Methode: Eigentliche Import-Methode fuer die Texte in die DB
    function updateContent($content_id, $content_text)
    {
        $return = xtc_db_query(
            "UPDATE content_manager SET content_text='" . $content_text . "' WHERE content_id=" . $content_id . " LIMIT 1"
        );
        return $return;
    }

    function install()
    {
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_KEY'  ) ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_IMPRESSUM_HBON') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_AGB_HBON') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_WIDERRUF_HBON') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_VERSANDINFO_HBON') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_DATENSCHUTZ_HBON') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_BATTERIEGESETZ_HBON') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_IMPRESSUM') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_AGB') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_WIDERRUF') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_VERSANDINFO') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_DATENSCHUTZ') ");
        xtc_db_query("INSERT INTO configuration ( configuration_key ) VALUES  ( 'HAENDLERBUND_BATTERIEGESETZ') ");
    }

    #getImportForm Methode: Generiert das Modul-Formular
    function getImportForm()
    {
        $installCheck = xtc_db_query(
            "SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key='HAENDLERBUND_KEY' LIMIT 1"
        );
        if (xtc_db_num_rows($installCheck) == 0) {
            $this->install();
        }

        $return = "";
        if ($this->getConfigurationValue("haendlerbund_key") == "" || $_GET["start_konfig"] == 1) {
            $return .= '<div class="content" id="container">
            <div class="title_hb">
            <h5 class="title_hb">H&auml;ndlerbund Mitgliedschaft</h5>
            </div>
                <br />
                Sie ben&ouml;tigen f&uuml;r die Verwendung dieser Schnittstellen eine H&auml;ndlerbund Mitgliedschaft, welche Sie hier beantragen k&ouml;nnen:<br /><br />&gt;&gt;&gt; <a target="_blank" href="https://partner.haendlerbund.de/partnerdoor.php?partnerid=partner_modified&bannerid=64" style="color: #3581C1; font-family: Arial,Helvetica,sans-serif; font-size: 12px; text-decoration: underline;">Jetzt Mitglied werden</a> &lt;&lt;&lt;
                <br />
                <br />
                <div class="title_hb">
                    <h5 class="title_hb">Schnittstellen-Konfiguration</h5>
                </div>
                <br />
                Bitte konfigurieren Sie Ihr pers&ouml;nliches H&auml;ndlerbund Modul indem Sie die folgenden 2 Schritte durchlaufen. 

                ' . xtc_draw_form('mainForm', 'haendlerbund.php', '', 'post') . '
                    <input type="hidden" name="config_save" value="1" />
                    <div class="widget">
                        <div class="wizard swMain">
                            <ul>
                                <li><a href="#step-1" class="bordLeft wFirst"><h5 class="stepDesc iComputer">Sicherheitsschl&uuml;ssel</h5></a></li>
                                <li><a href="#step-2" class="bordRight"><h5 class="stepDesc iSpeech">Inhaltszuweisung</h5></a></li>
                            </ul>
                            <div id="step-1">   
                                <div class="rowElem nobg" >
                                    <label  style="width:150px; float:left"><b>API Sicherheitsschl&uuml;ssel:</b></label>
                                    <div class="formRight"  style="width:500px">
                                        <input type="text" name="haendlerbund_key" value="' . $this->getConfigurationValue("haendlerbund_key")
                                            . '" id="haendlerbund_key" style="width: 100%;"/>
                                        </div>
                                        <div class="fix"></div>
                                    </div>
                                    <div class="fix"></div>
                                </div>
                                <div id="step-2" style="height:350px">
                                    <p>Bitte weisen Sie jedem Rechtstext einen passenden Inhalt aus dem Content-Manager zu.
                                    <br />
                                    <b>Achten Sie darauf, dass jeder Rechtstext einen unterschiedlichen Content zugewiesen ist.</b></p>
                                    <br />
                                    <strong id="text"><center>
                                    <br />
                                    <img src="includes/haendlerbund/images/loading.gif" />
                                    <br />
                                    <br />
                                    Einen Moment bitte, es wird geladen!</center></strong>
                                    <br style="clear: both" />
                                    <br/>
                                </div>
                            </div>
                            <div class="fix"></div>
                        </div>
                    </form>
            </div>';
        } else {
            $return .= '<div class="content" id="container">
                <div class="title_hb">
                    <h5 class="title_hb">Rechtstexte importieren</h5>
                </div>
                <br />
                ' . xtc_draw_form('AGBimport', 'haendlerbund.php', '', 'post') . '
                    <input type="hidden" name="agb_import" value="1" />
                    <div style="width:731px; height:300px; background-image:url(includes/haendlerbund/images/import_picture.jpg); background-position:top;">
                        <div style="padding:10px; padding-top:50px;">
                            <div style="line-height:200%; width:260px; float:left; padding-top:110px; font-size:10px">' . $this->rt_gen() . '</div>
                    <div style="float:left; width:216px;">
                        <div style="line-height:125%;">' .
                (($_SESSION["import_message"] != "") ?
                    '<img src="includes/haendlerbund/images/import_ok_btn.png" width="217" height="52">' . ($_SESSION["import_message"] = "")
                    :
                    '<img src="includes/haendlerbund/images/pfleil_blue.png" width="217" height="52">
                    <span style="color:#1E4565; font-weight:bold;">
                        <br>
                        WICHTIG!
                    </span>
                    <br>
                    Mit Ausf&uuml;hren des Imports werden alle bisher vorhandenen Rechtstexte ersetzt!<br>
                    <br>
                    <br>
                    <input type="submit" name="import" value="" style="background-image:url(includes/haendlerbund/images/import_btn.png); width:196px; height:26px; border:0;" />'
                ) .
                '</div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <a href="?start_konfig=1"><img src="includes/haendlerbund/images/konfig_btn.png" width="175" height="23" border="0"></a>
                    </form>
                </div>';
        }

        return $return;
    }


    function rt_gen()
    {
        $agb_service = array(
            "Impressum" => "haendlerbund_impressum",
            "AGB" => "haendlerbund_agb",
            "Widerrufsrecht" => "haendlerbund_widerruf",
            "Zahlung und Versand" => "haendlerbund_versandinfo",
            "Datenschutzerkl&auml;rung" => "haendlerbund_datenschutz",
            "Hinweise zur Batterieentsorgung" => "haendlerbund_batteriegesetz"
        );
        $rt_field = "";
        $sql_check = xtc_db_query(
            "SELECT configuration_key FROM configuration WHERE configuration_key LIKE '%_HBON' AND configuration_value = 1 ORDER BY  configuration_id"
        );
        while ($agb = xtc_db_fetch_array($sql_check)) {
            $key = str_replace("_HBON", "", $agb["configuration_key"]);
            if (in_array(strtolower($key), $agb_service)) {
                $rt_field .= '<br /><img src="includes/haendlerbund/images/arrow.png" /> &nbsp;&nbsp;' . array_search(strtolower($key), $agb_service);
            }
        }

        return $rt_field;
    }


    #getSelect Methode: Generiert ein Select-Formularfeld
    function getSelect($label, $name, $id, $options)
    {
        $return .= $this->getLabel($label, $id, $true);
        $return .= "<select name='" . $name . "' id='" . $id . "'>";
        if ($this->getConfigurationValue($name) and !$value) {
            $value = $this->getConfigurationValue($name);
        } elseif ($_POST[$name]) {
            $data = $this->getFormData(array($name));
            $value = $data[$name];
        }
        foreach ($options as $key => $option) {
            $selected = "";
            if ($option == $value) {
                $selected = " selected='selected'";
            }
            $return .= "<option value='" . $option . "'" . $selected . ">" . $option . "</option>";
        }
        $return .= "</select>";
        return $return;
    }


    #getLabel Methode: Generiert ein Label fuer ein Formularfeld
    function getLabel($label, $id, $required = false, $style = 'float:left;width:250px;')
    {
        if ($required) {
            $star = "*";
        }
        return "<label for='" . $id . "' id='" . $id . "_label' style='" . $style . "'>" . $label . $star . "</label>";
    }


    function getInputForm($type, $value, $label, $name, $id, $required = true, $function = '')
    {
        if ($this->getConfigurationValue($name) and !$value) {
            $value = $this->getConfigurationValue($name);
        } elseif ($_POST[$name]) {
            $data = $this->getFormData(array($name));
            $value = $data[$name];
        }
        return $this->getLabel(
                $label,
                $id,
                $required
            ) . "<input type='" . $type . "' name='" . $name . "' id='" . $id . "' value='" . $value . "' onChange='" . $function . "' />";
    }


    #getContentIDSelect Methode: Generiert ein Select-Formularfeld mit allen Inhalten des SHops
    function getContentIDSelect($label, $name, $id, $required = true, $lang = 1)
    {
        $return = "<div style='padding-left: 15px;padding-right: 15px;margin-bottom: 15px;'>";
        $return .= $this->getLabel($label, $id, $required) . "<select name='" . $name . "' id='" . $id . "'>";
        if ($this->getConfigurationValue($name) and !$value) {
            $value = $this->getConfigurationValue($name);
        } elseif ($_POST[$name]) {
            $data = $this->getFormData(array($name));
            $value = $data[$name];
        }
        $sql = xtc_db_query(
            "SELECT content_id,content_title FROM content_manager WHERE languages_id=" . $_SESSION["languages_id"] . " ORDER BY content_title"
        );
        while ($row = xtc_db_fetch_array($sql)) {
            $selected = "";
            if ($row['content_id'] == $value) {
                $selected = " selected='selected'";
            }
            $return .= "<option value='" . $row['content_id'] . "'" . $selected . ">" . $row['content_title'] . "</option>";
        }
        $return .= "</select></div>";

        return $return;
    }

}
?>
