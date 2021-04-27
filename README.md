# KeenMVC           {#mainpage}

[TOC]

# Introduction

**Keen** was born from my search for an MVC framework with all the *good* bits.  What are the good bits you say?  Here's my take (YMMV):

* Fast, Flexible, Powerful
* Inferred Models -- this one isn't too hard to find
* Lightweight -- it's so easy to go too feature-heavy
* Relatively Easy for Newcomers -- we've all been there
* View / Logic Separation  -- keep PHP and HTML separate

The last two were the key.

I don't know of anyone who was born a programmer. We all started somewhere. And yet, a very high percentage of the tools designed for programmers are not friendly to newbies. For example, I actually feel that Ruby would be somewhat easier than PHP to pick up. (There's even a Ruby framework called [Pakyow](https://www.pakyow.org/) with the "good bits".) Unfortunately, I haven't been able to find a server application in the Ruby world that's as easy to work with as [XAMPP](https://www.apachefriends.org/index.html).


And then views. I wanted to be able to take an unmodified HTML file and plug it in. No templating languages. No inserting bits of code. A view is just a view. To me, a framework that lacks this is not *really* an MVC framework. [Pakyow](https://www.pakyow.org/) is the only framework I'm aware of that does this.

All that said, I felt like it would be easier for me to create a framework for PHP (where I know there's a need for such a thing),  than to create a desktop server app for Ruby. Enter KeenMVC.

## A Note About `.ini` Files and Configuration

Much of the configuration for Keen is handled via `.ini` files. There are a few (I think) very good reasons for this:

1. PHP has a very good, built-in (and therefore fast) method that will parse such a file into an multi-level, associative array.
2. These files can be very clean with (generally) simpler syntax than would be required for a PHP file, for example.
3. The lack of a very rigid structure should make for more graceful feature growth as Keen matures -- just add sections to config files, as needed.
4. PHP itself uses `.ini` files for its configuration, so being familiar with the general syntax is very useful for PHP developers.

In the case of the `.ini` file templates include with Keen, they end with `.ini.php` to prevent the data stored therein from being displayed on the web. You'll note from comments in these template files that to get this magic to work the lines at the top and bottom of the file should be left alone, and your configuration values inserted in the middle.

Finally, in the `.htaccess` file include with KeenMVC, there is a section devoted to returning a 404 error should an `.ini` or `.ini.php` file be requested. That said, the ideal is to configure your web site so that the `public` directory is the document root of your site. If configured in this way, your configuration files won't even be in a place where they're accessible to web traffic. Or even better, move your config files completely away from your web files, and configure your site accordingly.

## Installation

When setting up a web site using KeenMVC for the first time, simply copy the directory as a whole to a place that makes sense on your server (for example, many Linux distributions keep web files at `/var/www`.) While the entire directory need not be present on your site, the `docs` and `tests` directories need not be present for Keen to function.

Ideally, when installing KeenMVC for a site that you're building, the `public` folder in the distribution should be made the webroot. If that's not possible &mdash; you have to install into a certain directory, for example &mdash; there is an `.htaccess` file at the root level of the distribution that will help things work properly when using a properly configured Apache server installation.

## Getting Started


