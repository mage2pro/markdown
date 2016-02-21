// 2016-01-08
define([
	'jquery'
	,'Magento_Ui/js/form/element/wysiwyg'
], function($, Wysiwyg) {return Wysiwyg.extend({
	defaults: {
		/**
		 * 2016-02-21
		 * Поле «${ $.provider }:${ $.parentScope }.markdown» здесь доступно,
		 * потому что блок загружает и передаёт клиентский части все свои данные:
		 * @see \Magento\Cms\Model\Block\DataProvide::getData()
		 * https://github.com/magento/magento2/blob/e0ed4bad/app/code/Magento/Cms/Model/Block/DataProvider.php#L61-L80
		 *
		 * «How does a backend CMS block form define the data to be passed
		 * to its UI component on the client (JavaScript) side?»
		 * https://mage2.pro/t/769
		 */
		imports: {markdown: '${ $.provider }:${ $.parentScope }.markdown'}
	},

	/**
	 * 2016-01-08
	 * @param {Object} config
	 * @param {Object} config.dfeConfig
	 * @returns {} Chainable
	 */
	initialize: function(config) {
		this._super();
		// 2016-02-21
		// Надо устанавливать именно value, а не initialValue;
		this.value(this.markdown);
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
		$(window).trigger('dfe.markdown.beforeValidation', this.source.data);
		var result =  this._super();
		if (result.valid) {
			$(window).trigger('dfe.markdown.afterValidation');
		}
		return result;
	}
});});