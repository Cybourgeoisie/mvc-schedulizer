app.CalendarView = Backbone.View.extend
({
	tplPath: VIEW_PATH + 'html/Calendar.html',

	JobCollection : [],

	initialize: function()
	{
		// Get the template
		$.get(this.tplPath, $.proxy(this.initPage, this));
	},

	initPage: function(pageData)
	{
		// Render the page
		this.renderTemplate(pageData);

		// Instantiate the collections
		this.JobCollection   = new app.JobCollection({});
		this.ShiftCollection = new app.ShiftCollection({});

		// Load the calendar
		this.loadCalendar();

		// Initialize event handlers
		this.initEvents();

		// Fetch the job and shift data
		this.JobCollection.  fetch({reset: true});
		this.ShiftCollection.fetch({reset: true});
	},

	initEvents: function()
	{
		// Populate the job and shift information when the data comes in
		this.listenTo(this.JobCollection,   'reset', this.populateJobList);
		this.listenTo(this.ShiftCollection, 'reset', this.addShiftsToCalendar);

		// Save the shifts
		this.$('button.save-calendar').on('click', $.proxy(this.saveCalendar, this));
	
		// Schedule!!!
		this.$('button.schedule').on('click', $.proxy(this.scheduleShifts, this));
	},

	scheduleShifts: function(event)
	{
		// Scope
		var self = this;

		// Schedule the shit
		$.ajax({
			url     : GATEWAY_URL + '?action=Schedulizer::makeSchedule',
			success : function(results) {
				self.$('.schedule-results').html(JSON.parse(results));
			}
		});

		// Don't redirect afterwards
		//event.preventDefault();

		// Or let's try returning a value... Success.
		//return true;  // Redirect
		return false; // Don't redirect
	},

	populateJobList: function()
	{
		// Reset the list, add each job
		this.$('.Calendar .events').html('');
		this.JobCollection.each(this.addJobToList, this);
	},

	addJobToList: function(job)
	{
		// Create the job
		var $job = $('<div>')
			.addClass('event')
			.html(job.get('name'));

		// Append the job to the list
		this.$('.Calendar .events').append($job);

		// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
		// it doesn't need to have a start or end
		var eventObject = {
			title:  $.trim($job.text()),
			job_id: job.get('job_id')
		};
		
		// store the Event Object in the DOM element so we can get to it later
		$job.data('eventObject', eventObject);
		
		// make the event draggable using jQuery UI
		$job.draggable({
			zIndex: 999,
			revert: true,      // will cause the event to go back to its
			revertDuration: 0  //  original position after the drag
		});
	},

	addShiftsToCalendar: function()
	{
		// Load the shifts to the calendar
		this.ShiftCollection.each(function(shift)
		{
			var shiftObj = {
				'title'    : shift.get('name'),
				'shift_id' : shift.get('shift_id'),
				'job_id'   : shift.get('job_id'),
				'start'    : new Date(parseInt(shift.get('start'))),
				'end'      : new Date(parseInt(shift.get('end'))),
				'allDay'   : false
			};

			this.$('.calendar').fullCalendar('renderEvent', shiftObj);
		}, this);
	},

	loadCalendar: function()
	{
		// Get today's date
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();

		// Load the calendar
		this.$('.calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			editable: true,
			droppable: true,
			drop: function(date, allDay)
			{
				// retrieve the dropped element's stored Event Object
				var originalEventObject = $(this).data('eventObject');
				
				// we need to copy it, so that multiple events don't have a reference to the same object
				var copiedEventObject = $.extend({}, originalEventObject);
				
				// assign it the date that was reported
				copiedEventObject.start = date;
				copiedEventObject.allDay = allDay;
				
				// render the event on the calendar
				// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
				$('.calendar').fullCalendar('renderEvent', copiedEventObject, true);
			}
		});
	},

	saveCalendar: function()
	{
		// Get the shifts
		var shifts = this.$('.calendar').fullCalendar('clientEvents');
		
		// Save each shift
		_.each(shifts, this.saveShift, this);

		// Reload the calendar
		$('.calendar').fullCalendar('removeEvents');
		this.ShiftCollection.fetch({reset: true});
	},

	saveShift: function(data)
	{
		// Get the shift
		if (data.shift_id)
		{
			var shift = this.ShiftCollection.get(data.shift_id);
		}
		// Create a new shift
		else
		{
			var shift = new app.Shift({});
		}

		// Set the new values
		shift.set('name',   data.title);
		shift.set('job_id', data.job_id)
		shift.set('start',  data.start.getTime());
		shift.set('end',    data.end.getTime());

		// Save this shift
		shift.save();
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
