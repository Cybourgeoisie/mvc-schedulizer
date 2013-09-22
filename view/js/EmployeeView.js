var EmployeeView = Backbone.View.extend
({
	tagName:  'div',

	// Cache the template function for a single item.
	tpl: _.template("An example template for each employee, like my name is <%= name %>"),

	initialize: function()
	{
		console.log('Created employee view');
		this.render();
	},

	// Render the title of the todo item.
	render: function()
	{
		this.$el.html(this.tpl(this.model.toJSON()));
		return this;
	}
});
