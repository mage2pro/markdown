// 2015-10-26
// Здесь надо использовать именно define, а не require.
// https://mage2.pro/t/146
define([
	'jquery'
	, 'df'
	, 'underscore'
	, 'SimpleMDE'
	, 'HighlightJs'
	// https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js
	,'mage/adminhtml/browser'
	/**
	 * 2015-10-31
	 * Загрузка этого модуля AMD инициализирует объекты window.WysiwygWidget и window.widgetTools.
	 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/wysiwyg/widget.js#L410-L411
	 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/internal/Magento/Framework/Data/Form/Element/Editor.php#L175
	 */
	,'mage/adminhtml/wysiwyg/widget'
	/**
	 * 2015-10-30
	 * Загрузка этого модуля AMD инициализирует объекты window.Variables и window.MagentovariablePlugin.
	 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Variable/view/adminhtml/web/variables.js
	 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Backend/view/adminhtml/web/js/bootstrap/editor.js#L7
	 */
	,'Magento_Variable/variables'
], function($, df, _, SimpleMDE) {return (
	/**
	 * @typedef {Object} Magento_Cms_Model_Wysiwyg_Config
	 * @property {Boolean} add_images
	 * @property {Boolean} add_variables
	 * @property {Boolean} add_widgets
	 * @property {String} files_browser_window_url
	 * @property {Object[]} plugins
	 * @property {?Number} store_id
	 * @property {String} widget_window_url
	 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Model/Wysiwyg/Config.php#L145-L198
	 * http://usejsdoc.org/tags-typedef.html#examples
	 */
	/**
	 * @param {Object} config
	 * @param {Magento_Cms_Model_Wysiwyg_Config} config.core
	 * @param {String} config.cssClass
	 * @param {String} config.id
	 * @param {String} config.mediaBaseURL
	 * @returns void
	 * http://stackoverflow.com/a/6460748
	 * https://code.google.com/p/jsdoc-toolkit/wiki/TagParam
	 */
	function(config) {
		debugger;
		hljs.initHighlightingOnLoad();
		/** @type {Magento_Cms_Model_Wysiwyg_Config} */
		var cc = config.core;
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
			 * 	simplemde.element.form.addEventListener("submit", function() {
					localStorage.setItem(simplemde.options.autosave.unique_id, "");
			 	});
			 *
			 * 2015-11-02
			 * Сделал unique_id: textarea.id + df.string.hash(location.href)
			 * чтобы редакторы разных объектов (например, разных самодельных страниц)
			 * имели разные идентификаторы.
			 */
			,autosave: {enabled: true, unique_id: textarea.id + df.string.hash(location.href)}
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
				// 2015-10-30
				// Замещаем {{media url="wysiwyg/528340.jpg"}} на реальный веб-адрес.
				markdown = markdown.replace(/\{\{media url="([^"]+)"}}/gm, config.mediaBaseURL + '$1');
				/** @type {Object} string => string */
				var widgetPlaceholders = cc['widget_placeholders'];
				if (widgetPlaceholders) {
					// Замещаем код виджетов пиктограммами (так же поступает и стандартный редактор)
					// https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/String/replace#Specifying_a_function_as_a_parameter
					// https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet#images
					markdown = markdown.replace(/\{\{widget type="([^"]+)"[^}]+}}/gm, function(match, type) {
						// Почему-то слэши в именах классов продублированы:
						// Magento\\CatalogWidget\\Block\\Product\\ProductsList
						return '![](' + widgetPlaceholders[type.replace(/\\\\/g, '\\')] + ')';
					});
				}
				return this.parent.markdown(markdown);
			}
			,toolbar: (function() {
				/** @type Array */
				var result = SimpleMDE.toolbar.slice();
				/**
				 * 2015-10-29
				 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Model/Wysiwyg/Config.php#L172-L181
				 */
				if (cc.add_images) {
					result[result.indexOf('image')] = {
						className: 'fa fa-picture-o'
						,name: 'image'
						,title: 'Insert Image (Ctrl+Alt+I)'
						/**
						 * 2015-10-29
						 * target_element_id:
						 * 1) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Block/Adminhtml/Wysiwyg/Images/Content.php#L160-L163
						 * 2) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Block/Adminhtml/Wysiwyg/Images/Content.php#L101
						 * 3) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js#L224-L232
						 * 4) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js#L185
						 * 5) https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/browser.js#L204-L208
						 */
						,action: function() {
							/** @type String */
							var url = cc.files_browser_window_url + 'target_element_id/' + config.id + '/';
							if (cc.store_id) {
								url += 'store/' + cc.store_id;
							}
							MediabrowserUtility.openDialog(url);
						}
					};
				}
				if (cc.add_variables) {
					// 2015-10-31
					// http://underscorejs.org/#findWhere
					/** @type {Object} */
					var pluginVariable = _.findWhere(cc.plugins, {name: 'magentovariable'}).options;
					result.push( {
						className: 'fa fa-at'
						,name: 'variable'
						,title: 'Insert Variable'
						/**
						 * 2015-10-31
						 * По аналогии с
						 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Variable/Model/Variable/Config.php#L49-L51
						 */
						,action: function() {MagentovariablePlugin.loadChooser(pluginVariable.url, config.id);}
					});
				}
				if (cc.add_widgets) {
					result.push( {
						className: 'fa fa-cogs'
						,name: 'widget'
						,title: 'Insert Widget'
						/**
						 * 2015-10-31
						 * По аналогии с
						 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/internal/Magento/Framework/Data/Form/Element/Editor.php#L256-L258
						 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js#L24
						 */
						,action: function() {
							widgetTools.openDialog(
								cc.widget_window_url + 'widget_target_id/' + config.id + '/'
							);
						}
					});
				}
				return result;
			})()
		});
		/**
		 * 2015-10-31
		 * http://codemirror.net/doc/manual.html#replaceSelection
		 * http://stackoverflow.com/a/23736834
		 * @param {String} newValue
		 * @returns void
		 */
		var replaceSelection = function(newValue) {editor.codemirror.doc.replaceSelection(newValue);};
		// https://learn.jquery.com/jquery-ui/widget-factory/extending-widgets/#extending-existing-methods
		$.widget('mage.mediabrowser', $.mage.mediabrowser, {
			insertAtCursor: function(element, value) {
				element.id === config.id ? replaceSelection(value) : this._superApply(arguments);
			}
		});
		/**
		 * 2015-10-31
		 * https://github.com/magento/magento2/blob/e3593aef4257c164fc1acd94b01c8c6ba8284989/app/code/Magento/Variable/view/adminhtml/web/variables.js#L121-L130
		 * @type {Function}
		 */
		var _insertVariable = MagentovariablePlugin.insertVariable;
		MagentovariablePlugin.insertVariable = function(value) {
			if (this.textareaId !== config.id) {
				_insertVariable.call(this, value);
			}
			else {
				Variables.closeDialogWindow();
				replaceSelection(value);
			}
		};
		/**
		 * 2015-10-31
		 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/wysiwyg/widget.js#L269-L277
		 * @type {Function}
		 */
		var widgetUpdateContent = WysiwygWidget.Widget.prototype.updateContent;
		WysiwygWidget.Widget.prototype.updateContent = function(value) {
			if (this.widgetTargetId !== config.id) {
				widgetUpdateContent.call(this, value);
			}
			else {
				replaceSelection(value);
			}
		};
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
	});
});