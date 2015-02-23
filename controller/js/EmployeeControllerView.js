app.EmployeeControllerView = Backbone.View.extend
({
	tplPath: VIEW_PATH + 'html/Employee.html',

	// Keep track of the data
	EmployeeCollection : [],
	JobCollection      : [],

	events: {
        ".employee-list ul li a": "viewEmployee"
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

		// Prepare the employee collection
		this.EmployeeCollection = new app.EmployeeCollection({});
		this.JobCollection      = new app.JobCollection({});

		// Initialize the availability calendar
		this.loadAvailabilityCalendar();

		// Initialize event handlers
		this.initEvents();

		// Fetch the employee and job data
		this.EmployeeCollection.fetch({reset: true});
		this.JobCollection.fetch({reset: true});
	},

	initEvents: function()
	{
		// Populate the employee and job lists
		this.listenTo(this.EmployeeCollection, 'reset', this.populateEmployeeList);
		this.listenTo(this.JobCollection,      'reset', this.populateJobList);

		// Populate the form
		this.$('.employee-list').on('click', 'a', $.proxy(this.viewEmployee, this));

		// Submit the form
		this.$('.employee-form button.employee-form-submit').on('click', $.proxy(this.saveEmployee, this));

		// Delete the employee
		this.$('.employee-form button.employee-delete').on('click', $.proxy(this.deleteEmployee, this));
	},

	loadAvailabilityCalendar: function()
	{
		// Keep the scope alive
		var self = this;

		// Set the form datetime pickers
		// Look into http://trentrichardson.com/examples/timepicker/
		//$('#calendar-event-dialog-form #event-start').datepicker();
		//$('#calendar-event-dialog-form #event-end').datepicker();

		// Load the calendar
		this.$('.availability-calendar').fullCalendar({
			header: {
				left:   'prev,next today',
				center: 'title',
				right:  'month,agendaWeek,agendaDay'
			},
			defaultView:  'agendaWeek',
			editable:     true,
			events:       [],
			selectable:   true,
			selectHelper: true,
			select:       function(start, end)
			{
				// Add the availability to the list
				var eventObj = {
					title       : 'Available',
					b_available : true,
					repeat      : 'no',
					start       : start,
					end         : end,
					editable    : true,
					allDay      : false
				};

				// Add an event
				self.$('.availability-calendar').fullCalendar('renderEvent', eventObj, true);
			},
			eventClick: function(calEvent, evt, view)
			{
				// Open the dialog
				$('#calendar-event-dialog-form').dialog('open');

				// Set the ID of the event on this form
				$('#calendar-event-dialog-form').data('calEvent', calEvent);

				// Populate the form
				//$('#calendar-event-dialog-form #event-start').    val(calEvent.start.getTime());
				//$('#calendar-event-dialog-form #event-end').      val(calEvent.end.getTime());
				$('#calendar-event-dialog-form #event-available').prop('checked', !!calEvent.b_available);
				$('#calendar-event-dialog-form #event-repeat').   val(calEvent.repeat);
			}
		});

		// Initialize the dialog form
		this.initDialog();
	},

	initDialog: function()
	{
		// Create the dialog
		$('#calendar-event-dialog-form').dialog({
			dialogClass: "calendar-event-dialog",
			autoOpen: false,
			minWidth: 400,
			buttons: [
				{
					text:  'Save',
					click: $.proxy(saveCalendarEvent, self)
				},
				{
					text:  'Delete',
					click: $.proxy(removeCalendarEvent, self)
				},
			]
		});

		function saveCalendarEvent()
		{
			// Get the calendar event ID from the dialog box
			var calEvent = $('#calendar-event-dialog-form').data('calEvent');

			// Update the event's values
			calEvent.b_available = !!$('#calendar-event-dialog-form #event-available').prop('checked');
			calEvent.repeat      = $('#calendar-event-dialog-form #event-repeat').val();
			calEvent.title       = (calEvent.b_available) ? 'Available' : 'Busy';
			calEvent.color       = !calEvent.b_available  ? '#FF6666' : '#3a87ad';

			// Update this event
			this.$('.availability-calendar').fullCalendar('updateEvent', calEvent);

			// Close this dialog
			$('#calendar-event-dialog-form').dialog('close');
		}

		function removeCalendarEvent()
		{
			// Get the calendar event ID from the dialog box
			var calEvent = $('#calendar-event-dialog-form').data('calEvent');

			// Remove the calendar event -- BUG: Newly created events?
			this.$('.availability-calendar').fullCalendar('removeEvents', calEvent.id + '');

			// Close this dialog
			$('#calendar-event-dialog-form').dialog('close');
		}
	},

	populateEmployeeList: function()
	{
		// Reset the list, add each employee
		this.$('.employee-list ul').html('');
		this.EmployeeCollection.each(this.addEmployeeToList, this);
	},

	addEmployeeToList: function(employee)
	{
		// Append the employee to the list
		this.$('.employee-list ul').append($('<li>')
			.append($('<a>')
				.html(employee.get('name'))
				.data('employee', employee)
			));
	},

	populateJobList: function()
	{
		// Reset the list, add each job
		this.$('.job-checkboxes').html('');
		this.JobCollection.each(this.addJobToList, this);
	},

	addJobToList: function(job)
	{
		// Append the job to the list
		this.$('.job-checkboxes').append($('<label for="employee-jobs[' + job.get('job_id') + ']">')
			.addClass('pure-checkbox')
			.append($('<input type="checkbox" id="employee-jobs[' + job.get('job_id') + ']">')
				.addClass('form-control')
				.data('job_id', job.get('job_id'))
			).append(' ' + job.get('name'))
		);
	},

	viewEmployee: function(el)
	{
		// Reset the form
		this.resetForm();

		// Get the employee
		var employee = $(el.currentTarget).data('employee');

		// If no employee, bail
		if (!employee) { return; }

		// Set the values with this employee
		this.$('.employee-form input#employee-name').val(employee.get('name'));
		this.$('.employee-form input#employee-id').val(employee.get('employee_id'));

		// Select their jobs
		this.$('.employee-form .job-checkboxes input:checkbox').each(function(id, el) {
			var job_id   = $(el).data('job_id');
			var job_list = employee.get('Job');
			$(el).prop('checked', _.contains(job_list, job_id));
		});

		// Load the employee availabilities
		_.each(employee.get('availability'), $.proxy(this.loadCalendarEvent, this));

		// Set the "Add Employee" button to "Edit Employee"
		this.$('.employee-form button.employee-form-submit').html('Save Employee');

		// Show the Delete button
		this.$('.employee-form button.employee-delete').show();
	},

	loadCalendarEvent: function(calEvent, key, context, repeatCount)
	{
		// Set the default
		repeatCount = (repeatCount == null) ? ((calEvent.repeat == 'daily') ? 70 : 10) : repeatCount;

		// Render the event
		this.$('.availability-calendar').fullCalendar('renderEvent',
			{
				'title'     : (calEvent.b_available) ? 'Available' : 'Busy',
				'start'     : new Date(calEvent.start),
				'end'       : new Date(calEvent.end),
				'allDay'    : false,
				b_available : !!calEvent.b_available,
				color       : !calEvent.b_available ? '#FF6666' : '#3a87ad',
				repeat      : calEvent.repeat,
				'id'        : key // calEvent.key
			},
			true // Stick to the calendar
		);

		// Terminate
		if (repeatCount == 0 || calEvent.repeat == 'no') { return; }

		// Handle repeating events
		var startDate = new Date(calEvent.start);
		var endDate   = new Date(calEvent.end);

		if (calEvent.repeat == 'daily')
		{
			// Set the start and end dates to tomorrow
			startDate = startDate.setDate(startDate.getDate() + 1);
			endDate   = endDate.setDate(endDate.getDate() + 1);
		}
		else if (calEvent.repeat == 'weekly')
		{
			// Set the start and end dates to the following week
			startDate = startDate.setDate(startDate.getDate() + 7);
			endDate   = endDate.setDate(endDate.getDate() + 7);
		}

		// Update the start and end dates
		calEvent.start = startDate;
		calEvent.end   = endDate;

		// Load the next event
		this.loadCalendarEvent(calEvent, key, context, --repeatCount);
	},

	serializeCalendarEvents: function(calEvent)
	{
		return {
			'start'     : calEvent.start.getTime(),
			'end'       : calEvent.end.getTime(),
			b_available : calEvent.b_available,
			repeat      : calEvent.repeat
		};
	},

	saveEmployee: function()
	{
		// Check for an employee id
		var employee_id = this.$('.employee-form #employee-id').val();

		// Get the employee
		if (employee_id)
		{
			var employee = this.EmployeeCollection.get(employee_id);
		}
		// Create a new employee
		else
		{
			var employee = new app.Employee({});
		}

		// Get all of the job IDs
		var jobs = this.$('.employee-form .job-checkboxes input:checkbox:checked')
			.map(function(){
				return $(this).data('job_id');
			}).get();

		// Get the availabilities
		var events = this.$('.availability-calendar').fullCalendar('clientEvents');

		// Filter out repeated events
		var filtered_events = [];
		var b_repeat_found;
		for (event_id in events)
		{
			// Flag a repeat
			b_repeat_found = false;

			// Go through currently filtered events
			for (filtered_event_id in filtered_events)
			{
				if (events[event_id].id == filtered_events[filtered_event_id].id)
				{
					b_repeat_found = true;
				}
			}

			// Add to filtered events
			if (!b_repeat_found)
			{
				filtered_events.push(events[event_id]);
			}
		}

		// Serialize the remaining events
		var availability = _.map(filtered_events, $.proxy(this.serializeCalendarEvents, this));

		// Set the new values
		employee.set('name', this.$('.employee-form #employee-name').val());
		employee.set('Job',  jobs);
		employee.set('availability', JSON.stringify(availability));

		// Save this employee
		employee.save(null, {
			'success' : $.proxy(this.reloadEmployees, this)
		});
	},

	deleteEmployee: function()
	{
		// Check for an employee id
		var employee_id = this.$('.employee-form #employee-id').val();
		if (!employee_id) { return; }

		// Get the employee from the collection
		var employee = this.EmployeeCollection.get(employee_id);

		// Delete the employee
		employee.destroy({
			'success' : $.proxy(this.reloadEmployees, this)
		});
	},

	reloadEmployees: function()
	{
		// Reset the form, fetch the employees
		this.resetForm();
		this.EmployeeCollection.fetch({reset: true});
	},

	resetForm: function()
	{
		// Reset all input fields
		this.$('.employee-form form input')
			.val('')
			.prop('checked', '');

		// Clear the availability
		this.$('.availability-calendar').fullCalendar('removeEvents');
	
		// Set the button to "Add Employee"
		this.$('.employee-form button.employee-form-submit').html('Add Employee');

		// Hide the Delete button
		this.$('.employee-form button.employee-delete').hide();
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
