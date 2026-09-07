import {
  buildRefPidToKeyMap,
  hasParentChildAttributes,
  getSelectedVid,
  getSelectedVids,
  getVisibleAttributeKeys,
} from '../utils/parentChildVisibility';
import {MarketplaceAttributes, SavedAttributeValue, SavedValues} from '../types';

// ---------------------------------------------------------------------------
// Test data – simulates real Temu API structure
// ---------------------------------------------------------------------------

/**
 * prop_12 (Material, refPid=12): parent
 *   childAttributes: { 969: [{ childRefPid: 7639, groupId: '' }] }
 *
 * prop_15 (Composition, refPid=15): root, no parent-child
 *
 * prop_7639 (Base Fabric Composition, refPid=7639): child of 12, triggerVid=969
 *
 * prop_7003 (Number Of Pieces, refPid=7003): parent
 *   childAttributes: {
 *     199770: [{ childRefPid: 6926, groupId: '' }, { childRefPid: 6988, groupId: '' }],
 *     199302: [{ childRefPid: 6926, groupId: '' }, { childRefPid: 6927, groupId: '' }],
 *   }
 *
 * prop_6926 (Fabric Texture 1, refPid=6926): child of 7003, triggerVid=199770
 *   ALSO parent with childAttributes: { 161198: [{ childRefPid: 6978, groupId: '' }] }
 *
 * prop_6927 (Fabric Texture 2, refPid=6927): child of 7003, triggerVid=199302
 *
 * prop_6988 (Lining Texture 1, refPid=6988): child of 7003, triggerVid=199770, childAttributes=[]
 *
 * prop_6978 (Gram Weight, refPid=6978): child of 6926, triggerVid=161198, childAttributes=[]
 */
const temuAttributes: MarketplaceAttributes = {
  prop_12: {
    value: 'Material',
    required: true,
    dataType: 'select',
    refPid: 12,
    childAttributes: {
      '969': [{childRefPid: 7639, groupId: ''}],
    },
  },
  prop_15: {
    value: 'Composition',
    required: false,
    dataType: 'text',
    refPid: 15,
    // no childAttributes, no parentRefPid → root
  },
  prop_7639: {
    value: 'Base Fabric Composition',
    required: false,
    dataType: 'text',
    refPid: 7639,
    parentRefPid: 12,
    triggerVid: 969,
    childAttributes: [],
  },
  prop_7003: {
    value: 'Number Of Pieces',
    required: true,
    dataType: 'select',
    refPid: 7003,
    childAttributes: {
      '199770': [
        {childRefPid: 6926, groupId: ''},
        {childRefPid: 6988, groupId: ''},
      ],
      '199302': [
        {childRefPid: 6926, groupId: ''},
        {childRefPid: 6927, groupId: ''},
      ],
    },
  },
  prop_6926: {
    value: 'Fabric Texture 1',
    required: false,
    dataType: 'select',
    refPid: 6926,
    parentRefPid: 7003,
    triggerVid: 199770,
    childAttributes: {
      '161198': [{childRefPid: 6978, groupId: ''}],
    },
  },
  prop_6927: {
    value: 'Fabric Texture 2',
    required: false,
    dataType: 'select',
    refPid: 6927,
    parentRefPid: 7003,
    triggerVid: 199302,
    childAttributes: [],
  },
  prop_6988: {
    value: 'Lining Texture 1',
    required: false,
    dataType: 'select',
    refPid: 6988,
    parentRefPid: 7003,
    triggerVid: 199770,
    childAttributes: [],
  },
  prop_6978: {
    value: 'Gram Weight',
    required: false,
    dataType: 'select',
    refPid: 6978,
    parentRefPid: 6926,
    triggerVid: 161198,
    childAttributes: [],
  },
};

// Flat attributes – no parent-child relationships
const flatAttributes: MarketplaceAttributes = {
  prop_1: {value: 'Color', required: true, dataType: 'select', refPid: 1},
  prop_2: {value: 'Size', required: false, dataType: 'text', refPid: 2},
  prop_3: {value: 'Brand', required: false, dataType: 'text', refPid: 3},
};

// ---------------------------------------------------------------------------
// buildRefPidToKeyMap
// ---------------------------------------------------------------------------

