"use strict";

/* Parse latitude and longitude from Google Maps URL */
jQuery(document).ready(function($) {
	$('.parse-gmaps-url').click(function() {
		var url = $(this).siblings('input[type="text"]').val();
		var parsed = parseUri(url);
		var ll = parsed.queryKey.ll;
		var ll_split = ll.split(',');

		var form = $(this).closest('.location-form');
		form.find('input[name="location-latitude"]').val(ll_split[0]);
		form.find('input[name="location-longitude"]').val(ll_split[1]);
	});
});

/* Add time picker widgets to date fields */
jQuery(document).ready(function($) {
	$('.form-date').datetimepicker({
								dateFormat: "dd/mm/yy",
								defaultDate: $(this).val()});
});

/* Show edit form when clicking on 'edit' */
jQuery(document).ready(function($) {
	$("#location-history table a.edit").click(function(evt) {
		evt.preventDefault();
		$(this).closest("tr").next("tr.edit-row").toggleClass("hidden");
	});
});

jQuery(document).ready(function($) {
	/* Edit action */
	$("#location-history tr.edit-row form").submit(function(evt) {
		evt.preventDefault();
		var form = $(this);
		var label = $(this).find('input[name="location-label"]').val();
		var date = $(this).find('input[name="location-date"]').val();
		var latitude = $(this).find('input[name="location-latitude"]').val();
		var longitude = $(this).find('input[name="location-longitude"]').val();
		var data = {
			action: 'edit_location',
			id: $(this).find('input[name="location-id"]').val(),
			label: label,
			date: date,
			latitude: latitude,
			longitude: longitude,
		};
		$.post(ajaxurl,
				data,
				function(response) {
					/* Update the row */
					if (response) {
						var row = form.closest('tr.edit-row').prev('tr');
						row.find('td.location-label').text(label);
						row.find('td.location-date').text(date);
						row.find('td.location-latitude').text(latitude);
						row.find('td.location-longitude').text(longitude);
						form.closest('tr.edit-row').addClass('hidden');
					}
				}
		);
	});

	/* Delete action */
	$("#location-history a.delete.action").click(function(evt) {
		evt.preventDefault();

		var link = $(this);
		var label = $(this).closest('td').siblings('td.location-label').text();
		var answer = confirm("Are you sure you want to delete the location '" + label + "'?");

		if (answer) {
			var data = {
				action: 'delete_location',
				id: $(this).closest('tr').data('id')
			};
			$.post(ajaxurl,
				   data,
				   function(response) {
					   /* Delete the row from the DOM */
					   if (response) {
						   var row = link.closest('tr');
						   var editrow = row.next('tr.edit-row');
						   row.remove();
						   editrow.remove();
					   }
				   }
			);
		}
	});
});
