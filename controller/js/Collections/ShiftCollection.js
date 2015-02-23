app.ShiftCollection = Backbone.Collection.extend
({
	url: GATEWAY_URL + '?action=Shift::getAll',
	model: app.Shift
});
