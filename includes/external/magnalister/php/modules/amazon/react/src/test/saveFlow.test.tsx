/**
 * BUG-018 — save flow regression tests.
 *
 * Covers the four root causes fixed in AmazonVariations.tsx:
 *  R1  multi-instance save registry: the unscoped window save fn flushes EVERY instance
 *      and survives a partial unmount
 *  R2  strictSave: real failures show the error toast and do NOT invoke the submit
 *      callback; default (Amazon/eBay) keeps the legacy always-succeed behavior
 *  R3  parent/child hierarchy: pending saves flush as ONE saveAttributeMatchingBatch
 *      request (atomic with the server-side stale-child prune)
 *  R5  hidden children keep their values client-side — no local delete, no queued
 *      backend delete
 */
import React from 'react';
import {fireEvent, render, screen, waitFor} from '@testing-library/react';
import AmazonVariations from '../AmazonVariations';
import {MarketplaceAttributes, SavedValues, ShopAttributes} from '../types';

jest.setTimeout(30000);

const API = 'https://shop.example/ml-endpoint';

const shopAttributes: ShopAttributes = {
    'Variations': {
        optGroupClass: 'variation',
        'color': {
            name: 'Color',
            type: 'select',
            values: {red: 'Red', blue: 'Blue'}
        }
    }
};

// Simple (non-hierarchy) attribute set factory — distinct keys per instance.
// NOTE: props passed to AmazonVariations MUST be referentially stable across renders
// (the component's savedValues/i18n effects loop on unstable identities), so every
// fixture below is a module-level constant.
const simpleAttributes = (key: string): MarketplaceAttributes => ({
    [key]: {
        value: 'Color',
        required: true,
        dataType: 'select',
        values: {Red: 'Red', Blue: 'Blue'}
    }
});

const attrsA = simpleAttributes('attr_a');
const attrsB = simpleAttributes('attr_b');
const emptySavedValues: SavedValues = {};
const strictI18n = {saveFailed: 'SAVE-FAILED-TOAST'};
const legacyI18n = {saveSuccess: 'SAVE-SUCCESS-TOAST', saveFailed: 'SAVE-FAILED-TOAST'};

// Temu-style parent/child hierarchy: parent vid "11" triggers the child
const hierarchyAttributes: MarketplaceAttributes = {
    parent_attr: {
        value: 'Material',
        required: true,
        dataType: 'select',
        refPid: 100,
        childAttributes: {'11': [{childRefPid: 200, groupId: 'g1'}]},
        values: {'11': 'Glass', '12': 'Steel'}
    } as any,
    child_attr: {
        value: 'Glass Type',
        required: false,
        dataType: 'select',
        refPid: 200,
        parentRefPid: 100,
        values: {'21': 'Tempered', '22': 'Laminated'}
    } as any
};

const hierarchySavedValues: SavedValues = {
    parent_attr: {Code: 'attribute_value', Values: '11'} as any,
    child_attr: {Code: 'attribute_value', Values: '21'} as any
};

type FetchCall = {action: string | null; params: URLSearchParams};

function installFetchMock(responder?: (call: FetchCall) => any) {
    const calls: FetchCall[] = [];
    (global as any).fetch = jest.fn(async (_url: string, init: any) => {
        const params = new URLSearchParams(init?.body || '');
        const call: FetchCall = {action: params.get('ml[action]'), params};
        calls.push(call);
        const payload = responder ? responder(call) : {success: true};
        return {json: async () => payload};
    });
    return calls;
}

function changeShopAttributeTo(container: HTMLElement, attributeKey: string, value: string) {
    const select = container.querySelector(
        `#attr-row-${attributeKey} .shop-attribute-container select`
    ) as HTMLSelectElement | null;
    expect(select).not.toBeNull();
    fireEvent.change(select as HTMLSelectElement, {target: {value}});
}

function amazonValueSelect(container: HTMLElement, attributeKey: string): HTMLSelectElement | null {
    return container.querySelector(
        `#attr-row-${attributeKey} .amazon-value-selector`
    ) as HTMLSelectElement | null;
}

async function externalSave(): Promise<{callback: jest.Mock}> {
    const callback = jest.fn();
    const saveFn = (window as any).magnalisterSaveVariations;
    expect(typeof saveFn).toBe('function');
    await saveFn(callback);
    return {callback};
}

afterEach(() => {
    jest.restoreAllMocks();
    delete (global as any).fetch;
});

describe('R1 — multi-instance save registry', () => {
    test('unscoped save fn flushes pending changes of EVERY mounted instance', async () => {
        const calls = installFetchMock();

        const a = render(
            <AmazonVariations
                variationGroup="groupA" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={attrsA}
                savedValues={emptySavedValues} apiEndpoint={API}
            />
        );
        const b = render(
            <AmazonVariations
                variationGroup="groupB" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={attrsB}
                savedValues={emptySavedValues} apiEndpoint={API}
            />
        );

        // One edit in EACH instance
        changeShopAttributeTo(a.container, 'attr_a', 'attribute_value');
        changeShopAttributeTo(b.container, 'attr_b', 'attribute_value');

        const {callback} = await externalSave();

        const savedGroups = calls
            .filter(c => c.action === 'saveAttributeMatching')
            .map(c => c.params.get('ml[variationGroup]'));
        expect(savedGroups).toContain('groupA');
        expect(savedGroups).toContain('groupB');
        expect(callback).toHaveBeenCalledTimes(1);
    });

    test('save fn survives a partial unmount and is removed with the last instance', () => {
        installFetchMock();

        const a = render(
            <AmazonVariations
                variationGroup="groupA" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={attrsA}
                savedValues={emptySavedValues} apiEndpoint={API}
            />
        );
        const b = render(
            <AmazonVariations
                variationGroup="groupB" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={attrsB}
                savedValues={emptySavedValues} apiEndpoint={API}
            />
        );

        expect(typeof (window as any).magnalisterSaveVariations).toBe('function');

        b.unmount();
        // Previously the unmounting instance deleted the shared globals unconditionally
        expect(typeof (window as any).magnalisterSaveVariations).toBe('function');
        expect(typeof (window as any).magnalisterSaveAmazonVariations).toBe('function');

        a.unmount();
        expect((window as any).magnalisterSaveVariations).toBeUndefined();
        expect((window as any).magnalisterSaveAmazonVariations).toBeUndefined();
    });
});