describe('buildRefPidToKeyMap', () => {
  it('maps refPid → attribute key for all attrs with refPid', () => {
    const map = buildRefPidToKeyMap(temuAttributes);
    expect(map[12]).toBe('prop_12');
    expect(map[15]).toBe('prop_15');
    expect(map[7639]).toBe('prop_7639');
    expect(map[7003]).toBe('prop_7003');
    expect(map[6926]).toBe('prop_6926');
    expect(map[6927]).toBe('prop_6927');
    expect(map[6988]).toBe('prop_6988');
    expect(map[6978]).toBe('prop_6978');
  });

  it('skips attributes that have no refPid', () => {
    const attrs: MarketplaceAttributes = {
      no_ref: {value: 'NoRef', required: false, dataType: 'text'},
      has_ref: {value: 'HasRef', required: false, dataType: 'text', refPid: 99},
    };
    const map = buildRefPidToKeyMap(attrs);
    expect(Object.keys(map)).toHaveLength(1);
    expect(map[99]).toBe('has_ref');
  });

  it('returns empty object for empty attributes', () => {
    expect(buildRefPidToKeyMap({})).toEqual({});
  });
});

// ---------------------------------------------------------------------------
// hasParentChildAttributes
// ---------------------------------------------------------------------------

describe('hasParentChildAttributes', () => {
  it('returns true when at least one attribute has parentRefPid', () => {
    expect(hasParentChildAttributes(temuAttributes)).toBe(true);
  });

  it('returns false for flat attributes with no parentRefPid', () => {
    expect(hasParentChildAttributes(flatAttributes)).toBe(false);
  });

  it('returns false for empty attributes', () => {
    expect(hasParentChildAttributes({})).toBe(false);
  });
});

// ---------------------------------------------------------------------------
// getSelectedVid
// ---------------------------------------------------------------------------

describe('getSelectedVid', () => {
  it('returns vid from Values.AttributeValue when Code is attribute_value', () => {
    const saved: SavedAttributeValue = {
      Code: 'attribute_value',
      Values: {AttributeValue: '969'},
    };
    expect(getSelectedVid(saved)).toBe('969');
  });

  it('returns vid from Values[0].Marketplace.Key when Code is matching', () => {
    const saved: SavedAttributeValue = {
      Code: 'prop_12',
      Values: [
        {Shop: {Key: 'cotton', Value: 'Cotton'}, Marketplace: {Key: '969', Value: 'Cotton'}},
      ],
    };
    expect(getSelectedVid(saved)).toBe('969');
  });

  it('returns null for freetext Code', () => {
    const saved: SavedAttributeValue = {
      Code: 'freetext',
      Values: {FreeText: 'custom text'},
    };
    expect(getSelectedVid(saved)).toBe(null);
  });

  it('returns null for empty SavedAttributeValue', () => {
    expect(getSelectedVid({})).toBe(null);
  });

  it('returns null for undefined', () => {
    expect(getSelectedVid(undefined)).toBe(null);
  });

  it('returns null when Values array is empty', () => {
    const saved: SavedAttributeValue = {
      Code: 'prop_12',
      Values: [],
    };
    expect(getSelectedVid(saved)).toBe(null);
  });

  it('returns vid from PHP object-map Values (initial load) when Code is matching', () => {
    // PHP serializes a matching value as an integer-keyed associative array,
    // which json_encode emits as an OBJECT {"1":{...},"2":{...}} — not a JS array.
    const saved: SavedAttributeValue = {
      Code: 'p_a67cdd96',
      Values: {
        '1': {Shop: {Key: 'cotton', Value: 'Cotton'}, Marketplace: {Key: '969', Value: 'Cotton'}},
        '2': {Shop: {Key: 'wood', Value: 'Wood'}, Marketplace: {Key: '2151', Value: 'Wood'}},
      },
    };
    expect(getSelectedVid(saved)).toBe('969');
  });
});

// ---------------------------------------------------------------------------
// getSelectedVids (all matched vids, not just the first)
// ---------------------------------------------------------------------------

