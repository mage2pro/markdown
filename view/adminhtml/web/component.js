// 2016-01-08
define([
	'Magento_Ui/js/form/element/wysiwyg'
], function(Wysiwyg) {return Wysiwyg.extend({
	/**
	 * 2016-01-08
	 * @param {Object} config
	 * @param {Object} config.dfeConfig
	 * @returns {} Chainable
	 */
	initialize: function(config) {
		this._super();
		this.dfeConfig = config.dfeConfig;
		return this;
	},
	/**
	 * 2016-01-08
	 * @param {HTMLElement} node
	 */
	setElementNode: function(node) {
		if (!this.dfeInit) {
			this.dfeInit = true;
			this._super();
			var config = this.dfeConfig;
			require(['Dfe_Markdown/main'], function(init) {init(config);});
		}
	}
});});