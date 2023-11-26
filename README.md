# CML - Framework [PHP]

> This framework is a powerful tool that seamlessly combines an efficient routing system with an HTML builder, providing you with numerous customization options for your web routes and pages. Moreover, it offers an easy and secure way to connect to databases, making data management a breeze. This framework also comes equipped with a variety of built-in functions to simplify the programming process. Additionally, comprehensive online documentation ensures that you can always refer back to it for guidance.

## Table of Contents

- [Documentation](#documentation)
- [Getting Started](#getting-started)
  - [Installation](#installation)
- [Features](#features)
  - [Router](#router)
  - [DB](#db)
  - [HTMLBuilder](#htmlbuilder)



## Documentation
[https://docs.callmeleon.de/](https://docs.callmeleon.de/)

## Getting Started

### Installation

```composer create-project callmeleon167/cml-framework my-app```

or

- Clone the repo
- Have composer installed
- Run the build.sh
- Duplicate the cml-config.template.php file in the app/config directory, and then remove the .template extension from the file name to obtain an cml-config.php file
- Configure your settings, and you're good to go!

## Features

### Router

#### The `Router` class in this PHP code provides a powerful and flexible routing system for web applications. Here are the main features and capabilities:

**1. Route Handling:** The class manages defined routes, allowing you to map URLs to specific callback functions based on HTTP request methods (GET, POST, etc.).

**2. Middleware Support:** You can add middleware functions to be executed before or after route callbacks, enhancing the flexibility of your application.

**3. Error Handling:** The class handles 404 errors by redirecting to a specified URL and provides a clear error message when a route is not found.

**4. Grouping Routes:** You can group related routes under a common URL prefix, improving organization and structure in routing.

**5. Route Aliases:** Route aliases can be set for more user-friendly URLs, and the original URL can be retrieved from an alias.

**6. Security Headers:** The class sets important security headers, enhancing the security of your web application. These headers include Content Security Policy (CSP), X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, HTTP Strict Transport Security (HSTS), and Referrer Policy.

**7. Route Parameter Handling:** The class allows you to extract and sanitize route parameters, enhancing security and handling user input.

**8. AJAX Routing:** Routes can be configured to be accessible only via AJAX requests.

**9. Query Parameter Handling:** The class provides a method to filter and retrieve query parameters from the current request URI.

**10. Environment Handling:** It initializes error reporting configurations based on the environment (production or development) and uses .env files for configuration.

**11. API Support:** You can set a route as an API route, returning responses in JSON format.

**12. Rate Limiting:** Rate limiting based on IP addresses is supported to prevent abuse and excessive requests.

**13. Integration with HTMLBuilder:** The class extends the HTMLBuilder class, allowing you to work with HTML templates and build web pages.

These features make the Router class a versatile tool for building web applications with flexible routing, enhanced security, and error handling. It simplifies the management of routes, middlewares, and security headers while supporting various HTTP request methods and AJAX requests.


### DB

#### The `DB` class in this PHP code provides functionalities for database interaction. Here is a feature description for your GitHub README.md:

**1. Database Connection:** The class establishes a connection to the database, allowing you to execute SQL queries.

**2. Environment Variable Loading:** It loads environment variables from a .env file for database configuration.

**3. Connection Management:**

- It can switch between different database connections, allowing you to work with multiple databases.
- It provides the ability to restore the default database connection.
 
**4. SQL Query Execution:**

- The class allows you to execute SQL queries and retrieve the results as an array.
- It supports parameterized queries, enhancing security and preventing SQL injection.
 
**5. SQL Query from File:** You can execute SQL queries stored in a file, providing a convenient way to manage and run complex queries.

**6. JSON Response Generation:** The class can execute SQL queries and return the results as JSON-encoded strings, making it suitable for building APIs.
Input Data Sanitization: The class includes methods for cleaning input data to prevent security issues, such as SQL injection.

**7. HTML Entity Decoding:** You can decode HTML entities in a string, which is useful when working with HTML content.

The DB class simplifies database interactions, supports parameterized queries for security, and can manage multiple database connections. It's a valuable tool for working with databases and executing SQL queries efficiently and securely.

### HTMLBuilder

#### The `HTMLBuilder` class is designed to assist in constructing HTML documents. Here's a feature description for your GitHub README.md:

**1. Document Structure:** It generates the basic structure of an HTML document, including the `<!DOCTYPE html>`, `<html>`, `<head>`, and `<body>` elements.

**2. Title and Favicon:** 
- You can set the title of the HTML document using the `setTitle` method. 
- The class includes a default favicon icon.

**3. Styles and Scripts:**
- It allows you to add external stylesheets and JavaScript files to the HTML document.
- These assets are linked to the document with appropriate HTML tags.

**4. Asset URL Management:** The `assetUrl` method provides a way to generate URLs for assets, taking into account the base URL.

**5. Comments for Readability:**
- It generates comments for the HTML document, including a centered welcome message and information about the project and its programmers.
- The comments provide a visually appealing and informative introduction to the source code.

**6. Centered Comment Generation:** The `center_comment` method can be used to generate centered comments, which is especially useful for creating visually pleasing headers and sections in the HTML source code.

**7.  Initialization Comment:** The `init_comment` method generates an introductory comment block that includes the project name, centered ASCII art, and information about the project's development and programmers.


This class is useful for quickly creating well-structured HTML documents, including setting titles, adding styles and scripts, and enhancing readability with attractive comments. It provides an elegant and informative start to your HTML source code. When used in conjunction with the `Router` class, you can easily build dynamic web applications and websites.
