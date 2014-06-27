// options page
jQuery(document).ready(function($) {
	$('#loadDefaultCSS').click(function() {
		$('#custom-css').slideToggle();
	});
});

// posts
jQuery(document).ready(function($){
	$('#customPostTypesToUse').click(function(){
		$('.customPostTypesToUse').slideToggle();
	});
});