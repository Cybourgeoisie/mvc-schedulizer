app.JobCollection = Backbone.Collection.extend
({
	url: GATEWAY_URL + '?action=Job::getAll',
	model: app.Job
});
