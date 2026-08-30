<?php
/***
Plugin Name: Tempest-Highlight
Plugin URI: https://github.com/edent/highlight
Description: Syntax highlighting. Based on Tempest Highlight - https://github.com/tempestphp/highlight
Author: Terence Eden
Version: 0.03
Author URI: https://edent.tel/

Tempest Highlight - https://github.com/tempestphp/highlight - is a package for server-side, high-performance, and flexible code highlighting.
***/
if ( ! defined( "ABSPATH" ) ) { exit; }

// Entry point of the plugin (after the template renders the HTML output).
add_action( "the_content", "tempest_highlight_main", 49 );

//	Load the Tempest Highlight library
require_once __DIR__ . "/autoload.php";

//	Define a theme
//	Choose from any in `src/Themes/Css/`
$css   = "light-plus";
$highlightTheme = new Tempest\Highlight\Themes\InlineTheme( __DIR__ . "/src/Themes/Css/{$css}.css");

//	Main function
function tempest_highlight_main( string $content ):string {
	//	Don't change the content on RSS / Atom feeds, nor on lists
	if ( is_feed() || !is_single() ) {
		return $content;
	}

	//	Set up variables
	global $highlightTheme;

	//	Create the highlighter.
	$highlighter = new Tempest\Highlight\Highlighter( $highlightTheme );
	//	Load the content into PHP 8.4's HTML DOM.
	$dom = Dom\HTMLDocument::createFromString( $content, LIBXML_NOERROR, "UTF-8" );
	
	//	Select the code snippets.
	//	`<pre><code class="language-*">`
	$codeSnippets = $dom->querySelectorAll( "pre>code[class^=language-]" );

	//	Unique ID
	$id = 0;

	//	Iterate through each snippet.
	foreach ( $codeSnippets as $code ) {

		//	What language is this written in?
		$originalClass = $code->className;
		
		//	Transform `language-whatever` into `whatever`.
		$language = explode( "-", $originalClass )[1];

		//	Language names and icons may be displayed differently.
		[$language, $language_logo, $language_display] = getLanguageProperties( $language );

		//	Get the content from within the <code>.
		//	Use `textContent` to avoid HTML entities being encoded.
		$originalCode = $code->textContent;

		//	Set the attributes on the parent <pre>.
		$code->parentNode->setAttribute( "class", "tempest-highlight" );
		$code->parentNode->setAttribute( "translate", "no" );
		$code->parentNode->setAttribute( "itemscope", "" );
		$code->parentNode->setAttribute( "itemtype", "https://schema.org/SoftwareSourceCode" );

		//	Set the attributes on the <code>.
		$code->setAttribute( "class", $language );
		$code->setAttribute( "itemprop", "text" );

		//	Replace the contents of <code> with the highlighted HTML.
		$code->innerHTML = $highlighter->parse( $originalCode, $language );

		//	Add the copy button.
		//	Timeout the popover after 3 seconds.
		$copy_button = "<button class='copy' title='Copy code' popovertarget='pop{$id}' popovertargetaction='show' onclick=\"navigator.clipboard.writeText( this.parentNode.getElementsByTagName('code')[0].textContent ); document.getElementById('pop{$id}').togglePopover({source: this});\"><span aria-hidden=true>⧉</span></button>";
		//	Create a new DOM for it.
		$copy_dom = Dom\HTMLDocument::createFromString( $copy_button, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED, "UTF-8" );
		//	Import the specific element and its attributes.
		$element = $dom->importNode( $copy_dom->firstChild, true );
		//	Insert it before the <code> element.
		$code->parentNode->insertBefore( $element, $code );

		//	Add the popover *outside* the <pre> for HTML validation.
		$popover = "<dialog id='pop{$id}' popover>Copied {$language_display} to 📋</dialog>";
		//	Use insertAdjacentHTML for strings
		$code->parentNode->insertAdjacentHTML( Dom\AdjacentPosition::BeforeBegin, $popover );

		//	Add the language header before the code.
		//	Construct the HTML.
		$language_html = generateLanguageHTML( $language_logo, $language_display );
		//	Create a new DOM for it.
		$language_dom = Dom\HTMLDocument::createFromString( $language_html, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED, "UTF-8" );
		
		if ( null != $language_dom->firstChild ) {
			//	Import the specific element and its attributes.
			$element = $dom->importNode( $language_dom->firstChild, true );

			//	Insert it before the <code> element.
			$code->parentNode->insertBefore( $element, $code );
		}

		$id++;
	}

	//	Add the base CSS to the page.
	enqueueBaseCSS();

	//	Return the altered HTML.
	return $dom->body->innerHTML;
}

