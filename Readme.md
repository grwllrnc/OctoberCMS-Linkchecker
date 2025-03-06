# LinkCheck Plugin for OctoberCMS
Schedules a task to check for broken links in database fields and/or CMS pages.

## Refactoring
* The plugin has been revised and optimised for October v3.x.
* DB: Splitting of URL/status and contexts (CMS pages, model fields) in which the URLs are located
* URL-based approach: URLs are stored in a set so that URLs are only checked once
* cURL only calls headers

## Features
* Checks for broken links within database fields and CMS text files
* User controls which fields to check
* User controls which response codes should be logged
* User controls task scheduling
* Management and use of user agents (new in v2.x)
* Dashboard report widget with overview (new in v2.x)
* Selection of plugins and directories for the check in the settings (new in v2.x)

## Usage
A link status report can be found in the backend Settings tab.

## Future
* Check for relative/dynamic links
* Translate plugin

## Like this plugin?
If you like this plugin, give this plugin a Like or please consider making a donation.
