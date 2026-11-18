const wordpress = require( '@wordpress/eslint-plugin' ).configs.recommended;

module.exports = [
	{ ignores: [ 'build/**', 'node_modules/**' ] },
	...wordpress,
];
