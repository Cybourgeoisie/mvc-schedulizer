var EmployeeCollection = Backbone.Collection.extend
({
	url: GATEWAY_URL + '?action=Employee::getAll',
	model: Employee
});
