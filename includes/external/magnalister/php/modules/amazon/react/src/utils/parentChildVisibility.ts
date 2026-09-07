/**
 * Utility functions for Temu parent-child attribute visibility.
 *
 * Temu attributes can have hierarchical relationships:
 *   - A parent attribute declares `childAttributes`: a map from vid → [{childRefPid, groupId}]
 *   - A child attribute declares `parentRefPid` pointing to its parent
 *   - When the user selects a specific vid for the parent, only the children listed
 *     under that vid should be shown
 *
 * Visibility is driven by the PARENT's `childAttributes` map — NOT the child's
 * `triggerVid` field (a child may appear under multiple parent values).
 */

import {MarketplaceAttribute, MarketplaceAttributes, MatchingValue, SavedAttributeValue, SavedValues} from '../types';

// ---------------------------------------------------------------------------
// buildRefPidToKeyMap
// ---------------------------------------------------------------------------

/**
 * Build a reverse-lookup map: refPid → attribute key.
 *
 * Used to find the attribute key for a given refPid (e.g. when traversing
 * childAttributes entries that reference children by refPid).
 */
export function buildRefPidToKeyMap(marketplaceAttributes: MarketplaceAttributes): Record<number, string> {
  const map: Record<number, string> = {};
  for (const [key, attr] of Object.entries(marketplaceAttributes)) {
    if (attr.refPid !== undefined) {
      map[attr.refPid] = key;
    }
  }
  return map;
}

// ---------------------------------------------------------------------------
// hasParentChildAttributes
// ---------------------------------------------------------------------------

/**
 * Returns true if the attribute set contains at least one child attribute
 * (i.e. an attribute with a `parentRefPid`).  When false, the fast-path in
 * `getVisibleAttributeKeys` can skip all parent-child logic.
 */
export function hasParentChildAttributes(marketplaceAttributes: MarketplaceAttributes): boolean {
  return Object.values(marketplaceAttributes).some(attr => attr.parentRefPid !== undefined);
}

// ---------------------------------------------------------------------------
// getSelectedVid
// ---------------------------------------------------------------------------

/**
 * Extract ALL currently-selected marketplace vids from a SavedAttributeValue.
 *
 * A matching-type parent can be matched to several marketplace values at once
 * (e.g. Material → Glass AND Stainless Steel). Each matched value can trigger a
 * different set of children, so visibility must consider every selected vid, not
 * just the first.
 *
 * Rules:
 *  - Code === 'attribute_value' → vids from `Values.AttributeValue` (string or list)
 *  - Code is anything else (shop attribute key / "matching") and Values is a
 *    MatchingValue[] (or PHP object-map) → the `Marketplace.Key` of every row
 *  - freetext / empty / undefined → []
 *
 * Returns vids as **strings** (matching the string keys in childAttributes),
 * de-duplicated and in first-seen order.
 */
export function getSelectedVids(savedValue: SavedAttributeValue | undefined): string[] {
  if (!savedValue) {
    return [];
  }

  const {Code, Values} = savedValue;

  if (!Code || !Values) {
    return [];
  }

  const collected: string[] = [];
  const push = (v: unknown) => {
    // Accept numeric vids too (defensive parity with the PHP counterpart's
    // pushVid(): PHP may emit a vid as an int in the stored blob JSON) —
    // childAttributes keys are strings, so normalize.
    if (typeof v === 'number' && Number.isFinite(v)) {
      collected.push(String(v));
    } else if (typeof v === 'string' && v !== '') {
      collected.push(v);
    }
  };

  // Code === 'attribute_value': user picked one or more predefined marketplace values
  if (Code === 'attribute_value') {
    // Temu single-select: Values is a direct string vid (e.g. "27294")
    if (typeof Values === 'string') {
      push(Values);
    } else if (Array.isArray(Values)) {
      // Temu multiselect: Values is a direct array of vids (e.g. ["98", "35385"]).
      // Each vid can trigger its own child, so collect every one.
      Values.forEach(push);
    } else if (typeof Values === 'object' && Values !== null) {
      const attrValues = Values as {AttributeValue?: string | string[]};
      if (attrValues.AttributeValue !== undefined) {
        // Standard format: Values is {AttributeValue: "27294"} or {AttributeValue: ["27294"]}
        const av = attrValues.AttributeValue;
        if (Array.isArray(av)) {
          av.forEach(push);
        } else {
          push(av);
        }
      } else {
        // Multiselect on initial load: PHP serialises the integer-keyed vid array
        // as an object-map {"0":"98","1":"35385"}. Collect the string vids.
        Object.values(Values as Record<string, unknown>).forEach(push);
      }
    }
  } else if (Code !== 'freetext') {
    // Otherwise: shop attribute matching — Values is MatchingValue[] after a React
    // edit, but on initial page load PHP delivers it as an integer-keyed
    // associative array, which json_encode emits as an object-map
    // {"1":{...},"2":{...}}. Normalize both shapes to a flat list, then collect
    // the Marketplace.Key of EVERY matched row.
    const matchingList: MatchingValue[] = Array.isArray(Values)
      ? Values
      : (typeof Values === 'object' ? Object.values(Values) as MatchingValue[] : []);

    for (const row of matchingList) {
      const key = row?.Marketplace?.Key;
      if (Array.isArray(key)) {
        key.forEach(push);
      } else {
        push(key);
      }
    }
  }

  // De-duplicate, preserving first-seen order
  return collected.filter((v, i) => collected.indexOf(v) === i);
}

