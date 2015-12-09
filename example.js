$.ajax('https://help.example.com/ajax.php/ajax-form/open', {
	dataType: 'json',
	jsonp: false
})
	.done(function(data) {
		console.log(data);
		var form = document.createElement('form');
		form.method = data.method;
		form.action = data.submit_url;

		for (var group_id in data.form_groups) {
			var group = data.form_groups[group_id];
			if (group.hidden) {
			} else if (group.dynamic) {
				var placeholder = document.createElement('div');
				placeholder.id = group.id;
				form.appendChild(placeholder);
			} else {
				var fieldset = document.createElement('fieldset');
				var legend = document.createElement('legend');
				legend.innerHTML = group.legend;
				fieldset.appendChild(legend);
				fieldset.id = 'ajaxform_group_' + group_id;
				form.appendChild(fieldset);
			}
		};

		var container = document.getElementById('formcontainer');
		container.innerHTML = '';
		container.appendChild(form);
	});