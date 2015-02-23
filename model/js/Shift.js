app.Shift = Backbone.Model.extend
({
	// Set the ID attribute
	idAttribute: 'shift_id',

	// Default values
	defaults: {
		name:     null,
		job_id:   null,
		start:    null,
		end:      null,
		Employee: [],
		Job:      null
	},
	
	// URL Root
	urlRoot: GATEWAY_URL + '?action=Shift::REST',
	//urlRoot: 'shift',
	
	initialize: function()
	{
		this.initEvents();
	},

	initEvents: function() { }
});
