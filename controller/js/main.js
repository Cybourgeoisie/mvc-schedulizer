$(document).ready(function()
{
	// Log
	console.log('Document ready');

	// Load the home page
	loadMainPage();

	/**
	 * Load the Main Page
	 **/
	function loadMainPage(evtObj)
	{
		// Log
		console.log('AJAX started');

		// Get the main HTML page
		$.ajax({
			url     : VIEW_DIR + 'html/main.html',
			success : handleResult
		});

		function handleResult(data, status)
		{
			// Log
			console.log('AJAX finished');

			// Update the UI
			$('#root').html(data);
		}
	}
});