$( function() {
	$( "#sortable" ).sortable({
		axis: "y"
	});
	$( "#sortable" ).disableSelection();

	$("body").on("click", ".add-element", function() {
		source = $(this).data("source");
		destination = $(this).data("destination");
		sourceCopy = $(source).clone(true);
		sourceCopy.removeClass("hidden");
		sourceCopy.appendTo(destination);
	});

	$("body").on("click", ".remove-element", function() {
		element = $(this).closest(".movable-element");
		fieldId = element.data("field-id");
		console.log(fieldId);
		$('<input>').attr({
			type: 'hidden',
			name: 'remove-field-ids[]',
			value: fieldId
		}).appendTo( $(this).closest("form") );
		element.remove();

	});
} );
