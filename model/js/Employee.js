var Employee = Backbone.Model.extend
({
	// Default values
	defaults: {
		employee_id: null,
		name:        null
	},
	
	// URL Root
	url: GATEWAY_URL + '?action=Employee::find',
	
	initialize: function()
	{
		console.log('An employee has been initialized.');

		this.initEvents();
	},

	initEvents: function()
	{
		this.on("invalid", function(model, error){ console.log(error); });
	},

	validate: function(attributes)
	{
		if (attributes.name === undefined || attributes.name == null)
		{
			return "An employee must have a name";
		}
	}
});
