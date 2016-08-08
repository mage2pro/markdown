// 2015-10-26
// Здесь надо использовать именно define, а не require.
// https://mage2.pro/t/146
define([
	'jquery'
	, 'df'
	, 'df-lodash'
	, 'Dfe_Markdown/SimpleMDE'
	, 'Df_Core/HighlightJs'
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
		/**
		 * 2016-03-01
		 * Отключаю на период отладки.
		 * @type {boolean}
		 */
		var enableAutoSave = true;
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
				: df.s.splice(name, index, config.suffixForCompiled)
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
		 * Сделал unique_id: textarea.id + df.s.hash(location.href)
		 * чтобы редакторы разных объектов (например, разных самодельных страниц)
		 * имели разные идентификаторы.
		 *
		 * Адрес может иметь вид
		 * http://site.com/admin/cms/page/new/#page_tabs_content_section_content
		 * Убираем часть адреса после #.
		 *
		 * 2015-11-05
		 * Раньше алгоритм был:
		 * unique_id: textarea.id + df.s.hash(location.href.split('#')[0])}
		 * Оказалось, что полагаться на адрес не совсем верно,
		 * потому что на странице редактирования товара адреса могут быть разными
		 * для одного и того же товара, например:
		 * http://site.com/admin/catalog/product/edit/id/1/set/4/
		 * http://site.com/admin/catalog/product/edit/id/1/set/4/back/edit/active_tab/autosettings/
		 */
		// 2015-11-12
		// Вынес вычисление идентификатора объекта в отдельное выражение,
		// чтобы задействовать локальное хранилище
		// только при ненулевом значени идентификатора объекта.
		// Это позволяет избежать проблемы, когда редактор для нового объекта
		// ошибочно заполняет себя значением от предыщущего нового объекта.
		/** @var int */
		var entityId = function() {
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
			return matches && matches[1] ? parseInt(matches[1]) : 0;
		}();
		/** @type {?String} */
		var localStorageId = entityId ? [textarea.id, config.action, entityId].join('-') : null;
		/**
		 * 2015-11-05
		 * Почему-то иногда получается, что в localStorage попадает пустое значение,
		 * хотя на самом деле содержимое редактора непусто.
		 * В такой ситуации удаляем ключ из localStorage,
		 * чтобы в редактор попало значение с сервера.
		 * http://stackoverflow.com/a/10710029
		 */
		if (localStorageId && !localStorage.getItem(localStorageId)) {
			localStorage.removeItem(localStorageId);
		}
		/**
		 * 2015-10-31
		 * По аналогии с
		 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/internal/Magento/Framework/Data/Form/Element/Editor.php#L256-L258
		 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/lib/web/mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js#L24
		 */
		var openWidgetDialog = function() {
			widgetTools.openDialog(
				cc.widget_window_url + 'widget_target_id/' + config.id + '/'
			);
		};
		var editor = new SimpleMDE({
			autofocus: true
			,autosave: {enabled: enableAutoSave && !!localStorageId, unique_id: localStorageId}
			,element: textarea
			/**
			 * 2015-11-02
			 * Проверка правописания для английского языка (не говоря уже о других)
			 * работает слабовато: например, выделяет красным слово GitHub.
			 * https://github.com/NextStepWebs/simplemde-markdown-editor/blob/0e6e46634610eab43a374389a757e680021fd6a5/src/js/simplemde.js#L912
			 *
			 * По хорошему, надо сделать интеграцию с Grammarly:
			 * https://code.dmitry-fedyuk.com/m2m/plan/issues/33
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
				/**
				 * 2016-02-01
				 * Раньше тут стояло:
				 * var result = SimpleMDE.toolbar.slice();
				 * В версии 1.10.0 свойство SimpleMDE.toolbar пропало.
				 * В версии 1.8.1 оно было:
				 * https://github.com/NextStepWebs/simplemde-markdown-editor/blob/1.8.1/debug/simplemde.debug.js#L13617
				 */
				/** @type Array */
				var result = ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'image', '|', 'preview', 'side-by-side', 'fullscreen', 'guide'];
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
					// https://lodash.com/docs#find
					/** @type {Object} */
					var pluginVariable = _.find(cc.plugins, {name: 'magentovariable'}).options;
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
						,action: openWidgetDialog
					});
				}
				return result;
			})()
		});
		var cm = editor.codemirror;
		/**
		 * 2016-02-01
		 * @return {Object}
		 */
		var getTextBeforeAndAfterTheCursor = function() {
			/**
			 * @param {String[]} lines
			 * @param {Object} pos
			 * @returns {String}
			 */
			var getTextFromBeginToCursor = function(lines, pos) {
				var fullLines = !pos.line ? [] : lines.slice(0, pos.line);
				if (pos.ch) {
					fullLines.push(lines[pos.line].substring(0, pos.ch));
				}
				return fullLines.join("\n");
			};
			var before = getTextFromBeginToCursor(cm.getValue().split("\n"), cm.getCursor());
			var after = cm.getValue().substring(before.length);
			return {before: before, after: after}
		};
		/**
		 * 2016-02-01
		 * @return {Boolean}
		 */
		var areTheCursorOnAWidget = function() {
			var ba = getTextBeforeAndAfterTheCursor();
			var startTag = '{{';
			var endTag = '}}';
			var indexOfStartTagInAfter = ba.after.indexOf(startTag);
			return (
				ba.before.lastIndexOf(startTag) > ba.before.lastIndexOf(endTag)
				&& (-1 === indexOfStartTagInAfter || indexOfStartTagInAfter > ba.after.indexOf(endTag))
			);
		};
		(function() {
			var $widgetButton = $(editor.toolbarElements['widget']);
			cm.on('cursorActivity', function() {
				$widgetButton.toggleClass('active', areTheCursorOnAWidget());
			});
		})();
		(function() {
			cm.on('dblclick', function() {});
		})();
		/**
		 * 2016-02-01
		 * Сначала хотел использовать cm.on('dblclick', function() {});
		 * однако в режиме preview события CodeMirror не работают,
		 * поэтому используем для их отлова jQuery.
		 */
		(function() {
			var $wrapper = $(cm.getWrapperElement());
			/**
			 * 2016-02-01
			 * По аналогии с https://github.com/NextStepWebs/simplemde-markdown-editor/blob/1.10.0/src/js/simplemde.js#L460-L465
			 */
			var $preview = $wrapper.children('.editor-preview');
			if (!$preview.length) {
				//noinspection ReuseOfLocalVariableJS
				$preview = $('<div>').addClass('editor-preview');
				$wrapper.append($preview)
			}
			$preview.dblclick(function(event) {
				var el = event.toElement;
				/**
				 * 2016-02-01
				 * https://github.com/NextStepWebs/simplemde-markdown-editor/commit/efc38f43dabf84345fa60811373473862d7a693d
				 * https://github.com/NextStepWebs/simplemde-markdown-editor#useful-methods
				 * https://github.com/NextStepWebs/simplemde-markdown-editor/blob/1.10.0/src/js/simplemde.js#L1619-L1625
				 */
				if (editor.isPreviewActive()
					&& el
					&& 'IMG' === el.tagName.toUpperCase()
					&& el.src
					&& -1 < el.src.indexOf('Magento_CatalogWidget')
				) {
					/**
					 * 2016-02-01
					 * Пока эта функциональность не доделана из-за дефекта ядра:
					 * «Bug: double click on a widget inside TinyMCE editor
					 * does not edit the widget but creates a new one»
					 * https://mage2.pro/t/157
					 * https://github.com/magento/magento2/issues/2238
					 */
					openWidgetDialog();
				}
			});
		})();
		/**
		 * 2015-10-31
		 * http://codemirror.net/doc/manual.html#replaceSelection
		 * http://stackoverflow.com/a/23736834
		 * @param {String} newValue
		 * @returns void
		 */
		var replaceSelection = function(newValue) {cm.doc.replaceSelection(newValue);};
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
						cm.refresh();
					}
				});
			}
		}
		// 2016-02-01
		// Грязный хак.
		// Без него положение курсора при переносе строк обрабатывается некорректно.
		$textarea
			.closest('.admin__collapsible-content')
			.siblings('.fieldset-wrapper-title')
			.click(function() {setTimeout(function() {cm.refresh();}, 500);})
		;
		/**
		 * 2016-02-03
		 * Компилируем Markdown в HTML перед отправкой формы на сервер.
		 */
		var prepareForSubmission = function() {
			/** @type {String} */
			var content = editor.value();
			$textarea.val(content);
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
			var widgets = {};
			var medias = {};
			//noinspection JSCheckFunctionSignatures
			content = content.replace(regex.widget, function(widget) {
				/** @type {Number} */
				var hash = df.s.hash(widget);
				widgets[hash] = widget;
				return 'widget-{' + hash + '}';
			});
			content = content.replace(regex.media, function(media) {
				/** @type {Number} */
				var hash = df.s.hash(media);
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
		};
		/** @type {jQuery} HTMLFormElement */
		var $form = $textarea.closest('form');
		/**
		 * 2015-11-02
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
		$form.bind('beforeSubmit', prepareForSubmission);
		// 2015-11-11
		// Вообще говоря, редактор заявляет, что делает это сам,
		// но у меня почему-то он порой (а может и всегда?) это не делает.
		// Сбрасывать localStorage надо именно на submit, а не на beforeSubmit,
		// чтобы сначала сработали валидаторы, а то вдруг валидация не пройдёт,
		// а localStorage уже уничтожен.
		var resetLocalStorage = function() {
			if (localStorageId) {
				localStorage.removeItem(localStorageId);
			}
		};
		$form.submit(resetLocalStorage);
		/**
		 * 2016-02-03
		 * В свежих версиях Magento 2
		 * административные экраны самодельных страниц и блоков реализованы как UI Components,
		 * там нет тега <form>, и поэтому используемый выше метод
		 * $form.bind('beforeSubmit', ...); не сработает.
		 * Обрабатываем вместо этого своё событие.
		 */
		/**
		 * 2016-02-21
		 * И административные интерфейсы товаров
		 * теперь тоже реализованы посредством UI Components.
		 */
		$(window).bind('dfe.markdown.beforeValidation', function(event, data) {
			prepareForSubmission();
			var target = data.product ? data.product : data;
			target[$contentCompiled.attr('name')] = $contentCompiled.val();
			target[$textarea.attr('name')] = $textarea.val();
		});
		$(window).bind('dfe.markdown.afterValidation', resetLocalStorage);
	});
});