app.EmployeeCollection = Backbone.Collection.extend
({
	url: GATEWAY_URL + '?action=Employee::getAll',
	model: app.Employee
});
