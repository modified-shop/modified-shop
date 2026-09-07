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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

if (!defined('_VALID_XTC') && !defined('DIR_MAGNALISTER_MODULES')) {
    die('Illegal access');
}

/**
 * Temu parent-child attribute visibility (server-side) — v2 port.
 *
 * Ported from v3 ML_Temu_Helper_Model_ParentChildVisibility. Two v2 differences:
 *   1. v2 stored/resolved attribute maps are keyed by the PLAIN attribute name
 *      (e.g. "prop_121"), NOT the hex encoding used in v3 — so no hex2bin/bin2hex.
 *   2. PHP 5.2 compatible: no closures.
 *
 * Temu category attributes can be hierarchical: a parent declares
 * `childAttributes` (a map from the parent's selected value id (vid) to a list
 * of {childRefPid, groupId}), and a child declares `parentRefPid`. A child only
 * "applies" when the parent's currently-selected value matches the vid that
 * triggers it. This mirrors the React form logic
 * (magnalister/php/modules/amazon/react/src/utils/parentChildVisibility.ts) so the
 * VerifyAddItems/AddItems upload only contains child attribute values the
 * parent's selected value(s) actually trigger — a stale child value (left over
 * from a previously-selected parent value) must not be submitted.
 *
 * All methods are pure (no DB/API access) for stand-alone testability.
 */
class TemuParentChildVisibility {

    /**
     * Extract ALL selected marketplace vids from a single stored attribute value.
     * Mirrors the React getSelectedVids(): a matching parent may be mapped to
     * several marketplace values at once, each triggering its own children.
     *
     * @param mixed $storedValue A stored attribute entry (assoc array Code/Values).
     * @return array De-duplicated vids (strings) in first-seen order.
     */
    public static function getSelectedVids($storedValue) {
        if (!is_array($storedValue)) {
            return array();
        }
        $sCode = isset($storedValue['Code']) ? $storedValue['Code'] : null;
        $mValues = isset($storedValue['Values']) ? $storedValue['Values'] : null;
        if (empty($sCode) || empty($mValues)) {
            return array();
        }

        $aCollected = array();

        if ($sCode === 'attribute_value') {
            // User picked one or more predefined marketplace values.
            if (is_string($mValues)) {
                self::pushVid($aCollected, $mValues);
            } elseif (is_array($mValues) && isset($mValues['AttributeValue'])) {
                $mAv = $mValues['AttributeValue'];
                if (is_array($mAv)) {
                    foreach ($mAv as $v) {
                        self::pushVid($aCollected, $v);
                    }
                } else {
                    self::pushVid($aCollected, $mAv);
                }
            } elseif (is_array($mValues)) {
                // Multiselect: Values is a plain array of vids (React) or an
                // integer-keyed object-map of vids on initial PHP load. Collect
                // every vid; each one can trigger its own children.
                foreach ($mValues as $v) {
                    self::pushVid($aCollected, $v);
                }
            }
        } elseif ($sCode !== 'freetext') {
            // Shop-attribute matching: Values is a list (React) or an integer-keyed
            // object-map (PHP initial load) of rows, each carrying Marketplace.Key.
            if (is_array($mValues)) {
                foreach ($mValues as $aRow) {
                    if (is_array($aRow) && isset($aRow['Marketplace']['Key'])) {
                        $mKey = $aRow['Marketplace']['Key'];
                        if (is_array($mKey)) {
                            foreach ($mKey as $v) {
                                self::pushVid($aCollected, $v);
                            }
                        } else {
                            self::pushVid($aCollected, $mKey);
                        }
                    }
                }
            }
        }

        return array_values(array_unique($aCollected));
    }

    /**
     * Append a vid to the collector when it is a non-empty scalar.
     * PHP 5.2 replacement for the v3 closure.
     */
    private static function pushVid(&$aCollected, $v) {
        if (is_string($v) && $v !== '') {
            $aCollected[] = $v;
        } elseif (is_int($v)) {
            $aCollected[] = (string)$v;
        }
    }

