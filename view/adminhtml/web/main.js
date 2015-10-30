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
	/** @type Object */
	var cc = config['core'];
	/** @type HTMLTextAreaElement|Element */
	var textarea = document.getElementById(config.id);
	/** @type {jQuery} HTMLTextAreaElement */
	var $textarea = $(textarea);
	// Добавление класса CSS позволяет нам задать разную высоту редактора
	// для описания и краткого описания товара.
	$textarea.wrap($("<div class='dfe-markdown'></div>").addClass(config.cssClass));
	var editor = new SimpleMDE({
		autofocus: true
		/**
		 * 2015-10-30
		 * https://github.com/NextStepWebs/simplemde-markdown-editor#configuration
		 * Автосохранение — это интересная функция:
		 * она восстанавливает состояние редактора после перезагрузки страницы.
		 * Однако как быть, если содержимое было отредактировано в обход нашего редактора?
		 * Я столкнулся с такой ситуацией:
		 * 1) администратор сначала редактирует текст в нашем редакторе и НЕ сохраняет его
		 * 2) затем администратор переключается на стандартный редактор TinyMCE,
		 * редактирует этот же текст снова и сохраняет его.
		 * 3) так вот, при обратном переключении на наш редактор тот игнорирует изменения,
		 * сделанные посредством TinyMCE
		 * Но, думаю, такая ситуация — слишко искусственная, и ей можно пренебречь,
		 * ибо зачем администратору на шаге 1 редактировать и не сохранять?
		 * А если же администратор сохранет изменения, то localStorage сбрасывается:
		 * https://github.com/NextStepWebs/simplemde-markdown-editor/blob/0e6e46634610eab43a374389a757e680021fd6a5/src/js/simplemde.js#L962-L964
		 * simplemde.element.form.addEventListener("submit", function() {
		 	localStorage.setItem(simplemde.options.autosave.unique_id, "");
		 });
		 */
		,autosave: {enabled: true, unique_id: textarea.id}
		,element: textarea
		,renderingConfig: {codeSyntaxHighlighting: true}
		,tabSize: 4
		/**
		 * 2015-10-30
		 * «Custom function for parsing the plaintext Markdown and returning HTML.
		 * Used when user previews.»
		 * https://github.com/NextStepWebs/simplemde-markdown-editor/blob/0e6e46634610eab43a374389a757e680021fd6a5/src/js/simplemde.js#L808-L813
		 */
		,previewRender: function(markdown) {
			/** @type {Object} string => string */
			var widgetPlaceholders = cc['widget_placeholders'];
			return this.parent.markdown(markdown
				// 2015-10-30
				// Замещаем {{media url="wysiwyg/528340.jpg"}} на реальный веб-адрес.
				.replace(/\{\{media url="([^"]+)"}}/gm, config.mediaBaseURL + '$1')
			    // Замещаем код виджетов пиктограммами (так же поступает и стандартный редактор)
			   	// https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/String/replace#Specifying_a_function_as_a_parameter
			    // https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet#images
				.replace(/\{\{widget type="([^"]+)"[^}]+}}/gm, function(match, type) {
					// Почему-то слэши в именах классов продублированы:
					// Magento\\CatalogWidget\\Block\\Product\\ProductsList
					return '![](' + widgetPlaceholders[type.replace(/\\\\/g, '\\')] + ')';
				})
			);
		}
		,toolbar: (function() {
			/** @type Array */
			var result = SimpleMDE.toolbar.slice();
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