=== Absolutely Glamorous Custom Admin ===
Contributors: cusmin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=agca@cusmin.com&item_name=Support+for+AGCA+Development
Tags: admin, customize, hide, change admin, themes, admin themes, admin bar, login page
Requires at least: 3.0
Tested up to: 4.9.5
Stable tag: 6.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl.txt
 
All-in-one tool for admin panel customization. Change almost everything: admin menu, dashboard, login page, admin bar, add custom scripts and more.

== Description ==
With this plugin you can easily customize WordPress **admin panel**, **login page**, **admin menu**, **admin bar** etc. in tiny details and apply free responsive and commercial **admin themes**.

* **ADMIN BAR**: Hide or change admin bar items. Remove WordPress traces from admin bar. Brand it with custom colors.
* **ADMIN MENU**: Change and remove existing and add custom admin menu items. Brand it with custom colors.
* **COLORIZER**: This tool is integrated with Absolutely Glamorous Custom Admin panel and can be used to change default WordPress admin panel colors, admin bar, admin menu, login page, backgrounds and text.
* **BRANDING**: Brand your admin panel pages with custom content, colors, images and text.
* **ADMIN PANEL OPTIONS**: There are dozens of other admin panel options used to improve WordPress UX on admin and login pages items like Dashboard widgets, Help and Screen options, WordPress logos, links, menus etc. See the full list down below.

