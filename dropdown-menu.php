<?php
// dropdown menu generator using http://www.cssplay.co.uk/menus/final_drop5.html
// menu stylesheet adpated by Ken True - webmaster@saratoga-weather.org
//
// Version 1.00 - 12-Aug-2007 - Initial release
// Version 1.01 - 16-Sep-2007 - added z-index to ul CSS to fix menu-under-buoy-map issue
// Version 1.02 - 24-Jul-2010 - added tags target=,img=,align= to parsing, use menu=<name> for dropdown-menu-<name>.xml
// Version 1.03 - 08-Dec-2012 - added fixes for PHP5.4
//
$Version = 'dropdown-menu.php Version 1.03 - 08-Dec-2012';
//
// ---------- settings ------------------------------
$MENUdef = './dropdown-menu.xml'; // (relative) file location of XML menu definition file
$MENUdefTest = './dropdown-menu-%s.xml'; // (relative) file location of test XML menu definition file
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
//  error_reporting(E_ALL); // for testing
if ( isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
//--self downloader --
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   
   readfile($filenameReal);
   exit;
}

$Debug = false;
if (isset($_REQUEST['debug']) && strtolower($_REQUEST['debug']) == 'y') {
  $Debug = true;
}
$doDiv = false;
if (isset($_REQUEST['gendiv']) && strtolower($_REQUEST['gendiv']) == 'y') {
  $doDiv = true;
}
if (isset($genDiv)) {
  $doDiv = $genDiv;
}

$doCSS = false;
if (isset($_REQUEST['css']) && strtolower($_REQUEST['css']) == 'y') {
  $doCSS = true;
}
if (isset($genCSS)) {
  $doCSS = $genCSS;
}
  $CSS = '';
  genCSS();

$doPrintMenu = true;
if (isset($PrintDropdownMenu)) {
  $doPrintMenu = $PrintDropdownMenu;
}
if ($doCSS) { // only return the necessary CSS for direct include
  print $DropdownCSS;
  return;

}
$usingAltXML = false;
if(isset($_REQUEST['menu']) and  preg_match('|%s|',$MENUdefTest)) {
   $tMenu = sprintf($MENUdefTest,strtolower($_REQUEST['menu']));
   // print "<!-- checking '$tMenu' -->\n";
   if(file_exists($tMenu)) {															
     $MENUdef = $tMenu;
     $usingAltXML = true;
     // print "<!-- using '$tMenu' -->\n";
   }
 }

$depth = array();
$MENU = array();
$MENUcnt = 0;
$lastDepth = 0;
$Status = "<!-- $Version -->\n";

// ------------- main routine --------------------
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElementDropdown", "endElementDropdown");
if (!($fp = fopen($MENUdef, "r"))) {
    die("could not open XML input");
}

while ($data = fread($fp, 4096)) {
    if (!xml_parse($xml_parser, $data, feof($fp))) {
        die(sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($xml_parser)),
                    xml_get_current_line_number($xml_parser)));
    }
}
xml_parser_free($xml_parser);

// ----------- generate the menu XHTML ---------------
$DropdownMenuText = "<!-- begin generated dropdown menu -->\n";

if ($doDiv) {
  $DropdownMenuText .= "<div class=\"dropdownmenu\">\n";
}
$DropdownMenuText .= "<!-- $Version -->\n";
$DropdownMenuText .= "<!-- by Ken True - webmaster[at]saratoga-weather.org -->\n";
$DropdownMenuText .= "<!-- Adapted from Stu Nicholl's CSS/XHTML at http://www.cssplay.co.uk/menus/final_drop5.html -->\n";
$DropdownMenuText .= "<!-- script available at http://saratoga-weather.org/scripts-CSSmenu.php#dropdown -->\n";

if($usingAltXML) {
  $DropdownMenuText .= "<!-- using $MENUdef for XML -->\n";
}

