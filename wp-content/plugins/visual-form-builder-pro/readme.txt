=== Visual Form Builder Pro ===
Contributors: mmuro
Requires at least: 3.4.1
Tested up to: 3.5
Stable tag: 2.1.2

Visual Form Builder Pro is an affordable WordPress plugin that helps you build beautiful, fully functional forms in only a few minutes without writing PHP, CSS, or HTML.

== Release Notes ==

**Version 2.1.2**

* Fix API call affecting WordPress plugins screen
* Properly load i18n file with plugins_loaded action

**Version 2.1.1**

* Fix bug where some server PHP configurations do not properly check method_exists

**Version 2.1**

* Add Akismet support
* Add Merge Tag screen option to help with templating
* Add more actions to email and confirmation functions
* Add integration support for new Add-Ons
* Update Analytics charts
* Update Analytics to include a Date filter
* Update Add New page, require Email details
* Update query for getting form data
* Update All Forms meta boxes link layout
* Update some JS files to local files instead of CDN
* Update Help tab to mirror Documentation on website; display on all pages now
* Register styles before enqueuing
* Properly hook update DB and SQL install to plugins_loaded action
* Increase size of field_rule from TEXT to LONGTEXT
* Fix Bulk Add when deleting options and/or Allow Other
* Fix bug for some form item descriptions where HTML tags were not encoded on display
* Fix bug for incorrect capability check on Import and Export pages
* Fix bug when duplicating forms when conditional fields are present

**Version 2.0.1**

* Add filter for removing attachments from email
* Update email headers
* Fix bug where notification email did not send
* Fix textarea value formatting in HTML email

**Version 2.0**

* Add Entries Allowed feature
* Add Form Schedule feature
* Add Duplicate Field feature
* Add Name field
* Add Other text input option to Radio field
* Add Word Count feature to Textarea field
* Add Tab Delimited option to Export
* Add CSS Class option to Submit button
* Add confirmation box to delete field
* Add more sanitization to form inputs
* Add new filters: Address labels, prepend confirmation message, CSV delimiter, word count message
* Update some filters to now include form ID
* Update jQuery UI CSS to pull locally instead of CDN
* Update first fieldset warning and output a more noticeable error
* Update tooltip CSS
* Update design of field item action links
* Fix bug where paragraph tags were added to Textarea in Plain Text emails
* Fix placeholder size when creating a new form item by dragging
* Fix media button to use correct action
* Fix mismatched translation strings

**Version 1.9.2**

* Add widget
* Update CSS to now prefix all classes to help eliminate theme conflicts
* Update email function to force a From email that exists on the same domain
* Update form/email previews to better anticipate where wp-load.php is going to be
* Update Email Designer and Analytics pages to check if forms exist before outputting content
* Fix bug affecting File Upload field validation
* Fix bug where inline form preview would not be visible if switching to third column layout
* Fix database install to use PRIMARY KEY instead of UNIQUE KEY
* Fix bug where JS may not work in IE
* Minor code cleanups

**Version 1.9.1**

* Add filter to let saving an entry optional
* Update Form Preview with new JS and CSS
* Fix bug where notification name was not being reset
* Fix bug removing errant console.log from JS
* Fix forms with the File Upload field by adding the accept() method back to the JS file
* Fix bug where form subject and title was not being escaped in the email/form preview
* Try to suppress getimagesize errors for some servers
* Fix bug where long sender emails were not saved properly in the entries table
* Fix bug where error messages were not printed during import

**Version 1.9**

* Add new Conditional Logic feature
* Add new Templating feature to subjects and confirmation messages
* Add Bulk Add filter for custom lists
* Add action vfb_after_email
* Add server-side input sanitization
* Add line breaks to textarea values in email/entries
* Add new template tag function and a template action
* Update forms listing to now sort by alphabetical order
* Update JavaScripts to now pull from Microsoft AJAX instead of Google and use SSL
* Update email function to no longer use mail_header filters
* Fix username validation to match WordPress requirements
* Fix bug where a single form export would always force the first form
* Fix bug where form export would fail in Safari
* Fix NetworkError 404 that appears when viewing the Form Preview

**Version 1.8**

* Add new Live Form Preview
* Add new All Forms box listing with drag and drop reordering
* Add new New Form screen
* Add new Quick Switch form selector
* Add customizable columns to admin form builder (see Screen Options tab)
* Update meta boxes to be reordered or hidden (see Screen Options tab)
* Update and clean up entry form design
* Fix bug where form rendering would behave erratically in Internet Explorer 9
* Fix bug where saving an entry in details view would redirect back out to the list view
* Minor admin CSS and JS updates

