# CSS-based Horizontal drop-down menu script

This PHP script reads a local XML file containing the menu item definitions and generates the HTML/CSS to make a JavaScript-free/CSS-only horizontal drop-down/fly-out menu set for your website.  

The script is based on [Stu Nicholls' CSS menu design](http://www.cssplay.co.uk/menus/final_drop5.html). Stu has graciously given his permission to redistribute his CSS and XHTML as long as his copyright notice remains intact inside the generated CSS (which is included in the PHP generated CSS) and it is distributed for free (which it is). If you find the menu system useful for your website, please consider making a [donation to CSSPlay](http://www.cssplay.co.uk/support.html) to support Stu's development efforts with CSS.  

The menu has three components, a locally created XML definition file, a helper transparent image .gif file, and a PHP script to generate the CSS and XHTML for the menu itself.  

## XML Menu definition file

The menu definition XML file looks like this:
```xml
<?xml version="1.0" encoding="iso-8859-1" ?>
<!-- menu control file for dropdown-menu.php program -->
<!-- Ken True - available at http://saratoga-weather.org/scripts-CSSmenu.php -->
<!-- NOTE: this file must be correct XML with all tag items properly closed  -->
<!-- Structure of file is

  <menu>
     <item caption="Main 1" link="main1.php" title="leftmost menu item"/>
	 <item caption="Main 2" title="Main menu number 2">
	    <item caption="Drop down 1" link="dropdown1.php"/>
	    <item caption="Drop down 2" link="dropdown2.php">
		   <item caption="Flyout 1" link="flyout1.php"/>
		   <item caption="Flyout 2" link="flyout2.php"/>
		   <item caption="Flyout 3" link="flyout3.php">
		     <item caption"Fourth Level Flyout 1" link"level4a.php"/>
		     <item caption"Fourth Level Flyout 2" link"level4b.php"/>
		     <item caption"Fourth Level Flyout 3" link"level4c.php"/>
		   </item>
		</item>
	    <item caption="Drop down 3" link="dropdown3.php"/>
	 </item>
   </menu>

 Failure to follow this structure or nest more than 3 sublevels
 may result in unexpected (and incorrect) results .. the top/sub1/sub2/sub3 four
 level menu should allow for highly complex websites.
-->
```

Getting correct XML syntax _is very important_ -- the XML parser will complain (and not generate the desired code) if the XML statements aren't terminated correctly. The XML file MUST start with the line:  
**<?xml version="1.0" encoding="iso-8859-1" ?>** and be followed by **<menu>** as the first non-comment statement, and the last line in the file must be **</menu>**

There are only two XML tags necessary:  
The **&lt;menu>&lt;/menu>** pair must surround the &lt;item>&lt;/item> tag set.  
The **&lt;item>** tags generate the menu links and here are the attributes available for each item tag:

<dl>

<dt>**caption="[link text to display]"** (required)</dt>

<dd>The caption="" attribute is required and specifies the text to display in the menu item. I recommend you keep it to one word for top level menus. Submenus may contain more words and will text-wrap if needed.</dd>

<dt>**link="[page url address]"** (optional)</dt>

<dd>The link="" attribute is optional for top level and submenu group items, but should specify the page to link to if the menu item is selected by the mouse. The (relative) URL of the page to link to should be used with this tag. Relative (./page.php) or absolute (/subdir/page.php) addresses may be used. If you have multi-level sites (with pages in subdirectories, using absolute addresses here is recommended. Any specification acceptable to the src="..." attribute of an &lt;a> tag is ok inside the link="" attribute.. so you could have a link="http://some.website.com/apage.html" as your link.</dd>

<dt>**title="[mouseover tooltip text]"** (optional)</dt>

<dd>The title="" attribute is optional. If used, the text will be displayed as a tooltip when the cursor is placed over the caption item. This may be useful to present additional information to the visitor, particularly for one-word top level menu items. It may be optionally used with any menu item, but should be used sparingly as the tooltip will appear and may temporarily mask lower menu items.</dd>

<dt>**target="_blank"** (optional)</dt>

<dd>The target="" attribute is optional and works the same way as target="" attribute does in the HTML &lt;a target="..."> tag does. If target="_blank" is used, then the link will open in a new browser window or tab.  
This attribute is ignored if a link="..." attribute is not present.</dd>

<dt>**img="./imagedir/image.gif"** (optional)</dt>

<dd>This attribute acts the same as the src="..." attribute in an &lt;img src="..."/> HTML statement.  
Use it to include a small image (10x10 or 14x14px) in the menu. Placement in the text (from the caption='...') defaults to the left of the caption. Change it to the right side (end) of the caption by using align="right" attribute.</dd>

<dt>**align="right|left"** (optional)</dt>

<dd>This attribute specifies where the image (from the img="..." attribute) is placed in the generated link.  
align="left" places the image in front of the caption="..." text.  
align="right" is the default and places the image after the caption="..." text.</dd>

</dl>


The order of the attributes in the <item> statement is not important. By convention, I used caption, link, title just for my readibility of the XML file. What is important is that the <item> needs to be closed, either by:  
**&lt;item caption="..." link="..." title="..."/>** [the trailing '/>' is a self close to the item tag] or by  
**&lt;item caption="..." link="..." title="..."> ... &lt;/item>** [the &lt;/item> closes a &lt;item> tag]

The &lt;item> tags can be nested up to 4 levels deep. The topmost <item> (the ones not enclosed by other <item> tags) are the basic selectors for the menu. The second, third and fourth level <item> are in dropdown and fly-out menus.

To create a submenu, simply enclose one or more &lt;item .../> tags within a &lt;item ...> &lt;/item> pair and a submenu will be created. See the example XML for more information. It is recommended that you use indent to help you visually inspect &lt;item> nesting .. it is not required by the XML parser, but it sure makes it easier to find errors. Whitespace (including new-lines) is ignored in all areas EXCEPT within quoted strings.

XML rules require you to use **&amp;amp;** for **&**, **&amp;lt;** for **<** , **&amp;gt;** for **>** and **&amp;quot;** for **"** within the double-quoted strings.

## Settings for dropdown-menu.php

```php
// ---------- settings ------------------------------
$MENUdef = './dropdown-menu.xml'; // (relative) file location of XML menu definition file
$MENUimageDir = '/images/';       // (relative) URL location of images dir (with trailing '/')
//
// change color settings if you like for the CSS file
 $TopMenuBkgnd		=	'#0033ff';  // overall background field of top menu
 // match above color to background of enclosing masthead area on website
 // first level menu (top)
 $TopMenuTextColor 	=   '#FFFFFF';  // text color - top menu
 $TopMenuTextBkgnd  =   '#0066FF';  // bkgnd color - top menu
 $TopMenuBkgrndHover=   '#d4d8bd';  // bkgnd color - top menu - hover
 $TopMenuBorder     =   '#E0E0E0';  // color of 1px box around top menu buttons
 // drop-down submenus
 $SubMenuTextColor  =   '#000000';  // text color - sub menu(s)
 $SubMenuTextHover  =   '#0000FF';  // text color - sub menu(s) - hover
 //
 $SubMenuBkgrnd     =   '#d4d8bd';  // bkgnd color - sub menu
 $SubMenuBkgrnd2    =   '#d4d8bd';  // bkgnd color - sub menu level 2
 $SubMenuBkgrnd3    =   '#b4be9c';  // bkgnd color - sub menu level 3
 $SubMenuBkgrnd4    =   '#d4d8bd';  // bkgnd color - sub menu level 4
 $SubMenuBkgrndHover=   '#d4d8bd';  // bkgnd color - sub menu - hover
 $SubMenuBkgrndHover2=  '#b4be9c';  // bkgnd color - sub menu level 2 - hover
 $SubMenuBkgrndHover3=  '#c4ceac';  // bkgnd color - sub menu level 3 - hover
 $SubMenuBkgrndHover4=  '#b4be9c';  // bkgnd color - sub menu level 4 - hover
// ---------- end settings --------------------------
```

The only required settings are for:  
**$MENUdef** which must be set to the (relative) **file** location of the XML menu definition file.  
The default is './dropdown-menu.xml'.  
**$MENUimageDir** should be set to the (relative) **URL** directory where the transp.gif file is loaded.  
The (default is '/images/' (be sure to include the trailing '/' in the directory as shown).  
The Color settings should be adjusted to meet your website color requirements.

The dropdown-menu.php script has two optional arguments for the URL:

<dl>

<dt>**gendiv=[y|n]**</dt>

<dd>Controls whether a <div class="dropdownmenu"></div> is emitted around the menu. Default is **n** (no)..  
This can also be controlled by setting the $genDiv variable before the script is included like this:  
```php
<?php $genDiv=true; include("dropdown-menu.php"); ?>
```
</dd>

<dt>**css=y**</dt>

<dd>If present, the script will output ONLY the CSS for the page.</dd>

<dt>**debug=y**</dt>

<dd>If present, the script will output debugging information as HTML comments in the output stream.</dd>

</dl>

There are two program switches that may be used to control generation/display with PHP code _before_ you  
<?php include("dropdown-menu.php"); ?> on your webpage::

<dl>

<dt>**$PrintDropdownMenu=true;** (default)</dt>

<dd>This switch controls whether the script prints any text or not.  
If **$PrintDropdownMenu = false;** then all printing is suppressed. See below for usage example.</dd>

<dt>**$genDiv=true;**</dt>

<dd>This switch controls whether the script puts a <div class="dropdownmenu"></div> around the **$DropdownMenuText** or not. The default is **false** (no <div> included). This option should NOT be selected on a page you wish to display a sitemap (see below).</dd>

</dl>

## Installation for PHP websites

1.  Download dropdown-menu package on your offline copy of your website.
2.  Update the **dropdown-menu.xml** file with the navigation structure for your website.
3.  Customize the settings in **dropdown-menu.php** as needed.
4.  Upload the **dropdown-menu.php, dropdown-menu.xm**l and the **images/*** directory to your website
5.  Edit your page to _insert_  
      ```php
      <?php $PrintDropdownMenu = false;
      include("dropdown-menu.php");
      print $DropdownCSS; ?>
      ```
    in the <head> </head> area of your page.
6.  Edit your page to _insert_  
      ```php
      <div class="dropdownmenu">
      <?php print $DropdownMenuText; ?>
      </div>
      ```
    on your page where you'd like the menu to appear, then save and upload the page to your website.
7.  Repeat (5) and (6) for each page to use the menu system.

You can also use the script to create a website map. On a page that uses the **$PrintDropdownMenu=false;** (see above), then just
```php
<?php print $DropdownMenuText; ?>
```
 where you'd like the menu tree to be printed. Do this **without** a surrounding <div class="dropdownmenu"></div> and the links will print as a series of nested unformatted lists. You can see this in action on my [sitemap page](/sitemap.php).

## Installation for .htm/.html/.shtml websites

If you want to stick to .htm/.html for your normal website, _and_ you have PHP available, you can use the script to generate a CSS and a HTML that you can include (static or dynamic) into your existing site. Follow these steps:

1.  Download dropdown-menu package on your offline copy of your website.
2.  Update the **dropdown-menu.xml** file with the navigation structure for your website.
3.  Customize the settings in **dropdown-menu.php** as needed.
4.  Upload the **dropdown-menu.php, dropdown-menu.xm**l and the **images/*** directory to your website
5.  Load **http://www.yourwebsite.com/dropdown-menu.php?css=y** in your browser
6.  Save the page locally as **wxmenu.css** and edit it to _remove_:  

       ```html
       <!-- begin dropdown-menu.php CSS definition -->  
       <style type="text/css">
       ```  

    at the top of the file, and _remove_ from the bottom of the file:  

       ```html
       </style>  
       <!--[if lte IE 6]>  
       <style type="text/css">  
       .dropdownmenu ul ul {left:-1px; margin-left:-1px;}  
       .dropdownmenu ul ul ul.left {margin-left:1px;}  
       </style>  
       <![endif]-->  
       <!-- end of dropdown-menu.php CSS definition -->
      ```  

    then save and upload the wxmenu.css file to your website.  

7.  Load **http://www.yourwebsite.com/dropdown-menu.php** in your browser
8.  Save the page locally as **wxmenu.html** and upload it to your website
9.  Edit your website page to _insert_  

       ```html
       <style type="text/css" src="wxmenu.css"></style>**  
       <!--[if lte IE 6]>  
       <style type="text/css">  
       .dropdownmenu ul ul {left:-1px; margin-left:-1px;}  
       .dropdownmenu ul ul ul.left {margin-left:1px;}  
       </style>  
       <![endif]-->
      ```  

    in the &lt;head>&lt;/head> section
10.  Edit your website page to include**  

       ```html
       <div class="dropdownmenu">[insert contents of wxmenu.html]</div>
       ```  

    where you'd like the menu to appear.  
11.  Repeat steps (9) and (10) for each page in your website.

If you have Server Side Includes (SSI) available, the step (10) can be replaced with the following SSI directive:  
    ```html
    <div class="dropdownmenu"><!--#include virtual="wxmenu.html" --></div>
    ```

This dropdown menu system has been tested and **works with**:  
Internet Explorer 5.5, 6 and 7, Firefox 1.5, 2.0, Opera 9, Safari for Windows 3.0.3, Netscape 8.1.2.  

The following browsers are **known to have problems** (popups at left side of the screen):  
Netscape Navigator 7.1 Mozilla 1.3, Opera 7

## Known Issues with other scripts/applets

This menu system is DHTML-based. Some applets and scripts are known to have problems with display of the dropdown menu shown behind certain objects on the screen.

<dl>

<dt>**Adobe/Macromedia Flash Player**</dt>

<dd>Flash has the annoying habit of trying to be 'uppermost' in the display on your browser. The way to get it to play nice (and allow DHTML to display over the top of the Flash Player) is to add

```
   <param name="wmode" value="transparent" />
```

 or

```
   <param name="wmode" value="opaque" />
```  

to the <object> invoking the Flash Player. If you're using the swfobject.js to launch the Flash Player, then include **so.addParam("wmode", "transparent");** before the **so.write("flashcontent");** in the JavaScript on the page. (see example at [WD-Live.php](/WD-Live.php) -- view source)</dd>

<dt>**Java Applet** (not JavaScript)</dt>

<dd>Java Applets have a similar issue -- they want to be uppermost in the display. Unfortunately, there is no known fix for this issue.</dd>
</dl>
