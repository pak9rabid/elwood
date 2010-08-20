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
		var popup = this.first();
		var zFade = 50;
		var zPopupBlock = 100;
		
		// check for any other popups and set z-index values accordingly
		if ($(".elwoodPopupBlock").length > 0)
		{
			zFade = parseInt($(".elwoodPopupBlock").last().css("z-index")) + 50;
			zPopupBlock = zFade + 50;
		}
				
		// create wrapping divs for popup
		popup
			.wrap("<div class='elwoodPopupBlock' style='z-index: " + zPopupBlock + ";'></div>")
		.parent()
			.wrap("<div class='elwoodHideShow'></div>")
			.before("<div class='elwoodFade' style='z-index: " + zFade + ";'></div>");
		
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
			
		return this;
	};
		
	$.fn.closeElwoodPopup = function()
	{
		if ($(".elwoodPopupBlock").size() > 0)
		{
			var popup = this.first();
			
			// make popup invisible
			popup
				.hide()
			.parent().parent()
				.hide();
			
			// remove wrapping divs for popup
			popup.parent().parent().children(".elwoodFade").remove();
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