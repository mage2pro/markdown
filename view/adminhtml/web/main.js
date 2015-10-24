require(['jquery', 'Dfe_Markdown_SimpleMDE'], function($, SimpleMDE) {$(function() {
	//console.log(SimpleMDE);
	/** @type {jQuery} HTMLTextAreaElement */
	var $textarea = $('#description');
	$textarea.wrap("<div class='dfe-markdown'></div>");
	var simplemde = new SimpleMDE({element: $textarea.get(0)});
});});