for ($i=1;$i<count($MENU);$i++) { // loop over all menu items -1
  $depth = $MENU[$i]['depth'];
  $nextdepth = $MENU[$i+1]['depth'];
  $indent = str_repeat("  ",$depth);
  $link = $MENU[$i]['link'];
  $title = $MENU[$i]['title'];
  $target = $MENU[$i]['target'];
  $img = $MENU[$i]['img'];
  $align = $MENU[$i]['align'];

  if ($target <> '') {
    $target = ' target="' . $target . '"';
  } else {
    $target = '';
  }
  
  if ($link <> '') {
    $link = 'href="' . $link . '"';
  } else {
    $link = 'href="' . "#" . '"';
  }
  
  if ($title <> '') {
    $title = ' title="' . $title . '"';
  } else {
    $title = '';
  }
  $leftimg = '';
  $rightimg = '';
  
  if ($img <> '') {
    $img = '<img src="' . $img . '" style="border:none" alt=" "/>';
	if (preg_match('|left|i',$align)) {
	   $leftimg = $img;
	} else {
	   $rightimg = $img;
	}
  }
  
  if ($i==1) {
    $DropdownMenuText .= "<ul>\n";
  }
  if ($Debug) {
    $DropdownMenuText .= "$indent<!-- $i: depth=$depth next=$nextdepth caption='" . $MENU[$i]['caption'] . "' link='" . $MENU[$i]['link'] ."' title='" . $MENU[$i]['title'] . "' -  ";
  }
  if ($depth < $nextdepth) { // -------------------  start of new submenu 
    if ($Debug) {
      $DropdownMenuText .= "Start new submenu -->\n";
	}
	$DropdownMenuText .= "$indent<li><a $link$title$target>$leftimg" . $MENU[$i]['caption'] . "$rightimg<!--[if IE 7]><!--></a><!--<![endif]-->
$indent  <!--[if lte IE 6]><table><tr><td><![endif]-->
$indent  <ul>\n";
	
  }
  
  if ($depth > $nextdepth) { // --------------------  end of new submenu
    if ($Debug) {
      $DropdownMenuText .= "End new submenu -->\n";
	}
	$DropdownMenuText .= "$indent<li><a $link$title$target>$leftimg" . $MENU[$i]['caption'] . "$rightimg</a></li>\n";
	
	for ($j=$depth; $j > $nextdepth ;$j--) { // close off intervening submenu(s)
	
	  $newindent = str_repeat("  ",$j-1);
	$DropdownMenuText .= "$newindent  </ul>
$newindent  <!--[if lte IE 6]></td></tr></table></a><![endif]-->
$newindent</li>\n";
    }
   

  }
  
  if ($depth == $nextdepth) { // ---------------------- menu item at current depth
    if ($Debug) {
      $DropdownMenuText .= "Normal menu item -->\n";
	}
	$DropdownMenuText .= "$indent<li><a $link$title$target>$leftimg" . $MENU[$i]['caption'] . "$rightimg</a></li>\n";
  
  }
  
  if ($i==count($MENU)-1) {
    $DropdownMenuText .= "</ul>\n";
  }
}
if ($doDiv) {
  $DropdownMenuText .= "</div>\n";
}
$DropdownMenuText .= "<!-- end generated dropdown menu -->\n";

if ($doPrintMenu) {
  print $DropdownMenuText;
}

if ($Debug) {
  print $Status;
}

// functions invoked by XML_parser

