app.JobControllerView = Backbone.View.extend
({
	tplPath: VIEW_PATH + 'html/Job.html',

	// Keep track of the data
	JobCollection : [],

	events: {
        ".job-list ul li a": "viewJob"
    },

	initialize: function()
	{
		// Get the template
		$.get(this.tplPath, $.proxy(this.initPage, this));
	},

	initPage: function(pageData)
	{
		// Render the page
		this.renderTemplate(pageData);

		// Prepare the job collection
		this.JobCollection = new app.JobCollection({});

		// Initialize event handlers
		this.initEvents();

		// Fetch the job data
		this.JobCollection.fetch({reset: true});
	},

	initEvents: function()
	{
		// Populate the job list
		this.listenTo(this.JobCollection, 'reset', this.populateJobList);

		// Populate the form
		this.$('.job-list').on('click', 'a', $.proxy(this.viewJob, this));

		// Submit the form
		this.$('.job-form button.job-form-submit').on('click', $.proxy(this.saveJob, this));

		// Delete the job
		this.$('.job-form button.job-delete').on('click', $.proxy(this.deleteJob, this));
	},

	populateJobList: function()
	{
		// Reset the list, add each job
		this.$('.job-list ul').html('');
		this.JobCollection.each(this.addJobToList, this);
	},

	addJobToList: function(job)
	{
		// Append the job to the list
		this.$('.job-list ul').append($('<li>')
			.append($('<a>')
				.html(job.get('name'))
				.data('job', job)
			));
	},

	viewJob: function(el)
	{
		// Reset the form
		this.resetForm();

		// Get the job
		var job = $(el.currentTarget).data('job');

		// If no job, bail
		if (!job) { return; }

		// Set the values with this job
		this.$('.job-form input#job-name').val(job.get('name'));
		this.$('.job-form input#job-id').val(job.get('job_id'));

		// Set the "Add Job" button to "Edit Job"
		this.$('.job-form button.job-form-submit').html('Save Job');

		// Show the Delete button
		this.$('.job-form button.job-delete').show();
	},

	saveJob: function()
	{
		// Check for an job id
		var job_id = this.$('.job-form #job-id').val();

		// Get the job
		if (job_id)
		{
			var job = this.JobCollection.get(job_id);
		}
		// Create a new job
		else
		{
			var job = new app.Job({});
		}

		// Set the new values
		job.set('name', this.$('.job-form #job-name').val());

		// Save this job
		job.save(null, {
			'success' : $.proxy(this.reloadJobs, this)
		});
	},

	deleteJob: function()
	{
		// Check for an job id
		var job_id = this.$('.job-form #job-id').val();
		if (!job_id) { return; }

		// Get the job from the collection
		var job = this.JobCollection.get(job_id);

		// Delete the job
		job.destroy({
			'success' : $.proxy(this.reloadJobs, this)
		});
	},

	reloadJobs: function()
	{
		// Reset the form, fetch the jobs
		this.resetForm();
		this.JobCollection.fetch({reset: true});
	},

	resetForm: function()
	{
		// Reset all input fields
		this.$('.job-form form input').val('');
	
		// Set the button to "Add Job"
		this.$('.job-form button.job-form-submit').html('Add Job');

		// Hide the Delete button
		this.$('.job-form button.job-delete').hide();
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
		this.$el.html(this.tpl());
		return this;
	}
});
