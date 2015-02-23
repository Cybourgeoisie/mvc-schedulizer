app.EmployeeView = Backbone.View.extend
({
	tplPath: VIEW_PATH + 'html/EmployeeView.html',

	initialize: function()
	{
		// Get the template
		$.get(this.tplPath, $.proxy(this.initPage, this));
	},

	initPage: function(pageData)
	{
		// Render the page
		this.renderTemplate(pageData);

		// Initialize event handlers
		this.initEvents();
	},

	initEvents: function()
	{

	},

	/**
	 * Render
	 */
	renderTemplate: function(data)
	{
		this.tpl = _.template(data);
		this.render();
	},

	// Render the title of the todo item.
	render: function()
	{
		this.$el.html(this.tpl(this.model.toJSON()));
		return this;
	}
});
