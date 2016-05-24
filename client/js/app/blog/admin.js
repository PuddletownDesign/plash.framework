$(function(){
	
	var $textarea = $('textarea#text');
	
	if ($textarea.length > 0) {
		var text = $textarea.text();

		//set up wmd html
		var wmdHtml = '<div class="wmd-panel">' + 
	        '<div id="wmd-button-bar"></div>' + 
	        '<textarea class="wmd-input" id="wmd-input" name="text">'+text+'</textarea>' + 
	        '</div><br><br>' + 
	    '<div id="wmd-preview" class="wmd-panel wmd-preview"></div>';

		//replace the existing text areas
		$textarea.replaceWith(wmdHtml);

		//make and run the converted
		var converter = new Markdown.Converter();

	    converter.hooks.chain("preConversion", function (text) {
	        return text.replace(/\b(a\w*)/gi, "*$1*");
	    });

	    converter.hooks.chain("plainLinkText", function (url) {
	        return "This is a link to " + url.replace(/^https?:\/\//, "");
	    });

	    var help = function () { alert("Do you need help?"); }

	    var editor = new Markdown.Editor(converter, null, { handler: help });

	    editor.run();
	}
	
	
});