=== AG Custom Admin ===
Contributors: argonius
Donate link: http://agca.argonius.com/ag-custom-admin/support-for-future-development
Tags: admin, customize, hide, change admin
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.2.7

Hide or change items in admin panel. Customize buttons from admin menu. Colorize admin and login page with custom colors.

== Description ==
With this plugin you can hide or change unwanted items in admin and login pages (like admin bar or footer text, remove Screen options, Help options or Favorites dropdown menu etc).
You can also completely change or hide buttons from admin menu, or add new customized buttons. With Colorizer you can completely customize background and text colors in admin and login page.

Here is the list of options:

	Admin Bar Options
	- Hide admin bar completely
	- Hide admin bar on front end
	- Change admin bar logo and link
	- Hide admin bar WordPress logo
	- Add custom image in admin header
	- Add custom admin bar logo
	- Hide admin bar dropdown menus
	- Hide "New" dropdown items
	- Hide comments from admin bar
	- Hide updates from admin bar
	- Hide WordPress update notification bar
	- Hide default blog heading in admin bar
	- Change admin bar heading text
	- Hide Screen Options menu
	- Hide Help menu
	- Hide Favorite Actions
	- Change/remove Howdy text
	- Change Log out text
	- Log out button only

	Admin Footer Options
	- Hide footer completely
	- Change/remove footer text
	- Change/remove footer version text

	Dashboard Page Options
	- Hide Dashboard heading icon
	- Change Dashboard heading text
	- Add custom Dashboard content
	- Hide dashboard widgets

	Login Page Options
	- Hide back to blog completely
	- Change login image	
	- Change hyperlink on login image
	- Hide login image
	- Round corners on login boxes
	- Hide register and lost password links
	- Change hyperlink on register button
	
	Admin Menu Options
	- Rename menu and submenu items
	- Remove menu and submenu items
	- Add new buttons with custom links
	- Remove icons from admin menu buttons
	- Remove spaces between admin menu buttons
	- Remove admin menu arrow on rollower
	- Round admin submenu popups	
	- Add custom branding image above the admin menu
	- Add custom link to branding image
	
	Colorizer
	- Change background and text colors on admin and login page	
	- Change admin menu colors
	- Change widget colors
	
	Advanced
	- Add custom CSS
	- Add custom JavaScript
	- Export/import customization settings


For more information about the plugin please see: http://agca.argonius.com/ag-custom-admin/

== Installation ==

1. Upload `ag-custom-admin` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Activated plugin should appear in 'Tools' menu

== Frequently Asked Questions ==

= Plugin does not work after upgrading to new version. What should I do? =
Try to clear browser's cache, and reload page. In extreme situations, you would need to remove plugin and to download and install fresh installation from WordPress repository.

= I see only blank page. What to do? =
This is caused by JavaScript error thrown by AGCA or some other plugin. If you want to deactivate plugin, but you can't access admin panel, turn off JavaScript in your browser and than log in back and deactivate plugin (This works only for administrator). Try also to clear browser's cache. If this does not work, try to find javaScript error in browser's console and post it to plugin's support page.

= Something is wrong with Admin Menu buttons =
Please use 'Reset Settings' button on Admin Menu page to reset menu configuration to defaults. Remember that you should postpone admin menu configuration to the very end of admin page customization, because, any changes made from other plugins to admin menu (e.g adding new button of plugin that is activated, or removing that button when plugin is deactivated) could corrupt admin menu configuration.

= Some errors appear on my page =
Go to browser's console and see if there are any errors. Try to locate them. If you can't fix error by yourself, post it back to plugin's support page.

= Plugin does not work =
Try clearing browser's cache. If that does not work, go to browser's console and see if there are any errors. Try to locate them. If you can't fix error by yourself, post it back to plugin's support page. When trying to locate the problem, the best way is to disable other plugins, because there could be a conflict with them. If there is a conflict with other plugin, it can be found by disabling one by one plugin, until the problem is solved.


== Screenshots ==

1. AG Custom Admin provides most of options for Admin Panel customization, and it's easy to use.
2. Login page can be customized and styled with custom branding logo or image, rounded borders, background colors.
3. Admin menu is completely customizable. You can rename or remove items from menu and submenu, or add brand new buttons with custom links.
4. Default WP grey colors can be refreshed with any colors that you like! There are a lot of Colorizer option which can be use to style text, background, login, widgets, admin menu, top bar, footer and many more!

== Changelog ==

= 1.2.7 =
* WordPress 3.5 compatible
* Added login page background color
* Resolving jQuery script
* Added feedback buttons (postitive/negative)
* Better user's experience (updated form buttons and textboxes)
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
* appling admin bar colors on front page
* fixed blank space in admin panel when admin bar is removed
* fixed some bugs in colorizer, better updating of input box colors, trigering colors on input box change, improved realtime color updating
* adding jquery script on login page only if it is not already loaded
* fixed background-size issue on login page image in chrome and some other browsers
* updated important message on admin menu tab

= 1.2.6.4 =
* Removing plugin options on plugin uninstall instead of on plugin deactivation
* Refreshed some button styles
* "But show logout button" option auto-hiding
* Fixed repeat login image bug
* Fixed bug on loading news