describe('getSelectedVids', () => {
  it('returns single vid for attribute_value', () => {
    const saved: SavedAttributeValue = {
      Code: 'attribute_value',
      Values: {AttributeValue: '969'},
    };
    expect(getSelectedVids(saved)).toEqual(['969']);
  });

  it('returns ALL vids for a multiselect attribute_value (direct array of vids)', () => {
    // Temu multiselect (e.g. Lining Ingredients) delivers the picked vids as a
    // plain array of strings — not a {AttributeValue} map. Each vid can trigger
    // its own child, so every vid must be collected.
    const saved: SavedAttributeValue = {
      Code: 'attribute_value',
      Values: ['98', '35385'] as unknown as SavedAttributeValue['Values'],
    };
    expect(getSelectedVids(saved)).toEqual(['98', '35385']);
  });

  it('normalizes NUMERIC vids to strings (defensive parity with PHP pushVid)', () => {
    // PHP may emit vids as ints in the stored blob JSON (e.g. [78, 98]); the
    // childAttributes keys are strings, so numeric vids must be collected as strings.
    const saved: SavedAttributeValue = {
      Code: 'attribute_value',
      Values: [78, 98] as unknown as SavedAttributeValue['Values'],
    };
    expect(getSelectedVids(saved)).toEqual(['78', '98']);
  });

  it('returns ALL vids for a multiselect attribute_value (PHP object-map of vids on initial load)', () => {
    // On initial load PHP serialises the integer-keyed vid array as an object-map.
    const saved: SavedAttributeValue = {
      Code: 'attribute_value',
      Values: {'0': '98', '1': '35385'} as unknown as SavedAttributeValue['Values'],
    };
    expect(getSelectedVids(saved)).toEqual(['98', '35385']);
  });

  it('returns ALL vids from a multi-row matching value (array)', () => {
    const saved: SavedAttributeValue = {
      Code: 'prop_12',
      Values: [
        {Shop: {Key: 'glass'}, Marketplace: {Key: '2144'}},
        {Shop: {Key: 'steel'}, Marketplace: {Key: '2171'}},
      ],
    };
    expect(getSelectedVids(saved)).toEqual(['2144', '2171']);
  });

  it('returns ALL vids from a PHP object-map matching value (initial load)', () => {
    const saved: SavedAttributeValue = {
      Code: 'p_a67cdd96',
      Values: {
        '1': {Shop: {Key: 'glass'}, Marketplace: {Key: '2144'}},
        '2': {Shop: {Key: 'steel'}, Marketplace: {Key: '2171'}},
      },
    };
    expect(getSelectedVids(saved)).toEqual(['2144', '2171']);
  });

  it('returns [] for freetext / empty / undefined', () => {
    expect(getSelectedVids({Code: 'freetext', Values: {FreeText: 'x'}})).toEqual([]);
    expect(getSelectedVids({})).toEqual([]);
    expect(getSelectedVids(undefined)).toEqual([]);
  });
});

// ---------------------------------------------------------------------------
// getVisibleAttributeKeys
// ---------------------------------------------------------------------------

