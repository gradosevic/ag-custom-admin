=== AG Custom Admin ===
Contributors: argonius
Donate link: http://wordpress.argonius.com/donate
Tags: admin, customize, hide, change admin
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.2.5

Hide or change items in admin panel. Customize buttons from admin menu. Colorize admin and login page with custom colors.

== Description ==
With this plugin you can hide or change unwanted items in admin and login pages (like admin bar or footer text, remove Screen options, Help options or Favorites dropdown menu etc).
You can also completely change or hide buttons from admin menu, or add new customized buttons. With Colorizer you can completely customize background and text colors in admin and login page.

Here is the list of options:

	Admin Bar Options
	- Hide admin bar completely
	- Hide Privacy link (link next to heading in admin bar)
	- Hide WordPress logo in admin bar
	- Add custom logo image in admin bar
	- Custom blog heading in admin bar
	- Hide WordPress update notification bar
	- Hide default blog heading in admin bar
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
	- Hide Login top bar completely
	- Change Login top bar text
	- Change/remove Login header image
	- Change hyperlink on Login image
	- Hide Login header image
	
	Admin Menu Options
	- Rename menu and submenu items
	- Remove menu and submenu items
	- Add new buttons with custom links
	- Remove icons from admin menu buttons
	
	Colorizer
	- Change background and text colors in admin and login page


For more information about the plugin please see: http://wordpress.argonius.com/ag-custom-admin

== Installation ==

1. Upload `ag-custom-admin` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Activated plugin should appear in 'Tools' menu

== Frequently Asked Questions ==

= My text is not in the right place. How to fix it? =
It is possible that WordPress usually wraps that text with some HTML tags. In that case use one of HTML tags to surround your text, e.g. &lt;h1&gt;My text&lt;/h1&gt;, or other (a, p, h2, h3..  etc.)

= Plugin does not work after upgrading to new version. =
Try to clear browser's cache, and reload page.

= I see only blank page. What to do? =
If you want to deactivate plugin, but you can't access admin panel, turn off JavaScript in your browser and than log in admin and deactivate plugin (This works only for administrator). Try also to clear browser's cache. If this does not work, try to find javaScript error in browser's console and post it to plugin's support page.

= Admin menu buttons are changed in a wrong way =
Please use 'Reset Settings' button on Admin Menu page to reset menu configuration to defaults. Remember that you should postpone admin menu configuration to the very end of admin page customization, because, any changes made from other plugins to admin menu (e.g adding new button of plugin that is activated, or removing that button when plugin is deactivated) could corrupt admin menu configuration.

== Screenshots ==

1. Text in header or footer could be customized.
2. Login page top bar could be changer or removed. Custom login photo could be added.
3. Admin menu is completely customizable. You can rename or remove items in menu and submenu, or add brand new buttons with custom links.
4. Add custom background and text colors in admin and login pages.

== Changelog ==

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
