# rest-router
REST Router for PHP

# Controller Versions, Classes and Actions
/controllers/version/controller/action
Example Request: GET /v1/home/index

## Default version is "v1"
## Default controller class is "home"
## Default action is "index"

The REST Router will do the following:
* try to find a folder called "v1" in the /controllers folder.
* Load "Home" controller in file /controllers/v1/home.php
* Because this is a GET request, it will try to following:
** check for a class method called "get_index" and run it
** if not found, it will search for "index" and run it
** if not found, it will return an error


# Request Methods
* GET
* POST
* PUT
* DELETE
