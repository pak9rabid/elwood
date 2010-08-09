/* Result after calling .toggleElwoodPopup() to create a popup:
 
<div id="elwoodHideShow">
	<div id="elwoodFade"></div>
	<div id="elwoodPopupBlock">
		<div id="popup">
			// content to popup
		</div>
	</div>
</div>

*/

(function($)
{
	$.fn.openElwoodPopup = function()
	{
		if ($("#elwoodPopupBlock").size() == 0)
		{
			var popup = this.first();
				
			// create wrapping divs for popup
			popup
				.wrap("<div id='elwoodPopupBlock'></div>")
			.parent()
				.wrap("<div id='elwoodHideShow'></div>")
				.before("<div id='elwoodFade'></div>");
		
			// make popup visible
			popup
				.show()
			.parent().parent()
				.show();
		
			// center popup
			var top  = ($(window).height() - popup.parent().outerHeight()) / 4 + $(window).scrollTop();
			var left = (($(window).width() - popup.parent().outerWidth()) / 2 + $(window).scrollLeft());
		
			popup.parent()
				.css("top",  top + "px")
				.css("left", left + "px");
		}
			
		return this;
	};
		
	$.fn.closeElwoodPopup = function()
	{
		if ($("#elwoodPopupBlock").size() > 0)
		{
			var popup = this.first();
			
			// make popup invisible
			popup
				.hide()
			.parent().parent()
				.hide();
			
			// remove wrapping divs for popup
			popup.parent().parent().children("#elwoodFade").remove();
			popup.parent().unwrap();
			popup.unwrap();
		}
			
		return this;
	};
	
	$.initElwoodPopups = function()
	{
		// make all popup elements invisible
		$(".elwoodPopup").each(function()
		{
			$(this).hide();
		});
	};
	
})(jQuery);