<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once DIR_MAGNALISTER_INCLUDES.'lib/v3fake/Alias/Filesystem.php';

class MLFileBrowserHelper {

    /**
     * @var MLFileBrowserHelper|null
     */
    private static $instance = null;
    private static $isJSLoaded = false;

    /**
     * @return MLFileBrowserHelper
     */
    public static function gi() {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function getConfiguredBasePath() {
        $dir = $_GET['configPath'];

        // if configuration is not preset -> set default directory
        if (empty($dir)) {
            $dir = $_SERVER['DOCUMENT_ROOT'];
        }

        $dir = ltrim($dir, DIRECTORY_SEPARATOR);
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        $explode = explode(DIRECTORY_SEPARATOR, $dir);
        //echo print_m($explode);

        $fullPathCheck = DIRECTORY_SEPARATOR;
        $countDirectories = 0;

        foreach ($explode as $item) {
            $fullPathCheck .= $item.DIRECTORY_SEPARATOR;
            // skip while just a part is in string and it not full match
            if (strstr($_SERVER['DOCUMENT_ROOT'], $fullPathCheck) && ($_SERVER['DOCUMENT_ROOT'] != $fullPathCheck)) {
                continue;
            }
            if (rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) == rtrim($fullPathCheck, DIRECTORY_SEPARATOR)) {
                $item = rtrim($fullPathCheck, DIRECTORY_SEPARATOR);
            }
            if (!is_dir($fullPathCheck)) {
                break;
            }
            $countDirectories++;

            // check if directory has subdirectories
            $subDir = new DirectoryIterator($fullPathCheck);
            $bHasSubDirs = false;
            foreach ($subDir as $subFileInfo) {
                if ($subFileInfo->isDir() && !$subFileInfo->isDot()) {
                    $bHasSubDirs = true;
                    break;
                }
            }
            if ($bHasSubDirs) {
                $class = 'plus';
            } else {
                $class = 'leaf';
            }

            // Select "tick" element which is configured
            if ($class == 'leaf'
                && (trim($dir, DIRECTORY_SEPARATOR) == trim($fullPathCheck, DIRECTORY_SEPARATOR))
            ) {
                $class .= ' tick';
            }

            echo '
                            <div class="catelem" id="y_'.$item.'">
                                <span class="toggle '.$class.' ml-tree-node" id="y_toggle_'.$item.'" data-path="'.$fullPathCheck.'">&nbsp;</span>
                                <div class="catname" id="y_select_'.$item.'">
                                    <span class="catname">'.fixHTMLUTF8Entities($item).'</span>
                        ';
        }
        while ($countDirectories > 0) {
            $countDirectories--;
            echo '
                                </div>
                            </div>
                        ';
        }
    }