    /**
     * Compute the set of VALID attribute names given the RESOLVED value of each
     * attribute for one product/variant. Mirrors v3 getValidResolvedNames().
     *
     * A child is valid only when its parent resolved to a value AND that value
     * triggers the child. Roots (no parentRefPid) are always valid. Fail-safe:
     * if a parent resolved to a value that is not one of its valid vids (a
     * mis-resolution), keep ALL of that parent's children rather than dropping
     * configured attributes.
     *
     * @param array $aCategoryAttributes GetCategoryDetails DATA.attributes (plain-name keyed).
     * @param array $aResolvedVids       plain name => resolved vid (string) or list of vids.
     * @return array Set of valid plain names as name => true.
     */
    public static function getValidResolvedNames($aCategoryAttributes, $aResolvedVids) {
        $aRefPidToName = array();
        foreach ($aCategoryAttributes as $sName => $aAttr) {
            if (isset($aAttr['refPid'])) {
                $aRefPidToName[(string)$aAttr['refPid']] = $sName;
            }
        }

        $aValid = array();
        $aQueue = array();

        foreach ($aCategoryAttributes as $sName => $aAttr) {
            if (!isset($aAttr['parentRefPid'])) {
                $aValid[$sName] = true;
                $aQueue[] = $sName;
            }
        }

        while (!empty($aQueue)) {
            $sName = array_shift($aQueue);
            $aAttr = isset($aCategoryAttributes[$sName]) ? $aCategoryAttributes[$sName] : null;
            if ($aAttr === null) {
                continue;
            }

            $mChildAttributes = isset($aAttr['childAttributes']) ? $aAttr['childAttributes'] : null;
            if (!is_array($mChildAttributes) || self::isSequentialArray($mChildAttributes)) {
                continue;
            }

            $mResolved = isset($aResolvedVids[$sName]) ? $aResolvedVids[$sName] : null;
            if ($mResolved === null || $mResolved === '') {
                continue; // Parent has no value → its children stay invalid (orphans).
            }
            $aVids = is_array($mResolved) ? $mResolved : array($mResolved);

            $aParentValues = (isset($aAttr['values']) && is_array($aAttr['values']))
                ? $aAttr['values'] : array();
            $blResolvedToValidVid = false;
            foreach ($aVids as $mVid) {
                if ((string)$mVid !== '' && isset($aParentValues[(string)$mVid])) {
                    $blResolvedToValidVid = true;
                    break;
                }
            }
            $blMisResolved = (!empty($aParentValues) && !$blResolvedToValidVid);

            $aTriggeredRefGroups = array();
            if ($blMisResolved) {
                foreach ($mChildAttributes as $aRefs) {
                    if (is_array($aRefs)) {
                        $aTriggeredRefGroups[] = $aRefs;
                    }
                }
            } else {
                foreach ($aVids as $mVid) {
                    $sVid = (string)$mVid;
                    if ($sVid !== '' && isset($mChildAttributes[$sVid]) && is_array($mChildAttributes[$sVid])) {
                        $aTriggeredRefGroups[] = $mChildAttributes[$sVid];
                    }
                }
            }

            foreach ($aTriggeredRefGroups as $aRefs) {
                foreach ($aRefs as $aRef) {
                    if (!isset($aRef['childRefPid'])) {
                        continue;
                    }
                    $sChildName = isset($aRefPidToName[(string)$aRef['childRefPid']])
                        ? $aRefPidToName[(string)$aRef['childRefPid']]
                        : null;
                    if ($sChildName !== null && !isset($aValid[$sChildName])) {
                        $aValid[$sChildName] = true;
                        $aQueue[] = $sChildName;
                    }
                }
            }
        }

        return $aValid;
    }

    /**
     * Drop orphaned/mismatched child attributes from the RESOLVED payload map.
     *
     * Operates on the resolved category attributes (after matching resolution),
     * keyed by the PLAIN attribute name. A child entry is removed when its
     * parent's resolved value does not trigger it — including when the parent
     * resolved to no value at all (absent from the payload → its children are
     * orphans). Non-child entries and entries not present in the category details
     * are always kept. Fail-open when there is no hierarchy.
     *
     * @param array $aResolvedByName    Resolved payload: plain name => resolved value.
     * @param array $aCategoryAttributes GetCategoryDetails DATA.attributes (plain-name keyed).
     * @return array Filtered resolved payload.
     */
    public static function filterResolvedOrphans($aResolvedByName, $aCategoryAttributes) {
        if (!is_array($aResolvedByName) || empty($aResolvedByName)
            || !is_array($aCategoryAttributes) || empty($aCategoryAttributes)) {
            return $aResolvedByName;
        }

        $blHasHierarchy = false;
        foreach ($aCategoryAttributes as $aAttr) {
            if (isset($aAttr['parentRefPid'])) {
                $blHasHierarchy = true;
                break;
            }
        }
        if (!$blHasHierarchy) {
            return $aResolvedByName;
        }

        // Build the plain name => resolved vid map used for validity, extracting a
        // single scalar/list of vids from each resolved payload value.
        $aResolvedVids = array();
        foreach ($aResolvedByName as $sName => $mValue) {
            $aResolvedVids[$sName] = self::extractVid($mValue);
        }

        $aValid = self::getValidResolvedNames($aCategoryAttributes, $aResolvedVids);

        foreach ($aResolvedByName as $sName => $mValue) {
            if (!isset($aCategoryAttributes[$sName])) {
                continue; // Not a known category attribute → keep untouched.
            }
            $blIsChild = isset($aCategoryAttributes[$sName]['parentRefPid']);
            if ($blIsChild && !isset($aValid[$sName])) {
                unset($aResolvedByName[$sName]);
            }
        }

        return $aResolvedByName;
    }

