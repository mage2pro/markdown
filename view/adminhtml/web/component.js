// 2016-01-08
define([
	'jquery'
	,'Magento_Ui/js/form/element/wysiwyg'
], function($, Wysiwyg) {return Wysiwyg.extend({
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
	},
	/**
	 * 2016-02-03
	 * https://github.com/magento/magento2/blob/c53d060/app/code/Magento/Ui/view/base/web/js/form/element/abstract.js#L327-L351
	 * @returns {Object}
	 */
	validate: function() {
		$(window).trigger('dfe.markdown.beforeValidation');
		var result =  this._super();
		if (result.valid) {
			$(window).trigger('dfe.markdown.afterValidation');
		}
		return result;
	}
});});