    /**
     * @param $controller MLFileBrowserHelper
     * @return null
     */
    public function getDirectories() {
        // Checks if directory has child's if not return 'leaf'
        $blHasChild = false;

        $basePath = $_GET['path'];

        $configPath = $_GET['configPath'];
        //echo $configPath."\n<br>";
        $dir = new DirectoryIterator($basePath);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $blHasChild = true;
                $item = $fileinfo->getFilename();

                //extend current path
                $currentPath = $basePath.$item.DIRECTORY_SEPARATOR;
                #echo "<br>".$configPath;
                #echo "<br>".$currentPath;
                #echo "<br>".var_dump(strpos($configPath, $currentPath));

                // if paths are the same then its already displayed
                if (strpos($configPath, $currentPath) !== false) {
                    continue;
                }

                $subDir = new DirectoryIterator($currentPath);
                $bHasSubDirs = false;
                foreach ($subDir as $subFileInfo) {
                    if ($subFileInfo->isDir() && !$subFileInfo->isDot()) {
                        $bHasSubDirs = true;
                        break;
                    }
                }

                if ($bHasSubDirs) {
                    $class = 'plus';
                } else {
                    $class = 'leaf';
                }

                //echo "\t\t".$fileinfo->getFilename()."\n";
                echo '
                            <div class="catelem" id="y_'.$item.'">
                                <span class="toggle '.$class.' ml-tree-node" id="y_toggle_'.$item.'" data-path="'.$currentPath.'">&nbsp;</span>
                                <div class="catname" id="y_select_'.$item.'">
                                    <span class="catname">'.fixHTMLUTF8Entities($item).'</span>
                                </div>
                            </div>
                ';
            }
        }

        if ($blHasChild === false) {
            echo 'leaf';
        }
    }

    public function getView($item, $idKey, $sCurrentURL, $value) {
        ob_start();
        $sHtmlId = 'config_'.$idKey;
        ?>
        <input class="fullwidth"
               type="text" id="<?php echo $sHtmlId ?>"
               name="conf[<?php echo $item['key'] ?>]"
               placeholder="<?php echo isset($item['placeholder']) ? $item['placeholder'] : ''; ?>"
            <?php echo 'value="'.htmlspecialchars((isset($value) && is_scalar($value) ? $value : $item['default']), ENT_COMPAT).'"' ?>
            <?php echo isset($item['maxlength']) ? "maxlength='{$item['maxlength']}'" : ''; ?>
               style="width: 80%"/>

        <a id="fileBrowserButton_<?php echo $sHtmlId; ?>" class="mlbtn abutton js-field" name="<?php echo $item['key'] ?>">
            <?php echo ML_GENERIC_CATEGORIES_CHOOSE ?>
        </a>

        <div id="fileBrowser_<?php echo $sHtmlId; ?>" class="dialog2" title="<?php echo ML_UPLOADINVOICE_FILEBROWSER_HEADLINE?>">
            <table id="catMatch">
                <tbody>
                <tr>
                    <td id="ebayCats" class="catView">
                        <div class="catView">
                            <!-- placeholder for Content -->
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <div id="messageDialog" class="dialog2"></div>
            <span class="small"><?php echo ML_UPLOADINVOICE_FILEBROWSER_INFORMATION?></span>
        </div>

        <script type="text/javascript">


            $(document).ready(function () {

                $('#fileBrowserButton_<?php echo $sHtmlId; ?>').on('click', function () {
                    if ($(this).hasClass('disabled')) {
                        return;
                    }
                    var oFileBrowser = $('#fileBrowser_<?php echo $sHtmlId; ?>'),
                        oFileBrowserButton = $(this);
                    $.blockUI(blockUILoading);

                    //empty filebrowser
                    oFileBrowser.find('div.catView').html('');

                    var url = '<?php echo toURL($sCurrentURL, array(
                        'action'     => 'fileBrowser',
                        'method'     => 'GetConfiguredBasePath',
                        'kind'       => 'ajax',
                        'configPath' => "{{config_path}}",
                    ), true) ?>';

                    $.ajax({
                        'method': 'get',
                        'url': url
                            .replace('{{config_path}}', oFileBrowserButton.parent().find('input[type="text"]').val()),
                        'success': function (data) {
                            $.unblockUI();
                            if (data == 'error') {
                            } else {
                                oFileBrowser.find('div.catView').append(data);
                            }
                        }
                    });

                    oFileBrowser.jDialog({
                        width: '75%',
                        minWidth: '300px',
                        buttons: {
                    <?php echo json_encode(ML_BUTTON_LABEL_ABORT); ?>:

                    function () {
                        $(this).dialog('close');
                    }

                ,
                    <?php echo json_encode(ML_BUTTON_LABEL_OK); ?>:

                    function () {
                        var path = $('#fileBrowser_<?php echo $sHtmlId; ?>').find('span.toggle.tick').data('path');
                        if (path != false) {
                            oFileBrowserButton.parent().find('input[type="text"]').val(path);
                            $(this).dialog('close');
                        }
                    }
                },
                    open: function (event, ui) {
                        var tbar = $('#ebayCategorySelector').parent().find('.ui-dialog-titlebar');
                        if (tbar.find('.ui-icon-arrowrefresh-1-n').length == 0) {
                            var rlBtn = $(
                                '<a class="ui-dialog-titlebar-close ui-corner-all ui-state-focus ml-js-noBlockUi" ' +
                                'role="button" href="#" style="right: 2em; padding: 0px;">' +
                                '<span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>' +
                                '</a>'
                            );
                            tbar.append(rlBtn);
                            rlBtn.click(function (event) {
                                event.preventDefault();
                                initEBayCategories(true);
                            });
                        }
                    }
                })
                    ;
                });
            });
        </script>
        <?php
        $sOut = $this->getTreeJS($sCurrentURL) .  ob_get_clean();
        return $sOut;
    }


    public function getTreeJS($sCurrentURL) {
        $sOut = '';
        if(!self::$isJSLoaded){
            self::$isJSLoaded = true;
        ob_start();
        ?>
        <script type="text/javascript">
            $(document).ready(function () {

                $('body').on('click', 'span.ml-tree-node', function (e) {
                    var oDir = $(this);
                    if (oDir.hasClass('minus')) {
                        oDir.removeClass('minus').addClass('plus');
                        oDir.parent().find('.catelem').each(function () {
                            var toggle = $(this).find('span.toggle');
                            if (toggle.hasClass('tick')) {
                            } else {
                                $(this).remove();
                            }
                        })
                        return;
                    }

                    // when directory got selected
                    if (oDir.hasClass('leaf')) {
                        $('div.catView').find('span.toggle.tick').removeClass('tick');
                        oDir.addClass('tick');
                        return;
                    }

                    var url = '<?php echo toURL($sCurrentURL, array(
                        'action'     => 'fileBrowser',
                        'method'     => 'GetDirectories',
                        'kind'       => 'ajax',
                        'path'       => "{{current_path}}",
                        'configPath' => "{{config_path}}",
                    ), true) ?>';

                    $.blockUI(blockUILoading);
                    $.ajax({
                        'method': 'get',
                        'url': url
                            .replace('{{current_path}}', $(this).data('path'))
                            .replace('{{config_path}}', oDir.parent().find('span.toggle.tick').data('path')),

                        'success': function (data) {
                            $.unblockUI();
                            if (data == 'error') {

                            } else if (data == 'leaf') {
                                oDir.removeClass('plus').addClass('leaf');
                            } else {
                                oDir.removeClass('plus').addClass('minus');
                                oDir.parent().find('span[class="catname"]').first().append(data);
                            }
                        }
                    });
                });
            });
        </script>
        <?php
            $sOut = ob_get_clean();
        }
        return $sOut;
    }

    public function getAndGenerateErpDirectoryPath($path) {
        try {
            MLFilesystem::gi()->write($path);
        } catch (\Exception $e) {
            return $_SERVER['DOCUMENT_ROOT'];
        }

        return $path;
    }

}
