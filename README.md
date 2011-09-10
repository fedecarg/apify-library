# Introduction

Apify is an alternative to bloated enterprise frameworks. It's small, easy to understand 
and use. If you are familiar with the Zend Framework, then you already know how to use Apify.

This library allows developers to quickly create anything, from a Web application to a RESTful Web service. 

Web services are a great way to extend your application, however, adding a Web API 
to an existing web application can be a tedious and time-consuming task. Apify was 
developed to ease that pain. It takes certain common patterns found in most web services 
and abstract them so that you can quickly write Web APIs without having to write too much 
code. Example: [UsersController][9].

See it in action: http://www.youtube.com/watch?v=7ptoB0yCsDo

# Features

- Small and simple web application.
- Easy to install, easy to use and easy to extend.
- Powerful to satisfy most requirements.
- Standalone. Works with any existing website (just unzip the files to your server and it's ready to use).
- Supports the following API styles: [Twitter][1], [Delicious][2], [Scribd][3], [Yahoo! Upcoming][4], [Microsoft Zoom.it][5], and more.
- OOP-compliant architecture.
- Explicit Request and Response classes.
- RESTful URL mappings.
- Easy API versioning.
- Out of the box support for the following representations: JSON, XML, RSS and HTML.
- Content type negotiation.
- Encourages proper use of HTTP response codes.
- Domain models with out-of-the-box default implementations and input validation.

# Requirements

- PHP 5.2 or greater.
- MySQL and PDO_MYSQL driver (optional).

# Installation

- Download the .zip or .tar.gz file from the GitHub website and decompress it.
- Upload the files to your web server via FTP, SFTP or SSH.

Optional:

- Create a MySQL database and user/password.
- Import the schema.sql file into your database.
- Open the config.php file and set your database settings.
- Make sure the pdo_mysql PHP extension for MySQL is enabled.

# Debugging

The debugger is enabled by default. You can always turn it off by
setting the "debug" option to false in the config.php file.

# Demo

To demonstrate the possibilities of Apify and to show how it works, 
I've created http://www.apifysnippets.com, a web application/service 
that allows Facebook users to submit posts, vote and add comments.

URL scheme:

- http://apifysnippets.com/posts
- http://apifysnippets.com/posts/new
- http://apifysnippets.com/posts/1/edit
- http://apifysnippets.com/posts/1/vote.json
- http://apifysnippets.com/posts/1/comment.json

Representations:

- http://apifysnippets.com/posts
- http://apifysnippets.com/posts.json
- http://apifysnippets.com/posts.rss
- http://apifysnippets.com/posts/popular
- http://apifysnippets.com/posts/popular.json
- http://apifysnippets.com/posts/popular.rss

# Reporting bugs

We use [GitHub as issue tracker][6]. Bug reports are incredibly helpful, so take time to report bugs and request features in our ticket tracker.

# Contributing

The best ways to contribute are by finding and reporting bugs, writing tests for bugs, and improving the documentation. We're always grateful for patches to Apify's code. If you want to change the code, be careful follow our design-decisions. Be especially careful not to increase complexity if you don't have a really good reason.

# Contact

- If you encounter any problems, please use the issue tracker.
- For updates follow [@fedecarg][7] on Twitter.
- If you like Apify and use it in the wild, let me know.

# License

- Copyright (c) 2011, [Kewnode Ltd][8]. All rights reserved.

[1]: href="https://dev.twitter.com/docs/api
[2]: href="http://www.delicious.com/help/api
[3]: http://www.scribd.com/developers
[4]: http://upcoming.yahoo.com/services/api/
[5]: http://zoom.it/pages/api/
[6]: https://github.com/apify/apify-library/issues
[7]: https://twitter.com/fedecarg
[8]: http://www.kewnode.com/
[9]: https://github.com/apify/apify-library/blob/master/app/controllers/UsersController.php