/**
 * Convenience wrapper returning only the first selected vid (or null).
 * Prefer {@link getSelectedVids} when a parent may be matched to several values.
 */
export function getSelectedVid(savedValue: SavedAttributeValue | undefined): string | null {
  return getSelectedVids(savedValue)[0] ?? null;
}

// ---------------------------------------------------------------------------
// getVisibleAttributeKeys
// ---------------------------------------------------------------------------

/**
 * Compute the set of attribute keys that should be visible in the UI given
 * the current attribute values.
 *
 * Algorithm:
 * 1. Fast path: if no attribute has a `parentRefPid`, return all keys.
 * 2. Build a refPid → key reverse-lookup map.
 * 3. Add all root attributes (no `parentRefPid`) to the visible set.
 * 4. Recursively process each visible attribute that has a `childAttributes`
 *    map: look up the selected vid for that attribute, then add all children
 *    whose `childRefPid` appears in the childAttributes entry for that vid.
 *
 * Note: `childAttributes` is either `[]` (no children for any vid) or a plain
 * object `{vid: [{childRefPid, groupId}]}`.  The `Array.isArray()` guard is
 * required because the Temu API can return an empty array instead of `{}`.
 */
export function getVisibleAttributeKeys(
  marketplaceAttributes: MarketplaceAttributes,
  attributeValues: SavedValues,
): Set<string> {
  // Fast path — no hierarchy at all
  if (!hasParentChildAttributes(marketplaceAttributes)) {
    return new Set(Object.keys(marketplaceAttributes));
  }

  const refPidToKey = buildRefPidToKeyMap(marketplaceAttributes);
  const visible = new Set<string>();

  // BFS/iterative processing queue — start with root attributes
  const queue: string[] = [];

  for (const [key, attr] of Object.entries(marketplaceAttributes)) {
    if (attr.parentRefPid === undefined) {
      visible.add(key);
      queue.push(key);
    }
  }

  // Process queue: for each visible attribute that is also a parent, find
  // which children should become visible based on the currently selected vid.
  while (queue.length > 0) {
    const currentKey = queue.shift()!;
    const currentAttr: MarketplaceAttribute = marketplaceAttributes[currentKey];

    if (!currentAttr.childAttributes || Array.isArray(currentAttr.childAttributes)) {
      // Empty array → no children defined, nothing to add
      continue;
    }

    // childAttributes is Record<string, ChildAttributeRef[]>
    const childAttributes = currentAttr.childAttributes as Record<string, Array<{childRefPid: number; groupId: string}>>;

    // Determine which vid(s) the user has selected for this attribute. A matching
    // parent can be mapped to several marketplace values at once, each triggering
    // its own children — union the children of every selected vid.
    const selectedVids = getSelectedVids(attributeValues[currentKey]);
    if (selectedVids.length === 0) {
      continue; // No value selected → no children visible
    }

    for (const selectedVid of selectedVids) {
      const childRefs = childAttributes[selectedVid];
      if (!childRefs || childRefs.length === 0) {
        continue; // This vid has no children
      }

      for (const {childRefPid} of childRefs) {
        const childKey = refPidToKey[childRefPid];
        if (childKey && !visible.has(childKey)) {
          visible.add(childKey);
          queue.push(childKey); // Recurse: the child may itself be a parent
        }
      }
    }
  }

  return visible;
}