function startElementDropdown($parser, $name, $attrs) 
{
    global $depth,$Status,$lastDepth,$MENU,$MENUcnt;
	$indent = '';
	if (! empty($depth[(integer)$parser]) ) {
	  $j = $depth[(integer)$parser];
	} else {
	  $j = 0;
	}
    for ($i = 0; $i < $j; $i++) {
        $Status .= "  ";
		$indent .= "  ";
    }
    $Status .= "<!-- Depth: $j - $name " . print_r($attrs,true) . " -->\n";
    // format the CAPTION and LINK entries
	if (! empty($attrs['LINK']) ) {
	  $link = $attrs['LINK'];
	 } else {
	  $link = '';
	 }
	if (! empty($attrs['CAPTION']) ) {
	  $caption = $attrs['CAPTION'];
	} else {
	  $caption = '';
	}

	if (! empty($attrs['TITLE']) ) {
	  $title = $attrs['TITLE'];
	} else {
	  $title = '';
	}
	if (! empty($attrs['TARGET']) ) {
	  $target = $attrs['TARGET'];
	} else {
	  $target = '';
	}
	if (! empty($attrs['IMG']) ) {
	  $img = $attrs['IMG'];
	} else {
	  $img = '';
	}
	if (! empty($attrs['ALIGN']) ) {
	  $align = preg_match('|left|i',$attrs['ALIGN'])?'left':'right';
	} else {
	  $align = '';
	}

    if ($caption <> '' or $link <> '') { // ignore entries that are wholly blank
	  $MENUcnt++;
	  $MENU[$MENUcnt]['depth'] = $j;
	  $MENU[$MENUcnt]['caption'] = $caption;
	  $MENU[$MENUcnt]['title'] = $title;
	  $MENU[$MENUcnt]['link'] = $link;
	  $MENU[$MENUcnt]['target'] = $target;
	  $MENU[$MENUcnt]['img'] = $img;
	  $MENU[$MENUcnt]['align'] = $align;
	  // store dummy next entry at highest level for final run-through to generate
	  // the XHTML.  This will be overwritten by a 'real' entry if any
	  $MENU[$MENUcnt+1]['depth'] = 1; 
	  $MENU[$MENUcnt+1]['caption'] = '';
	  $MENU[$MENUcnt+1]['title'] = '';
	  $MENU[$MENUcnt+1]['link'] = '';
	  $MENU[$MENUcnt+1]['target'] = '';
	  $MENU[$MENUcnt+1]['img'] = '';
	  $MENU[$MENUcnt+1]['align'] = '';
	
	}
	
	$lastDepth = $j; // remember for next time
	$j++;
    $depth[(integer)$parser] = $j;
}

// called at end of particular element
function endElementDropdown($parser, $name) 
{
    global $depth,$Status;
    $depth[(integer)$parser]--;
}
// end of XML_parser functions