= 1.2.6.3 =
* Included colorizer ON/OFF option to exported settings
* Automaticaly saving settings after importing
* Fixed issue with admin bar on site pages
* Added option for switching between admin and site pages(on wp logo, top left corner on admin bar)
* Fixed custom Howdy text for other languanges than English.

= 1.2.6.2 =
* Added custom brand logo url
* Added %BLOG% variables for custom urls
* Option for removing custom admin menu settings in export

= 1.2.6.1 =
* Bug fixes
* Added +/- indicators to admin menu editor
* Custom top bar logo can be of any width. Height is expanded to 28px
* Saving custom scripts to database instead of saving to files
* Fixed error message on wordpress thickbox window
* Removed About WordPress message on top bar logo(on mouseover)
* Using %BLOG% variable as hyperlink on login page image
* Few other fixes on WordPress top bar
* Fixed color bug on admin menu hover()

= 1.2.6 =
* Fixed hidding top bar issue for site pages
* Fixed issues with custom content on Dashboard page. Any custom HTML can be used now.
* Custom CSS script support
* Custom JAvaScript support
* Exporting / importing AG Custom Admin customizations
* Added custom branding field above the admin menu for adding custom brand images
* Rounding admin menu popups and login page
* Option for removing Welcome dashboard widget
* New colorizer options for admin menu, top bar
* New AGCA info area
* Fixed caching issues on updates

= 1.2.5.4 =
* Fixed fatal error from version 1.2.5.3

= 1.2.5.3 =
* Fixed issues related to WP 3.3.1 version
* Fixed bug: Hidding/changing top admin bar WP icon
* Advanced customization of admin top bar 
* Added options for hiding admin top bar elements
* Login page background same as admin background
* Added target options for custom admin buttons

= 1.2.5.2 =
* Fixed several issues with WP 3.3
* Added options for hidding "comments" and "new" from admin bar
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
* Added support for hidding new Wordpress release notification.

= 1.0.1 =
* Changed text in Settings page to be more clearer.
* Updated list of options on plugin page
* Removed blank characters in textareas

= 1.0 =
* Initial version

== Upgrade Notice ==

= 1.2.7 =
Plugin prepared for WordPress 3.5. Few additional improvements.

= 1.2.6.5 =
New features and bug fixes. Improved colorizer, fixed admin bar issues, using capabilities to define admin users, added new options for login page, options to remove AGCA scripts on front-end. Using collapsed menus.

= 1.2.6.4 =
Bug fixes. Removing plugin's option on uninstall, not on deactivation.

= 1.2.6.3 =
Fixed several bugs: Admin bar issues, not saving settings after import, changing Howdy text in other languages, colorizer ON/OFF option in settings export.

= 1.2.6.2 =
 Ready for WordPress 3.4. A lot of improvements in 1.2.6 series, fixed issues with top bar, caching on updates, better styles...  and new features: export/import new colorizer options, better admin menu styles, custom css, custom js, admin bar on site pages.. Fixed bugs since 1.2.6 version. Wrapped up 1.2.6 series release.

= 1.2.6.1 =
Fixing bugs from 1.2.6 version

= 1.2.6 =
A lot of improvements in 1.2.6, fixed issues with top bar, caching on updates, better styles, etc.  and new features: export/import new colorizer options, better admin menu styles, custom css, custom js, admin bar on site pages etc.

= 1.2.5.4 =
Fixed fatal error from version 1.2.5.3

= 1.2.5.3 =
Fixed issues related to WordPress version 3.3.1

= 1.2.5.2 =
Full compatibility with WP 3.3. Fixed several issues with this version. Removed obsolete options for old WP versions, added new options to fit 3.3 version. Update is highly recommonded for WP 3.3 users.

= 1.2.5.1 =
Plugin is ready for 3.3. WordPress version. Major issues with WP 3.3 are fixed. However, some options are not fully functional, yet. Recommended only for users with 3.3. WP version. Fixed bug with custom admin menu buttons.

= 1.2.5 =
Improved compatibility with other plugins. Tested with Ozh' Admin Drop Down Menu and SexyBookmarks. Fixed screen freezing and white screen of death. Improved error handling and showing.

= 1.2.4 =
This plugin version is ready for WordPress release 3.2, and fully compatible with it. Added few new options, and improved plugin stability.

= 1.2.3 =
Fixed 'White screen of death' bug, and few other improvements. Added colorizer options for widgets. Added support for changing admin bar logo with custom image.

= 1.2.2 =
Added demo Colorizer for adding custom colors in admin and login pages. Fixed bug for slow computers, default settings should not be visible before custom settings. Added support for excluding administrator from settings. Added option for removing icons from admin menu buttons.

= 1.2.1 =
This is minor upgrade from 1.2 to 1.2.1 version. Mostly it is based on optimization for screen readers and improving accessibility. You definitly need to upgrade this plugin if you have version older than 1.2.

= 1.2 =
This is major milestone of this plugin. All elements are grouped and organized into small pages for better navigation. 
Settings are styled in better way and some attractive interactions are added to make using of this plugin very easy.
Added settings for fully customization of admin menus.

= 1.0.1 =
Better explainations in Settings page. No changes in functionality. 

= 1.0 =
Initial version.
