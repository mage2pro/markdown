// 2015-10-26
// Здесь надо использовать именно define, а не require.
// https://mage2.pro/t/146
define([
	'jquery', 'SimpleMDE', 'HighlightJs'
], function($, SimpleMDE, HighlightJs) {return function(config) {
	hljs.initHighlightingOnLoad();
	/** @type HTMLTextAreaElement|Element */
	var textarea = document.getElementById(config.id);
	// Добавление класса CSS позволяет нам задать разную высоту редактора
	// для описания и краткого описания товара.
	$(textarea).wrap($("<div class='dfe-markdown'></div>").addClass(config.id));
	new SimpleMDE({
		autofocus: true
		,autosave: {enabled: true, unique_id: textarea.id}
		,element: textarea
		,renderingConfig: {codeSyntaxHighlighting: true}
		,tabSize: 4
	});
};});