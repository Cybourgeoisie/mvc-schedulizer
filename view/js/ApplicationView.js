app.ApplicationView = Backbone.View.extend
({
	tplPath: VIEW_PATH + 'html/ApplicationView.html',

	// Keep track of the controllers
	Router             : null,
	EmployeeController : null,
	JobController      : null,
	CalendarController : null,

	initialize: function(config)
	{
		// Set configurations
		this.Router = config.Router || null;

		// Get the template
		$.get(this.tplPath, $.proxy(this.initPage, this));
	},

	initPage: function(pageData)
	{
		// Render the page
		this.renderTemplate(pageData);

		// Load a page
		this.loadEmployeePage();

		// Initialize event handlers
		this.initEvents();
	},

	initEvents: function()
	{
		// Hook into Router events
		if (this.Router)
		{
			this.Router.on('route:loadCalendar', $.proxy(this.loadCalendarPage), this);
			this.Router.on('route:loadEmployee', $.proxy(this.loadEmployeePage), this);
			this.Router.on('route:loadJob',      $.proxy(this.loadJobPage),      this);
		}
	},

/**
 * Load Pages
 **/
 	loadCalendarPage: function()
 	{
 		// Load the calendar page
 		this.CalendarView = new app.CalendarView({
			el: $('#page')
		});
 	},

 	loadEmployeePage: function()
 	{
 		// Load the employee page
		this.EmployeeControllerView = new app.EmployeeControllerView({
			el: $('#page')
		});
 	},

 	loadJobPage: function()
 	{
 		// Load the job page
		this.JobControllerView = new app.JobControllerView({
			el: $('#page')
		});
 	},

/**
 * Render
 **/
	renderTemplate: function(data)
	{
		this.tpl = _.template(data);
		this.render();
	},

	// Render the title of the todo item.
	render: function()
	{
		this.$el.html(this.tpl());
		return this;
	}
});
