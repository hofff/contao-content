(function($) {
	window.addEvent("domready", function() {
		if(!window.Hofff || !Hofff.Selectri) return;

		$(document.body).addEvent("click:relay(a.hofff-content-edit)", function(event, $target) {
			event.preventDefault();

			Backend.openModalIframe({
				width: 768,
				title: $target.get("data-title"),
				url: $target.get("href") + "&popup=1&nb=1"
			});
		});
		
		Hofff.Selectri.scan();
		
		$$(".hofff-selectri-widget.hofff-content").each(function(selectri) {
			selectri = new Hofff.Selectri(selectri);
			selectri.selection.getElements(".hofff-content-html").each(function($html) {
				$html.getParent().set("html", $html.get("data-hofff-content-html"));
			});
			selectri.addEvent("selected", function(key) {
				$html = selectri.getNode(selectri.selection, key).getElement(".hofff-content-html");
				$html.getParent().set("html", $html.get("data-hofff-content-html"));
			});
		});
	});
})(document.id);
