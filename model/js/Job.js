app.Job = Backbone.Model.extend
({
	// Set the ID attribute
	idAttribute: 'job_id',

	// Default values
	defaults: {
		name:     null,
		Employee: []
	},
	
	// URL Root
	urlRoot: GATEWAY_URL + '?action=Job::REST',
	//urlRoot: 'job',
	
	initialize: function()
	{
		this.initEvents();
	},

	initEvents: function() { }
});
