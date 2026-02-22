# REST Router for PHP
This code provides the basic framework for a PHP developer that wants to build a REST service.

Some of the features provided in this framework are:
* Multiple request methods (GET, POST, PUT, DELETE)
* Custom URL routing to support variables
* Automatic method name resolution based on HTTP verbs

## Table of Contents
1. [Basic Routing](#basic-routing)
2. [Controller Structure](#controller-structure)
3. [Automatic Route Resolution](#automatic-route-resolution)
4. [Custom Routes](#custom-routes)
5. [Route Parameters](#route-parameters)
6. [Best Practices](#best-practices)

---

## Basic Routing

### URL Structure
```
/controller/action
```

Example Request: `GET /users/profile`

* Default controller is **index**
* Default action is **index**

### How the Router Works

The REST Router will:
1. Parse the incoming request URL
2. Load the appropriate controller file from the `/controllers` directory
3. Instantiate the controller class
4. Call the appropriate method based on HTTP verb + action

---

## Controller Structure

### File Naming Convention
Controllers should follow your application's naming convention. Common patterns:
- `/controllers/c_users.php` (with prefix)

### Class Example
```php
<?php
// File: /controllers/c_users.php

class Users extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // GET /users/profile
    public function get_profile()
    {
        // Return user profile
    }

    // PUT /users/profile
    public function put_profile($data = [])
    {
        // Update user profile
    }
}
```

---

## Automatic Route Resolution

The router automatically resolves routes by combining the **HTTP method** + **action name**.

### Simple Routes (Automatic)

These routes work **without** needing explicit rules in `routes.php`:

| HTTP Request | Controller Method |
|-------------|------------------|
| `GET /users/profile` | `Users->get_profile()` |
| `PUT /users/profile` | `Users->put_profile()` |
| `DELETE /users/profile` | `Users->delete_profile()` |
| `POST /users/create` | `Users->post_create()` |

### Method Naming Pattern
```
{http_method}_{action}
```

Examples:
- `Users->get_profile()` - handles GET requests to `/users/profile`
- `Auth->post_login()` - handles POST requests to `/auth/login`
- `Resource->put_update()` - handles PUT requests to `/resource/update`
- `Resource->delete_item()` - handles DELETE requests to `/resource/item`

### Fallback Behavior

If a method-specific function is not found (e.g., `get_profile()`), the router will fall back to calling just the action name (e.g., `profile()`).

---

## Custom Routes

### When to Use Custom Routes

Use explicit routes in `routes.php` when:
1. **Nested paths** that would be misinterpreted by automatic routing
2. **Path parameters** like IDs in the middle of the URL
3. **Special routing logic** that doesn't follow the standard pattern

### Nested Path Example

**Problem:** `PUT /users/profile/password`
- Without custom route: Router tries to call `put_profile($data['password'])`
- With custom route: Router calls `put_profile_password($data)`

**Solution in routes.php:**
```php
case 'put':
    $router->addRule('/users/profile/password', array(
        'controller' => 'users',
        'action' => 'put_profile_password'
    ));
    break;
```

### Order Matters!

Always define **more specific routes first**:
```php
// ✅ CORRECT - specific route first
$router->addRule('/users/profile/password', array('controller' => 'users', 'action' => 'put_profile_password'));
$router->addRule('/users/profile', array('controller' => 'users', 'action' => 'put_profile'));

// ❌ WRONG - generic route matches first, specific never reached
$router->addRule('/users/profile', array('controller' => 'users', 'action' => 'put_profile'));
$router->addRule('/users/profile/password', array('controller' => 'users', 'action' => 'put_profile_password'));
```

---

## Route Parameters

### Path Parameters with `:parameter_name`

Use `:parameter_name` syntax for dynamic URL segments:

```php
case 'get':
    $router->addRule('/customer/:id', array(
        'controller' => 'customers',
        'action' => 'get_customer'
    ));

    $router->addRule('/blog/:category/:post_id', array(
        'controller' => 'blog',
        'action' => 'get_post'
    ));
    break;
```

### Accessing Parameters in Controller

Parameters are passed to the controller method:

```php
class Customers extends Controller
{
    // GET /customer/123
    public function get_customer($params = [])
    {
        $customer_id = $params['id']; // "123"
        // Fetch and return customer data
    }
}
```

### Multiple Parameters

```php
$router->addRule('/blog/:category/:post_id', array(
    'controller' => 'blog',
    'action' => 'get_post'
));

// Controller receives both parameters
public function get_post($params = [])
{
    $category = $params['category'];
    $post_id = $params['post_id'];
    // Fetch and return blog post
}
```

---

## Best Practices

### 1. Consistent Method Naming
```php
// ✅ CORRECT - follows {verb}_{action} pattern
public function get_profile() { }
public function put_profile() { }
public function delete_profile() { }
public function post_login() { }

// ❌ WRONG - inconsistent naming
public function getProfile() { }     // Wrong case
public function profile() { }        // Missing verb
public function user_profile() { }   // Doesn't match URL
```

### 2. Handle Request Data
```php
// GET/DELETE - parameters from URL
public function get_item($params = [])
{
    $id = $params['id'];
}

// POST/PUT - data from request body
public function post_create($data = [])
{
    $name = $data['name'];
    $email = $data['email'];
}
```

### 3. Use Explicit Routes for Nested Paths
```php
// ✅ CORRECT - explicit route for nested path
// routes.php:
$router->addRule('/users/profile/password', array(
    'controller' => 'users',
    'action' => 'put_profile_password'
));

// Controller:
public function put_profile_password($data = []) { }

// ❌ WRONG - relying on automatic routing for nested paths
// Will try to call put_profile($data['password']) instead
```

### 4. Validate Input
```php
public function post_register($data = [])
{
    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        // Return error response
        return json_encode([
            'error' => true,
            'message' => 'Email and password are required'
        ]);
    }

    // Process registration...
}
```

---

## Complete Example

### routes.php
```php
<?php
switch($data->getMethod()) {
    case 'get':
        // Custom route with parameter
        $router->addRule('/customer/:id', array(
            'controller' => 'customers',
            'action' => 'get_customer'
        ));

        // Nested path
        $router->addRule('/users/profile/settings', array(
            'controller' => 'users',
            'action' => 'get_profile_settings'
        ));
        break;

    case 'put':
        // Nested paths (order matters!)
        $router->addRule('/users/profile/password', array(
            'controller' => 'users',
            'action' => 'put_profile_password'
        ));
        $router->addRule('/users/profile/settings', array(
            'controller' => 'users',
            'action' => 'put_profile_settings'
        ));
        break;

    case 'post':
        // Add custom POST routes here
        break;

    case 'delete':
        // Add custom DELETE routes here
        break;
}

$router->init();
```

### users.php Controller
```php
<?php

class Users extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * GET /users/profile
     * Automatic routing - no explicit rule needed
     */
    public function get_profile()
    {
        // Return user profile data
        return json_encode([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * PUT /users/profile
     * Automatic routing - no explicit rule needed
     */
    public function put_profile($data = [])
    {
        // Update user profile
        return json_encode([
            'success' => true,
            'data' => $updatedProfile
        ]);
    }

    /**
     * PUT /users/profile/password
     * Requires explicit route in routes.php
     */
    public function put_profile_password($data = [])
    {
        if (empty($data['current_password']) || empty($data['new_password'])) {
            return json_encode([
                'error' => true,
                'message' => 'current_password and new_password are required'
            ]);
        }

        // Update password logic...
        return json_encode([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
```

---

## Request Methods

The REST Router supports the following HTTP methods:

* **GET** - Retrieve resources
* **POST** - Create new resources
* **PUT** - Update existing resources
* **DELETE** - Delete resources
