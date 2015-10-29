// 2015-10-26
// Здесь надо использовать именно define, а не require.
// https://mage2.pro/t/146
define([
	'jquery'
	, 'SimpleMDE'
	, 'HighlightJs'
	// https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js
	,'mage/adminhtml/browser'
], function($, SimpleMDE, HighlightJs) {return function(config) {
	hljs.initHighlightingOnLoad();
	/** @type HTMLTextAreaElement|Element */
	var textarea = document.getElementById(config.id);
	/** @type {jQuery} HTMLTextAreaElement */
	var $textarea = $(textarea);
	// Добавление класса CSS позволяет нам задать разную высоту редактора
	// для описания и краткого описания товара.
	$textarea.wrap($("<div class='dfe-markdown'></div>").addClass(config.cssClass));
	var editor = new SimpleMDE({
		autofocus: true
		,autosave: {enabled: true, unique_id: textarea.id}
		,element: textarea
		,renderingConfig: {codeSyntaxHighlighting: true}
		,tabSize: 4
		,toolbar: (function() {
			/** @type Array */
			var result = SimpleMDE.toolbar.slice();
			/** @type Object */
			var cc = config['coreConfig'];
			/**
			 * 2015-10-29
			 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Model/Wysiwyg/Config.php#L172-L181
			 */
			if (cc['add_images']) {
				result[result.indexOf('image')] = {
					className: 'fa fa-picture-o'
					,name: 'image'
					,title: 'Insert Image (Ctrl+Alt+I)'
					,action: function() {
						/**
						 * 2015-10-29
						 * target_element_id:
						 * 1) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Block/Adminhtml/Wysiwyg/Images/Content.php#L160-L163
						 * 2) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Block/Adminhtml/Wysiwyg/Images/Content.php#L101
						 * 3) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js#L224-L232
						 * 4) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js#L185
						 * 5) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js#L204-L208
						 */
						/** @type String */
						var url = cc['files_browser_window_url'] + 'target_element_id/' + config.id + '/';
						/** @type ?Number */
						var storeId = cc['store_id'];
						if (storeId) {
							url += 'store/' + storeId;
						}
						MediabrowserUtility.openDialog(url);
					}
				};
			}
			return result;
		})()
	});
	window.dfEditor = editor;
	$textarea.bind('dfe.markdown.insert', function(event, id, newValue) {
		if (id === config.id) {
			// http://codemirror.net/doc/manual.html#replaceSelection
			// http://stackoverflow.com/a/23736834
			editor.codemirror.doc.replaceSelection(newValue);
		}
	});
	// https://learn.jquery.com/jquery-ui/widget-factory/extending-widgets/#extending-existing-methods
	$.widget('mage.mediabrowser', $.mage.mediabrowser, {
		insertAtCursor: function(element, value) {
			element.id !== config.id
				? this._superApply(arguments)
				: $textarea.trigger('dfe.markdown.insert', [element.id, value])
			;
		}
	});
	/**
	 * 2015-10-27
	 * http://stackoverflow.com/a/8353537
	 * На административной странице редактирования самодельных страниц
	 * редактор расположен не на первой (открытой по умолчанию) вкладке,
	 * а на второй (скрытой по умолчанию).
	 * По этой причине проявляется проблема, когда изначально содержимое редактора не видно,
	 * и появляется только при клике по области редактирования:
	 * http://stackoverflow.com/questions/8349571
	 * http://stackoverflow.com/questions/17086538
	 * Нам надо при переходе на вкладку с релактором вызывать метод refresh() редактора:
	 * http://stackoverflow.com/a/8353537
	 */
	/** @type {jQuery} HTMLDivElement */
	var $tabs = $('#page_tabs');
	if ($tabs.length) {
		/** @type HTMLDivElement */
		var tabContent = $textarea.closest('.ui-tabs-panel').get(0);
		if (tabContent) {
			$tabs.bind('tabsactivate', function(event, data) {
				if (data.newPanel.get(0) === tabContent) {
					editor.codemirror.refresh();
				}
			});
		}
	}
};});