// return the CSS
function genCSS ()
{
global  $DropdownCSS,$MENUimageDir;
global  $TopMenuBkgnd,$TopMenuTextColor,$TopMenuTextBkgnd,$TopMenuBkgrndHover,$TopMenuBorder;
global  $SubMenuTextColor,$SubMenuTextHover;
global  $SubMenuBkgrnd,$SubMenuBkgrnd2,$SubMenuBkgrnd3,$SubMenuBkgrnd4;
global  $SubMenuBkgrndHover,$SubMenuBkgrndHover2,$SubMenuBkgrndHover3,$SubMenuBkgrndHover4;

$DropdownCSS = <<<END_OF_CSS
<!-- begin dropdown-menu.php CSS definition -->
<style type="text/css">
/* ================================================================ 
This copyright notice must be untouched at all times.

The original version of this stylesheet and the associated (x)html
is available at http://www.cssplay.co.uk/menus/final_drop5.html
Copyright (c) 2005-2007 Stu Nicholls. All rights reserved.
This stylesheet and the associated (x)html may be modified in any 
way to fit your requirements.
=================================================================== */
/*
  adapted for use with dropdown-menu.php menu generator by
  Ken True - Saratoga-Weather.org - 9-Aug-2007                      
                                                                    */
/* style the outer div to give it width */
.dropdownmenu {font-size:0.75em;padding-bottom:1px;margin-left: 5px;}

/* remove all the bullets, borders and padding from the default list styling */
.dropdownmenu ul {padding:0;margin:0;list-style-type:none; height:1.5em; background:$TopMenuBkgnd; 
  position: relative; z-index: 10;} /* added to fix menu-over-buoy-map issue */

/* style the sub-level lists */
.dropdownmenu ul ul {width:15em;left: 100%; top:100%;}

/* float the top list items to make it horizontal and a relative positon so that you can control the dropdown menu positon */
.dropdownmenu ul li {float:left;height:1.5em;line-height:1.5em;}

/* style the sub level list items */
.dropdownmenu ul ul li {display:block;width:12em;height:auto; line-height:1em;}

/* style the links for the top level */
.dropdownmenu a, .dropdownmenu a:visited {display:block;float:left;height:100%;font-size:1em;text-decoration:none;color:$TopMenuTextColor;background:$TopMenuTextBkgnd;font-weight: bold;padding:0 1em 0 1em; border:1px solid $TopMenuBorder;}

/* style the sub level links */
.dropdownmenu ul ul a, .dropdownmenu ul ul a:visited {display:block;float:none; background:$SubMenuBkgrnd; color:$SubMenuTextColor;width:12em;height:100%;line-height:1em; padding:0.25em 1em;}
* html .dropdownmenu ul ul a, * html .dropdownmenu ul ul a:visited  {width:14em; w\idth:12em;}


/* style the table so that it takes no part in the layout - required for IE to work */
.dropdownmenu table {position:absolute; left:1px; top:0; width:0; height:0; font-size:1em; z-index:-1;}


/* style the third level background */
.dropdownmenu ul ul ul a, .dropdownmenu ul ul ul a:visited {background:$SubMenuBkgrnd3;}
/* style the fourth level background */
.dropdownmenu ul ul ul ul a, .dropdownmenu ul ul ul ul a:visited {background:$SubMenuBkgrnd4;}
/* style the sub level 1 background */
.dropdownmenu ul :hover a.sub1 {background:$SubMenuBkgrndHover;}
/* style the sub level 2 background */
.dropdownmenu ul ul :hover a.sub2 {background:$SubMenuBkgrndHover2;}

/* style the level hovers */
/* first */
* html .dropdownmenu a:hover {color:$SubMenuTextColor;background:$SubMenuBkgrnd; position:relative; z-index:100;}
.dropdownmenu li:hover {position:relative;}
.dropdownmenu :hover > a {color:$SubMenuTextHover;background:$TopMenuBkgrndHover;}
/* second */
* html .dropdownmenu ul ul a:hover{color:$SubMenuTextHover;background:$SubMenuBkgrnd2; position:relative; z-index:110;}
.dropdownmenu ul ul li:hover {position:relative;}
.dropdownmenu ul ul :hover > a {color:$SubMenuTextHover;background:$SubMenuBkgrndHover2;}
/* third */
* html .dropdownmenu ul ul ul a:hover {background:$SubMenuBkgrnd3; position:relative; z-index:120;}
.dropdownmenu ul ul ul :hover > a {color:$SubMenuTextHover;background:$SubMenuBkgrndHover3;}
/* fourth */
.dropdownmenu ul ul ul ul a:hover {background:$SubMenuBkgrndHover4; position:relative; z-index:130;}


/* hide the sub levels and give them a positon absolute so that they take up no room */
.dropdownmenu ul ul {visibility:hidden;position:absolute;height:0;top:1.5em;left:0;width:14em;}

/* position the third level flyout menu */
.dropdownmenu ul ul ul{left:12em;top:0;width:14em;}


/* make the second level visible when hover on first level list OR link */
.dropdownmenu ul :hover ul{visibility:visible; height:auto; padding-bottom:3em; 
  background:transparent url(${MENUimageDir}trans.gif);}
/* keep the third level hidden when you hover on first level list OR link */
.dropdownmenu ul :hover ul ul{visibility:hidden;}
/* keep the fourth level hidden when you hover on second level list OR link */
.dropdownmenu ul :hover ul :hover ul ul{visibility:hidden;}
/* make the third level visible when you hover over second level list OR link */
.dropdownmenu ul :hover ul :hover ul{visibility:visible;}
/* make the fourth level visible when you hover over third level list OR link */
.dropdownmenu ul :hover ul :hover ul :hover ul {visibility:visible;}
/* end of wx-menu.css */
</style>
<!--[if lte IE 6]>
<style type="text/css">
.dropdownmenu ul ul {left:-1px; margin-left:-1px;}
.dropdownmenu ul ul ul.left {margin-left:1px;}
</style>
<![endif]-->
<!-- end of dropdown-menu.php CSS definition -->

END_OF_CSS;

} // end of genCSS function
// end of dropdown-menu.php
?>