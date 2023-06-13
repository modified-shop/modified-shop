<?php

/**
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
 * $Id: GenerateProductsDetailInput.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
class GenerateProductsDetailInput {
    /* TODO
     *   - LIMIT Parameter fuer Textfields und inputs
     *   - optiongroup bei select
     */

    private $structure = array();
    private $data = array();
    private $prefilledReadonlyFields = array();
    private $field = 'add';
    private $out = '';
    private $curBlock = array();
    private $curField = array();
    private $curRow = array();
    private $curInput = array();
    private $curInputReadonly = false;
    private $oddEven = false;
    private $curProcItem = array();
    private $failedItems = array();
    private $tinyMCEInited = false;

    public function __construct($structure, $data = array(), $prefilledReadonlyFields = array()) {
        $this->structure = $structure;
        $this->data = $data;
        $this->prefilledReadonlyFields = $prefilledReadonlyFields;
    }

    private function debugBacktrace($pos = 0) {
        if (version_compare(PHP_VERSION, '5.2.5', '>=')) {
            $errorBacktrace = @debug_backtrace(true);
        } else {
            $errorBacktrace = @debug_backtrace();
        }
        if (empty($errorBacktrace))
            return array();
        $errorBacktrace = array_slice($errorBacktrace, $pos);
        $errorBacktrace = $this->stripObjectsAndResources($errorBacktrace);
        return $errorBacktrace;
    }

    private function stripObjectsAndResources(&$a) {
        if (empty($a))
            return $a;
        foreach ($a as $key => &$value) {
            if (is_object($value)) {
                $value = 'OBJECT (' . get_class($value) . ')';
            }
            if (is_resource($value)) {
                $value = 'RESOURCE (' . get_resource_type($value) . ')';
            }
            if (is_array($value)) {
                $value = $this->stripObjectsAndResources($value);
            }
        }
        return $a;
    }

    private function getInputValueHelper($from, $keys) {
        switch ($from) {
            case 'data': {
                    $val = $this->data;
                    break;
                }
            case 'post': {
                    # echo print_m($this->field);
                    $keys = array_merge(
                        $this->field, $keys
                    );
                    $val = $_POST;
                    break;
                }
            default: {
                    return '';
                }
        }
        foreach ($keys as $key) {
            if (!is_array($val) || !array_key_exists($key, $val)) {
                return '';
            }
            $val = $val[$key];
        }
        if (is_array($val)) {
            array_walk_recursive($val, 'fixHTMLUTF8Entities');
        } else {
            $val = fixHTMLUTF8Entities($val);
        }
        return $val;
    }

    private function getInputValue($curRowC, $isMulticolumn, $subKey = '') {
        $default = null;
        if (array_key_exists('default', $this->curInput)) {
            $default = $this->curInput['default'];
        }
        $keys = array($this->curInput['key']);
        if ($isMulticolumn) {
            $keys[] = $curRowC;
        }
        if ($subKey != '') {
            $keys[] = $subKey;
        }

        $tmpdata = $this->getInputValueHelper('data', $keys);
        $tmppost = $this->getInputValueHelper('post', $keys);

        $this->curInputReadonly = false;
        // code to fill MPN , EAN and Brand in ebay product attributes

        /* ggf noch ausgrauen u. tooltip? */
        if (in_array($this->curInput['type'], array('text', 'select&text')) && !empty($this->prefilledReadonlyFields)) {
            global $_MagnaSession;
            $sValue = '';
            if (in_array($this->curField['label'], array('Marke', 'Hersteller', 'Brand')) && $this->prefilledReadonlyFields['Brand'] !== null) {
                $sValue = $this->prefilledReadonlyFields['Brand'];
                $isReadOnly = true;
            } elseif (in_array($this->curField['label'], array('Herstellernummer', 'MPN')) && $this->prefilledReadonlyFields['MPN'] !== null) {
                $sValue = $this->prefilledReadonlyFields['MPN'];
                $aMatching = getDBConfigValue($_MagnaSession['currentPlatform'] . '.listingdetails.mpn.dbmatching.table', $_MagnaSession['mpID'], false);
                $isReadOnly = is_array($aMatching) && !empty($aMatching['column']) && !empty($aMatching['table']);
            } elseif (in_array($this->curField['label'], array('EAN', 'ISBN', 'UPC')) && $this->prefilledReadonlyFields['EAN'] !== null) {
                $aMatching = getDBConfigValue($_MagnaSession['currentPlatform'] . '.listingdetails.ean.dbmatching.table', $_MagnaSession['mpID'], false);
                $isReadOnly = is_array($aMatching) && !empty($aMatching['column']) && !empty($aMatching['table']);
                if ($isReadOnly || !array_key_exists($this->curInput['key'], $this->data)) {// default value from prefilled
                    $sValue = $this->prefilledReadonlyFields['EAN'];
                } else {
                    $sValue = $this->data[$this->curInput['key']];//prepared value
                }
            }
            if ($sValue !== null && $sValue !== '') {
                if ($isReadOnly) {
                    $this->curInputReadonly = true;
                    if ($this->curInput['type'] == 'select&text' && 'select' == $subKey) {
                        return "-6";
                    } else {
                        return $sValue;
                    }
                } else {
                    $default = $sValue;
                }
            }
        }

        if (!empty($tmppost) || ('0' == (string) ($tmppost))) {
            return $tmppost;
        }
        if (!empty($tmpdata) || ('0' == (string) ($tmpdata))) {
            return $tmpdata;
        }
        if (('text' == $subKey) && ('select&text' == $this->curInput['type'])) {
            return '';
        }
        return $default;
    }

    private function renderInput($curRowC, $isMulticolumn) {
        if (!array_key_exists('type', $this->curInput)) {
            return '\'type\' is missing';
        }
        $class = array();
        if (array_key_exists('class', $this->curInput)) {
            $class = $this->curInput['class'];
        } else if (($this->curInput['type'] == 'text') || ($this->curInput['type'] == 'textarea') || ($this->curInput['type'] == 'select')
        ) {
            $class[] = 'fullwidth';
        }

        $inputPre = '';
        foreach ($this->field as $f) {
            if (empty($inputPre)) {
                $inputPre = $f;
            } else {
                $inputPre .= '[' . $f . ']';
            }
        }
        $inputName = $inputPre . '[' . $this->curInput['key'] . ']' . ($isMulticolumn ? '[' . $curRowC . ']' : '');
        $inputID = rtrim(str_replace(array('][', '[', ']'), '_', $inputName), '_');

        if (array_key_exists($inputName, $this->failedItems)) {
            $class[] = 'wrong';
        }

        if (array_key_exists('dependsOn', $this->curInput) && $this->curInput['dependsOn']['blockIfNotSet']) {
            $dependantInputName = $inputPre . '[' . $this->curInput['dependsOn']['key'] . ']' . ($isMulticolumn ? '[' . $curRowC . ']' : '');
            $dependantInputID = rtrim(str_replace(array('][', '[', ']'), '_', $dependantInputName), '_');
            $this->out .= '
				<script type="text/javascript">/*<![CDATA[*/
					function handleBlock_' . $dependantInputID . ' () {
						var ' . $dependantInputID . '_Value = jQuery.trim($(\'#' . $dependantInputID . '\').val());
						if (' . $dependantInputID . '_Value == \'\') {
							$(\'#' . $inputID . '\').addClass(\'blocked\').attr(\'disabled\', \'disabled\');
						} else {
							$(\'#' . $inputID . '\').removeClass(\'blocked\').removeAttr(\'disabled\');
						}
					}
					$(document).ready(function() {
						handleBlock_' . $dependantInputID . '();
						$(\'#' . $dependantInputID . '\').blur(function() {
							myConsole.log(\'blur: ' . $dependantInputID . '\');
							handleBlock_' . $dependantInputID . '();
						}).keyup(jQuery.debounce(150, true, function() {
							myConsole.log(\'keyup + debounce: ' . $dependantInputID . '\');
							handleBlock_' . $dependantInputID . '();
						}));
					});
				/*]]>*/</script>
			';
        }
        switch ($this->curInput['type']) {
            case 'text': {
                    if (empty($class)) {
                        $class = '';
                    } else {
                        $class = 'class="' . implode(' ', $class) . '"';
                    }
                    #$this->out .= '<input type="text" id="'.$inputID.'" name="'.$inputName.'" value="'.$this->getInputValue($curRowC, $isMulticolumn).'" '.$class.'/>'."\n";
                    $this->out .= '<input type="text" id="' . $inputID . '" name="' . $inputName . '" value="' . $this->getInputValue($curRowC, $isMulticolumn) . '" ' . ($this->curInputReadonly ? 'readonly="readonly" title="' . ML_EBAY_PRODUCTDETAIL_PREFILLED . '" style="color:#555555;background-color:#DCDAD5" ' : '') . $class . '/>' . "\n";
                    break;
                }
            case 'select': {
                    if (empty($class)) {
                        $class = '';
                    } else {
                        $class = 'class="' . implode(' ', $class) . '"';
                    }
                    $ret = '<select id="' . $inputID . '" name="' . $inputName . '" ' . $class . '>' . "\n";
                    $default = fixHTMLUTF8Entities($this->getInputValue($curRowC, $isMulticolumn));
                    foreach ($this->curInput['values'] as $val => $text) {
                        if ($default == fixHTMLUTF8Entities($val)) {
                            $sel = ' selected="selected"';
                        } else {
                            $sel = '';
                        }
                        $ret .= '	<option value="' . fixHTMLUTF8Entities($val) . '"' . $sel . '>' . fixHTMLUTF8Entities($text) . '</option>' . "\n";
                    }
                    $this->out .= $ret . '</select>' . "\n";
                    break;
                }
            case 'multipleselect': {
                    if (empty($class)) {
                        $class = '';
                    } else {
                        $class = 'class="' . implode(' ', $class) . '"';
                    }
                    $ret = '<select id="' . $inputID . '" name="' . $inputName . '[]" ' . $class . ' size="5" ' .
                        'multiple="multiple"' . (isset($this->curInput['limit']) ? ' data-limit="' . $this->curInput['limit'] . '"' : '') . '>' . "\n";
                    $default = $this->getInputValue($curRowC, $isMulticolumn);
                    arrayEntitiesFixHTMLUTF8($default);
                    foreach ($this->curInput['values'] as $val => $text) {
                        if (is_array($default) && in_array(fixHTMLUTF8Entities($val), $default)) {
                            $sel = ' selected="selected"';
                        } else {
                            $sel = '';
                        }
                        $ret .= '	<option value="' . fixHTMLUTF8Entities($val) . '"' . $sel . '>' . fixHTMLUTF8Entities($text) . '</option>' . "\n";
                    }
                    $this->out .= $ret . '</select>' . "\n";
                    break;
                }
            case 'select&text': {
                    $class[] = 'autoWidth';
                    if (($pos = array_search('fullWidth', $class)) !== false) {
                        unset($class[$pos]);
                    }
                    if (empty($class)) {
                        $class = '';
                    } else {
                        $class = 'class="' . implode(' ', $class) . '"';
                    }
                    $default = fixHTMLUTF8Entities($this->getInputValue($curRowC, $isMulticolumn, 'select'));
                    if ($this->curInputReadonly) {
                        $ret = '<input  type="hidden" name="' . $inputName . '[select]" value="-6" ' . "/>\n";
                    } else {
                        $ret = '<select id="' . $inputID . '_select" name="' . $inputName . '[select]" ' . $class . '>' . "\n";
                        foreach ($this->curInput['values'] as $val => $text) {
                            if ($default == fixHTMLUTF8Entities($val)) {
                                $sel = ' selected="selected"';
                            } else {
                                $sel = '';
                            }
                            $ret .= '	<option value="' . fixHTMLUTF8Entities($val) . '"' . $sel . '>' . fixHTMLUTF8Entities($text) . '</option>' . "\n";
                        }
                        $ret .= '</select>' . "\n";
                        $js = '';
                        $style = '';
                        if (array_key_exists('textOnKey', $this->curInput)) {
                            $js = '
						<script type="text/javascript">/*<![CDATA[*/
							$(document).ready(function() {
								$(\'#' . $inputID . '_select\').change(function() {
									textEl = $(\'#' . $inputID . '_text\');
									if ($(this).val() == \'' . $this->curInput['textOnKey'] . '\') {
										textEl.css({\'display\':\'inline\'});
									} else {
										textEl.val(\'\');
										textEl.css({\'display\':\'none\'});
									}
								}).trigger("change");
							});
						/*]]>*/</script>' . "\n";
                            if ($default != $this->curInput['textOnKey']) {
                                $style = 'display: none;';
                            }
                        }
                    }
                    if ($this->curInputReadonly) {
                        $style = 'color:#555555; background-color:#DCDAD5';
                        $addStuff = 'readonly="readonly" title="' . ML_EBAY_PRODUCTDETAIL_PREFILLED . '" ';
                    } else {
                        $addStuff = '';
                    }
                    $ret .= '<input style="' . $style . '" type="text" id="' . $inputID . '_text" name="' . $inputName . '[text]" value="' .
                        $this->getInputValue($curRowC, $isMulticolumn, 'text') .
                        '" ' . $addStuff . $class . '/>' . $js . "\n";
                    $this->out .= $ret;
                    break;
                }
            case 'date': {
                    if (empty($class)) {
                        $class = '';
                    } else {
                        $class = 'class="' . implode(' ', $class) . '"';
                    }

                    $default = $this->getInputValue($curRowC, $isMulticolumn);
                    if (!empty($default)) {
                        $default = strtotime($default);
                        if ($default > 0) {
                            $default = date('Y/m/d', $default);
                        } else {
                            $default = '';
                        }
                    }
                    if (empty($default) && array_key_exists('verify', $this->curInput) && !empty($this->curInput['verify'])) {
                        $default = date('Y/m/d');
                    }
                    $langCode = 'de';
                    $deleteButton = '';
                    if (!array_key_exists('verify', $this->curInput) || empty($this->curInput['verify'])) {
                        $deleteButton = '
						<span id="' . $inputID . '_clear" style="font-weight: bold; color: #900; cursor: pointer;">X</span>
						<script type="text/javascript">/*<![CDATA[*/
						$(document).ready(function() {
							$("#' . $inputID . '_clear").click(function() {
								$("#' . $inputID . '_visual").val(\'\');
							});
						});
						/*]]>*/</script>';
                    }
                    $this->out .= '
					<input type="text" id="' . $inputID . '_visual" value="" readonly="readonly" ' . $class . '/>
					<input type="hidden" id="' . $inputID . '" name="' . $inputName . '" value="' . $default . '"/>
					' . $deleteButton . '
					<script type="text/javascript">/*<![CDATA[*/
						$(document).ready(function() {
							jQuery.datepicker.setDefaults(jQuery.datepicker.regional[\'\']);
							$("#' . $inputID . '_visual").datepicker(
								jQuery.datepicker.regional[\'' . $langCode . '\']
							).datepicker(
								"option", "altField", "#' . $inputID . '"
							).datepicker(
								"option", "altFormat", "yy-mm-dd"
							)' . (!empty($default) ? '.datepicker(
								"option", "defaultDate", new Date(\'' . $default . '\')
							)' : '') . ';
							var dateFormat' . $this->curInput['key'] . ' = $("#' . $inputID . '_visual").datepicker("option", "dateFormat");
							' . (!empty($default) ? '
							$("#' . $inputID . '_visual").val(
								jQuery.datepicker.formatDate(dateFormat' . $this->curInput['key'] . ', new Date(\'' . $default . '\'))
							);
							$("#' . $inputID . '").val(
								jQuery.datepicker.formatDate("yy-mm-dd", new Date(\'' . $default . '\'))
							);' : '') . '
						});
					/*]]>*/</script>' . "\n";
                    break;
                }
            case 'textarea': {
                    if (('tinyMCE' == getDBConfigValue('general.editor', 0, 'tinyMCE')) && (array_key_exists('wysiwyg', $this->curInput) && $this->curInput['wysiwyg'])
                    ) {
                        $class[] = $inputID;
                        $this->out .= '
						' . $this->initTinyMCE() . '
						<script type="text/javascript">/*<![CDATA[*/
							$(document).ready(function() {
								tinyMCE.init(jQuery.extend(tinyMCEMagnaDefaultConfig, {
									editor_selector : "' . $inputID . '"' . (array_key_exists('validTags', $this->curInput) ? ',
									valid_elements : "' . $this->tagsToTinyMCEValidElements($this->curInput['validTags']) . '"' : ''
                            ) . '
								}));
							});
						/*]]>*/</script>
					';
                    }
                    if (empty($class)) {
                        $class = '';
                    } else {
                        $class = 'class="' . implode(' ', $class) . '"';
                    }
                    $this->out .= '
					<textarea type="text" id="' . $inputID . '" name="' . $inputName . '" rows="10" ' . $class . '>' .
                        $this->getInputValue($curRowC, $isMulticolumn) .
                        '</textarea>' . "\n";
                    break;
                }
            default: {
                    $this->out .= '\'type\':\'' . $this->curInput['type'] . '\' not supported' . "\n";
                }
        }
    }

    private function renderInputs() {
        array_push($this->curProcItem, 'inputs');
        foreach ($this->curField['inputs'] as $key => $curRow) {
            $this->curRow = $curRow;
            array_push($this->curProcItem, $key);

            $hasIndex = 1;
            if (array_key_exists('repeat', $this->curRow) && is_int($this->curRow['repeat'])) {
                $hasIndex = $this->curRow['repeat'];
            }
            array_push($this->curProcItem, 'cols');
            $this->out .= '<table class="attrTable noborder"><tbody>';
            for ($i = 0; $i < $hasIndex; ++$i) {
                $this->out .= '<tr>';
                $first = true;
                $rows = count($this->curRow['cols']);
                $j = 1;
                foreach ($this->curRow['cols'] as $key => $curInput) {
                    $this->curInput = $curInput;
                    if (!is_array($this->curInput)) {
                        # echo var_dump_pre($this->curInput);
                    }
                    array_push($this->curProcItem, $key);

                    $classes = array();
                    $colspace = 2;
                    $last = $rows == $j++;
                    if (array_key_exists('label', $this->curInput)) {
                        $colspace = 1;
                        if ($first)
                            $classes[] = 'first';
                        $first = false;
                        $classes[] = 'key';
                        $this->out .= '<td class="' . implode(' ', $classes) . '">' . fixHTMLUTF8Entities($this->curInput['label']) . ':</td>';
                        $classes = array();
                    }
                    if ($first)
                        $classes[] = 'first';
                    if ($last)
                        $classes[] = 'last';

                    $this->out .= '<td class="' . implode(' ', $classes) . '" colspace="' . $colspace . '">' . "\n";
                    $this->renderInput($i, (count($this->curRow['cols']) > 1));
                    $this->out .= '</td>';

                    array_pop($this->curProcItem);
                }
                $this->out .= '</tr>';
                #$this->out .= ($hasIndex > 2) ? '<br />'."\n" : '';
            }
            $this->out .= '</tbody></table>';

            $this->curInput = null;
            array_pop($this->curProcItem);

            array_pop($this->curProcItem);
        }
        $this->curRow = null;
        array_pop($this->curProcItem);
    }

    private function renderField() {
        $required = false;
        if (array_key_exists('required', $this->curField) && ($this->curField['required'] === true)) {
            $required = true;
        }
        $text = '';
        if (array_key_exists('desc', $this->curField)) {
            $text = $this->curField['desc'];
        }

        $this->out .= '
			<tr class="' . (($this->oddEven = !$this->oddEven) ? 'odd' : 'even') . '">
				<th>' . fixHTMLUTF8Entities($this->curField['label']) . '' . ($required ? ' <span>&bull;</span>' : '') . '</th>
				<td class="input">' . "\n";
        $this->renderInputs();
        $this->out .= '
				</td>
				<td class="info">' . $text . '</td>
			</tr>';
    }

    private function renderBlock(&$hidden) {
        if (array_key_exists('head', $this->curBlock) && !empty($this->curBlock['head'])) {
            $this->out .= '
			<tr class="headline">
				<td colspan="3"><h4>' . $this->curBlock['head'] . '</h4></td>
			</tr>';
            if (array_key_exists('desc', $this->curBlock) && !empty($this->curBlock['desc'])) {
                $this->out .= '
				<tr class="desc">
					<td colspan="3">' . $this->curBlock['desc'] . '</td>
				</tr>';
            }
        }
        if (is_array($this->curBlock['key'])) {
            $this->field = $this->curBlock['key'];
        } else {
            $this->field = array($this->curBlock['key']);
        }

        array_push($this->curProcItem, 'fields');
        foreach ($this->curBlock['fields'] as $key => $curField) {
            $this->curField = $curField;
            array_push($this->curProcItem, $key);
            $this->renderField();
            array_pop($this->curProcItem);
        }
        $this->curField = null;
        array_pop($this->curProcItem);

        $this->out .= '
			<tr class="spacer">
				<td colspan="3">&nbsp;' . $hidden . '</td>
			</tr>';
        $hidden = '';
    }

    protected function renderAdditionalJs() {
        ob_start();
        ?>
        <script type="text/javascript">/*<![CDATA[*/
            jQuery(document).ready(function () {
                jQuery('select[data-limit]')
                        .off('click.limit')
                        .on('click.limit', 'option', function (event) {
                            if (jQuery(this).parent('select').data('limit') <= jQuery(this).siblings(':selected').length) {
                                jQuery(this).removeAttr("limit");
                            }
                        })
                        .off('change.limit')
                        .on('change.limit', function () {
                            maxSize = jQuery(this).data('limit');
                            if (jQuery(this).find('option:selected').length > maxSize) {
                                jQuery.each(jQuery(this).find('option:selected').slice(maxSize), function (idx, el) {
                                    jQuery(el).removeAttr('selected');
                                });
                            }
                        });
            });
            /*]]>*/</script>
        <?php

        return ob_get_clean();
    }

    public function render() {
        #echo print_m($this->failedItems);
        if (empty($this->structure)) {
            return $this->out;
        }
        # echo print_m($this->failedItems);
        $hidden = '<input type="hidden" name="__' . get_class($this) . '__" value="' . time() . '"/>';
        foreach ($this->structure as $key => $block) {
            $hidden .= '<input type="hidden" name="__loadedBlocks[]" value="' . $key . '" />';
            $this->curBlock = $block;
            array_push($this->curProcItem, $key);
            $this->renderBlock($hidden);
            array_pop($this->curProcItem);
        }
        $this->curBlock = null;
        return $this->out . $this->renderAdditionalJs();
    }

    private static function keysToHTMLName($keys) {
        $fkey = array_shift($keys);
        if (!empty($keys)) {
            $fkey .= '[' . implode('][', $keys) . ']';
        }
        return $fkey;
    }

    private function verifyDependency($_col, $isMulticolumn, $i) {
        if (!array_key_exists('dependsOn', $_col)) {
            return;
        }
        $ignore = ($_col['dependsOn']['ifNotSet'] == 'ignore');

        $okeys = $dkeys = $this->field;

        $okeys[] = $_col['key'];
        $dkeys[] = $_col['dependsOn']['key'];
        if ($isMulticolumn) {
            $okeys[] = $i;
            $dkeys[] = $i;
        }

        $ovalue = $this->getInputValueHelper('post', $okeys);
        $dvalue = $this->getInputValueHelper('post', $dkeys);

        $htmlOKey = self::keysToHTMLName($okeys);
        $htmlDKey = self::keysToHTMLName($dkeys);

        if ((!empty($ovalue) && !empty($dvalue)) || (empty($ovalue) && empty($dvalue))
        ) {
            return;
        }

        #echo print_m($_col['dependsOn'], $ignore ? 'ignore' : 'error');
        #echo var_dump_pre($value,    'Value:    '.$key);
        #echo var_dump_pre($depValue, 'DepValue: '.$depKey);
        #echo '<br>';

        if ($ignore) {
            $tmpPost = &$_POST;
            $lastKey = array_pop($okeys);
            foreach ($okeys as $key) {
                $tmpPost = &$tmpPost[$key];
            }
            unset($tmpPost[$lastKey]);
            #echo '<br>';
            return;
        }
        #echo '<br>';
        $this->failedItems[$htmlOKey] = 'depend';
        $this->failedItems[$htmlDKey] = 'depend';
    }

    private function verifyItem($_col, $isMulticolumn, $i) {
        if (!array_key_exists('verify', $_col) || empty($_col['verify'])) {
            return;
        }
        $keys = $this->field;
        $keys[] = $_col['key'];
        if ($isMulticolumn) {
            $keys[] = $i;
        }

        $value = $this->getInputValueHelper('post', $keys);
        $key = self::keysToHTMLName($keys);

        if (is_array($_col['verify'])) {
            $verify = $_col['verify'];
        } else {
            $verify = array($_col['verify']);
        }

        //echo print_m($value, $key);
        foreach ($verify as $v) {
            switch ($v) {
                case 'notEmpty': {
                        if (empty($value)) {
                            $this->failedItems[$key] = 'verify';
                        }
                        break;
                    }
                case 'isINT': {
                        if (!empty($value) && !ctype_digit($value)) {
                            $this->failedItems[$key] = 'verify';
                        }
                        break;
                    }
            }
        }
    }

    public function verifyItems() {
        if (!array_key_exists('__' . get_class($this) . '__', $_POST)) {
            return true;
        }

        $curProcItem = array();
        foreach ($this->structure as $keyBlock => $_block) {
            if (is_array($_block['key'])) {
                $this->field = $_block['key'];
            } else {
                $this->field = array($_block['key']);
            }
            array_push($curProcItem, $keyBlock);
            array_push($curProcItem, 'fields');
            foreach ($_block['fields'] as $keyField => $_field) {
                array_push($curProcItem, $keyField);
                array_push($curProcItem, 'inputs');
                foreach ($_field['inputs'] as $keyRow => $_row) {
                    array_push($curProcItem, $keyRow);
                    array_push($curProcItem, 'cols');
                    $hasIndex = 1;
                    if (array_key_exists('repeat', $_row) && is_int($_row['repeat'])) {
                        $hasIndex = $_row['repeat'];
                    }
                    $isMulticolumn = count($_row['cols']) > 1;
                    foreach ($_row['cols'] as $keyCol => $_col) {
                        array_push($curProcItem, $keyCol);
                        for ($i = 0; $i < $hasIndex; ++$i) {
                            $this->verifyItem($_col, $isMulticolumn, $i);
                            $this->verifyDependency($_col, $isMulticolumn, $i);
                        }
                        array_pop($curProcItem); # $keyCol
                    }
                    array_pop($curProcItem); # cols
                    array_pop($curProcItem); # $keyRow
                }
                array_pop($curProcItem); # inputs
                array_pop($curProcItem); # $keyField
            }
            array_pop($curProcItem); # fields
            array_pop($curProcItem); # $keyBlock
        }
        return empty($this->failedItems);
    }

    private function initTinyMCE() {
        if (!$this->tinyMCEInited) {
            $this->tinyMCEInited = true;
            return '
				<script type="text/javascript" src="' . DIR_MAGNALISTER_WS . 'js/tinymce/tinymce.min.js"></script>
				<script type="text/javascript">/*<![CDATA[*/
' . getTinyMCEDefaultConfigObject() . '
				/*]]>*/</script>';
        }
        return '';
    }

    private function tagsToTinyMCEValidElements($tags) {
        if (empty($tags)) {
            return '' .
                'a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmo' .
                'usemove|onmouseout|onmouseover|onmouseup|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],abbr[class|dir<ltr?rtl|id|lang|onclic' .
                'k|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],acronym[class|dir<ltr?rtl|id|lang|oncli' .
                'ck|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],address[class|align|dir<ltr?rtl|id|lan' .
                'g|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],applet[align<bottom?left?middle' .
                '?right?top|alt|archive|class|code|codebase|height|hspace|id|name|object|style|title|vspace|width],area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|l' .
                'ang|nohref<nohref|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|shape<circle?' .
                'default?poly?rect|style|tabindex|title|target],base[href|target],basefont[color|face|id|size],bdo[class|dir<ltr?rtl|id|lang|style|title],big[class|dir<' .
                'ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],blockquote[cite|c' .
                'lass|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],body[ali' .
                'nk|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseo' .
                'ver|onmouseup|onunload|style|title|text|vlink],br[class|clear<all?left?none?right|id|style|title],button[accesskey|class|dir<ltr?rtl|disabled<disabled|' .
                'id|lang|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|tit' .
                'le|type|value],caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|on' .
                'mouseout|onmouseover|onmouseup|style|title],center[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|on' .
                'mouseout|onmouseover|onmouseup|style|title],cite[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmo' .
                'useout|onmouseover|onmouseup|style|title],code[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmous' .
                'eout|onmouseover|onmouseup|style|title],col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|on' .
                'keypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title|valign<baseline?bottom?middle?top|width],colgroup[align<cent' .
                'er?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|on' .
                'mouseover|onmouseup|span|style|title|valign<baseline?bottom?middle?top|width],dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onke' .
                'yup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onke' .
                'ypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],dfn[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypr' .
                'ess|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],dir[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onke' .
                'ydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],div[align<center?justify?left?right|class|dir<ltr?rtl|id' .
                '|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],dl[class|compact<compact|di' .
                'r<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],dt[class|dir<lt' .
                'r?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],em/i[class|dir<ltr?' .
                'rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],fieldset[class|dir<lt' .
                'r?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],font[class|color|di' .
                'r<ltr?rtl|face|id|lang|size|style|title],form[accept|action|class|dir<ltr?rtl|enctype|id|lang|method<get?post|name|onclick|ondblclick|on' .
                'keydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit|style|title|target],frame[class|frameborder|id|lon' .
                'gdesc|marginheight|marginwidth|name|noresize<noresize|scrolling<auto?no?yes|src|style|title],frameset[class|cols|id|onload|onunload|rows|style|title],h' .
                '1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseo' .
                'ver|onmouseup|style|title],h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onm' .
                'ousemove|onmouseout|onmouseover|onmouseup|style|title],h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeyp' .
                'ress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick' .
                '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],h5[align<center?justify?left?right|class' .
                '|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],h6[align<cen' .
                'ter?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouse' .
                'up|style|title],head[dir<ltr?rtl|lang|profile],hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick|ondblclick|onkeydown|onkeyp' .
                'ress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style|title|width],html[dir<ltr?rtl|lang|version],iframe[align<bottom?left?m' .
                'iddle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width],img[align<bottom?left?m' .
                'iddle?right?top|alt|border|class|dir<ltr?rtl|height|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedow' .
                'n|onmousemove|onmouseout|onmouseover|onmouseup|src|style|title|usemap|vspace|width],input[accept|accesskey|align<bottom?left?middle?right?top|alt|check' .
                'ed<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmous' .
                'edown|onmousemove|onmouseout|onmouseover|onmouseup|onselect|readonly<readonly|size|src|style|tabindex|title|type<button?checkbox?file?hidden?image?pass' .
                'word?radio?reset?submit?text|usemap|value],ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmo' .
                'usemove|onmouseout|onmouseover|onmouseup|style|title],isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],kbd[class|dir<ltr?rtl|id|lang|onclick|ondbl' .
                'click|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],label[accesskey|class|dir<ltr?rtl|for|id|lang|' .
                'onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],legend[align<botto' .
                'm?left?right?top|accesskey|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onm' .
                'ouseup|style|title],li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouse' .
                'up|style|title|type|value],link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmou' .
                'semove|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type],map[class|dir<ltr?rtl|id|lang|name|onclick|ondblclick|onkeydown|onkeypress|onk' .
                'eyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],menu[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|' .
                'onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme],noframes' .
                '[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],noscri' .
                'pt[class|dir<ltr?rtl|id|lang|style|title],object[align<bottom?left?middle?right?top|archive|border|class|classid|codebase|codetype|data|declare|dir<ltr' .
                '?rtl|height|hspace|id|lang|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|standby|style|' .
                'tabindex|title|type|usemap|vspace|width],ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmou' .
                'semove|onmouseout|onmouseover|onmouseup|start|style|title|type],optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick|onkeydown' .
                '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|oncl' .
                'ick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|selected<selected|style|title|value],p[align<cente' .
                'r?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup' .
                '|style|title],param[id|name|type|value|valuetype<DATA?OBJECT?REF],pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydow' .
                'n|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|width],q[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|on' .
                'keydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],s[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydo' .
                'wn|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],samp[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown' .
                '|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],script[charset|defer|language|src|type],select[class|dir<ltr?' .
                'rtl|disabled<disabled|id|lang|multiple<multiple|name|onblur|onchange|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|on' .
                'mouseout|onmouseover|onmouseup|size|style|tabindex|title],small[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|o' .
                'nmousemove|onmouseout|onmouseover|onmouseup|style|title],span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|on' .
                'keypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],strike[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|on' .
                'keypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],strong/b[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|' .
                'onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],style[dir<ltr?rtl|lang|media|title|type],sub[class|dir<ltr?rtl' .
                '|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],sup[class|dir<ltr?rtl|id' .
                '|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],table[align<center?left?rig' .
                'ht|bgcolor|border|cellpadding|cellspacing|class|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemov' .
                'e|onmouseout|onmouseover|onmouseup|rules|style|summary|title|width],tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id|lang|o' .
                'nclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline?bottom?middle?top],' .
                'td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick|ondblclic' .
                'k|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<base' .
                'line?bottom?middle?top|width],textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur|onclick|ondblclick|onfocus|onkeydown|onke' .
                'ypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect|readonly<readonly|rows|style|tabindex|title],tfoot[align<center?char?j' .
                'ustify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover' .
                '|onmouseup|style|title|valign<baseline?bottom?middle?top],th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?' .
                'rtl|headers|height|id|lang|nowrap<nowrap|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rowsp' .
                'an|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],thead[align<center?char?justify?left?right|char|charoff|class|d' .
                'ir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline' .
                '?bottom?middle?top],title[dir<ltr?rtl|lang],tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class|rowspan|dir<ltr?rtl|id|lang|onclick' .
                '|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline?bottom?middle?top],tt[cla' .
                'ss|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],u[class|di' .
                'r<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],ul[class|compac' .
                't<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type' .
                '],var[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title]';
        }
        $out = '';
        foreach ($tags as $tag => $attrs) {
            $out .= $tag;
            if (!empty($attrs)) {
                $out .= '[';
                foreach ($attrs as $attr => $val) {
                    $val = implode('?', $val);
                    if (!empty($val)) {
                        $val = '<' . $val;
                    }
                    $out .= $attr . $val . '|';
                }
                $out = substr($out, 0, -1) . '],';
            } else {
                $out .= ',';
            }
        }
        return substr($out, 0, -1);
    }

}
