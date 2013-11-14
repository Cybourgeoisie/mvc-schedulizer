// Initialize
var app = app || {};

// Runs when everything is loaded
$(document).ready(function()
{
	// Log
	console.log('Document ready');

	// Create the router and start catching history
	var Router = new app.Router();
	Backbone.history.start();

	// Load the application view
	var applicationView = new app.ApplicationView({
		el:     $('#root'),
		Router: Router
	});
});

// Handle all AJAX errors
$(document).ajaxError(function(err)
{
	console.log('An error has occurred: ' + JSON.stringify(err));
});
