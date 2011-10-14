# Introduction

Apify is a small and powerful open source library that delivers new levels of developer 
productivity by simplifying the creation of RESTful architectures. It helps development 
teams deliver quality web services and applications in reduced amounts of time. If you are 
familiar with the Zend Framework, then you already know how to use Apify. Take a look at 
the [UsersController][9] class.

Web services are a great way to extend your application, however, adding a Web API 
to an existing web application can be a tedious and time-consuming task. Apify was 
developed to ease that pain. It takes certain common patterns found in most web services 
and abstract them so that you can quickly write Web APIs without having to write too much 
code.

You can see it in action here:   
http://www.youtube.com/watch?v=7ptoB0yCsDo

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

# Documentation

- [Apify Overview](http://apifydoc.com/posts/3/apify-overview)
- [Installation Guide](http://apifydoc.com/posts/7/installation-guide)
- [Controllers](http://apifydoc.com/posts/17/action-controllers)
- [Error Messages](http://apifydoc.com/posts/23/error-messages)
- [Error Templates](http://apifydoc.com/posts/24/error-templates)
- [URL Dispatcher](http://apifydoc.com/posts/18/url-dispatcher)
- [Content Negotiation](http://apifydoc.com/posts/22/content-negotiation)
- [The Domain Object Model](http://apifydoc.com/posts/27/the-domain-object-model)
- [Basic CRUD Operations](http://apifydoc.com/posts/29/basic-crud-operations)
- [Web APIs](http://apifydoc.com/posts/21/building-a-web-service-api)
- [Web Applications](http://apifydoc.com/posts/20/building-a-web-application)

# Demo

The following Apify web application/service allows users to submit posts, vote and add comments.

URL scheme:

- http://apifydoc.com/posts
- http://apifydoc.com/posts/new
- http://apifydoc.com/posts/1/edit
- http://apifydoc.com/posts/1/vote.json
- http://apifydoc.com/posts/1/comment.json

Representations:

- http://apifydoc.com/posts
- http://apifydoc.com/posts.json
- http://apifydoc.com/posts.rss
- http://apifydoc.com/posts/popular
- http://apifydoc.com/posts/popular.json
- http://apifydoc.com/posts/popular.rss

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
- http://kewnode.com/apify/license

[1]: https://dev.twitter.com/docs/api
[2]: http://www.delicious.com/help/api
[3]: http://www.scribd.com/developers
[4]: http://upcoming.yahoo.com/services/api/
[5]: http://zoom.it/pages/api/
[6]: https://github.com/apify/apify-library/issues
[7]: https://twitter.com/fedecarg
[8]: http://www.kewnode.com/
[9]: https://github.com/apify/apify-library/blob/master/app/controllers/UsersController.php
