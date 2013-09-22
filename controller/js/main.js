$(document).ready(function()
{
	// Log
	console.log('Document ready');

	// Create the app global object
	var app = {};

	// Create the router and start catching history
	app.Router = new Router();
	Backbone.history.start();

	// Start the application
	//app.ApplicationView = new ApplicationView();

	var e  = new Employee({name:'Rachel'});
	var eV = new EmployeeView({model: e, el: $('#root')});
	e.fetch({data: {id: 2}});
});