/** @return array<string> */
function getLanguageProperties( string $language ):array {

	$language = strtolower( $language );
	
	switch( $language ) {
		case "bash":
			$language         = "bash";
			$language_logo    = "bash";
			$language_display = "Bash";
			break;
		case "sh":
			$language         = "bash";
			$language_logo    = "bash";
			$language_display = "Bash";
			break;
		case "shell":
			$language         = "bash";
			$language_logo    = "bash";
			$language_display = "Bash";
			break;
		case "html":
			$language         = "html";
			$language_logo    = "html";
			$language_display = "HTML";
			break;
		case "html5":
			$language         = "html";
			$language_logo    = "html";
			$language_display = "HTML";
			break;
		case "py":
			$language         = "python";
			$language_logo    = "python";
			$language_display = "Python 3";
			break;
		case "python":
			$language         = "python";
			$language_logo    = "python";
			$language_display = "Python 3";
			break;
		case "python3":
			$language         = "python";
			$language_logo    = "python";
			$language_display = "Python 3";
			break;
		case "python2":
			$language         = "python";
			$language_logo    = "python";
			$language_display = "Python 2";
			break;
		case "json":
			$language         = "json";
			$language_logo    = "json";
			$language_display = "JSON";
			break;
		case "js":
			$language         = "javascript";
			$language_logo    = "javascript";
			$language_display = "JavaScript";
			break;
		case "sql":
			$language         = "sql";
			$language_logo    = "mysql";
			$language_display = "SQL";
			break;
		case "markdown":
			$language         = "markdown";
			$language_logo    = "markdown";
			$language_display = "Markdown";
			break;
		case "md":
			$language         = "markdown";
			$language_logo    = "markdown";
			$language_display = "Markdown";
			break;
		case "mysql":
			$language         = "sql";
			$language_logo    = "mysql";
			$language_display = "MySQL";
			break;
		case "svg":
			$language         = "xml";
			$language_logo    = "svg";
			$language_display = "SVG";
			break;
		case "_":
			$language         = "_";
			$language_logo    = "";
			$language_display = "";
			break;
		default:
			$language_logo    = $language;
			$language_display =	strtoupper( $language );
	}

	return [$language, $language_logo, $language_display];
}

function generateLanguageHTML( string $language_logo, string $language_display ):string {
	//	Display an icon if one exists.
	if ( file_exists( plugin_dir_path( __FILE__ ) . "svg/" . $language_logo . ".svg" ) ) {
		$language_icon = plugin_dir_url(  __FILE__ ) . "svg/" . $language_logo . ".svg";
	} else {
		//	Default just show a placeholder icon.
		$language_icon = plugin_dir_url(  __FILE__ ) . "svg/notepad.svg";
	}
	$language_html =
		"<span class=tempest-highlight-language>" .
			"<img src=\"{$language_icon}\" width=32 height=32 alt class=tempest-highlight-language-icon>".
			"<span itemprop=programmingLanguage> {$language_display}</span>".
		"</span>";

	return $language_html;
}

//	Enqueue any base CSS
function enqueueBaseCSS():void {

	//	Prevent the CSS being added multiple times
	static $already_added = false;
	if ( $already_added ) {
		return;
	}
	$already_added = true;

	//	CSS file in the root of the plugin's directory.
	$baseCSS = __DIR__ . "/tempest-highlight-base.css";

	//	Insert it into the head.
	if ( file_exists( $baseCSS ) ) {
		$css = file_get_contents( $baseCSS );
		wp_register_style(   "tempest-highlight-base", false );
		wp_enqueue_style(    "tempest-highlight-base" );
		wp_add_inline_style( "tempest-highlight-base", $css );
	}
}