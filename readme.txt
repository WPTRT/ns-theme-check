=== Theme Sniffer ===
Contributors: dingo_d, rabmalin, grapplerulrich, timph, vyskoczilova, abdullahramzan, williampatton, passoniate
Tags: check, checker, coding standards, theme, tool
Requires at least: 5.0
Tested up to: 5.3.2
Requires PHP: 7.0
Stable tag: 1.1.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Theme Sniffer will help you analyze your theme code, ensuring the PHP and WordPress coding standards compatibility.

== Description ==

Theme Sniffer is a plugin utilizing custom sniffs for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) that statically analyzes your theme and ensures that it adheres to WordPress coding conventions, as well as checking your code against PHP version compatibility.

== Installation ==

= Installing from WordPress repository: =

1. From the dashboard of your site, navigate to Plugins –> Add New.
2. In the Search type Theme Sniffer
3. Click Install Now
4. When it’s finished, activate the plugin via the prompt. A message will show confirming activation was successful.

Make sure that your server has php version greater or equal to 7.0, otherwise, the plugin won't activate.

= Uploading the .zip file: =

1. From the dashboard of your site, navigate to Plugins –> Add New.
2. Select the Upload option and hit "Choose File."
3. When the pop-up appears select the theme-sniffer.x.x.zip file from your desktop. (The ‘x.x’ will change depending on the current version number).
4. Follow the on-screen instructions and wait as the upload completes.
When it’s finished, activate the plugin via the prompt. A message will show confirming activation was successful.

== Screenshots ==

1. Theme Sniffer main screen

== Frequently Asked Questions ==

= How to use the plugin? =

* Go to `Theme Sniffer` menu
* Select theme from the drop-down
* Select the desired options
* Click `GO` to start the Theme Sniffer

= What options are there? =

* `Select Standard` - Select the standard with which you would like to sniff the theme
* `Hide Warning` - Enable this to hide warnings
* `Raw Output` - Enable this to display sniff report in plain text format. Suitable to copy/paste report to trac ticket
* `Ignore annotations` - Ignores any comment annotation that might disable the sniff
* `Check only PHP files` - Only checks PHP files, not CSS and JS - use this to prevent any possible memory leaks
* `Minimum PHP version` - Select the minimum PHP Version to check if your theme will work with that version

= How can I help with the development of the plugin? =

Go to the official repo on Github (https://github.com/WPTRT/theme-sniffer), fork the plugin, read the readme and go through the issues. Any kind of help is appreciated. Either manually testing or writing code is invaluable for the open source project such as this.

= Contributors and testers thanks =

Thanks to Danny Cooper, Liton Arefin and metallicarosetail (slack) for testing the plugin and finding bugs in the development stage.

Thanks to Abdullah Ramzan and Arslan Ahmed for fixing minor typos, William Patton for help with the required files checks. Thanks to Karolína Vyskočilová for finding out the issue with cross-env issue.

Thanks to the TRT for the support.


== Upgrade Notice ==

The latest upgrade mostly with development changes and some minor improvements in sniff handling.

== Changelog ==

= 1.1.2 =
* Added a way to remove node_modules, vendor, and test folders in the theme prior to sniffing
  * This fixes out of memory issues when large files are found
* Fixed minor styling issue
* Removed option to check JS files

= 1.1.1 =
* Fixed bug in the screenshot ratio calculation

= 1.1.0 =
* Added sniff codes that can be copied for easier whitelisting of the false issues
* Added readme validator
* Added Screenshot validator
* Added required files checks
* Added checks for core minimum PHP version
* Added a license validator
* Updated WPThemeReview coding standards to the 0.2.0 version
* Moved JS checking to esprima
* Moved installation error to admin notice
* Validation improvements
* Fixed annotation issue - the ingore annotation checkbox worked counter to what it should
* Fixed cross-env issue for development on Windows machines
* Minor fixes

= 1.0.0 =
* Added the WPThemeReview standard
* Added the theme prefix checks
* Added `Check only PHP files`option
* Added additional functionality
* Updated the PHPCS version to the latest one, as well as WPCS version
* Refactored the code structure to more modern workflow
* Theme tags are pulled from the API

= 0.1.5 =
* Change the development process
* Modern JS development workflow

= 0.1.4 =
* Using REST instead of admin-ajax for checks
* Code optimization

= 0.1.3 =
* Update zip link in the readme file

= 0.1.2 =
* Add option to display report in HTML or raw format
* Update to latest sniffs

= 0.1.1 =
* Fix sniffer issues in admin files

= 0.1.0 =
* Initial pre-release
