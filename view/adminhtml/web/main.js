require([
	'jquery', 'SimpleMDE', 'HighlightJs'
], function($, SimpleMDE, HighlightJs) {$(function() {
	hljs.initHighlightingOnLoad();
	/** @type {jQuery} HTMLTextAreaElement */
	var $textarea = $('#description');
	$textarea.wrap("<div class='dfe-markdown'></div>");
	/** @type {SimpleMDE} */
	var editor = new SimpleMDE({
		autofocus: true
		,autosave: {
			enabled: true
			,unique_id: $textarea.get(0).id
		}
		,element: $textarea.get(0)
		,renderingConfig: {
			codeSyntaxHighlighting: true
		}
		,tabSize: 4
	});
});});