describe('R2 — strictSave failure reporting', () => {
    test('strictSave: failed save shows the error toast and does NOT invoke the submit callback', async () => {
        installFetchMock(() => ({success: false, message: 'nope'}));

        const {container} = render(
            <AmazonVariations
                variationGroup="groupA" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={attrsA}
                savedValues={emptySavedValues} apiEndpoint={API}
                strictSave={true}
                i18n={strictI18n}
            />
        );

        changeShopAttributeTo(container, 'attr_a', 'attribute_value');

        const {callback} = await externalSave();

        expect(callback).not.toHaveBeenCalled();
        await waitFor(() => {
            expect(screen.getByText('SAVE-FAILED-TOAST')).toBeInTheDocument();
        });
    });

    test('default (Amazon/eBay): failed save keeps legacy behavior — callback fires, success toast shows', async () => {
        installFetchMock(() => ({success: false, message: 'nope'}));

        const {container} = render(
            <AmazonVariations
                variationGroup="groupA" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={attrsA}
                savedValues={emptySavedValues} apiEndpoint={API}
                i18n={legacyI18n}
            />
        );

        changeShopAttributeTo(container, 'attr_a', 'attribute_value');

        const {callback} = await externalSave();

        expect(callback).toHaveBeenCalledTimes(1);
        await waitFor(() => {
            expect(screen.getByText('SAVE-SUCCESS-TOAST')).toBeInTheDocument();
        });
        expect(screen.queryByText('SAVE-FAILED-TOAST')).not.toBeInTheDocument();
    });
});

describe('R3 — atomic batch flush for parent/child hierarchies', () => {
    test('pending saves flush as ONE saveAttributeMatchingBatch request containing every changed attribute', async () => {
        const calls = installFetchMock();

        const {container} = render(
            <AmazonVariations
                variationGroup="temuCategory" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={hierarchyAttributes}
                savedValues={hierarchySavedValues} apiEndpoint={API}
            />
        );

        // Initial mount batch save fires (saved values exist) — wait for it, then reset
        await waitFor(() => {
            expect(calls.some(c => c.action === 'saveAttributeMatchingBatch')).toBe(true);
        });
        calls.length = 0;

        // Change the child's marketplace value (parent stays matched to vid 11)
        const childSelect = amazonValueSelect(container, 'child_attr');
        expect(childSelect).not.toBeNull();
        fireEvent.change(childSelect as HTMLSelectElement, {target: {value: '22'}});

        await externalSave();

        const batchCalls = calls.filter(c => c.action === 'saveAttributeMatchingBatch');
        const singleSaves = calls.filter(c => c.action === 'saveAttributeMatching');
        expect(batchCalls).toHaveLength(1);
        expect(singleSaves).toHaveLength(0);

        const attributesData = JSON.parse(batchCalls[0].params.get('ml[attributesData]') || '{}');
        expect(Object.keys(attributesData)).toContain('child_attr');
    });
});

describe('R5 — hidden children keep their values', () => {
    test('hiding a child via parent change queues NO delete and restores the child value on toggle back', async () => {
        const calls = installFetchMock();

        const {container} = render(
            <AmazonVariations
                variationGroup="temuCategory" customIdentifier="p1"
                shopAttributes={shopAttributes}
                marketplaceAttributes={hierarchyAttributes}
                savedValues={hierarchySavedValues} apiEndpoint={API}
            />
        );

        // Child is visible with its saved value
        expect(amazonValueSelect(container, 'child_attr')).not.toBeNull();
        expect((amazonValueSelect(container, 'child_attr') as HTMLSelectElement).value).toBe('21');

        // Switch parent 11 → 12: child gets hidden
        const parentSelect = amazonValueSelect(container, 'parent_attr') as HTMLSelectElement;
        fireEvent.change(parentSelect, {target: {value: '12'}});
        await waitFor(() => {
            expect(amazonValueSelect(container, 'child_attr')).toBeNull();
        });

        calls.length = 0;
        await externalSave();

        // No delete request was queued for the hidden child
        const deletes = calls.filter(c => c.params.get('ml[actionType]') === 'delete');
        expect(deletes).toHaveLength(0);

        // Toggle parent back 12 → 11: child re-appears WITH its previous value
        fireEvent.change(
            amazonValueSelect(container, 'parent_attr') as HTMLSelectElement,
            {target: {value: '11'}}
        );
        await waitFor(() => {
            expect(amazonValueSelect(container, 'child_attr')).not.toBeNull();
        });
        expect((amazonValueSelect(container, 'child_attr') as HTMLSelectElement).value).toBe('21');
    });
});
