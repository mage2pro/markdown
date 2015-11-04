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
	 * @param {String} config.action
	 * @param {Magento_Cms_Model_Wysiwyg_Config} config.core
	 * @param {String} config.cssClass
	 * @param {String} config.id
	 * @param {String} config.mediaBaseURL
	 * @param {String} config.suffixForCompiled
	 * @returns void
	 * http://stackoverflow.com/a/6460748
	 * https://code.google.com/p/jsdoc-toolkit/wiki/TagParam
	 */
	function(config) {
		hljs.initHighlightingOnLoad();
		/** @type {Magento_Cms_Model_Wysiwyg_Config} */
		var cc = config.core;
		/** @type HTMLTextAreaElement|Element */
		var textarea = document.getElementById(config.id);
		/** @type {jQuery} HTMLTextAreaElement */
		var $textarea = $(textarea);
		/** @type {jQuery} HTMLInputElement */
		var $contentCompiled = $("<input type='hidden'/>");
		// 2015-11-02
		// Не забывайте, что в одной форме может быть сразу несколько редакторов
		// (например, такова ситуация при редактировании товара, где редакторов 2:
		// для «description» и для «short_description»),
		// поэтому нам нужно сделать имя скрытого поля заведомо уникальным
		// и в то же время вычисляемым по имени поля редактора.
		$contentCompiled.attr('name', function() {
			/** @type {String} */
			var name = $textarea.attr('name');
			// 2015-11-04
			// На странице товара поля редактора имеют имена вида
			// product[description] и product[short_description],
			// и для таких имён неправильно добавлять наш суффикс в конце имени:
			// надо добавлять суффикс в конце внутренней части имени, которая в скобках.
			//
			// Используем именно lastIndexOf на случай сложных имён типа product[area1][area2].
			/** @type {Number} */
			var index = name.lastIndexOf(']');
			return (
				-1 === index
				? name + config.suffixForCompiled
				: df.string.splice(name, index, config.suffixForCompiled)
			);
		}());
		$textarea.after($contentCompiled);
		// Добавление класса CSS позволяет нам задать разную высоту редактора
		// для описания и краткого описания товара.
		$textarea.wrap($("<div class='dfe-markdown'></div>").addClass(config.cssClass));
		/** @type {Object} */
		var regex = {
			media: /\{\{media url="([^"]+)"}}/gm
			,widget: /\{\{widget type="([^"]+)"[^}]+}}/gm
		};
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
			 *
			 * Адрес может иметь вид
			 * http://site.com/admin/cms/page/new/#page_tabs_content_section_content
			 * Убираем часть адреса после #.
			 *
			 * 2015-11-05
			 * Раньше алгоритм был:
			 * unique_id: textarea.id + df.string.hash(location.href.split('#')[0])}
			 * Оказалось, что полагаться на адрес не совсем верно,
			 * потому что на странице редактирования товара адреса могут быть разными
			 * для одного и того же товара, например:
			 * http://site.com/admin/catalog/product/edit/id/1/set/4/
			 * http://site.com/admin/catalog/product/edit/id/1/set/4/back/edit/active_tab/autosettings/
			 */
			,autosave: {
				enabled: true
				, unique_id: [
					textarea.id
					, config.action
					, function() {
						/** @type {?Array} */
						var matches = location.href.match(/\/(?:id|block_id|page_id)\/(\d+)/);
						// 2015-11-04
						// JavaScript вполне позволяет обращения к несуществующим индексам массива,
						// просто при преобразовании undefined к строке получается не пустая строка,
						// а строка «undefined».
						//
						// Если строка не соответствует регулярному выражению,
						// то .match возвращает null:
						// https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/String/match#Return_value
						return matches && 1 < matches.length ? matches[1] : 0;
					}()
				].join('-')
			}
			,element: textarea
			/**
			 * 2015-11-02
			 * Проверка правописания для английского языка (не говоря уже о других)
			 * работает слабовато: например, выделяет красным слово GitHub.
			 * https://github.com/NextStepWebs/simplemde-markdown-editor/blob/0e6e46634610eab43a374389a757e680021fd6a5/src/js/simplemde.js#L912
			 *
			 * По хорошему, надо сделать интеграцию с Grammarly:
			 * http://code.dmitry-fedyuk.com/m2m/plan/issues/33
			 */
			,spellChecker: false
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
				markdown = markdown.replace(regex.media, config.mediaBaseURL + '$1');
				/** @type {Object} string => string */
				var widgetPlaceholders = cc['widget_placeholders'];
				if (widgetPlaceholders) {
					// Замещаем код виджетов пиктограммами (так же поступает и стандартный редактор)
					// https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/String/replace#Specifying_a_function_as_a_parameter
					// https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet#images
					markdown = markdown.replace(regex.widget, function(match, type) {
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
		// 2015-11-04
		// Левое меню-гармошка.
		// Раньше искал его по идентификатору #page_tabs,
		// однако гармошка имеет этот идентификатор только на экране самодельной страницы,
		// а на экране товара идентификатор у гармошки другой.
		var $tabs = $('.ui-tabs');
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
		/**
		 * 2015-11-02
		 * Компилируем Markdown в HTML перед отправкой формы на сервер.
		 * Нам надо подписываться именно на событие beforeSubmit:
		 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/lib/web/mage/backend/form.js#L194-L196
			if (false !== this._beforeSubmit(e.type, data)) {
				this.element.trigger('submit', e);
			}
		 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/lib/web/mage/backend/form.js#L173-L185
		 * Если вместо beforeSubmit делать нашу обработку на submit, то валидатор сработает раньше,
		 * и тогда валидатор скажет, что наш textarea пусть,
		 * потому что наш редактор не обновляет textarea автоматически.
		 * https://mage2.pro/t/158
		 */
		$textarea.closest('form').bind('beforeSubmit', function() {
			$textarea.val(editor.value());
			// По аналогии с https://github.com/NextStepWebs/simplemde-markdown-editor/blob/0e6e46634610eab43a374389a757e680021fd6a5/src/js/simplemde.js#L355
			// Наверное, можно использовать и $textarea.val()
			//
			// 2015-11-04
			// Раньше использовал тут код: editor.options.previewRender(editor.value())
			// Этот код ошибочен, потому что editor.options.previewRender
			// отрисовывает виджеты картинками,
			// а нам на сервер нужно передать виджеты в первозданном виде: в виде кода.
			//
			// editor.options.parent.markdown херит нам код виджетов и медиа, например:
			// {{widget type=&quot;Magento\CatalogWidget\Block\Product\ProductsList&quot; display_type=&quot;all_products&quot;}}
			/** @type {String} */
			var content = editor.value();
			var widgets = {};
			var medias = {};
			content = content.replace(regex.widget, function(widget) {
				/** @type {Number} */
				var hash = df.string.hash(widget);
				widgets[hash] = widget;
				return 'widget-{' + hash + '}';
			});
			content = content.replace(regex.media, function(media) {
				/** @type {Number} */
				var hash = df.string.hash(media);
				medias[hash] = media;
				return 'media-{' + hash + '}';
			});
			content = editor.options.parent.markdown(content);
			content = content.replace(/widget\-{([^}]+)}/, function(match, hash) {
				return widgets[hash];
			});
			content = content.replace(/media\-{([^}]+)}/, function(match, hash) {
				return medias[hash];
			});
			$contentCompiled.val(content);
		});
	});
});