// Jest stub for static asset imports (svg, png, jpg, ...).
// SVGs are also imported as React components in a couple of places, so expose
// both a default string and a named `ReactComponent` that render harmlessly.
const React = require('react');
const Stub = React.forwardRef((props, ref) => React.createElement('span', {ref, ...props}));
module.exports = Stub;
module.exports.ReactComponent = Stub;
module.exports.default = 'test-file-stub';