    /**
     * Drop stale child attribute values from the STORED attribute-matching blob
     * before it is persisted on save.
     *
     * Unlike filterResolvedOrphans (which runs on the resolved upload payload),
     * this operates on the raw stored blob keyed by plain attribute name, where
     * each value is a {Code, Values} matching entry. A stored entry is dropped
     * when it is a CHILD that is not visible for the parent's current selection:
     *   - a child still defined by the current category details (has parentRefPid), or
     *   - a synthetic free-text child key (`prop_-<synthetic refPid>`) that the
     *     current details no longer define at all — e.g. a leftover from a previous
     *     marketplace attribute-id format after the canonical format changed.
     * Both are only ever emitted as children, so removing an invisible one never
     * touches a real root/parent attribute (those use `prop_<positive id>`).
     * Non-child entries are kept. Fail-open when there is no hierarchy.
     *
     * @param array $aStored             Stored matching blob: plain name => {Code, Values}.
     * @param array $aCategoryAttributes GetCategoryDetails DATA.attributes (plain-name keyed).
     * @return array Filtered stored blob.
     */
    public static function filterStaleChildren($aStored, $aCategoryAttributes) {
        if (!is_array($aStored) || empty($aStored)
            || !is_array($aCategoryAttributes) || empty($aCategoryAttributes)) {
            return $aStored;
        }

        $blHasHierarchy = false;
        foreach ($aCategoryAttributes as $aAttr) {
            if (isset($aAttr['parentRefPid'])) {
                $blHasHierarchy = true;
                break;
            }
        }
        if (!$blHasHierarchy) {
            return $aStored;
        }

        // Build the plain name => selected-vids map from the stored matching
        // entries (Code/Values) so a parent's current vids are recognised.
        $aResolvedVids = array();
        foreach ($aStored as $sName => $mEntry) {
            $aVids = self::getSelectedVids($mEntry);
            if (!empty($aVids)) {
                $aResolvedVids[$sName] = $aVids;
            }
        }

        $aValid = self::getValidResolvedNames($aCategoryAttributes, $aResolvedVids);

        foreach ($aStored as $sName => $mEntry) {
            $blIsKnownChild = isset($aCategoryAttributes[$sName])
                && isset($aCategoryAttributes[$sName]['parentRefPid']);
            $blIsSyntheticChildKey = (strpos($sName, 'prop_-') === 0);
            if (($blIsKnownChild || $blIsSyntheticChildKey) && !isset($aValid[$sName])) {
                unset($aStored[$sName]);
            }
        }

        return $aStored;
    }

    /**
     * Extract the vid(s) from a resolved payload value. The payload value is
     * either a plain scalar vid, a {vid: x} / {vid: [a,b]} wrapper, or a
     * free-text string (returned as-is so mis-resolution fail-safe can detect it).
     *
     * @param mixed $mValue
     * @return mixed string vid, array of vids, or '' when empty.
     */
    private static function extractVid($mValue) {
        if (is_array($mValue)) {
            if (isset($mValue['vid'])) {
                return $mValue['vid']; // {vid: scalar} or {vid: [..]} wrap
            }
            // Multiselect attributes resolve to a PLAIN LIST of vids (e.g.
            // ["74","78"]) — the filter runs before the {vid: …} wrapping in
            // TemuHelper. Returning '' here made the parent look unresolved, so
            // getValidResolvedNames() dropped its (controlType=16 proportion)
            // children as orphans and the API rejected the upload with
            // val_missing_required_attribute although the values were saved.
            if (self::isSequentialArray($mValue)) {
                return empty($mValue) ? '' : $mValue;
            }
            return '';
        }
        return ($mValue === null) ? '' : $mValue;
    }

    /**
     * True when the array is a sequential (list) array. An empty array counts as
     * a list — the Temu API emits `[]` for "no children" instead of `{}`.
     */
    private static function isSequentialArray($aArr) {
        if (!is_array($aArr)) {
            return false;
        }
        if (empty($aArr)) {
            return true;
        }
        return array_keys($aArr) === range(0, count($aArr) - 1);
    }
}