= Recommended =
> <strong>CUSMIN</strong><br>
> Cusmin is the premium version of the Absolutely Glamorous Custom Admin that gives you advanced features for customization and branding. It's perfect for professionals, but can be used on personal sites as well.<br><br>
> For more info about Cusmin, please go to [Cusmin](https://cusmin.com?ref=agca) site.<br><br>
> <strong>CUSMIN THEMES</strong><br>
> Free themes for your admin panel and login page.<br><br>
> For more info about Cusmin themes, please go to [Cusmin Themes](https://wordpress.org/plugins/cusmin-themes/).<br><br>

= General Settings =
* Hide "Screen Options" menu
* Hide "Help" menu
* Hide Color scheme on Profile page

= Admin Bar Settings =
* Hide admin bar completely
* Show Log out button only if admin bar is completely hidden
* Hide admin bar on site pages only
* Replace WordPress logo with custom branding image
* Change link on admin bar logo
* Custom page title template on admin pages (change <head> => <title> tag)
* Add custom branding image above the content in admin bar
* Hide WordPress logo
* Hide site name
* Hide admin bar logo context menu
* Hide update notifications block
* Hide "Comments" block
* Hide "New" menu block
* Hide "New" -> "Post"
* Hide "New" -> "Link"
* Hide "New" -> "Page"
* Hide "New" -> "User"
* Hide "New" -> "Media"
* Change site name to custom text
* Hide yellow WordPress update notification
* Change Howdy text
* Change Log Out text
* Remove "Edit My Profile" option from context menu
* Show "Log Out" button only on the right

= Admin Footer Options =
* Hide footer completely
* Change/remove footer text
* Change/remove footer version text

= Dashboard Page Options =
* Change Dashboard heading text
* Add custom Dashboard HTML content
* Hide dashboard widgets

= Login Page Options =
* Hide back to blog text
* Change back to blog text
* Change login image
* Change hyperlink on login image
* Hide login image
* Round corners on login boxes
* Hide register and lost password links
* Change hyperlink on register button
	
= Admin Menu Options =
* Rename menu and sub-menu items
* Hide menu and sub-menu items
* Add new buttons with custom links
* Remove icons from admin menu buttons
* Remove spaces between admin menu buttons
* Remove admin menu arrow on hover
* Round admin sub-menu popups
* Remove "Collapse" button	
* Add custom branding image above the admin menu
* Add custom link to branding image
* Change admin menu auto-folding settings
	
= Colorizer =
* Change background and text colors on admin and login page	
* Change admin menu colors
* Change widget colors

= Advanced =
* Add custom CSS
* Add custom JavaScript
* Export/import customization settings

= Contact =
If you are unable to login or you have any other problems, please check our [FAQ](http://wordpress.org/extend/plugins/ag-custom-admin/faq/) and [SUPPORT](http://wordpress.org/support/plugin/ag-custom-admin) page.

== Installation ==

1. Upload `ag-custom-admin` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to plugin's settings page: 'Tools => AGCA'

== Frequently Asked Questions ==

= I see errors on my page. What can I do? =
Please follow these steps:
<br>* Open browser's console and try to find JavaScript errors.
<br>* Check if you can locate the plugin that throws the error, if so, try disabling that plugin and contacting their support.
<br>* Try disabling plugins one-by-one to see when this problem occurs. If you can't access login page, please temporary disable JavaScript in your browser and refresh the page.
<br>* Try updating WordPress core and plugins
<br>* If problem still persists, please contact the support

= Plugin does not work after upgrading to new version. What should I do? =
Make sure to have latest WordPress version installed. If nothing of these solutions help, please contact support.

= I use older version of WordPress, can I still use this plugin? =
Yes, just make sure to install older version of AGCA that works for you.

= Admin Menu buttons are misaligned =
Please use 'Reset Settings' button on Admin Menu page to reset menu configuration to defaults. Make sure to use latest plugin version with latest patches.

= Some options are not working =
If you have the latest WordPress and plugin versions and you still experience some issues, please contact us and report the issue.

== Screenshots ==

1. Absolutely Glamorous Custom Admin provides many options for admin panel customization, and it's quite easy to use it.
2. Login page can be customized and styled with custom branding logo or image, rounded borders, background colors.
3. Colorizer options for branding with custom colors.
4. Custom Admin menu items.
5. Admin menu editor.
6. Add your own styles and JavaScript code.
7. Style Example #1
8. Style Example #2
9. Style Example #3
10. Style Example #4
11. Login style example

== Change Log ==

= 6.2 =
* Fixed 500 error in Multisite

= 6.1 =
* Fixed Log Out button issue

= 6.0.1 =
* Bug fixes.

= 6.0 =
* AGCA Re-branding
* Added support for shortcodes in custom dashboard content

= 5.7.4 =
* Updated plugin links to secure links

= 5.7.3 =
* Fix for ABSPATH value
* Added option to hide profile color scheme

= 5.7.1 =
* Added General Options tests

= 5.7 =
* Added Save button on top
* Resolved blank page issue on first time install
* Few other small improvements
* Ready for WP 4.7
* Resolved issue: QuotaExceededError due to LocalStorage when Private Browsing in Safari
* Added acceptance test suite

= 5.6.5 =
* Resolved Colorizer background color issue in WP 4.6
* Removed AGCA script from admin bar front end when user is not logged in
* Removed WP version from AGCA front end script
* Login page compatibility with WP Cerber plugin

= 5.6.4 =
* Added option to remove Customize button from admin bar on front end
* Re-organized admin hooks
* Changed icons for removing Profile and Customize buttons from "plus" to "eye" icon

= 5.6.3 =
* Compatibility with WordPress 4.6
* Resolved styling issue for AGCA admin menu button, when default menu is hidden
* Improved styling of admin bar logo
* A couple of visual improvements on input fields and text
* Removed dashboard empty box placeholders

= 5.6.2 =
* Updated readme.txt

= 5.6.1 =
* Added branding images for AGCA
* Protocol agnostic jQuery script url on login page
* Updated upgrade options

= 5.6 =
* Internationalization, ready for translation
* Added option for removing Profile menu item from user's menu
* Added patch for network admin dashboard notices
* Code refactoring
* Resolved save issues in multi-site environment (POST validation failing)
* Added patch for WPS Hide Login compatibility in multi-site environment
* Added max width for custom admin header image
* Removed "sub-menu" text from sub menu items in admin menu editor

= 5.5 =
* Resolved JavaScript error on AGCA page in WP 4.5 version
* Added compatibility support with Cusmin plugin
* Added compatibility support with WPS Hide Login plugin
* Changed plugin versioning

= 1.5.4.3 =
* Patched security issues.

= 1.5.4.2 =
* Fixed new post blank page issue

= 1.5.4 =
* Patched security issues
* Added support for custom CSS on public pages
* Fixed appearance issus for custom admin bar icon and log out button on mobile devices and smaller screens
* Fixed missing link issue with Back to blog link when custom name is used
* Added AGCA social buttons
* Fixed broken links in admin bar and plugins page on multisite network site
* Removed php short tags that are not supported in older PHP versions
* Updated license to GPLv3

= 1.5.2 =
* Small UX improvements on options

= 1.5.1 =
* Added support for older PHP versions, starting from 5.2

= 1.5 =
* Improved UX with new icons and styles
* Fixed admin menu issues when menu is updated outside of AGCA
* Code reorganizing
* Using dashboard icons instead of images
* Updated option names

= 1.4.9 =
* Issues fixed

= 1.4.8.2 =
* Added check for non existing 'pagenow' GLOBAL variable
* Using default WordPress theme when AGCA theme is activated
* Fixed unknown font theme issue
* Option to remove AGCA themes from admin bar

= 1.4.8.1 =
* Small bug fix

= 1.4.8 =
* Style improvements
* Tab navigation remembers previously used tab

= 1.4.7 =
* Removed AG Custom Admin menu top item
* Better error handling

= 1.4.6 =
* Removed obsolete Colorizer's border options
* Removed obsolete code
* Changed some options labels/descriptions
* Handling user's errors caused by mistake in Custom JS
* Removed first and second menu item separator options, replaced with remove all separators option

= 1.4.5 =
* Fixed issues

= 1.4.4 =
* Admin themes, caching support

= 1.4.3 =
* Fixes admin bar compatibility issue with BuddyPress on public pages
* Dashboard -> Updates submenu item can be hidden now
* Added address bar option for debugging

= 1.4.2 =
* Internal links changes, support for the new support site domain

= 1.4.1 =
* Support up to WordPress 4.1
* Improved branding
* Better error handling

= 1.4 =
* Fixed issue with Colorizer. Updated plugin colors.

= 1.3.9 =
* Fixed issue on Dashboard widgets page: Disabled third-party widget cannot be re-enabled

= 1.3.8 =
* Central alignment for smaller login images, and for different form widths
* Option to remove "WordPress News" dashboard widget
* Better "Error Caught" message with more useful information

= 1.3.7 =
* Fixed issues with custom dashboard content

= 1.3.6 =
* Fixed issues with special characters in admin menu editor

= 1.3.5 =
* Prepared for WordPress 3.9
* Fixed style for custom Howdy name

= 1.3.4 =
* Fixed add_filter undefined function error
* Better exception handling

= 1.3.3 =
* Fixed compatibility issues with MailPoet plugin

= 1.3.2 =
* Bug fixes

= 1.3.1 =
* First production version with AGCA Themes

= 1.3 =
* Introducing AG Custom Admin Themes

= 1.2.8 =
* Fixed PHP errors in DEBUG mode

= 1.2.7.9 =
* Fixed remove admin submenu arrow option in WordPress 3.8

= 1.2.7.8 =
* Fixed admin menu bug

= 1.2.7.7 =
* Updated options labels
* Better checkbox and radio box styles
* Some additional style improvements
* Updated option for auto folding admin menu on mobile devices
* Fixed AGCA error popup on reset password page

= 1.2.7.6 =
* Better style for close button
* Fixed bugs when adding new admin menu buttons

= 1.2.7.5 =
* Better style for close button
* Removed deprecated jQuery live function
* Including jQuery migrate plugin on Advanced tab

= 1.2.7.4 =
* Removing submenu box on mouseover, if all submenu items are hidden (Fixed empty box issue)
* Added WYSIWYG editor for Dashboard page

= 1.2.7.3 =
* Fixed single quote character escaping on several settings
* Added option for removing AGCA JS warning message
* Fixed "Illegal string offset" server error

= 1.2.7.2 =
* Escaping characters (like single quote) in footer text
* Fixed AGCA alert message issue
* Prepared for 3.6. WordPress release
* Other bug fixes.

= 1.2.7.1 =
* Removed AGCA script on site pages automatically, if user is not logged in
* Custom WordPress admin title. Using custom title templates
* Added option for removing blog title from top bar
* Fixed misaligned menu when using custom content types plugins
* Fixed bug "Save button does not work" caused by unescaped characters in footer text and custom button names
* Updated AGCA buttons styles

= 1.2.7 =
* WordPress 3.5 compatible
* Added login page background color
* Resolving jQuery script
* Added feedback buttons (positive/negative)
* Better user's experience (updated form buttons and textbooks)
* Fixed problem with Howdy renaming

= 1.2.6.5 =
* using capabilities instead of user levels
* define who is admin with choosing capability (Advanced tab)
* added option to hide admin bar on front end only
* added option to remove all AGCA customizations on front end
* added option to remove register button on login page
* added option to change hyperlink on register button on login page
* added option to remove "Lost Password" link on login page
* added support for collapse button on customized admin menu
* option to show/hide collapse button on admin menu
* added styles for collapsed menu
* added WP filter to remove admin bar on front page
* applying admin bar colors on front page
* fixed blank space in admin panel when admin bar is removed
* fixed some bugs in Colorizer, better updating of input box colors, triggering colors on input box change, improved realtime color updating
* adding jQuery script on login page only if it is not already loaded
* fixed background-size issue on login page image in chrome and some other browsers
* updated important message on admin menu tab

= 1.2.6.4 =
* Removing plugin options on plugin uninstall instead of on plugin deactivation
* Refreshed some button styles
* "But show logout button" option auto-hiding
* Fixed repeat login image bug
* Fixed bug on loading news

= 1.2.6.3 =
* Included Colorizer ON/OFF option to exported settings
* Automatically saving settings after importing
* Fixed issue with admin bar on site pages
* Added option for switching between admin and site pages(on WP logo, top left corner on admin bar)
* Fixed custom Howdy text for other languages than English.

= 1.2.6.2 =
* Added custom brand logo url
* Added %BLOG% variables for custom urls
* Option for removing custom admin menu settings in export

= 1.2.6.1 =
* Bug fixes
* Added +/- indicators to admin menu editor
* Custom top bar logo can be of any width. Height is expanded to 28px
* Saving custom scripts to database instead of saving to files
* Fixed error message on WordPress thick-box window
* Removed About WordPress message on top bar logo(on mouseover)
* Using %BLOG% variable as hyperlink on login page image
* Few other fixes on WordPress top bar
* Fixed color bug on admin menu hover()

= 1.2.6 =
* Fixed hiding top bar issue for site pages
* Fixed issues with custom content on Dashboard page. Any custom HTML can be used now.
* Custom CSS script support
* Custom JAvaScript support
* Exporting / importing AG Custom Admin customizations
* Added custom branding field above the admin menu for adding custom brand images
* Rounding admin menu popups and login page
* Option for removing Welcome dashboard widget
* New Colorizer options for admin menu, top bar
* New AGCA info area
* Fixed caching issues on updates

= 1.2.5.4 =
* Fixed fatal error from version 1.2.5.3

= 1.2.5.3 =
* Fixed issues related to WP 3.3.1 version
* Fixed bug: Hiding/changing top admin bar WP icon
* Advanced customization of admin top bar
* Added options for hiding admin top bar elements
* Login page background same as admin background
* Added target options for custom admin buttons

= 1.2.5.2 =
* Fixed several issues with WP 3.3
* Added options for hiding "comments" and "new" from admin bar
* Support for custom admin bar images
* Support for custom Log Out text
* Hide everything except Log Out button works now
* Removed few obsolete options
* Removing "Edit My Profile"
* Fixed bug when trying to edit custom buttons in admin menu

= 1.2.5.1 =
* Plugin tested up to 3.3
* Fixed major issues with WP 3.3. version
* Fixed bug when trying to edit custom buttons in admin menu

= 1.2.5 =
* Plugin tested up to 3.2.1
* Fixed some compatibility issues with other plugins
* Tested compatibility with Ozh' Admin Drop Down Menu v3.6.1
* Tested compatibility with SexyBookmarks (by Shareaholic) v4.0.5.6
* Fixed blank screen of death if an error from outside of plugin is thrown
* Improved error handling and showing
* Improved versioning

= 1.2.4 =
* Plugin is now fully compatible with WordPress version 3.2
* Fixed update notification bar for lower WP versions
* Login image can be now of any size.
* Improved error management
* Added options in 3.2 for changing background behind admin menu
* Added option in 3.2 for removing "Your profile" from admin bar
* Added invalid characters escaping when adding new custom buttons
* Fixed few minor issues

= 1.2.3 =
* Added Colorizer options for changing widgets colors.
* Added option for changing logo image in admin bar with custom image.
* Fixed bug on add new post page: After clicking on buttons Add new image, Add new media pop-up window is blank.
* Added info bar for displaying news and information about plugin.
* Fixed few issues.

= 1.2.2 =
* Added Colorizer for changing colors in admin and login panel
* Added option for excluding admin from settings.
* Fixed bug for slow computers: Default settings are visible few moments before applying custom settings.
* Added option for removing icons from admin menu buttons

= 1.2.1 =
* Improved accessibility
* Optimization for screen readers
* Added sub-page titles
* Updated styles in edit admin menu table

= 1.2 =
* All elements are grouped into small pages for better navigation.
* Added color styles and improved user experience.
* Added settings page for fully control of main admin menu.
* Added support for hiding items from admin menu.
* Added support for changing items in admin menu.
* Added support for adding new custom items in admin menu.
* Added tool tips on option labels for better explanation of option.
* Deprecated 'Hide Dashboard button from main menu' in 'Dashboard settings'. This option can be used now in 'Admin menu settings'.
* Added settings for hiding/showing Dashboard widgets:"Recent Comments", "Incoming Links", "Plugins", "Quick Press", "Right Now", "Recent Drafts", primary widget area, secondary widget area.
* Added option "(but show 'Log Out' button)" for displaying 'Log Out' button if admin top bar is completely removed.
* Added option "Hide footer text".
* Added support for hiding new WordPress release notification.

= 1.0.1 =
* Changed text in Settings page to be more clearer.
* Updated list of options on plugin page
* Removed blank characters in text areas

= 1.0 =
* Initial version

== Upgrade Notice ==

= 6.2 =
* Fixed 500 error in Multisite

= 6.1 =
* Fixed Log Out button issue

= 6.0.1 =
* Bug fixes

= 6.0 =
* AGCA Re-branding and improvements

= 5.7.4 =
* Updated plugin links to secure links

= 5.7.3 =
* Fix for ABSPATH value, and added option to hide profile color scheme

= 5.7.1 =
* Added tests.

= 5.7 =
* Bug fixes. Ready for WordPress 4.7.

= 5.6.5 =
* Compatibility improvements

= 5.6.4 =
* Added new setting. Improvements.

= 5.6.3 =
* Compatibility with WordPress 4.6

= 5.6.2 =
* Updated readme

= 5.6.1 =
* Minor plugin changes

= 5.6 =
* Internationalization. Support for removing admin menu Profile button. Other patches and compatibility fixes.

= 5.5 =
* Compatibility updates with WP 4.5 and some other plugins

= 1.5.4.3 =
* Patched security issues.

= 1.5.4.2 =
* Fixed new post blank page issue.

= 1.5.4 =
* Fixed several very important issues. Highly recommended upgrade.

= 1.5.2 =
* Small UX improvements on options

= 1.5.1 =
* Added support for older PHP versions, starting from 5.2

= 1.5 =
* Improved UX with new icons and styles
* Fixed admin menu issues when menu is updated outside of AGCA
* Code reorganizing
* Using dashboard icons instead of images
* Updated option names

= 1.4.9 =
* Issues fixed

= 1.4.8.2 =
* Added check for non existing 'pagenow' GLOBAL variable
* Using default WordPress theme when AGCA theme is activated
* Fixed unknown font theme issue
* Option to remove AGCA themes from admin bar

= 1.4.8.1 =
* Small bug fix

= 1.4.8 =
* Style improvements
* Tab navigation remembers previously used tab

= 1.4.7 =
* Improvements

= 1.4.6 =
* Refactoring. Removed obsolete code. Fixes some issues.

= 1.4.5 =
* Fixed issues

= 1.4.4 =
* Admin themes, caching support

= 1.4.3 =
* Fixed some issues

= 1.4.2 =
* Internal links changes

= 1.4.1 =
* Improvements

= 1.4 =
* Fixed issue with Colorizer. Updated plugin colors.

= 1.3.9 =
* Fixed issue on Dashboard widgets page

= 1.3.8 =
* Bug fixes: WordPress News dashboard widget, login image center alignment

= 1.3.7 =
* Fixed issues with custom dashboard content

= 1.3.6 =
* Fixed issues with special characters in admin menu editor

= 1.3.5 =
* Prepared for WordPress 3.9
* Fixed style for custom Howdy name

= 1.3.4 =
* Bug fixes. Better error handling.

= 1.3.3 =
* Fixed compatibility issues with MailPoet plugin

= 1.3.2 =
* Bug fixes

= 1.3.1 =
* Introducing AG Custom Admin Themes

= 1.3 =
* Introducing AG Custom Admin Themes

= 1.2.8 =
* Fixed PHP errors in DEBUG mode

= 1.2.7.9 =
* Fixed remove admin submenu arrow option in WordPress 3.8

= 1.2.7.8 =
* Fixed admin menu bug

= 1.2.7.7 =
Updated admin menu auto-folding options. Fixed bug on reset password page. Updated options styles.

= 1.2.7.6 =
Bug fixes. Better close button styles.

= 1.2.7.5 =
Better style for close button. Removed deprecated jQuery live function.

= 1.2.7.4 =
Few improvements.

= 1.2.7.3 =
Bug fixes.

= 1.2.7.2 =
Bug fixes. Preparation for 3.6 WordPress

= 1.2.7.1 =
Bug fixes. Removing admin bar site title. Custom page titles in WP admin.

= 1.2.7 =
Plugin prepared for WordPress 3.5. Few additional improvements.

= 1.2.6.5 =
New features and bug fixes. Improved Colorizer, fixed admin bar issues, using capabilities to define admin users, added new options for login page, options to remove AGCA scripts on front-end. Using collapsed menus.

= 1.2.6.4 =
Bug fixes. Removing plugin's option on uninstall, not on deactivation.

= 1.2.6.3 =
Fixed several bugs: Admin bar issues, not saving settings after import, changing Howdy text in other languages, Colorizer ON/OFF option in settings export.

= 1.2.6.2 =
 Ready for WordPress 3.4. A lot of improvements in 1.2.6 series, fixed issues with top bar, caching on updates, better styles...  and new features: export/import new Colorizer options, better admin menu styles, custom css, custom js, admin bar on site pages.. Fixed bugs since 1.2.6 version. Wrapped up 1.2.6 series release.

= 1.2.6.1 =
Fixing bugs from 1.2.6 version

= 1.2.6 =
A lot of improvements in 1.2.6, fixed issues with top bar, caching on updates, better styles, etc.  and new features: export/import new Colorizer options, better admin menu styles, custom css, custom js, admin bar on site pages etc.

= 1.2.5.4 =
Fixed fatal error from version 1.2.5.3

= 1.2.5.3 =
Fixed issues related to WordPress version 3.3.1

= 1.2.5.2 =
Full compatibility with WP 3.3. Fixed several issues with this version. Removed obsolete options for old WP versions, added new options to fit 3.3 version. Update is highly recommended for WP 3.3 users.

= 1.2.5.1 =
Plugin is ready for 3.3. WordPress version. Major issues with WP 3.3 are fixed. However, some options are not fully functional, yet. Recommended only for users with 3.3. WP version. Fixed bug with custom admin menu buttons.

= 1.2.5 =
Improved compatibility with other plugins. Tested with Ozh' Admin Drop Down Menu and SexyBookmarks. Fixed screen freezing and white screen of death. Improved error handling and showing.

= 1.2.4 =
This plugin version is ready for WordPress release 3.2, and fully compatible with it. Added few new options, and improved plugin stability.

= 1.2.3 =
Fixed 'White screen of death' bug, and few other improvements. Added Colorizer options for widgets. Added support for changing admin bar logo with custom image.

= 1.2.2 =
Added demo Colorizer for adding custom colors in admin and login pages. Fixed bug for slow computers, default settings should not be visible before custom settings. Added support for excluding administrator from settings. Added option for removing icons from admin menu buttons.

= 1.2.1 =
This is minor upgrade from 1.2 to 1.2.1 version. Mostly it is based on optimization for screen readers and improving accessibility. You definitly need to upgrade this plugin if you have version older than 1.2.

= 1.2 =
This is major milestone of this plugin. All elements are grouped and organized into small pages for better navigation.
Settings are styled in better way and some attractive interactions are added to make using of this plugin very easy.
Added settings for fully customization of admin menus.

= 1.0.1 =
Better explanations in Settings page. No changes in functionality. 

= 1.0 =
Initial version.