**Version 1.7.4**

* Fix bug where verification would validate, whether it was set to display or not

**Version 1.7.3**

* Fix bug where jQuery wasn't included for PayPal redirect
* Fix error during plain text email send

**Version 1.7.2**

* Fix bug for items with duplicate ID attributes
* Fix bug where Date Picker would not select a date
* Fix misspelled function name in upgrade function
* Fix bug where HTML buttons would not be displayed
* Fix bug where images less than 600px would not be uploaded in the email design

**Version 1.7.1**

* Fix bug where PayPal fields were not being set

**Version 1.7**

* Add new Import and Export pages
* Add new capabilities for both import and export
* Add new VFB Pro menu to the WordPress admin toolbar
* Deprecate Export All from Entries Bulk Actions (to export entire forms, see new Export page)
* Update name attribute to remove field key in attempts to prevent $_POST limit from reaching max memory
* Fix bug where form name override was not being updated when copying a form
* Minor admin CSS update

**Version 1.6.1**

* Fix media button submit button
* Update JavaScript files to only load on pages that include the shortcode
* Minor admin layout fixes

**Version 1.6**

* Add sticky scroll to Form Items sidebar
* Add collapse ability to Form Items and Form Output boxes
* Add Bulk Add Options feature
* Add Header Image option to Email Design
* Fix a few minor bugs
* Update entries data field from TEXT to LONGTEXT
* Update media button to now use AJAX instead of hidden HTML in the footer

**Version 1.5**

* Add ability to turn off the spam Verification section
* Add custom capabilities for user roles
* Add various filters
* Add nag message if free version of Visual Form Builder is detected and still active
* Fix bug in Analytics and Email Design where the initial form might not display
* Fix bug where certain rows in the email would not use the alt row color
* Fix bug for plain text email formatting
* Fix bug where notification email would send as HTML even if plain text was selected
* Update subnav to accommodate new custom capabilities
* Update list of spam bots
* Update spam bot check to only execute when form is submitted

**Version 1.4.1**

* Fix bug where Export feature was broken
* Fix bug where server validation failed on certain data types
* Add months drop down filter to Entries list

**Version 1.4**

* Add media button to Posts/Pages to easily embed forms
* Add search feature to Entries
* Add Notes field to Entries detail
* Add Default Value option to fields
* Add Default Country option to Address block
* Fix bug where Plain Text emails would not send
* Fix bug where Required option was not being set on File Upload fields
* Fix bug where Form Name was not required on Add New page
* Update plugin menus to be added the "right" way
* Update jQuery UI version to 1.8.19
* Update and optimize Entries query
* Update menu icon to custom form icon (thanks to Paul Armstrong Designs!)
* Update Security Check messages to be more verbose
* Update email formatting to add line breaks
* Update how the entries files are included to eliminate PHP notices
* Minor updates to CSS

**Version 1.3.1**

* Fix bug that prevented URL field from passing server side validation
* Updated translation file

**Version 1.3**

* Add Drag and Drop ability to add Form Items
* Add Plain Text email design option
* Add Additional Footer Text option
* Add option to remove footer link back
* Add Label Alignment option
* Add server side form validation; SPAM hardening
* Add inline Field help tooltip popups
* Update Form Settings UI
* Update File Upload field to place attachments in Media Library
* Update Field Description to allow HTML tags
* Update Field Name and CSS Classes to enforce a maxlength of 255 characters
* Fix bug preventing form deletion
* Fix bug preventing Custom Static Variable in Hidden Field
* Fix bug where Verification and Secret fields were displayed on Entries Detail page

**Version 1.2**

* Add Accepts option to File Upload field
* Add Small size to field options
* Add Options Layout to Radio and Checkbox fields
* Add Field Layout to field options
* Update jQuery in admin
* Verification fields now customizable
* Verification field now can be set to not required

**Version 1.1.1**

* Fix bug where adding fields was broken

**Version 1.1**

* Fix bug where assigning a price to PayPal did not save
* Minor updates to CSS
* Minor updates to database structure

**Version 1.0**

* 10 new Form Fields (Username, Password, Color Picker, Autocomplete, and more)
* Edit and Update Entries
* Quality HTML Email Template
* Email Designer
* Analytics
* Data & Form Migration
* PayPal Integration
* Form Paging
* No License Key
* Unlimited Use
* Automatic Updates