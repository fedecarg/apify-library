# Getting started

## Apify Overview

Apify is a small and powerful open source library that delivers new levels of developer productivity by simplifying the creation of RESTful architectures. It helps development teams deliver quality web services and applications in reduced amounts of time.

### Meet Apify
Web services are a great way to extend your application, however, adding a web API to an existing web application can be a tedious and time-consuming task. Apify takes certain common patterns found in most web services and abstract them so that you can quickly write web APIs without having to write too much code. Here's an example: UsersController.

The library supports the following web API styles: Twitter, Delicious, Scribd, Yahoo! Upcoming and Microsoft Zoom.it.

You can see it in action here:
http://www.youtube.com/watch?v=7ptoB0yCsDo

### Features
* Easy to install, easy to use and easy to extend.
* Powerful to satisfy most requirements.
* Standalone. Works with any existing website (just unzip the files to your server and it's ready to use).
* OOP-compliant architecture.
* Explicit Request and Response classes.
* RESTful URL mappings.
* Easy API versioning.
* Out of the box support for the following representations: JSON, XML, HTML.
* Content type negotiation.
* Encourages proper use of HTTP response codes.
* Domain models with out-of-the-box default implementations and input validation.

### Reporting bugs
We use GitHub as issue tracker. Bug reports are incredibly helpful, so take time to report bugs and request features in our ticket tracker.

### Contributing
The best ways to contribute are by finding and reporting bugs, writing tests for bugs, and improving the documentation. We're always grateful for patches to Apify's code. If you want to change the code, be careful follow our design-decisions. Be especially careful not to increase complexity if you don't have a really good reason.

### Contact
* If you encounter any problems, please use the issue tracker.
* For updates follow @fedecarg on Twitter.
* If you like Apify and use it in the wild, let me know.

### License
Copyright © 2011, Kewnode Ltd. All rights reserved.

## Installation Guide

This document will get you up and running with Apify.

### Prerequisites

* Check that you have installed and configured Apache with PHP 5.2 or higher.
* Enable mod_rewrite on your local server.

### Installation

Download the .zip or .tar.gz file from the GitHub website and decompress it. Apify offers a starter project that you can download and begin developing in immediately.

Create a virtual host:

    <VirtualHost *:80>
        ServerName mysite.com
        DocumentRoot /path/to/mysite.com/public

        <Directory /path/to/mysite.com/public>
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>

    </VirtualHost>

Test your installation by requesting:

    GET /example/request 
    GET /example/response.json
    GET /example/response.xml

### Optional

* Create a MySQL database and user/password.
* Import the schema.sql file into your database.
* Open the config.php file and set your database settings.
* Make sure the pdo_mysql PHP extension for MySQL is enabled.

### Directory Structure

The directory structure of an Apify web application is standardized to keep things as simple as possible.

    project/
        app/
            controllers/
                IndexController.php
            models/
            views/
                error/
                index/
                    index.phtml
                layout/
                    main.phtml
        config/
            config.php
            routes.php
        library/
            Request.php
            Response.php
        public/
            css/
            img/
            js/
            index.php
        tests/

Here's an example of a Custom Directory Structure (GitHub)

### Environments

Apify implements two distinct environments: DEV mode during the development phase and PROD mode when the application is deployed.

You can run an application either in a DEV or PROD mode. You toggle this mode using the DEBUG configuration setting. When DEBUG is set to true, Apify will display exceptions and errors (E_ALL and E_STRICT) in the browser. The PROD mode is fully optimized for production.
