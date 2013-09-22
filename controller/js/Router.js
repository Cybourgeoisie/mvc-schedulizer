var Router = Backbone.Router.extend({
    /* define the route and function maps for this router */
    routes: {
        // About
        "about" : "showAbout",

        // Match employees
        "employee/:id" : "getEmployee",

        // Default
        "*other" : "defaultRoute"
    },

    showAbout: function()
    {
        console.log("About page");
    },

    getEmployee: function(id)
    {
        console.log("You are trying to reach employee " + id);
    },

    defaultRoute: function(other){
        console.log('Invalid. You attempted to reach:' + other);
    }
});
