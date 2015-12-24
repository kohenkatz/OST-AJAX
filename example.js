function makeFormField(field_data) {
	var field;
	switch (field_data.type) {
		case 'hidden':
			field = document.createElement('input');
			field.type = 'hidden';
			break;
		case 'text':
			field = document.createElement('input');
			field.type = 'text';
			break;
		case 'choices':
			field = document.createElement('select');
			for (var choice_key in field_data.choices) {
				var option = document.createElement('option');
				option.value = choice_key;
				option.innerHTML = field_data.choices[choice_key];
				field.appendChild(option);
			}
			break;
	}
	if (field) {
		field.name = field_data.name;
		field.id = field_data.name;

		var container = document.createElement('div');
		container.classList.add('ost-form-row');
		var label = document.createElement('label');
		label.innerHTML = field_data.label;
		label.setAttribute('for', field.name);
		container.appendChild(label);
		container.appendChild(field);
		return container;
	}
	return;
}

var url = new URL('https://help.yadyehuda.org/ajax.php/ajax-form/open');
//url.searchParams.append('debug', 1);
url.searchParams.append('topics[]', 2);
url.searchParams.append('topics[]', 12);
url.searchParams.append('topics[]', 'Report a Problem / With a Specific Shiur');

fetch(url)
	.then(function(data) { return data.json(); })
	.then(function(data) {
		console.log(data);
		var form = document.createElement('form');
		form.method = data.method;
		form.action = data.submit_url;

		for (var group_id in data.form_groups) {
			var group = data.form_groups[group_id];
			var container;
			if (group.hidden) {
				container = document.createElement('div');
				if (group.id) {
					container.id = group.id;
				}
				container.style = 'position: absolute; left: -200%';
				form.appendChild(container);
			} else if (group.dynamic) {
				container = document.createElement('div');
				container.id = group.id;
				form.appendChild(container);
			} else {
				var container = document.createElement('fieldset');
				var legend = document.createElement('legend');
				legend.innerHTML = group.legend;
				container.appendChild(legend);
				container.id = 'ost_ajaxform_group_' + group_id;
				form.appendChild(container);
			}
			if (group.fields) {
				for (var field_number in group.fields) {
					var field_name = group.fields[field_number];
					console.log(field_name);
					console.log(data.form_fields[field_name]);
					if (data.form_fields[field_name]) {
						var field = makeFormField(data.form_fields[field_name]);
						if (field) {
							container.appendChild(field);
						}
					}
				}
			}
		};

		var container = document.getElementById('formcontainer');
		container.innerHTML = '';
		container.appendChild(form);
	});