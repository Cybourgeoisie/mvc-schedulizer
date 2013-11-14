app.Employee = Backbone.Model.extend
({
	// Set the ID attribute
	idAttribute: 'employee_id',

	// Default values
	defaults: {
		name:         null,
		availability: {},
		Job:          []
	},
	
	// URL Root
	urlRoot: GATEWAY_URL + '?action=Employee::REST',
	//urlRoot: 'employee',
	
	initialize: function()
	{
		console.log('An employee has been initialized.');

		this.initEvents();
	},

	initEvents: function()
	{
		this.on('invalid', function(model, error){ console.log(error); });
	},

	parse: function(response)
	{
		if (response.availability)
		{
			response.availability = JSON.parse(response.availability);
		}

		return response;
	},

	validate: function(attributes)
	{
		if (attributes.name === undefined || attributes.name == null)
		{
			return "An employee must have a name";
		}
	}
});
