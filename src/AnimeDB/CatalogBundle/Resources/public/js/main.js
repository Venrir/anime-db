
var Form = {};
Form.Collection = {
	classes: {
		collection: 'b-col-r',
		button_add: 'b-col-add-item',
		button_remove: 'b-col-rm-item'
	},
	templates: {
		row: '<div class="f-row">__data__</div>',
		add: 'Add item',
		remove: 'Remove item'
	},

	init: function() {
		var fc = Form.Collection;
		// each collections
		$.each($('.'+fc.classes.collection), function() {
			var collection = $(this);
			collection.data('index', collection.find(':input').length);
			// add "remove" button
			var tpl = '<a href="#" class="'+fc.classes.button_remove+'">'+fc.templates.remove+'</a>';
			collection.children().append($(tpl));
			// add "add" button
			var tpl = '<a href="#" class="'+fc.classes.button_add+'">'+fc.templates.add+'</a>';
			collection.append($(fc.templates.row.replace(/__data__/, tpl)));
		});
		// add "remove" and "add" buttons
		$('.'+fc.classes.button_add).bind('click', fc.onAdd);
		$('.'+fc.classes.button_remove).bind('click', fc.orRemove);
	},
	onAdd: function(e) {
		e.preventDefault();
		var fc = Form.Collection;
		var collection = $(this).parent().parent();
		// increment index
		var index = collection.data('index');
		collection.data('index', index + 1);
		// prototype of new item
		var new_item = collection.data('prototype').replace(/__name__(label__)?/g, index);
		// remove button temaplte
		var tpl = '<a href="#" class="'+fc.classes.button_remove+'">'+fc.templates.remove+'</a>';
		new_item = $(new_item).append($(tpl).bind('click', fc.orRemove));
		// add new item
		collection.find('.'+fc.classes.button_add).parent().before(new_item);
	},
	orRemove: function(e) {
		e.preventDefault();
		$(this).parent().remove();
	}
};


$(function(){

// add button jQuery UI style
$('input:submit, input:button, input:reset, button, .catalog-last-added .details').button();

// resize content wrapper
var resizeContentWrapper = function() {
	var footer = $('#footer');
	var content = $('#content-wrapper').css('height', 'auto');
	console.log($(document).height());
	content.css('height', 
		$(document).height()
		- footer.height()
		- parseInt(footer.css('paddingTop'))
		- parseInt(footer.css('paddingBottom'))
		- $('#header').height()
		- parseInt(content.css('borderTop'))
	);
};
$(window).resize(resizeContentWrapper).load(resizeContentWrapper);

Form.Collection.init();

});