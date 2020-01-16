[![Travis](https://img.shields.io/travis/WPTRT/theme-sniffer.svg?style=for-the-badge)](https://travis-ci.org/WPTRT/theme-sniffer.svg?branch=master)
[![License: MIT](https://img.shields.io/github/license/WPTRT/theme-sniffer.svg?style=for-the-badge)](https://github.com/WPTRT/theme-sniffer/blob/master/LICENSE)
[![GitHub All Releases](https://img.shields.io/github/downloads/WPTRT/theme-sniffer/total.svg?style=for-the-badge)](https://github.com/WPTRT/theme-sniffer/releases/)

[![Minimum PHP Version](https://img.shields.io/packagist/php-v/wptrt/theme-sniffer.svg?style=for-the-badge&maxAge=3600)](https://packagist.org/packages/wptrt/theme-sniffer)
[![Tested on PHP 7.0 to nightly](https://img.shields.io/badge/tested%20on-PHP%207.0%20|%207.1%20|%207.2%20|%207.3|%20nightly-green.svg?style=for-the-badge&maxAge=2419200)](https://travis-ci.org/WPTRT/theme-sniffer)
[![Number of Contributors](https://img.shields.io/github/contributors/WPTRT/theme-sniffer.svg?maxAge=3600&style=for-the-badge)](https://github.com/WPTRT/theme-sniffer/graphs/contributors)

# Theme Sniffer

* [Description](#description)
* [Requirements](#requirements)
* [Installation](#installation)
* [Contributing](#contributing)
* [License](#license)

## Description

Theme Sniffer is a plugin utilizing custom sniffs for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) that statically analyzes your theme and ensures that it adheres to WordPress coding conventions, as well as checking your code against PHP version compatibility.

## Requirements

The Theme Sniffer requires:

* PHP 7.0 or higher.
* WordPress 4.7 or higher.

## Installation - development

### For themes development and checking

* Download [zip file](https://github.com/WPTRT/theme-sniffer/releases/download/1.1.0/theme-sniffer.zip). **Note**: Please use this distribution plugin zip. GitHub provided zip will not work.
* Install this as you normally install a WordPress plugin
* Activate plugin

### For Theme Sniffer development

* Clone this repository under `wp-content/plugins/`
* Run `composer install`
* Run `npm install`
* Run `npm run build`
* Activate plugin

#### On Windows

* When developing on Windows machine you might run into various issues with CLI when not installed globally. Installing following items globally might help, but use it with caution:

    * Run `npm install --global eslint-cli`
    * Run `npm install --global stylelint`
    * Run `composer global require "squizlabs/php_codesniffer=*"` & make sure that you have installed [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) and [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility) by running `phpcs -i`. When PHPCS installed globally, you might need clone both repositories and add them to PHPCS with `phpcs --config-set installed_paths /path/to/PHPCompatibility,/path/to/wpcs`

**Note**: If you build the plugin this way you'll have extra `node_modules/` folders which are not required for the plugin to run, and just take up space. They are to be used for the development purposes mainly. Some of the `vendor/` folders are necessary for Theme Sniffer to run

![Screenshot](screenshot.png?raw=true)

## Usage

* Go to `Theme Sniffer`
* Select theme from the dropdown
* Select options
* Click `GO`

### Options

* `Select Standard` - Select the standard with which you would like to sniff the theme
* `Hide Warning` - Enable this to hide warnings
* `Raw Output` - Enable this to display sniff report in plain text format. Suitable to copy/paste report to trac ticket
* `Ignore annotations` - Ignores any comment annotation that might disable the sniff
* `Check only PHP files` - Only checks PHP files, not CSS and JS - use this to prevent any possible memory leaks
* `Minimum PHP version` - Select the minimum PHP Version to check if your theme will work with that version

## Development

Development prerequisites:

* Installed [Node.js](https://nodejs.org/en/)
* Installed [Composer](https://getcomposer.org/)
* Test environment - [Local by Flywheel](https://local.getflywheel.com/), [VVV](https://varyingvagrantvagrants.org/), [Docker](https://www.docker.com/), [MAMP](https://www.mamp.info/en/), [XAMPP](https://www.apachefriends.org/index.html), [WAMP](http://www.wampserver.com/en/) (whatever works for you)

All of the development asset files are located in the `assets/dev/` folder. We have refactored the plugin to use the latest JavaScript development methods. This is why we are using [webpack](https://webpack.js.org/) to bundle our assets.

When wanting to add a new feature fork the plugin. If you are a maintainer create a `feature/*` branch.

To start developing, first clone this repo under `wp-content/plugins/`. Then run in the terminal:

`composer install`
`npm install`

Then you can run:

`npm run start`

This will run webpack in the watch mode, so your changes will be saved in the build folder on the fly. After you're done making changes, run:

`npm run build`

This will create the `assets/build/` folder with js and css files that the plugin will use and a zip file for installation.

If you want to skip creating the zip file, you can use

`npm run dev`

This command behaves like the build one, but it skips creation of the zip file.

When developing JavaScript code keep in mind the separation of concerns principle - data access and business logic should be separate from the presentation. If you 'sniff' (no pun intended) through the js code, you'll see that `index.js` holds all event triggers and calls the method for sniff start that is located in the separate `ThemeSniffer` class. Business logic modules should contain plain JavaScript (no framework), which makes it reusable. Of course, there is still room for improvement, so if you notice something that could be improved we encourage you to make a PR.

The same is valid for PHP code. The business logic is stored in the `src/` folder, the JS and CSS are located in `assets/` folder and the views are located in the `views/` folder.


