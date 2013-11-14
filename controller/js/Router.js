app.Router = Backbone.Router.extend
({
	// Route URL requests
	routes: {
		// Employees
		"employee"     : "loadEmployee",
		"employee/:id" : "getEmployee",

		// Calendar
		"calendar" : "loadCalendar",

		// Jobs
		"job" : "loadJob",

		// Default
		"*other" : "defaultRoute"
	},

	getEmployee: function(id)
	{
		console.log("You are trying to reach employee " + id);
	},

	loadEmployee: function(id)
	{
		console.log("You are trying to reach the employee view!");
	},

	loadCalendar: function()
	{
		console.log('You are trying to reach the calendar view!');
	},

	loadJob: function()
	{
		console.log('You are trying to reach the job view!');
	},

	defaultRoute: function(other)
	{
		console.log('Invalid. You attempted to reach:' + other);
	}
});