describe('getVisibleAttributeKeys', () => {
  it('returns all keys when no parent-child present (flat attributes)', () => {
    const visible = getVisibleAttributeKeys(flatAttributes, {});
    expect(visible).toEqual(new Set(['prop_1', 'prop_2', 'prop_3']));
  });

  it('returns only root attributes when no values provided', () => {
    const visible = getVisibleAttributeKeys(temuAttributes, {});
    // Root attrs (no parentRefPid): prop_12, prop_15, prop_7003
    expect(visible.has('prop_12')).toBe(true);
    expect(visible.has('prop_15')).toBe(true);
    expect(visible.has('prop_7003')).toBe(true);
    // Children should not be visible
    expect(visible.has('prop_7639')).toBe(false);
    expect(visible.has('prop_6926')).toBe(false);
    expect(visible.has('prop_6927')).toBe(false);
    expect(visible.has('prop_6988')).toBe(false);
    expect(visible.has('prop_6978')).toBe(false);
  });

  it('shows child when parent has a matching value selected (attribute_value)', () => {
    const values: SavedValues = {
      prop_12: {
        Code: 'attribute_value',
        Values: {AttributeValue: '969'},
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_7639')).toBe(true);
  });

  it('shows child when parent has a matching value selected (matching/array)', () => {
    const values: SavedValues = {
      prop_12: {
        Code: 'prop_12_shop',
        Values: [
          {Shop: {Key: 'cotton'}, Marketplace: {Key: '969'}},
        ],
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_7639')).toBe(true);
  });

  it('shows child when parent matching value is a PHP object-map (initial load)', () => {
    // On initial page load PHP delivers the matching value as an object-map
    // {"1":{...}} rather than a JS array. The child must still be visible.
    const values: SavedValues = {
      prop_12: {
        Code: 'p_a67cdd96',
        Values: {
          '1': {Shop: {Key: 'cotton'}, Marketplace: {Key: '969'}},
        },
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_7639')).toBe(true);
  });

  it('does NOT show child when parent value does not match (different vid)', () => {
    const values: SavedValues = {
      prop_12: {
        Code: 'attribute_value',
        Values: {AttributeValue: '999'}, // vid 999 has no children
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_7639')).toBe(false);
  });

  it('shows multiple children when parent value triggers them', () => {
    // prop_7003 with vid 199770 triggers prop_6926 and prop_6988
    const values: SavedValues = {
      prop_7003: {
        Code: 'attribute_value',
        Values: {AttributeValue: '199770'},
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_6926')).toBe(true);
    expect(visible.has('prop_6988')).toBe(true);
    // prop_6927 is only triggered by vid 199302, not 199770
    expect(visible.has('prop_6927')).toBe(false);
  });

  it('shows different children for different parent values', () => {
    // prop_7003 with vid 199302 triggers prop_6926 and prop_6927
    const values: SavedValues = {
      prop_7003: {
        Code: 'attribute_value',
        Values: {AttributeValue: '199302'},
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_6926')).toBe(true);
    expect(visible.has('prop_6927')).toBe(true);
    // prop_6988 is only triggered by vid 199770, not 199302
    expect(visible.has('prop_6988')).toBe(false);
  });

  it('follows multi-level chain: grandchild visible when parent and mid-level have matching values', () => {
    // prop_7003 (vid 199770) → prop_6926 visible
    // prop_6926 (vid 161198) → prop_6978 visible
    const values: SavedValues = {
      prop_7003: {
        Code: 'attribute_value',
        Values: {AttributeValue: '199770'},
      },
      prop_6926: {
        Code: 'attribute_value',
        Values: {AttributeValue: '161198'},
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_6926')).toBe(true);
    expect(visible.has('prop_6978')).toBe(true);
  });

  it('hides grandchild when its parent (mid-level) is not visible', () => {
    // prop_7003 has no matching value → prop_6926 is not visible
    // → prop_6978 (child of prop_6926) must also be hidden
    const values: SavedValues = {
      prop_6926: {
        Code: 'attribute_value',
        Values: {AttributeValue: '161198'},
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_6926')).toBe(false);
    expect(visible.has('prop_6978')).toBe(false);
  });

  it('unions children of ALL matched values when parent has multiple matching rows', () => {
    // Multi-material matching (e.g. Material → Glass AND Stainless Steel):
    // prop_7003 matched to vid 199770 (→ prop_6926, prop_6988) AND
    // vid 199302 (→ prop_6926, prop_6927). All three children must be visible.
    const values: SavedValues = {
      prop_7003: {
        Code: 'prop_7003_shop',
        Values: [
          {Shop: {Key: 'a'}, Marketplace: {Key: '199770'}},
          {Shop: {Key: 'b'}, Marketplace: {Key: '199302'}},
        ],
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_6926')).toBe(true); // triggered by both
    expect(visible.has('prop_6988')).toBe(true); // triggered by 199770 only
    expect(visible.has('prop_6927')).toBe(true); // triggered by 199302 only — was dropped before fix
  });

  it('unions children across multiple matched values (PHP object-map form)', () => {
    const values: SavedValues = {
      prop_7003: {
        Code: 'p_obj',
        Values: {
          '1': {Shop: {Key: 'a'}, Marketplace: {Key: '199770'}},
          '2': {Shop: {Key: 'b'}, Marketplace: {Key: '199302'}},
        },
      },
    };
    const visible = getVisibleAttributeKeys(temuAttributes, values);
    expect(visible.has('prop_6926')).toBe(true);
    expect(visible.has('prop_6988')).toBe(true);
    expect(visible.has('prop_6927')).toBe(true);
  });
});
