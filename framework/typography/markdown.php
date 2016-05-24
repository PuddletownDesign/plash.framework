<?php

#
# Global default settings:
#

# Change to ">" for HTML output or " />" or xhtml
@define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX',  ">");

# Define the width of a tab for code blocks.
@define( 'MARKDOWN_TAB_WIDTH',     4 );

# Optional title attribute for footnote links and backlinks.
@define( 'MARKDOWN_FN_LINK_TITLE',         "" );
@define( 'MARKDOWN_FN_BACKLINK_TITLE',     "" );

# Optional class attribute for footnote links and backlinks.
@define( 'MARKDOWN_FN_LINK_CLASS',         "" );
@define( 'MARKDOWN_FN_BACKLINK_CLASS',     "" );

@define( 'MARKDOWN_PARSER_CLASS',  'MarkdownExtra_Parser' );



class Markdown {
	
	//------------------------------------------------------
	//               control the type
	//------------------------------------------------------
	
	public $header_level;
	public $link_rel;
	public $xss;
	public $baseurl = '';
	public $use_smarty;
	
	protected $htmlTagsBlackList = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
	
	# Regex to match balanced [brackets].
	# Needed to insert a maximum bracked depth while converting to PHP.
	protected $nested_brackets_depth = 6;
	protected $nested_brackets;
	
	//check this shit
	protected $in_anchor = false;
	
	protected $nested_url_parenthesis_depth = 4;
	protected $nested_url_parenthesis;

	# Table of hash values for escaped characters:
	protected $escape_chars = '\`*_{}[]()>#+-.!';

	# Change to ">" for HTML output.
	protected $empty_element_suffix = MARKDOWN_EMPTY_ELEMENT_SUFFIX;
	protected $tab_width = MARKDOWN_TAB_WIDTH;
	
	# Change to `true` to disallow markup or entities.
	protected $no_markup = false;
	protected $no_entities = false;
	
	
	
	public function __construct($header_level=2, $xss=true, $link_rel='no-follow', $use_smarty=true) 
	{
		
		#$this->baseurl = $baseurl;
		$this->header_level = $header_level-1;
		$this->link_rel = $link_rel;
		$this->xss = $xss;
		$this->use_smarty = $use_smarty;
		#
		# Constructor function. Initialize appropriate member variables.
		#
		$this->_initDetab();
	
		$this->nested_brackets = 
			str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth).
			str_repeat('\])*', $this->nested_brackets_depth);
	
		$this->nested_url_parenthesis = 
			str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth).
			str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth);
		
		# Sort document, block, and span gamut in ascendent priority order.
		asort($this->document_gamut);
		asort($this->block_gamut);
		asort($this->span_gamut);
	}	
	
	# function encodeBlackListTags($text)
	#{
	#	foreach($this->htmlTagsBlackList as $key => $value) {
	#		//open tag
	#		$tag = "<".$this->htmlTagsBlackList[$key].">";
	#		if (strstr($text, $tag)) {
	#			$text = str_replace($tag, htmlspecialchars($tag), $text);
	#		}
	#		
	#		//end tag
	#		$tag = "</".$this->htmlTagsBlackList[$key].">";
	#		if (strstr($text, $tag)) {
	#			$text = str_replace($tag, htmlspecialchars($tag), $text);
	#		}
	#	}
	#	return $text;
	#}

	function transform($text) {
	#
	# Main function. The order in which other subs are called here is
	# essential. Link and image substitutions need to happen before
	# _EscapeSpecialCharsWithinTagAttributes(), so that any *'s or _'s in the <a>
	# and <img> tags get encoded.
	#
		# Clear the global hashes. If we don't clear these, you get conflicts
		# from other articles when generating a page which contains more than
		# one article (e.g. an index page that shows the N most recent
		# articles):
		$this->urls = array();
		$this->titles = array();
		$this->html_hashes = array();
		
		#encode the black list tags even the allow html is true
		#$text = $this->encodeBlackListTags($text);
		
		//added methods
		if ($this->xss) {
			$text = htmlspecialchars($text);
			
			//for the block quotes only
			$text = str_replace("\n&gt; ", "\n>", $text);
		}

		# Standardize line endings:
		#   DOS to Unix and Mac to Unix
		$text = preg_replace('{\r\n?}', "\n", $text);

		# Make sure $text ends with a couple of newlines:
		$text .= "\n\n";

		# Convert all tabs to spaces.
		$text = $this->detab($text);

		# Turn block-level HTML blocks into hash entries
		$text = $this->hashHTMLBlocks($text);

		# Strip any lines consisting only of spaces and tabs.
		# This makes subsequent regexen easier to write, because we can
		# match consecutive blank lines with /\n+/ instead of something
		# contorted like /[ ]*\n+/ .
		$text = preg_replace('/^[ ]+$/m', '', $text);

		# Run document gamut methods.
		foreach ($this->document_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		
		

		//see if smarty exists and if it does use it
		if ($this->use_smarty) {			
			
			//if html is escaped set the double quotes back to raw quotes for smarty pants
			if ($this->xss) {
				$text = str_replace('&quot;', '"', $text);
			}
			$text = SmartyPants($text);
		}
		
		return $text;
	}
	
	var $document_gamut = array(
		# Strip link definitions, store in hashes.
		"stripLinkDefinitions" => 20,
		
		"runBasicBlockGamut"   => 30,
		);


	function stripLinkDefinitions($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: ^[id]: url "optional title"
		$text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							<?(\S+?)>?			# url = $2
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $3
								[")]
								[ ]*
							)?	# title is optional
							(?:\n+|\Z)
			}xm',
			array(&$this, '_stripLinkDefinitions_callback'),
			$text);
		return $text;
	}
	function _stripLinkDefinitions_callback($matches) {
		$link_id = strtolower($matches[1]);
		$this->urls[$link_id] = $this->encodeAmpsAndAngles($matches[2]);
		if (isset($matches[3]))
			$this->titles[$link_id] = str_replace('"', '&quot;', $matches[3]);
		return ''; # String that will replace the block
	}


	function hashHTMLBlocks($text) {
		if ($this->no_markup)  return $text;

		$less_than_tab = $this->tab_width - 1;

		# Hashify HTML blocks:
		# We only want to do this for block-level HTML tags, such as headers,
		# lists, and tables. That's because we still want to wrap <p>s around
		# "paragraphs" that are wrapped in non-block-level tags, such as anchors,
		# phrase emphasis, and spans. The list of tags we're looking for is
		# hard-coded:
		#
		# *  List "a" is made of tags which can be both inline or block-level.
		#    These will be treated block-level when the start tag is alone on 
		#    its line, otherwise they're not matched here and will be taken as 
		#    inline later.
		# *  List "b" is made of tags which are always block-level;
		#
		$block_tags_a = 'ins|del';
		$block_tags_b = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'.
						'script|noscript|form|fieldset|iframe|math';

		# Regular expression for the content of a block tag.
		$nested_tags_level = 4;
		$attr = '
			(?>				# optional tag attributes
			  \s			# starts with whitespace
			  (?>
				[^>"/]+		# text outside quotes
			  |
				/+(?!>)		# slash not followed by ">"
			  |
				"[^"]*"		# text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'	# text inside single quotes (tolerate ">")
			  )*
			)?	
			';
		$content =
			str_repeat('
				(?>
				  [^<]+			# content without tag
				|
				  <\2			# nested opening tag
					'.$attr.'	# attributes
					(?>
					  />
					|
					  >', $nested_tags_level).	# end of opening tag
					  '.*?'.					# last level nested tag content
			str_repeat('
					  </\2\s*>	# closing nested tag
					)
				  |				
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*',
				$nested_tags_level);
		$content2 = str_replace('\2', '\3', $content);

		# First, look for nested blocks, e.g.:
		# 	<div>
		# 		<div>
		# 		tags for inner block must be indented.
		# 		</div>
		# 	</div>
		#
		# The outermost tags must start at the left margin for this to match, and
		# the inner nested divs must be indented.
		# We need to do this before the next, more liberal match, because the next
		# match will start at the first `<div>` and stop at the first `</div>`.
		$text = preg_replace_callback('{(?>
			(?>
				(?<=\n\n)		# Starting after a blank line
				|				# or
				\A\n?			# the beginning of the doc
			)
			(						# save in $1

			  # Match from `\n<tag>` to `</tag>\n`, handling nested tags 
			  # in between.
					
						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b.')# start tag = $2
						'.$attr.'>			# attributes followed by > and \n
						'.$content.'		# content, support nesting
						</\2>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a.')# start tag = $3
						'.$attr.'>[ ]*\n	# attributes followed by >
						'.$content2.'		# content, support nesting
						</\3>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document
					
			| # Special case just for . It was easier to make a special 
			  # case than to make the other regex more complicated.
			
						[ ]{0,'.$less_than_tab.'}
						<(hr)				# start tag = $2
						\b					# word break
						([^<>])*?			# 
						/?>					# the matching end tag
						[ ]*
						(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # Special case for standalone HTML comments:
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # PHP and ASP-style processor instructions (<? and <%)
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])			# $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
					
			)
			)}Sxmi',
			array(&$this, '_hashHTMLBlocks_callback'),
			$text);

		return $text;
	}
	function _hashHTMLBlocks_callback($matches) {
		$text = $matches[1];
		$key  = $this->hashBlock($text);
		return "\n\n$key\n\n";
	}
	
	
	function hashPart($text, $boundary = 'X') {
	#
	# Called whenever a tag must be hashed when a function insert an atomic 
	# element in the text stream. Passing $text to through this function gives
	# a unique text-token which will be reverted back when calling unhash.
	#
	# The $boundary argument specify what character should be used to surround
	# the token. By convension, "B" is used for block elements that needs not
	# to be wrapped into paragraph tags at the end, ":" is used for elements
	# that are word separators and "S" is used for general span-level elements.
	#
		# Swap back any tag hash found in $text so we do not have to `unhash`
		# multiple times at the end.
		$text = $this->unhash($text);
		
		# Then hash the block.
		static $i = 0;
		$key = "$boundary\x1A" . ++$i . $boundary;
		$this->html_hashes[$key] = $text;
		return $key; # String that will replace the tag.
	}


	function hashBlock($text) {
	#
	# Shortcut function for hashPart with block-level boundaries.
	#
		return $this->hashPart($text, 'B');
	}


	var $block_gamut = array(
	#
	# These are all the transformations that form block-level
	# tags like paragraphs, headers, and list items.
	#
		"doHeaders"         => 10,
		"doHorizontalRules" => 20,
		
		"doLists"           => 40,
		"doCodeBlocks"      => 50,
		"doBlockQuotes"     => 60,
		);

	function runBlockGamut($text) {
	#
	# Run block gamut tranformations.
	#
		# We need to escape raw HTML in Markdown source before doing anything 
		# else. This need to be done for each block, and not only at the 
		# begining in the Markdown function since hashed blocks can be part of
		# list items and could have been indented. Indented blocks would have 
		# been seen as a code block in a previous pass of hashHTMLBlocks.
		$text = $this->hashHTMLBlocks($text);
		
		return $this->runBasicBlockGamut($text);
	}
	
	function runBasicBlockGamut($text) {
	#
	# Run block gamut tranformations, without hashing HTML blocks. This is 
	# useful when HTML blocks are known to be already hashed, like in the first
	# whole-document pass.
	#
		foreach ($this->block_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		
		# Finally form paragraph and restore hashed blocks.
		$text = $this->formParagraphs($text);

		return $text;
	}
	
	
	function doHorizontalRules($text) {
		# Do Horizontal Rules:
		return preg_replace(
			'{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx',
			"\n".$this->hashBlock("<hr$this->empty_element_suffix")."\n", 
			$text);
	}


	var $span_gamut = array(
	#
	# These are all the transformations that occur *within* block-level
	# tags like paragraphs, headers, and list items.
	#
		# Process character escapes, code spans, and inline HTML
		# in one shot.
		"parseSpan"           => -30,

		# Process anchor and image tags. Images must come first,
		# because ![foo][f] looks like an anchor.
		"doImages"            =>  10,
		"doAnchors"           =>  20,
		
		# Make links out of things like `<http://example.com/>`
		# Must come after doAnchors, because you can use < and >
		# delimiters in inline links like [this](<url>).
		"doAutoLinks"         =>  30,
		"encodeAmpsAndAngles" =>  40,

		"doItalicsAndBold"    =>  50,
		"doHardBreaks"        =>  60,
		);

	function runSpanGamut($text) {
	#
	# Run span gamut tranformations.
	#
		foreach ($this->span_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		return $text;
	}
	
	
	function doHardBreaks($text) {
		# Do hard breaks:
		return preg_replace_callback('/ {2,}\n/', 
			array(&$this, '_doHardBreaks_callback'), $text);
	}
	function _doHardBreaks_callback($matches) {
		return $this->hashPart("<br$this->empty_element_suffix\n");
	}


	function doAnchors($text) {
	#
	# Turn Markdown link shortcuts into XHTML <a> tags.
	#
		if ($this->in_anchor) return $text;
		$this->in_anchor = true;
		
		#
		# First, handle reference-style links: [link text] [id]
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		#
		# Next, inline-style links: [link text](url "optional title")
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets.')	# link text = $2
			  \]
			  \(			# literal paren
				[ ]*
				(?:
					<(\S*)>	# href = $3
				|
					('.$this->nested_url_parenthesis.')	# href = $4
				)
				[ ]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ ]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			)
			}xs',
			array(&$this, '_DoAnchors_inline_callback'), $text);

		#
		# Last, handle reference-style shortcuts: [link text]
		# These must come last in case you've also got [link test][1]
		# or [link test](/foo)
		#
//		$text = preg_replace_callback('{
//			(					# wrap whole match in $1
//			  \[
//				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
//			  \]
//			)
//			}xs',
//			array(&$this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}
	function _doAnchors_reference_callback($matches) {
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == "") {
			# for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}
		
		# lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);
		
		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeAmpsAndAngles($url);
			#set baseurl and rel values to links
			if (!strstr($url, "http://")) {
				$url = $this->baseurl . $url;
			}
			
			#add external attribute to links with http
			if ($this->link_rel!='') {
				$url = $this->baseurl . $url;
				
				$result = "<a href=\"$url\" rel=\"{$this->link_rel}\"";
			}
			elseif (strstr($url, "http://")) {
				$result = "<a href=\"$url\" rel=\"external\"";
			}
			else {
				$url = $this->baseurl . $url;
				$result = "<a href=\"$url\"";
			}
			
			
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAmpsAndAngles($title);
				$result .=  " title=\"$title\"";
			}
		
			$link_text = $this->runSpanGamut($link_text);
			$result .= ">$link_text</a>";
			$result = $this->hashPart($result);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}
	function _doAnchors_inline_callback($matches) {
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$url = $this->encodeAmpsAndAngles($url);
		
		
		#set baseurl and rel values to links
		if (!strstr($url, "http://")) {
			$url = $this->baseurl . $url;
		}
		
		#add external attribute to links with http
		if ($this->link_rel!='') {
			$result = "<a href=\"$url\" rel=\"{$this->link_rel}\"";
		}
		elseif (strstr($url, "http://")) {
			$result = "<a href=\"$url\" rel=\"external\"";
		}
		else {
			$result = "<a href=\"$url\"";
		}
		
		/* CHANGED - added href attributes */
		/*  rel=\"nofollow\" */
		#$result = "<a href=\"$url\"";
		
		if (isset($title)) {
			$title = str_replace('"', '&quot;', $title);
			$title = $this->encodeAmpsAndAngles($title);
			$result .=  " title=\"$title\"";
		}
		
		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}


	function doImages($text) {
	#
	# Turn Markdown image shortcuts into <img> tags.
	#
		#
		# First, handle reference-style labeled images: ![alt text][id]
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', 
			array(&$this, '_doImages_reference_callback'), $text);

		#
		# Next, handle inline images:  ![alt text](url "optional title")
		# Don't forget: encode * and _
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ ]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis.')	# src url = $4
				)
				[ ]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ ]*
				)?			# title is optional
			  \)
			)
			}xs',
			array(&$this, '_doImages_inline_callback'), $text);

		return $text;
	}
	function _doImages_reference_callback($matches) {
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == "") {
			$link_id = strtolower($alt_text); # for shortcut links like ![this][].
		}

		$alt_text = str_replace('"', '&quot;', $alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			
			#if the image is a js file return an empty result
			if (!strstr($url, ".jpg") && !strstr($url, ".png") && !strstr($url, ".gif")) {
				$result = "";
			}
			else {
				#add the baseurl to images without http
				if (strstr($url, "http://")) {
					$result = "<img src=\"$url\" alt=\"$alt_text\"";
				}
				else {
					$url = $this->baseurl . $url;
					$result = "<img src=\"$url\" alt=\"$alt_text\"";
				}

				if (isset($title)) {
					$title = str_replace('"', '&quot;', $title);
					$result .=  " title=\"$title\""; # $title already quoted

				}
				$result .= $this->empty_element_suffix;
			}
		}
		else {
			# If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}
	function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];
		$alt_text = str_replace('"', '&quot;', $alt_text);
		
	
		#if the image is a js file return an empty result
		if (!strstr($url, ".jpg") && !strstr($url, ".png") && !strstr($url, ".gif")) {
			$result = "";
		}
		else {
			if (strstr($url, "http://")) {
				$result = "<img src=\"$url\" alt=\"$alt_text\"";
			}
			else {
				$url = $this->baseurl . $url;
				$result = "<img src=\"$url\" alt=\"$alt_text\"";
			}

			if (isset($title)) {
				$title = str_replace('"', '&quot;', $title);
				$result .=  " title=\"$title\""; # $title already quoted

			}
			$result .= $this->empty_element_suffix;
		}

		return $this->hashPart($result);
	}


	function doHeaders($text) {
		# Setext-style headers:
		#	  Header 1
		#	  ========
		#  
		#	  Header 2
		#	  --------
		#
		$text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
			array(&$this, '_doHeaders_callback_setext'), $text);

		# atx-style headers:
		#	# Header 1
		#	## Header 2
		#	## Header 2 with closing hashes ##
		#	...
		#	###### Header 6
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	function _doHeaders_callback_setext($matches) {

			$level = $matches[2]{0} == '=' ? 1 : 2;
			$level = $level + $this->header_level;
			$block = "<h$level>".$this->runSpanGamut($matches[1])."</h$level>";
			return "\n" . $this->hashBlock($block) . "\n\n";	
	
	}
	function _doHeaders_callback_atx($matches) {
	   $level = strlen($matches[1]);
	   $level = $level + $this->header_level;
	   $block = "<h$level>".$this->runSpanGamut($matches[2])."</h$level>";
	   return "\n" . $this->hashBlock($block) . "\n\n";
	}


	function doLists($text) {
	#
	# Form HTML ordered (numbered) and unordered (bulleted) lists.
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul  = '[*+-]';
		$marker_ol  = '\d+[.]';
		$marker_any = "(?:$marker_ul|$marker_ol)";

		$markers = array($marker_ul, $marker_ol);

		foreach ($markers as $marker) {
			# Re-usable pattern to match any entirel ul or ol list:
			$whole_list = '
				(								# $1 = whole list
				  (								# $2
					[ ]{0,'.$less_than_tab.'}
					('.$marker.')				# $3 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $4
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						'.$marker.'[ ]+
					  )
				  )
				)
			'; // mx
			
			# We use a different prefix before nested lists than top-level lists.
			# See extended comment in _ProcessListItems().
		
			if ($this->list_level) {
				$text = preg_replace_callback('{
						^
						'.$whole_list.'
					}mx',
					array(&$this, '_doLists_callback'), $text);
			}
			else {
				$text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list.'
					}mx',
					array(&$this, '_doLists_callback'), $text);
			}
		}

		return $text;
	}
	function _doLists_callback($matches) {
		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul  = '[*+-]';
		$marker_ol  = '\d+[.]';
		$marker_any = "(?:$marker_ul|$marker_ol)";
		
		$list = $matches[1];
		$list_type = preg_match("/$marker_ul/", $matches[3]) ? "ul" : "ol";
		
		$marker_any = ( $list_type == "ul" ? $marker_ul : $marker_ol );
		
		$list .= "\n";
		$result = $this->processListItems($list, $marker_any);
		
		$result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>");
		return "\n". $result ."\n\n";
	}

	var $list_level = 0;

	function processListItems($list_str, $marker_any) {
	#
	#	Process the contents of a single ordered or unordered list, splitting it
	#	into individual list items.
	#
		# The $this->list_level global keeps track of when we're inside a list.
		# Each time we enter a list, we increment it; when we leave a list,
		# we decrement. If it's zero, we're not in a list anymore.
		#
		# We do this because when we're not inside a list, we want to treat
		# something like this:
		#
		#		I recommend upgrading to version
		#		8. Oops, now this line is treated
		#		as a sub-list.
		#
		# As a single paragraph, despite the fact that the second line starts
		# with a digit-period-space sequence.
		#
		# Whereas when we're inside a list (or sub-list), that line will be
		# treated as the start of a sub-list. What a kludge, huh? This is
		# an aspect of Markdown's syntax that's hard to parse perfectly
		# without resorting to mind-reading. Perhaps the solution is to
		# change the syntax rules such that sub-lists must start with a
		# starting cardinal number; e.g. "1." or "a.".
		
		$this->list_level++;

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?							# leading line = $1
			(^[ ]*)						# leading whitespace = $2
			('.$marker_any.') [ ]+		# list marker = $3
			((?s:.+?))						# list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any.') [ ]+))
			}xm',
			array(&$this, '_processListItems_callback'), $list_str);

		$this->list_level--;
		return $list_str;
	}
	function _processListItems_callback($matches) {
		$item = $matches[4];
		$leading_line =& $matches[1];
		$leading_space =& $matches[2];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line || 
			preg_match('/\n{2,}/', $item))
		{
			$item = $this->runBlockGamut($this->outdent($item)."\n");
		}
		else {
			# Recursion for sub-lists:
			$item = $this->doLists($this->outdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = $this->runSpanGamut($item);
		}

		return "<li>" . $item . "</li>\n";
	}


	function doCodeBlocks($text) {
	#
	#	Process Markdown `<pre><code>` blocks.
	#
		$text = preg_replace_callback('{
				(?:\n\n|\A)
				(	            # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array(&$this, '_doCodeBlocks_callback'), $text);

		return $text;
	}
	function _doCodeBlocks_callback($matches) {
		$codeblock = $matches[1];

		$codeblock = $this->outdent($codeblock);
		if (!$this->xss) {
			$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);	
		}

		# trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

		$codeblock = "<pre><code>$codeblock\n</code></pre>";
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}


	function makeCodeSpan($code) {
	#
	# Create a code span markup for $code. Called from handleSpanToken.
	#
		if (!$this->xss) {
			$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
		}
		
		return $this->hashPart("<code>$code</code>");
	}


	function doItalicsAndBold($text) {
		# <strong> must go first:
		$text = preg_replace_callback('{
				(						# $1: Marker
					(?<!\*\*) \* |		#     (not preceded by two chars of
					(?<!__)   _			#      the same marker)
				)
				\1
				(?=\S) 					# Not followed by whitespace 
				(?!\1\1)				#   or two others marker chars.
				(						# $2: Content
					(?>
						[^*_]+?			# Anthing not em markers.
					|
										# Balence any regular emphasis inside.
						\1 (?=\S) .+? (?<=\S) \1
					|
						.				# Allow unbalenced * and _.
					)+?
				)
				(?<=\S) \1\1			# End mark not preceded by whitespace.
			}sx',
			array(&$this, '_doItalicAndBold_strong_callback'), $text);
		# Then <em>:
		$text = preg_replace_callback(
			'{ ( (?<!\*)\* | (?<!_)_ ) (?=\S) (?! \1) (.+?) (?<=\S)(?<!\s(?=\1).) \1 }sx',
			array(&$this, '_doItalicAndBold_em_callback'), $text);

		return $text;
	}
	function _doItalicAndBold_em_callback($matches) {
		$text = $matches[2];
		$text = $this->runSpanGamut($text);
		return $this->hashPart("<em>$text</em>");
	}
	function _doItalicAndBold_strong_callback($matches) {
		$text = $matches[2];
		$text = $this->runSpanGamut($text);
		return $this->hashPart("<strong>$text</strong>");
	}


	function doBlockQuotes($text) {
		//check to see if allow html is true or not, 
		//if not the > will be escaped, but will still need to be used for the blockqutes
		
		if (!$this->xss) {
			$text = preg_replace_callback('/
				  (								# Wrap whole match in $1
					(?>
					  ^[ ]*>[ ]?			# ">" at the start of a line
						.+\n					# rest of the first line
					  (.+\n)*					# subsequent consecutive lines
					  \n*						# blanks
					)+
				  )
				/xm',
				array(&$this, '_doBlockQuotes_callback'), $text);
		} else {
			$text = preg_replace_callback('/
				  (								# Wrap whole match in $1
					(?>
					  ^[ ]*>[ ]?			# ">" at the start of a line
						.+\n					# rest of the first line
					  (.+\n)*					# subsequent consecutive lines
					  \n*						# blanks
					)+
				  )
				/xm',
				array(&$this, '_doBlockQuotes_callback'), $text);
		}

		
		return $text;
	}
	function _doBlockQuotes_callback($matches) {
		$bq = $matches[1];
		# trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		
		$bq = $this->runBlockGamut($bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		# These leading spaces cause problem with <pre> content, 
		# so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', 
			array(&$this, '_DoBlockQuotes_callback2'), $bq);

		return "\n". $this->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n";
	}
	function _doBlockQuotes_callback2($matches) {
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}


	function formParagraphs($text) {
	#
	#	Params:
	#		$text - string to process with html <p> tags
	#
		# Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		#
		# Wrap <p> tags and unhashify HTML blocks
		#
		foreach ($grafs as $key => $value) {
			if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
				# Is a paragraph.
				$value = $this->runSpanGamut($value);
				$value = preg_replace('/^([ ]*)/', "<p>", $value);
				$value .= "</p>";
				$grafs[$key] = $this->unhash($value);
			}
			else {
				# Is a block.
				# Modify elements of @grafs in-place...
				$graf = $value;
				$block = $this->html_hashes[$graf];
				$graf = $block;
//				if (preg_match('{
//					\A
//					(							# $1 = <div> tag
//					  <div  \s+
//					  [^>]*
//					  \b
//					  markdown\s*=\s*  ([\'"])	#	$2 = attr quote char
//					  1
//					  \2
//					  [^>]*
//					  >
//					)
//					(							# $3 = contents
//					.*
//					)
//					(</div>)					# $4 = closing tag
//					\z
//					}xs', $block, $matches))
//				{
//					list(, $div_open, , $div_content, $div_close) = $matches;
//
//					# We can't call Markdown(), because that resets the hash;
//					# that initialization code should be pulled into its own sub, though.
//					$div_content = $this->hashHTMLBlocks($div_content);
//					
//					# Run document gamut methods on the content.
//					foreach ($this->document_gamut as $method => $priority) {
//						$div_content = $this->$method($div_content);
//					}
//
//					$div_open = preg_replace(
//						'{\smarkdown\s*=\s*([\'"]).+?\1}', '', $div_open);
//
//					$graf = $div_open . "\n" . $div_content . "\n" . $div_close;
//				}
				$grafs[$key] = $graf;
			}
		}

		return implode("\n\n", $grafs);
	}


	function encodeAmpsAndAngles($text) {
	# Smart processing for ampersands and angle brackets that need to be encoded.
		if (!$this->xss) {
			if ($this->no_entities) {
				$text = str_replace('&', '&amp;', $text);
				$text = str_replace('<', '&lt;', $text);
				return $text;
			}
			
		
			
			

			# Ampersand-encoding based entirely on Nat Irons's Amputator MT plugin:
			#   http://bumppo.net/projects/amputator/
			$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', 
								 '&amp;', $text);
			
			if ($this->use_smarty) {
				# Encode naked <'s
				$text = preg_replace('{&quot;(?![a-z/?\$!%])}i', '"', $text);
			}					
			
			# Encode naked <'s
			$text = preg_replace('{<(?![a-z/?\$!%])}i', '&lt;', $text);
		}
		

		return $text;
	}


	function doAutoLinks($text) {
		$text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}', 
			array(&$this, '_doAutoLinks_url_callback'), $text);

		# Email addresses: <address@domain.foo>
		$text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				[-.\w\x80-\xFF]+
				\@
				[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
			)
			>
			}xi',
			array(&$this, '_doAutoLinks_email_callback'), $text);

		return $text;
	}
	
	/* CHANGED AUTO LINK - added rel=\"external\" */
	function _doAutoLinks_url_callback($matches) {
		$url = $this->encodeAmpsAndAngles($matches[1]);
		$link = "<a href=\"$url\">$url</a>";
		return $this->hashPart($link);
	}
	function _doAutoLinks_email_callback($matches) {
		$address = $matches[1];
		$link = $this->encodeEmailAddress($address);
		return $this->hashPart($link);
	}


	function encodeEmailAddress($addr) {
	#
	#	Input: an email address, e.g. "foo@example.com"
	#
	#	Output: the email address as a mailto link, with each character
	#		of the address encoded as either a decimal or hex entity, in
	#		the hopes of foiling most address harvesting spam bots. E.g.:
	#
	#	  <p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x66;o&#111;
	#        &#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;&#101;&#46;&#x63;&#111;
	#        &#x6d;">&#x66;o&#111;&#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;
	#        &#101;&#46;&#x63;&#111;&#x6d;</a></p>
	#
	#	Based by a filter by Matthew Wickline, posted to BBEdit-Talk.
	#   With some optimizations by Milian Wolff.
	#
		$addr = "mailto:" . $addr;
		$chars = preg_split('/(?<!^)(?!$)/', $addr);
		$seed = (int)abs(crc32($addr) / strlen($addr)); # Deterministic seed.
		
		foreach ($chars as $key => $char) {
			$ord = ord($char);
			# Ignore non-ascii chars.
			if ($ord < 128) {
				$r = ($seed * (1 + $key)) % 100; # Pseudo-random function.
				# roughly 10% raw, 45% hex, 45% dec
				# '@' *must* be encoded. I insist.
				if ($r > 90 && $char != '@') /* do nothing */;
				else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
				else              $chars[$key] = '&#'.$ord.';';
			}
		}
		
		$addr = implode('', $chars);
		$text = implode('', array_slice($chars, 7)); # text without `mailto:`
		$addr = "<a href=\"$addr\" >$text</a>";

		return $addr;
	}


	function parseSpan($str) {
	#
	# Take the string $str and parse it into tokens, hashing embeded HTML,
	# escaped characters and handling code spans.
	#
		$output = '';
		
		$regex = '{
				(
					\\\\['.preg_quote($this->escape_chars).']
				|
					(?<![`\\\\])
					`+						# code span marker
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
			').'
				)
				}xs';

		while (1) {
			#
			# Each loop iteration seach for either the next tag, the next 
			# openning code span marker, or the next escaped character. 
			# Each token is then passed to handleSpanToken.
			#
			$parts = preg_split($regex, $str, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			# Create token from text preceding tag.
			if ($parts[0] != "") {
				$output .= $parts[0];
			}
			
			# Check if we reach the end.
			if (isset($parts[1])) {
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else {
				break;
			}
		}
		
		return $output;
	}
	
	
	function handleSpanToken($token, &$str) {
	#
	# Handle $token provided by parseSpan by determining its nature and 
	# returning the corresponding value that should replace it.
	#
		switch ($token{0}) {
			case "\\":
				return $this->hashPart("&#". ord($token{1}). ";");
			case "`":
				# Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])'.$token.'(?!`)(.*)$/sm', 
					$str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}
				return $token; // return as text since no ending marker found.
			default:
				return $this->hashPart($token);
		}
	}


	function outdent($text) {
	#
	# Remove one level of line-leading tabs or spaces
	#
		return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text);
	}


	# String length function for detab. `_initDetab` will create a function to 
	# hanlde UTF-8 if the default function does not exist.
	var $utf8_strlen = 'mb_strlen';
	
	function detab($text) {
	#
	# Replace tabs with the appropriate amount of space.
	#
		# For each line we separate the line in blocks delemited by
		# tab characters. Then we reconstruct every line by adding the 
		# appropriate number of space between each blocks.
		
		$text = preg_replace_callback('/^.*\t.*$/m',
			array(&$this, '_detab_callback'), $text);

		return $text;
	}
	function _detab_callback($matches) {
		$line = $matches[0];
		$strlen = $this->utf8_strlen; # strlen function for UTF-8.
		
		# Split in blocks.
		$blocks = explode("\t", $line);
		# Add each blocks to the line.
		$line = $blocks[0];
		unset($blocks[0]); # Do not add first block twice.
		foreach ($blocks as $block) {
			# Calculate amount of space, insert spaces, insert block.
			$amount = $this->tab_width - 
				$strlen($line, 'UTF-8') % $this->tab_width;
			$line .= str_repeat(" ", $amount) . $block;
		}
		return $line;
	}
	function _initDetab() {
	#
	# Check for the availability of the function in the `utf8_strlen` property
	# (initially `mb_strlen`). If the function is not available, create a 
	# function that will loosely count the number of UTF-8 characters with a
	# regular expression.
	#
		if (function_exists($this->utf8_strlen)) return;
		$this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);');
	}


	function unhash($text) {
	#
	# Swap back in all the tags hashed by _HashHTMLBlocks.
	#
		return preg_replace_callback('/(.)\x1A[0-9]+\1/', 
			array(&$this, '_unhash_callback'), $text);
	}
	function _unhash_callback($matches) {
		return $this->html_hashes[$matches[0]];
	}

}


#
# Markdown Extra Parser Class
#

class MarkdownExtra_Parser extends Markdown {

	
	# Prefix for footnote ids.
	protected $fn_id_prefix = "";
	
	# Optional title attribute for footnote links and backlinks.
	protected $fn_link_title = MARKDOWN_FN_LINK_TITLE;
	protected $fn_backlink_title = MARKDOWN_FN_BACKLINK_TITLE;
	
	# Optional class attribute for footnote links and backlinks.
	protected $fn_link_class = MARKDOWN_FN_LINK_CLASS;
	protected $fn_backlink_class = MARKDOWN_FN_BACKLINK_CLASS;


	public function __construct($header_level=2, $xss=false, $link_rel='no-follow', $use_smarty=true) 
	{
	#
	# Constructor function. Initialize the parser object.
	#
		# Add extra escapable characters before parent constructor 
		# initialize the table.
		$this->escape_chars .= ':|';
    
		# Insert extra document, block, and span transformations. 
		# Parent constructor will do the sorting.
		$this->document_gamut += array(
			"stripFootnotes"     => 15,
			"stripAbbreviations" => 25,
			"appendFootnotes"    => 50,
			);
		$this->block_gamut += array(
			"doTables"           => 15,
			"doDefLists"         => 45,
			);
		$this->span_gamut += array(
			"doFootnotes"        => 5,
			"doAbbreviations"    => 70,
			);
    
		parent::__construct($header_level, $xss, $link_rel, $use_smarty);
	}
	
	
	# Extra hashes used during extra transformations.
	var $footnotes = array();
	var $footnotes_ordered = array();
	var $abbr_desciptions = array();
	var $abbr_matches = array();
	
	# Status flag to avoid invalid nesting.
	var $in_footnote = false;
	
	
	function transform($text) {
	#
	# Added clear to the new $html_hashes, reordered `hashHTMLBlocks` before 
	# blank line stripping and added extra parameter to `runBlockGamut`.
	#
		# Clear the global hashes. If we don't clear these, you get conflicts
		# from other articles when generating a page which contains more than
		# one article (e.g. an index page that shows the N most recent
		# articles):
		$this->footnotes = array();
		$this->footnotes_ordered = array();
		$this->abbr_desciptions = array();
		$this->abbr_matches = array();

		return parent::transform($text);
	}
	
	
	### HTML Block Parser ###
	
	# Tags that are always treated as block tags:
	var $block_tags = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend';
	
	# Tags treated as block tags only if the opening tag is alone on it's line:
	var $context_block_tags = 'script|noscript|math|ins|del';
	
	# Tags where markdown="1" default to span mode:
	var $contain_span_tags = 'p|h[1-6]|li|dd|dt|td|th|legend|address';
	
	# Tags which must not have their contents modified, no matter where 
	# they appear:
	var $clean_tags = 'script|math';
	
	# Tags that do not need to be closed.
	var $auto_close_tags = 'hr|img';
	

	function hashHTMLBlocks($text) {
	#
	# Hashify HTML Blocks and "clean tags".
	#
	# We only want to do this for block-level HTML tags, such as headers,
	# lists, and tables. That's because we still want to wrap <p>s around
	# "paragraphs" that are wrapped in non-block-level tags, such as anchors,
	# phrase emphasis, and spans. The list of tags we're looking for is
	# hard-coded.
	#
	# This works by calling _HashHTMLBlocks_InMarkdown, which then calls
	# _HashHTMLBlocks_InHTML when it encounter block tags. When the markdown="1" 
	# attribute is found whitin a tag, _HashHTMLBlocks_InHTML calls back
	#  _HashHTMLBlocks_InMarkdown to handle the Markdown syntax within the tag.
	# These two functions are calling each other. It's recursive!
	#
		#
		# Call the HTML-in-Markdown hasher.
		#
		list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text);
		
		return $text;
	}
	function _hashHTMLBlocks_inMarkdown($text, $indent = 0, 
										$enclosing_tag = '', $span = false)
	{
	#
	# Parse markdown text, calling _HashHTMLBlocks_InHTML for block tags.
	#
	# *   $indent is the number of space to be ignored when checking for code 
	#     blocks. This is important because if we don't take the indent into 
	#     account, something like this (which looks right) won't work as expected:
	#
	#     <div>
	#         <div markdown="1">
	#         Hello World.  <-- Is this a Markdown code block or text?
	#         </div>  <-- Is this a Markdown code block or a real tag?
	#     <div>
	#
	#     If you don't like this, just don't indent the tag on which
	#     you apply the markdown="1" attribute.
	#
	# *   If $enclosing_tag is not empty, stops at the first unmatched closing 
	#     tag with that name. Nested tags supported.
	#
	# *   If $span is true, text inside must treated as span. So any double 
	#     newline will be replaced by a single newline so that it does not create 
	#     paragraphs.
	#
	# Returns an array of that form: ( processed text , remaining text )
	#
		if ($text === '') return array('', '');

		# Regex to check for the presense of newlines around a block tag.
		$newline_match_before = '/(?:^\n?|\n\n)*$/';
		$newline_match_after = 
			'{
				^						# Start of text following the tag.
				(?:[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					# Must be followed by newline.
			}xs';
		
		# Regex to match any tag.
		$block_tag_match =
			'{
				(					# $2: Capture hole tag.
					</?					# Any opening or closing tag.
						(?:				# Tag name.
							'.$this->block_tags.'			|
							'.$this->context_block_tags.'	|
							'.$this->clean_tags.'        	|
							(?!\s)'.$enclosing_tag.'
						)
						\s*				# Whitespace.
						(?>
							".*?"		|	# Double quotes (can contain `>`)
							\'.*?\'   	|	# Single quotes (can contain `>`)
							.+?				# Anything but quotes and `>`.
						)*?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';

		
		$depth = 0;		# Current depth inside the tag tree.
		$parsed = "";	# Parsed text that will be returned.

		#
		# Loop through every tag until we find the closing tag of the parent
		# or loop until reaching the end of text if no parent tag specified.
		#
		do {
			#
			# Split the text using the first $tag_match pattern found.
			# Text before  pattern will be first in the array, text after
			# pattern will be at the end, and between will be any catches made 
			# by the pattern.
			#
			$parts = preg_split($block_tag_match, $text, 2, 
								PREG_SPLIT_DELIM_CAPTURE);
			
			# If in Markdown span mode, add a empty-string span-level hash 
			# after each newline to prevent triggering any block element.
			if ($span) {
				$void = $this->hashPart("", ':');
				$newline = "$void\n";
				$parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void;
			}
			
			$parsed .= $parts[0]; # Text before current tag.
			
			# If end of $text has been reached. Stop loop.
			if (count($parts) < 3) {
				$text = "";
				break;
			}
			
			$tag  = $parts[1]; # Tag to handle.
			$text = $parts[2]; # Remaining text after current tag.
			
			#
			# Check for: Tag inside code block or span
			#
			if (# Find current paragraph
				preg_match('/(?>^\n?|\n\n)((?>.+\n?)*?)$/', $parsed, $matches) &&
				(
				# Then match in it either a code block...
				preg_match('/^ {'.($indent+4).'}.*(?>\n {'.($indent+4).'}.*)*'.
							'(?!\n)$/', $matches[1], $x) ||
				# ...or unbalenced code span markers. (the regex matches balenced)
				!preg_match('/^(?>[^`]+|(`+)(?>[^`]+|(?!\1[^`])`)*?\1(?!`))*$/s',
							 $matches[1])
				))
			{
				# Tag is in code block or span and may not be a tag at all. So we
				# simply skip the first char (should be a `<`).
				$parsed .= $tag{0};
				$text = substr($tag, 1) . $text; # Put back $tag minus first char.
			}
			#
			# Check for: Opening Block level tag or
			#            Opening Content Block tag (like ins and del) 
			#               used as a block tag (tag is alone on it's line).
			#
			else if (preg_match("{^<(?:$this->block_tags)\b}", $tag) ||
				(	preg_match("{^<(?:$this->context_block_tags)\b}", $tag) &&
					preg_match($newline_match_before, $parsed) &&
					preg_match($newline_match_after, $text)	)
				)
			{
				# Need to parse tag and following text using the HTML parser.
				list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashBlock", true);
				
				# Make sure it stays outside of any paragraph by adding newlines.
				$parsed .= "\n\n$block_text\n\n";
			}
			#
			# Check for: Clean tag (like script, math)
			#            HTML Comments, processing instructions.
			#
			else if (preg_match("{^<(?:$this->clean_tags)\b}", $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				# Need to parse tag and following text using the HTML parser.
				# (don't check for markdown attribute)
				list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashClean", false);
				
				$parsed .= $block_text;
			}
			#
			# Check for: Tag with same name as enclosing tag.
			#
			else if ($enclosing_tag !== '' &&
				# Same name as enclosing tag.
				preg_match("{^</?(?:$enclosing_tag)\b}", $tag))
			{
				#
				# Increase/decrease nested tag count.
				#
				if ($tag{1} == '/')						$depth--;
				else if ($tag{strlen($tag)-2} != '/')	$depth++;

				if ($depth < 0) {
					#
					# Going out of parent element. Clean up and break so we
					# return to the calling function.
					#
					$text = $tag . $text;
					break;
				}
				
				$parsed .= $tag;
			}
			else {
				$parsed .= $tag;
			}
		} while ($depth >= 0);
		
		return array($parsed, $text);
	}
	function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr) {
	#
	# Parse HTML, calling _HashHTMLBlocks_InMarkdown for block tags.
	#
	# *   Calls $hash_method to convert any blocks.
	# *   Stops when the first opening tag closes.
	# *   $md_attr indicate if the use of the `markdown="1"` attribute is allowed.
	#     (it is not inside clean tags)
	#
	# Returns an array of that form: ( processed text , remaining text )
	#
		if ($text === '') return array('', '');
		
		# Regex to match `markdown` attribute inside of a tag.
		$markdown_attr_match = '
			{
				\s*			# Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?:
					(["\'])		# $1: quote delimiter		
					(.*?)		# $2: attribute value
					\1			# matching delimiter	
				|
					([^\s>]*)	# $3: unquoted attribute value
				)
				()				# $4: make $3 always defined (avoid warnings)
			}xs';
		
		# Regex to match any tag.
		$tag_match = '{
				(					# $2: Capture hole tag.
					</?					# Any opening or closing tag.
						[\w:$]+			# Tag name.
						\s*				# Whitespace.
						(?>
							".*?"		|	# Double quotes (can contain `>`)
							\'.*?\'   	|	# Single quotes (can contain `>`)
							.+?				# Anything but quotes and `>`.
						)*?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';
		
		$original_text = $text;		# Save original text in case of faliure.
		
		$depth		= 0;	# Current depth inside the tag tree.
		$block_text	= "";	# Temporary text holder for current text.
		$parsed		= "";	# Parsed text that will be returned.

		#
		# Get the name of the starting tag.
		#
		if (preg_match("/^<([\w:$]*)\b/", $text, $matches))
			$base_tag_name = $matches[1];

		#
		# Loop through every tag until we find the corresponding closing tag.
		#
		do {
			#
			# Split the text using the first $tag_match pattern found.
			# Text before  pattern will be first in the array, text after
			# pattern will be at the end, and between will be any catches made 
			# by the pattern.
			#
			$parts = preg_split($tag_match, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			if (count($parts) < 3) {
				#
				# End of $text reached with unbalenced tag(s).
				# In that case, we return original text unchanged and pass the
				# first character as filtered to prevent an infinite loop in the 
				# parent function.
				#
				return array($original_text{0}, substr($original_text, 1));
			}
			
			$block_text .= $parts[0]; # Text before current tag.
			$tag         = $parts[1]; # Tag to handle.
			$text        = $parts[2]; # Remaining text after current tag.
			
			#
			# Check for: Auto-close tag (like <hr/>)
			#			 Comments and Processing Instructions.
			#
			if (preg_match("{^</?(?:$this->auto_close_tags)\b}", $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				# Just add the tag to the block as if it was text.
				$block_text .= $tag;
			}
			else {
				#
				# Increase/decrease nested tag count. Only do so if
				# the tag's name match base tag's.
				#
				if (preg_match("{^</?$base_tag_name\b}", $tag)) {
					if ($tag{1} == '/')						$depth--;
					else if ($tag{strlen($tag)-2} != '/')	$depth++;
				}
				
				#
				# Check for `markdown="1"` attribute and handle it.
				#
				if ($md_attr && 
					preg_match($markdown_attr_match, $tag, $attr_m) &&
					preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3]))
				{
					# Remove `markdown` attribute from opening tag.
					$tag = preg_replace($markdown_attr_match, '', $tag);
					
					# Check if text inside this tag must be parsed in span mode.
					$this->mode = $attr_m[2] . $attr_m[3];
					$span_mode = $this->mode == 'span' || $this->mode != 'block' &&
						preg_match("{^<(?:$this->contain_span_tags)\b}", $tag);
					
					# Calculate indent before tag.
					preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text, $matches);
					$indent = strlen($matches[1]);
					
					# End preceding block with this tag.
					$block_text .= $tag;
					$parsed .= $this->$hash_method($block_text);
					
					# Get enclosing tag name for the ParseMarkdown function.
					preg_match('/^<([\w:$]*)\b/', $tag, $matches);
					$tag_name = $matches[1];
					
					# Parse the content using the HTML-in-Markdown parser.
					list ($block_text, $text)
						= $this->_hashHTMLBlocks_inMarkdown($text, $indent, 
														$tag_name, $span_mode);
					
					# Outdent markdown text.
					if ($indent > 0) {
						$block_text = preg_replace("/^[ ]{1,$indent}/m", "", 
													$block_text);
					}
					
					# Append tag content to parsed text.
					if (!$span_mode)	$parsed .= "\n\n$block_text\n\n";
					else				$parsed .= "$block_text";
					
					# Start over a new block.
					$block_text = "";
				}
				else $block_text .= $tag;
			}
			
		} while ($depth > 0);
		
		#
		# Hash last block text that wasn't processed inside the loop.
		#
		$parsed .= $this->$hash_method($block_text);
		
		return array($parsed, $text);
	}


	function hashClean($text) {
	#
	# Called whenever a tag must be hashed when a function insert a "clean" tag
	# in $text, it pass through this function and is automaticaly escaped, 
	# blocking invalid nested overlap.
	#
		return $this->hashPart($text, 'C');
	}


	function doHeaders($text) {
	#
	# Redefined to add id attribute support.
	#
		# Setext-style headers:
		#	  Header 1  {#header1}
		#	  ========
		#  
		#	  Header 2  {#header2}
		#	  --------
		#
		$text = preg_replace_callback(
			'{
				(^.+?)								# $1: Header text
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?	# $2: Id attribute
				[ ]*\n(=+|-+)[ ]*\n+				# $3: Header footer
			}mx',
			array(&$this, '_doHeaders_callback_setext'), $text);

		# atx-style headers:
		#	# Header 1        {#header1}
		#	## Header 2       {#header2}
		#	## Header 2 with closing hashes ##  {#header3}
		#	...
		#	###### Header 6   {#header2}
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? # id attribute
				[ ]*
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	function _doHeaders_attr($attr) {
		if (empty($attr))  return "";
		return " id=\"$attr\"";
	}
	function _doHeaders_callback_setext($matches) {
		$level = $matches[3]{0} == '=' ? 1 : 2;
		$attr  = $this->_doHeaders_attr($id =& $matches[2]);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	
	#MAKE HEADERS FOR PAGES
	function _doHeaders_callback_atx($matches) {
		if (isset($_GET['list']))
		{	
			$level = strlen($matches[1]);
			
			#custom header levels
			$level = $level + $this->header_level;
			$level = $level>6 ? 6 : $level;
			
			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
			return "\n" . $this->hashBlock($block) . "\n\n";
		}
		else
		{
			$level = strlen($matches[1]);
			#custom header levels
			$level = $level + $this->header_level;
			$level = $level>6 ? 6 : $level;
			
			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
			return "\n" . $this->hashBlock($block) . "\n\n";	
		}	
	}


	function doTables($text) {
	#
	# Form HTML tables.
	#
		$less_than_tab = $this->tab_width - 1;
		#
		# Find tables with leading pipe.
		#
		#	| Header 1 | Header 2
		#	| -------- | --------
		#	| Cell 1   | Cell 2
		#	| Cell 3   | Cell 4
		#
		$text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|]							# Optional leading pipe (present)
				(.+) \n						# $1: Header row (at least one pipe)
				
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|] ([ ]*[-:]+[-| :]*) \n	# $2: Header underline
				
				(							# $3: Cells
					(?>
						[ ]*				# Allowed whitespace.
						[|] .* \n			# Row content.
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array(&$this, '_doTable_leadingPipe_callback'), $text);
		
		#
		# Find tables without leading pipe.
		#
		#	Header 1 | Header 2
		#	-------- | --------
		#	Cell 1   | Cell 2
		#	Cell 3   | Cell 4
		#
		$text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				(\S.*[|].*) \n				# $1: Header row (at least one pipe)
				
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				([-:]+[ ]*[|][-| :]*) \n	# $2: Header underline
				
				(							# $3: Cells
					(?>
						.* [|] .* \n		# Row content
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array(&$this, '_DoTable_callback'), $text);

		return $text;
	}
	function _doTable_leadingPipe_callback($matches) {
		$head		= $matches[1];
		$underline	= $matches[2];
		$content	= $matches[3];
		
		# Remove leading pipe for each row.
		$content	= preg_replace('/^ *[|]/m', '', $content);
		
		return $this->_doTable_callback(array($matches[0], $head, $underline, $content));
	}
	function _doTable_callback($matches) {
		$head		= $matches[1];
		$underline	= $matches[2];
		$content	= $matches[3];

		# Remove any tailing pipes for each line.
		$head		= preg_replace('/[|] *$/m', '', $head);
		$underline	= preg_replace('/[|] *$/m', '', $underline);
		$content	= preg_replace('/[|] *$/m', '', $content);
		
		# Reading alignement from header underline.
		$separators	= preg_split('/ *[|] */', $underline);
		foreach ($separators as $n => $s) {
			if (preg_match('/^ *-+: *$/', $s))		$attr[$n] = ' align="right"';
			else if (preg_match('/^ *:-+: *$/', $s))$attr[$n] = ' align="center"';
			else if (preg_match('/^ *:-+ *$/', $s))	$attr[$n] = ' align="left"';
			else									$attr[$n] = '';
		}
		
		# Parsing span elements, including code spans, character escapes, 
		# and inline HTML tags, so that pipes inside those gets ignored.
		$head		= $this->parseSpan($head);
		$headers	= preg_split('/ *[|] */', $head);
		$col_count	= count($headers);
		
		# Write column headers.
		$text = "<table>\n";
		$text .= "<thead>\n";
		$text .= "<tr>\n";
		foreach ($headers as $n => $header)
			$text .= "  <th$attr[$n]>".$this->runSpanGamut(trim($header))."</th>\n";
		$text .= "</tr>\n";
		$text .= "</thead>\n";
		
		# Split content by row.
		$rows = explode("\n", trim($content, "\n"));
		
		$text .= "<tbody>\n";
		foreach ($rows as $row) {
			# Parsing span elements, including code spans, character escapes, 
			# and inline HTML tags, so that pipes inside those gets ignored.
			$row = $this->parseSpan($row);
			
			# Split row by cell.
			$row_cells = preg_split('/ *[|] */', $row, $col_count);
			$row_cells = array_pad($row_cells, $col_count, '');
			
			$text .= "<tr>\n";
			foreach ($row_cells as $n => $cell)
				$text .= "  <td$attr[$n]>".$this->runSpanGamut(trim($cell))."</td>\n";
			$text .= "</tr>\n";
		}
		$text .= "</tbody>\n";
		$text .= "</table>";
		
		return $this->hashBlock($text) . "\n";
	}

	
	function doDefLists($text) {
	#
	# Form HTML definition lists.
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable pattern to match any entire dl list:
		$whole_list = '(?>
			(								# $1 = whole list
			  (								# $2
				[ ]{0,'.$less_than_tab.'}
				((?>.*\S.*\n)+)				# $3 = defined term
				\n?
				[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
			  )
			  (?s:.+?)
			  (								# $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						# Negative lookahead for another term
					[ ]{0,'.$less_than_tab.'}
					(?: \S.*\n )+?			# defined term
					\n?
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
				  (?!						# Negative lookahead for another definition
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; // mx

		$text = preg_replace_callback('{
				(?:(?<=\n\n)|\A\n?)
				'.$whole_list.'
			}mx',
			array(&$this, '_doDefLists_callback'), $text);

		return $text;
	}
	function _doDefLists_callback($matches) {
		# Re-usable patterns to match list item bullets and number markers:
		$list = $matches[1];
		
		# Turn double returns into triple returns, so that we can make a
		# paragraph for the last item in a list, if necessary:
		$result = trim($this->processDefListItems($list));
		$result = "<dl>\n" . $result . "\n</dl>";
		return $this->hashBlock($result) . "\n\n";
	}


	function processDefListItems($list_str) {
	#
	#	Process the contents of a single definition list, splitting it
	#	into individual term and definition list items.
	#
		$less_than_tab = $this->tab_width - 1;
		
		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		# Process definition terms.
		$list_str = preg_replace_callback('{
			(?:\n\n+|\A\n?)					# leading line
			(								# definition terms = $1
				[ ]{0,'.$less_than_tab.'}	# leading whitespace
				(?![:][ ]|[ ])				# negative lookahead for a definition 
											#   mark (colon) or more whitespace.
				(?: \S.* \n)+?				# actual term (not whitespace).	
			)			
			(?=\n?[ ]{0,3}:[ ])				# lookahead for following line feed 
											#   with a definition mark.
			}xm',
			array(&$this, '_processDefListItems_callback_dt'), $list_str);

		# Process actual definitions.
		$list_str = preg_replace_callback('{
			\n(\n+)?						# leading line = $1
			[ ]{0,'.$less_than_tab.'}		# whitespace before colon
			[:][ ]+							# definition mark (colon)
			((?s:.+?))						# definition text = $2
			(?= \n+ 						# stop at next definition mark,
				(?:							# next term or end of text
					[ ]{0,'.$less_than_tab.'} [:][ ]	|
					<dt> | \z
				)						
			)					
			}xm',
			array(&$this, '_processDefListItems_callback_dd'), $list_str);

		return $list_str;
	}
	function _processDefListItems_callback_dt($matches) {
		$terms = explode("\n", trim($matches[1]));
		$text = '';
		foreach ($terms as $term) {
			$term = $this->runSpanGamut(trim($term));
			$text .= "\n<dt>" . $term . "</dt>";
		}
		return $text . "\n";
	}
	function _processDefListItems_callback_dd($matches) {
		$leading_line	= $matches[1];
		$def			= $matches[2];

		if ($leading_line || preg_match('/\n{2,}/', $def)) {
			$def = $this->runBlockGamut($this->outdent($def . "\n\n"));
			$def = "\n". $def ."\n";
		}
		else {
			$def = rtrim($def);
			$def = $this->runSpanGamut($this->outdent($def));
		}

		return "\n<dd>" . $def . "</dd>\n";
	}


	function doItalicsAndBold($text) {
	#
	# Redefined to change emphasis by underscore behaviour so that it does not 
	# work in the middle of a word.
	#
		# <strong> must go first:
		$text = preg_replace_callback(array(
			'{
				(						# $1: Marker
					(?<![a-zA-Z0-9])	# Not preceded by alphanum
					(?<!__)				#	or by two marker chars.
					__
				)
				(?=\S) 					# Not followed by whitespace 
				(?!__)					#   or two others marker chars.
				(						# $2: Content
					(?>
						[^_]+?			# Anthing not em markers.
					|
										# Balence any regular _ emphasis inside.
						(?<![a-zA-Z0-9]) _ (?=\S) (.+?) 
						(?<=\S) _ (?![a-zA-Z0-9])
					|
						_+				# Allow unbalenced as last resort.
					)+?
				)
				(?<=\S) __				# End mark not preceded by whitespace.
				(?![a-zA-Z0-9])			# Not followed by alphanum
				(?!__)					#   or two others marker chars.
			}sx',
			'{
				( (?<!\*\*) \*\* )		# $1: Marker (not preceded by two *)
				(?=\S) 					# Not followed by whitespace 
				(?!\1)					#   or two others marker chars.
				(						# $2: Content
					(?>
						[^*]+?			# Anthing not em markers.
					|
										# Balence any regular * emphasis inside.
						\* (?=\S) (.+?) (?<=\S) \*
					|
						\*				# Allow unbalenced as last resort.
					)+?
				)
				(?<=\S) \*\*			# End mark not preceded by whitespace.
			}sx',
			),
			array(&$this, '_doItalicAndBold_strong_callback'), $text);
		# Then <em>:
		$text = preg_replace_callback(array(
			'{ ( (?<![a-zA-Z0-9])(?<!_)_ ) (?=\S) (?! \1) (.+?) (?<=\S) \1(?![a-zA-Z0-9]) }sx',
			'{ ( (?<!\*)\* ) (?=\S) (?! \1) (.+?) (?<=\S)(?<!\s\*) \1 }sx',
			),
			array(&$this, '_doItalicAndBold_em_callback'), $text);

		return $text;
	}


	function formParagraphs($text) {
	#
	#	Params:
	#		$text - string to process with html <p> tags
	#
		# Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);
		
		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		#
		# Wrap <p> tags and unhashify HTML blocks
		#
		foreach ($grafs as $key => $value) {
			$value = trim($this->runSpanGamut($value));
			
			# Check if this should be enclosed in a paragraph.
			# Clean tag hashes & block tag hashes are left alone.
			$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);
			
			if ($is_p) {
				$value = "<p>$value</p>";
			}
			$grafs[$key] = $value;
		}
		
		# Join grafs in one text, then unhash HTML tags. 
		$text = implode("\n\n", $grafs);
		
		# Finish by removing any tag hashes still present in $text.
		$text = $this->unhash($text);
		
		return $text;
	}
	
	
	### Footnotes
	
	function stripFootnotes($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: [^id]: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[\^(.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					# maybe *one* newline
			(						# text = $2 (no blank lines allowed)
				(?:					
					.+				# actual text
				|
					\n				# newlines but 
					(?!\[\^.+?\]:\s)# negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed 
									# by non-indented content
				)*
			)		
			}xm',
			array(&$this, '_stripFootnotes_callback'),
			$text);
		return $text;
	}
	function _stripFootnotes_callback($matches) {
		$note_id = $this->fn_id_prefix . $matches[1];
		$this->footnotes[$note_id] = $this->outdent($matches[2]);
		return ''; # String that will replace the block
	}


	function doFootnotes($text) {
	#
	# Replace footnote references in $text [^id] with a special text-token 
	# which will be can be
	#
		if (!$this->in_footnote && !$this->in_anchor) {
			$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
		}
		return $text;
	}

	
	function appendFootnotes($text) {
	#
	# Append footnote list to text.
	#
	
		$text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
			array(&$this, '_appendFootnotes_callback'), $text);
	
		if (!empty($this->footnotes_ordered)) {
			$text .= "\n\n";
			$text .= "<div class=\"footnotes\">\n";
			$text .= "<hr". MARKDOWN_EMPTY_ELEMENT_SUFFIX ."\n";
			$text .= "<ol>\n\n";
			
			$attr = " rev=\"footnote\"";
			if ($this->fn_backlink_class != "") {
				$class = $this->fn_backlink_class;
				$class = $this->encodeAmpsAndAngles($class);
				$class = str_replace('"', '&quot;', $class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_backlink_title != "") {
				$title = $this->fn_backlink_title;
				$title = $this->encodeAmpsAndAngles($title);
				$title = str_replace('"', '&quot;', $title);
				$attr .= " title=\"$title\"";
			}
			$num = 0;
			
			$this->in_footnote = true;
			
			foreach ($this->footnotes_ordered as $note_id => $footnote) {
				$footnote .= "\n"; # Need to append newline before parsing.
				$footnote = $this->runBlockGamut("$footnote\n");
				
				$attr2 = str_replace("%%", ++$num, $attr);
				
				# Add backlink to last paragraph; create new paragraph if needed.
				$backlink = "<a href=\"#fnref:$note_id\"$attr2 >&#8617;</a>";
				if (preg_match('{</p>$}', $footnote)) {
					$footnote = substr($footnote, 0, -4) . "&#160;$backlink</p>";
				} else {
					$footnote .= "\n\n<p>$backlink</p>";
				}
				
				$text .= "<li id=\"fn:$note_id\">\n";
				$text .= $footnote . "\n";
				$text .= "</li>\n\n";
			}
			
			$this->in_footnote = false;
			
			$text .= "</ol>\n";
			$text .= "</div>";
		}
		return $text;
	}
	function _appendFootnotes_callback($matches) {
		$node_id = $this->fn_id_prefix . $matches[1];
		
		# Create footnote marker only if it has a corresponding footnote *and*
		# the footnote hasn't been used by another marker.
		if (isset($this->footnotes[$node_id])) {
			# Transfert footnote content to the ordered list.
			$this->footnotes_ordered[$node_id] = $this->footnotes[$node_id];
			unset($this->footnotes[$node_id]);
			
			$num = count($this->footnotes_ordered);
			$attr = " rel=\"footnote\"";
			if ($this->fn_link_class != "") {
				$class = $this->fn_link_class;
				$class = $this->encodeAmpsAndAngles($class);
				$class = str_replace('"', '&quot;', $class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_link_title != "") {
				$title = $this->fn_link_title;
				$title = $this->encodeAmpsAndAngles($title);
				$title = str_replace('"', '&quot;', $title);
				$attr .= " title=\"$title\"";
			}
			$attr = str_replace("%%", $num, $attr);
			
			return
				"<sup id=\"fnref:$node_id\">".
				"<a href=\"#fn:$node_id\"$attr >$num</a>".
				"</sup>";
		}
		
		return "[^".$matches[1]."]";
	}
		
	
	### Abbreviations ###
	
	function stripAbbreviations($text) {
	#
	# Strips abbreviations from text, stores titles in hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: [id]*: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\*\[(.+?)\][ ]?:	# abbr_id = $1
			(.*)					# text = $2 (no blank lines allowed)	
			}xm',
			array(&$this, '_stripAbbreviations_callback'),
			$text);
		return $text;
	}
	function _stripAbbreviations_callback($matches) {
		$abbr_word = $matches[1];
		$abbr_desc = $matches[2];
		$this->abbr_matches[] = preg_quote($abbr_word);
		$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		return ''; # String that will replace the block
	}
	
	
	function doAbbreviations($text) {
	#
	# Find defined abbreviations in text and wrap them in <abbr> elements.
	#
		if ($this->abbr_matches) {
			// cannot use the /x modifier because abbr_matches may 
			// contain spaces:
			$text = preg_replace_callback('{'.
				'(?<![\w\x1A])'.
				'(?:'. implode('|', $this->abbr_matches) .')'.
				'(?![\w\x1A])'.
				'}', 
				array(&$this, '_doAbbreviations_callback'), $text);
		}
		return $text;
	}
	function _doAbbreviations_callback($matches) {
		$abbr = $matches[0];
		if (isset($this->abbr_desciptions[$abbr])) {
			$desc = $this->abbr_desciptions[$abbr];
			if (empty($desc)) {
				return $this->hashPart("<abbr>$abbr</abbr>");
			} else {
				$desc = htmlspecialchars($desc, ENT_NOQUOTES);
				return $this->hashPart("<abbr title=\"$desc\">$abbr</abbr>");
			}
		} else {
			return $matches[0];
		}
	}

}


/*

PHP Markdown Extra
==================

Description
-----------

This is a PHP port of the original Markdown formatter written in Perl 
by John Gruber. This special "Extra" version of PHP Markdown features 
further enhancements to the syntax for making additional constructs 
such as tables and definition list.

Markdown is a text-to-HTML filter; it translates an easy-to-read /
easy-to-write structured text format into HTML. Markdown's text format
is most similar to that of plain text email, and supports features such
as headers, *emphasis*, code blocks, blockquotes, and links.

Markdown's syntax is designed not as a generic markup language, but
specifically to serve as a front-end to (X)HTML. You can use span-level
HTML tags anywhere in a Markdown document, and you can use block level
HTML tags (like <div> and <table> as well).

For more information about Markdown's syntax, see:

<http://daringfireball.net/projects/markdown/>


Bugs
----

To file bug reports please send email to:

<michel.fortin@michelf.com>

Please include with your report: (1) the example input; (2) the output you
expected; (3) the output Markdown actually produced.


Version History
--------------- 

See the readme file for detailed release notes for this version.


Copyright and License
---------------------

PHP Markdown & Extra
Copyright (c) 2004-2007 Michel Fortin  
<http://www.michelf.com/>  
All rights reserved.

Based on Markdown  
Copyright (c) 2003-2006 John Gruber   
<http://daringfireball.net/>   
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

*	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.

*	Redistributions in binary form must reproduce the above copyright
	notice, this list of conditions and the following disclaimer in the
	documentation and/or other materials provided with the distribution.

*	Neither the name "Markdown" nor the names of its contributors may
	be used to endorse or promote products derived from this software
	without specific prior written permission.

This software is provided by the copyright holders and contributors "as
is" and any express or implied warranties, including, but not limited
to, the implied warranties of merchantability and fitness for a
particular purpose are disclaimed. In no event shall the copyright owner
or contributors be liable for any direct, indirect, incidental, special,
exemplary, or consequential damages (including, but not limited to,
procurement of substitute goods or services; loss of use, data, or
profits; or business interruption) however caused and on any theory of
liability, whether in contract, strict liability, or tort (including
negligence or otherwise) arising in any way out of the use of this
software, even if advised of the possibility of such damage.

*/



#
# SmartyPants Typographer  -  Smart typography for web sites
#
# PHP SmartyPants & Typographer  
# Copyright (c) 2004-2006 Michel Fortin
# <http://www.michelf.com/>
#
# Original SmartyPants
# Copyright (c) 2003-2004 John Gruber
# <http://daringfireball.net/>
#


define( 'SMARTYPANTS_VERSION',            "1.5.1oo2" ); # Unreleased
define( 'SMARTYPANTSTYPOGRAPHER_VERSION', "1.0"      ); # Wed 28 Jun 2006


#
# Default configuration:
#
#  1  ->  "--" for em-dashes; no en-dash support  
#  2  ->  "---" for em-dashes; "--" for en-dashes  
#  3  ->  "--" for em-dashes; "---" for en-dashes  
#  See docs for more configuration options.
#
define( 'SMARTYPANTS_ATTR',    1 );

# Openning and closing smart double-quotes. 
# ---default &#8220; &#8221;
define( 'SMARTYPANTS_SMART_DOUBLEQUOTE_OPEN',  "&#8220;" );
define( 'SMARTYPANTS_SMART_DOUBLEQUOTE_CLOSE', "&#8221;" );


# Space around em-dashes.  "He_—_or she_—_should change that."
define( 'SMARTYPANTS_SPACE_EMDASH',      " " );

# Space around en-dashes.  "He_–_or she_–_should change that."
define( 'SMARTYPANTS_SPACE_ENDASH',      " " );

# Space before a colon. "He said_: here it is."
define( 'SMARTYPANTS_SPACE_COLON',       "&#160;" );

# Space before a semicolon. "That's what I said_; that's what he said."
define( 'SMARTYPANTS_SPACE_SEMICOLON',   "&#160;" );

# Space before a question mark and an exclamation mark: "¡_Holà_! What_?"
define( 'SMARTYPANTS_SPACE_MARKS',       "&#160;" );

# Space inside french quotes. "Voici la «_chose_» qui m'a attaqué."
define( 'SMARTYPANTS_SPACE_FRENCHQUOTE', "&#160;" );

# Space as thousand separator. "On compte 10_000 maisons sur cette liste."
define( 'SMARTYPANTS_SPACE_THOUSAND',    "&#160;" );

# Space before a unit abreviation. "This 12_kg of matter costs 10_$."
define( 'SMARTYPANTS_SPACE_UNIT',        "&#160;" );

# SmartyPants will not alter the content of these tags:
define( 'SMARTYPANTS_TAGS_TO_SKIP', 'pre|code|kbd|script|math');



### Standard Function Interface ###

define( 'SMARTYPANTS_PARSER_CLASS', 'SmartyPantsTypographer_Parser' );

function SmartyPants($text, $attr = SMARTYPANTS_ATTR) {
#
# Initialize the parser and return the result of its transform method.
#
	# Setup static parser array.
	static $parser = array();
	if (!isset($parser[$attr])) {
		$parser_class = SMARTYPANTS_PARSER_CLASS;
		$parser[$attr] = new $parser_class($attr);
	}

	# Transform text using parser.
	return $parser[$attr]->transform($text);
}

function SmartQuotes($text, $attr = 1) {
	switch ($attr) {
		case 0:  return $text;
		case 2:  $attr = 'qb'; break;
		default: $attr = 'q'; break;
	}
	return SmartyPants($text, $attr);
}

function SmartDashes($text, $attr = 1) {
	switch ($attr) {
		case 0:  return $text;
		case 2:  $attr = 'D'; break;
		case 3:  $attr = 'i'; break;
		default: $attr = 'd'; break;
	}
	return SmartyPants($text, $attr);
}

function SmartEllipsis($text, $attr = 1) {
	switch ($attr) {
		case 0:  return $text;
		default: $attr = 'e'; break;
	}
	return SmartyPants($text, $attr);
}


### WordPress Plugin Interface ###

/*
Plugin Name: SmartyPants Typographer
Plugin URI: http://www.michelf.com/projects/php-smartypants/
Description: SmartyPants is a web publishing utility that translates plain ASCII punctuation characters into &#8220;smart&#8221; typographic punctuation HTML entities. The Typographer extension will also replace normal spaces with unbrekable ones where appropriate to silently remove unwanted line breaks around punctuation and at some other places. This plugin <strong>replace the default WordPress Texturize algorithm</strong> for the content and the title of your posts, the comments body and author name, and everywhere else Texturize normally apply.
Version: 1.0
Author: Michel Fortin
Author URI: http://www.michelf.com/
*/

if (isset($wp_version)) {
	# Remove default Texturize filter that would conflict with SmartyPants.
	remove_filter('category_description', 'wptexturize');
	remove_filter('list_cats', 'wptexturize');
	remove_filter('comment_author', 'wptexturize');
	remove_filter('comment_text', 'wptexturize');
	remove_filter('single_post_title', 'wptexturize');
	remove_filter('the_title', 'wptexturize');
	remove_filter('the_content', 'wptexturize');
	remove_filter('the_excerpt', 'wptexturize');
	# Add SmartyPants filter with priority 10 (same as Texturize).
	add_filter('category_description', 'SmartyPants', 10);
	add_filter('list_cats', 'SmartyPants', 10);
	add_filter('comment_author', 'SmartyPants', 10);
	add_filter('comment_text', 'SmartyPants', 10);
	add_filter('single_post_title', 'SmartyPants', 10);
	add_filter('the_title', 'SmartyPants', 10);
	add_filter('the_content', 'SmartyPants', 10);
	add_filter('the_excerpt', 'SmartyPants', 10);
}


### Smarty Modifier Interface ###

function smarty_modifier_smartypants($text, $attr = NULL) {
	return SmartyPants($text, $attr);
}



#
# SmartyPants Parser Class
#

class SmartyPants_Parser {

	# Options to specify which transformations to make:
	var $do_nothing   = 0;
	var $do_quotes    = 0;
	var $do_backticks = 0;
	var $do_dashes    = 0;
	var $do_ellipses  = 0;
	var $do_stupefy   = 0;
	var $convert_quot = 0; # should we translate &quot; entities into normal quotes?

	function SmartyPants_Parser($attr = SMARTYPANTS_ATTR) {
	#
	# Initialize a SmartyPants_Parser with certain attributes.
	#
	# Parser attributes:
	# 0 : do nothing
	# 1 : set all
	# 2 : set all, using old school en- and em- dash shortcuts
	# 3 : set all, using inverted old school en and em- dash shortcuts
	# 
	# q : quotes
	# b : backtick quotes (``double'' only)
	# B : backtick quotes (``double'' and `single')
	# d : dashes
	# D : old school dashes
	# i : inverted old school dashes
	# e : ellipses
	# w : convert &quot; entities to " for Dreamweaver users
	#
		if ($attr == "0") {
			$this->do_nothing   = 1;
		}
		else if ($attr == "1") {
			# Do everything, turn all options on.
			$this->do_quotes    = 1;
			$this->do_backticks = 1;
			$this->do_dashes    = 1;
			$this->do_ellipses  = 1;
		}
		else if ($attr == "2") {
			# Do everything, turn all options on, use old school dash shorthand.
			$this->do_quotes    = 1;
			$this->do_backticks = 1;
			$this->do_dashes    = 2;
			$this->do_ellipses  = 1;
		}
		else if ($attr == "3") {
			# Do everything, turn all options on, use inverted old school dash shorthand.
			$this->do_quotes    = 1;
			$this->do_backticks = 1;
			$this->do_dashes    = 3;
			$this->do_ellipses  = 1;
		}
		else if ($attr == "-1") {
			# Special "stupefy" mode.
			$this->do_stupefy   = 1;
		}
		else {
			$chars = preg_split('//', $attr);
			foreach ($chars as $c){
				if      ($c == "q") { $this->do_quotes    = 1; }
				else if ($c == "b") { $this->do_backticks = 1; }
				else if ($c == "B") { $this->do_backticks = 2; }
				else if ($c == "d") { $this->do_dashes    = 1; }
				else if ($c == "D") { $this->do_dashes    = 2; }
				else if ($c == "i") { $this->do_dashes    = 3; }
				else if ($c == "e") { $this->do_ellipses  = 1; }
				else if ($c == "w") { $this->convert_quot = 1; }
				else {
					# Unknown attribute option, ignore.
				}
			}
		}
	}

	function transform($text) {

		if ($this->do_nothing) {
			return $text;
		}

		$tokens = $this->tokenizeHTML($text);
		$result = '';
		$in_pre = 0;  # Keep track of when we're inside <pre> or <code> tags.

		$prev_token_last_char = ""; # This is a cheat, used to get some context
									# for one-character tokens that consist of 
									# just a quote char. What we do is remember
									# the last character of the previous text
									# token, to use as context to curl single-
									# character quote tokens correctly.

		foreach ($tokens as $cur_token) {
			if ($cur_token[0] == "tag") {
				# Don't mess with quotes inside tags.
				$result .= $cur_token[1];
				if (preg_match('@<(/?)(?:'.SMARTYPANTS_TAGS_TO_SKIP.')[\s>]@', $cur_token[1], $matches)) {
					$in_pre = isset($matches[1]) && $matches[1] == '/' ? 0 : 1;
				}
			} else {
				$t = $cur_token[1];
				$last_char = substr($t, -1); # Remember last char of this token before processing.
				if (! $in_pre) {
					$t = $this->educate($t, $prev_token_last_char);
				}
				$prev_token_last_char = $last_char;
				$result .= $t;
			}
		}

		return $result;
	}


	function educate($t, $prev_token_last_char) {
		$t = $this->processEscapes($t);

		if ($this->convert_quot) {
			$t = preg_replace('/&quot;/', '"', $t);
		}

		if ($this->do_dashes) {
			if ($this->do_dashes == 1) $t = $this->educateDashes($t);
			if ($this->do_dashes == 2) $t = $this->educateDashesOldSchool($t);
			if ($this->do_dashes == 3) $t = $this->educateDashesOldSchoolInverted($t);
		}

		if ($this->do_ellipses) $t = $this->educateEllipses($t);

		# Note: backticks need to be processed before quotes.
		if ($this->do_backticks) {
			$t = $this->educateBackticks($t);
			if ($this->do_backticks == 2) $t = $this->educateSingleBackticks($t);
		}

		if ($this->do_quotes) {
			if ($t == "'") {
				# Special case: single-character ' token
				if (preg_match('/\S/', $prev_token_last_char)) {
					$t = "&#8217;";
				}
				else {
					$t = "&#8216;";
				}
			}
			else if ($t == '"') {
				# Special case: single-character " token
				if (preg_match('/\S/', $prev_token_last_char)) {
					$t = "&#8221;";
				}
				else {
					$t = "&#8220;";
				}
			}
			else {
				# Normal case:
				$t = $this->educateQuotes($t);
			}
		}

		if ($this->do_stupefy) $t = $this->stupefyEntities($t);

		return $t;
	}


	function educateQuotes($_) {
	#
	#   Parameter:  String.
	#
	#   Returns:    The string, with "educated" curly quote HTML entities.
	#
	#   Example input:  "Isn't this fun?"
	#   Example output: &#8220;Isn&#8217;t this fun?&#8221;
	#
		# Make our own "punctuation" character class, because the POSIX-style
		# [:PUNCT:] is only available in Perl 5.6 or later:
		$punct_class = "[!\"#\\$\\%'()*+,-.\\/:;<=>?\\@\\[\\\\\]\\^_`{|}~]";

		# Special case if the very first character is a quote
		# followed by punctuation at a non-word-break. Close the quotes by brute force:
		$_ = preg_replace(
			array("/^'(?=$punct_class\\B)/", "/^\"(?=$punct_class\\B)/"),
			array('&#8217;',                 '&#8221;'), $_);


		# Special case for double sets of quotes, e.g.:
		#   <p>He said, "'Quoted' words in a larger quote."</p>
		$_ = preg_replace(
			array("/\"'(?=\w)/",    "/'\"(?=\w)/"),
			array('&#8220;&#8216;', '&#8216;&#8220;'), $_);

		# Special case for decade abbreviations (the '80s):
		$_ = preg_replace("/'(?=\\d{2}s)/", '&#8217;', $_);

		$close_class = '[^\ \t\r\n\[\{\(\-]';
		$dec_dashes = '&\#8211;|&\#8212;';

		# Get most opening single quotes:
		$_ = preg_replace("{
			(
				\\s          |   # a whitespace char, or
				&nbsp;      |   # a non-breaking space entity, or
				--          |   # dashes, or
				&[mn]dash;  |   # named dash entities
				$dec_dashes |   # or decimal entities
				&\\#x201[34];    # or hex
			)
			'                   # the quote
			(?=\\w)              # followed by a word character
			}x", '\1&#8216;', $_);
		# Single closing quotes:
		$_ = preg_replace("{
			($close_class)?
			'
			(?(1)|          # If $1 captured, then do nothing;
			  (?=\\s | s\\b)  # otherwise, positive lookahead for a whitespace
			)               # char or an 's' at a word ending position. This
							# is a special case to handle something like:
							# \"<i>Custer</i>'s Last Stand.\"
			}xi", '\1&#8217;', $_);

		# Any remaining single quotes should be opening ones:
		$_ = str_replace("'", '&#8216;', $_);


		# Get most opening double quotes:
		$_ = preg_replace("{
			(
				\\s          |   # a whitespace char, or
				&nbsp;      |   # a non-breaking space entity, or
				--          |   # dashes, or
				&[mn]dash;  |   # named dash entities
				$dec_dashes |   # or decimal entities
				&\\#x201[34];    # or hex
			)
			\"                   # the quote
			(?=\\w)              # followed by a word character
			}x", '\1&#8220;', $_);

		# Double closing quotes:
		$_ = preg_replace("{
			($close_class)?
			\"
			(?(1)|(?=\\s))   # If $1 captured, then do nothing;
							   # if not, then make sure the next char is whitespace.
			}x", '\1&#8221;', $_);

		# Any remaining quotes should be opening ones.
		$_ = str_replace('"', '&#8220;', $_);

		return $_;
	}


	function educateBackticks($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with ``backticks'' -style double quotes
	#               translated into HTML curly quote entities.
	#
	#   Example input:  ``Isn't this fun?''
	#   Example output: &#8220;Isn't this fun?&#8221;
	#

		$_ = str_replace(array("``",       "''",),
						 array('&#8220;', '&#8221;'), $_);
		return $_;
	}


	function educateSingleBackticks($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with `backticks' -style single quotes
	#               translated into HTML curly quote entities.
	#
	#   Example input:  `Isn't this fun?'
	#   Example output: &#8216;Isn&#8217;t this fun?&#8217;
	#

		$_ = str_replace(array("`",       "'",),
						 array('&#8216;', '&#8217;'), $_);
		return $_;
	}


	function educateDashes($_) {
	#
	#   Parameter:  String.
	#
	#   Returns:    The string, with each instance of "--" translated to
	#               an em-dash HTML entity.
	#

		$_ = str_replace('--', '&#8212;', $_);
		return $_;
	}


	function educateDashesOldSchool($_) {
	#
	#   Parameter:  String.
	#
	#   Returns:    The string, with each instance of "--" translated to
	#               an en-dash HTML entity, and each "---" translated to
	#               an em-dash HTML entity.
	#

		#                      em         en
		$_ = str_replace(array("---",     "--",),
						 array('&#8212;', '&#8211;'), $_);
		return $_;
	}


	function educateDashesOldSchoolInverted($_) {
	#
	#   Parameter:  String.
	#
	#   Returns:    The string, with each instance of "--" translated to
	#               an em-dash HTML entity, and each "---" translated to
	#               an en-dash HTML entity. Two reasons why: First, unlike the
	#               en- and em-dash syntax supported by
	#               EducateDashesOldSchool(), it's compatible with existing
	#               entries written before SmartyPants 1.1, back when "--" was
	#               only used for em-dashes.  Second, em-dashes are more
	#               common than en-dashes, and so it sort of makes sense that
	#               the shortcut should be shorter to type. (Thanks to Aaron
	#               Swartz for the idea.)
	#

		#                      en         em
		$_ = str_replace(array("---",     "--",),
						 array('&#8211;', '&#8212;'), $_);
		return $_;
	}


	function educateEllipses($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with each instance of "..." translated to
	#               an ellipsis HTML entity. Also converts the case where
	#               there are spaces between the dots.
	#
	#   Example input:  Huh...?
	#   Example output: Huh&#8230;?
	#

		$_ = str_replace(array("...",     ". . .",), '&#8230;', $_);
		return $_;
	}


	function stupefyEntities($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with each SmartyPants HTML entity translated to
	#               its ASCII counterpart.
	#
	#   Example input:  &#8220;Hello &#8212; world.&#8221;
	#   Example output: "Hello -- world."
	#

							#  en-dash    em-dash
		$_ = str_replace(array('&#8211;', '&#8212;'),
						 array('-',       '--'), $_);

		# single quote         open       close
		$_ = str_replace(array('&#8216;', '&#8217;'), "'", $_);

		# double quote         open       close
		$_ = str_replace(array('&#8220;', '&#8221;'), '"', $_);

		$_ = str_replace('&#8230;', '...', $_); # ellipsis

		return $_;
	}


	function processEscapes($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with after processing the following backslash
	#               escape sequences. This is useful if you want to force a "dumb"
	#               quote or other character to appear.
	#
	#               Escape  Value
	#               ------  -----
	#               \\      &#92;
	#               \"      &#34;
	#               \'      &#39;
	#               \.      &#46;
	#               \-      &#45;
	#               \`      &#96;
	#
		$_ = str_replace(
			array('\\\\',  '\"',    "\'",    '\.',    '\-',    '\`'),
			array('&#92;', '&#34;', '&#39;', '&#46;', '&#45;', '&#96;'), $_);

		return $_;
	}


	function tokenizeHTML($str) {
	#
	#   Parameter:  String containing HTML markup.
	#   Returns:    An array of the tokens comprising the input
	#               string. Each token is either a tag (possibly with nested,
	#               tags contained therein, such as <a href="<MTFoo>">, or a
	#               run of text between tags. Each element of the array is a
	#               two-element array; the first is either 'tag' or 'text';
	#               the second is the actual value.
	#
	#
	#   Regular expression derived from the _tokenize() subroutine in 
	#   Brad Choate's MTRegex plugin.
	#   <http://www.bradchoate.com/past/mtregex.php>
	#
		$index = 0;
		$tokens = array();

		$match = '(?s:<!(?:--.*?--\s*)+>)|'.	# comment
				 '(?s:<\?.*?\?>)|'.				# processing instruction
												# regular tags
				 '(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'; 

		$parts = preg_split("{($match)}", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		foreach ($parts as $part) {
			if (++$index % 2 && $part != '') 
				$tokens[] = array('text', $part);
			else
				$tokens[] = array('tag', $part);
		}
		return $tokens;
	}

}















#
# SmartyPants Typographer Parser Class
#
class SmartyPantsTypographer_Parser extends SmartyPants_Parser {

	# Options to specify which transformations to make:
	var $do_comma_quotes      = 0;
	var $do_guillemets        = 0;
	var $do_space_emdash      = 0;
	var $do_space_endash      = 0;
	var $do_space_colon       = 0;
	var $do_space_semicolon   = 0;
	var $do_space_marks       = 0;
	var $do_space_frenchquote = 0;
	var $do_space_thousand    = 0;
	var $do_space_unit        = 0;

	# Smart quote characters:
	var $smart_doublequote_open  = SMARTYPANTS_SMART_DOUBLEQUOTE_OPEN;
	var $smart_doublequote_close = SMARTYPANTS_SMART_DOUBLEQUOTE_CLOSE;
	var $smart_singlequote_open  = '&#8216;';
	var $smart_singlequote_close = '&#8217;'; # Also apostrophe.

	# Space characters for different places:
	var $space_emdash      = SMARTYPANTS_SPACE_EMDASH;
	var $space_endash      = SMARTYPANTS_SPACE_ENDASH;
	var $space_colon       = SMARTYPANTS_SPACE_COLON;
	var $space_semicolon   = SMARTYPANTS_SPACE_SEMICOLON;
	var $space_marks       = SMARTYPANTS_SPACE_MARKS;
	var $space_frenchquote = SMARTYPANTS_SPACE_FRENCHQUOTE;
	var $space_thousand    = SMARTYPANTS_SPACE_THOUSAND;
	var $space_unit        = SMARTYPANTS_SPACE_UNIT;

	# Expression of a space (breakable or not):
	var $space = '(?: | |&nbsp;|&#0*160;|&#x0*[aA]0;)';



	function SmartyPantsTypographer_Parser($attr = SMARTYPANTS_ATTR) {
	#
	# Initialize a SmartyPantsTypographer_Parser with certain attributes.
	#
	# Parser attributes:
	# 0 : do nothing
	# 1 : set all, except dash spacing
	# 2 : set all, except dash spacing, using old school en- and em- dash shortcuts
	# 3 : set all, except dash spacing, using inverted old school en and em- dash shortcuts
	# 
	# Punctuation:
	# q -> quotes
	# b -> backtick quotes (``double'' only)
	# B -> backtick quotes (``double'' and `single')
	# c -> comma quotes (,,double`` only)
	# g -> guillemets (<<double>> only)
	# d -> dashes
	# D -> old school dashes
	# i -> inverted old school dashes
	# e -> ellipses
	# w -> convert &quot; entities to " for Dreamweaver users
	#
	# Spacing:
	# : -> colon spacing +-
	# ; -> semicolon spacing +-
	# m -> question and exclamation marks spacing +-
	# h -> em-dash spacing +-
	# H -> en-dash spacing +-
	# f -> french quote spacing +-
	# t -> thousand separator spacing -
	# u -> unit spacing +-
	#   (you can add a plus sign after some of these options denoted by + to 
	#    add the space when it is not already present, or you can add a minus 
	#    sign to completly remove any space present)
	#
		# Initialize inherited SmartyPants parser.
		parent::SmartyPants_Parser($attr);

		if ($attr == "1" || $attr == "2" || $attr == "3") {
			# Do everything, turn all options on.
			$this->do_comma_quotes      = 1;
			$this->do_guillemets  = 1;
			$this->do_space_emdash      = 1;
			$this->do_space_endash      = 1;
			$this->do_space_colon       = 1;
			$this->do_space_semicolon   = 1;
			$this->do_space_marks       = 1;
			$this->do_space_frenchquote = 1;
			$this->do_space_thousand    = 1;
			$this->do_space_unit        = 1;
		}
		else if ($attr == "-1") {
			# Special "stupefy" mode.
			$this->do_stupefy   = 1;
		}
		else {
			$chars = preg_split('//', $attr);
			foreach ($chars as $c){
				if      ($c == "c") { $current =& $this->do_comma_quotes; }
				else if ($c == "g") { $current =& $this->do_guillemets; }
				else if ($c == ":") { $current =& $this->do_space_colon; }
				else if ($c == ";") { $current =& $this->do_space_semicolon; }
				else if ($c == "m") { $current =& $this->do_space_marks; }
				else if ($c == "h") { $current =& $this->do_space_emdash; }
				else if ($c == "H") { $current =& $this->do_space_endash; }
				else if ($c == "f") { $current =& $this->do_space_frenchquote; }
				else if ($c == "t") { $current =& $this->do_space_thousand; }
				else if ($c == "u") { $current =& $this->do_space_unit; }
				else if ($c == "+") {
					$current = 2;
					unset($current);
				}
				else if ($c == "-") {
					$current = -1;
					unset($current);
				}
				else {
					# Unknown attribute option, ignore.
				}
				$current = 1;
			}
		}
	}


	function educate($t, $prev_token_last_char) {
		$t = parent::educate($t, $prev_token_last_char);

		if ($this->do_comma_quotes)      $t = $this->educateCommaQuotes($t);
		if ($this->do_guillemets)        $t = $this->educateGuillemets($t);

		if ($this->do_space_emdash)      $t = $this->spaceEmDash($t);
		if ($this->do_space_endash)      $t = $this->spaceEnDash($t);
		if ($this->do_space_colon)       $t = $this->spaceColon($t);
		if ($this->do_space_semicolon)   $t = $this->spaceSemicolon($t);
		if ($this->do_space_marks)       $t = $this->spaceMarks($t);
		if ($this->do_space_frenchquote) $t = $this->spaceFrenchQuotes($t);
		if ($this->do_space_thousand)    $t = $this->spaceThousandSeparator($t);
		if ($this->do_space_unit)        $t = $this->spaceUnit($t);

		return $t;
	}


	function educateQuotes($_) {
	#
	#   Parameter:  String.
	#
	#   Returns:    The string, with "educated" curly quote HTML entities.
	#
	#   Example input:  "Isn't this fun?"
	#   Example output: &#8220;Isn&#8217;t this fun?&#8221;
	#
		$dq_open  = $this->smart_doublequote_open;
		$dq_close = $this->smart_doublequote_close;
		$sq_open  = $this->smart_singlequote_open;
		$sq_close = $this->smart_singlequote_close;

		# Make our own "punctuation" character class, because the POSIX-style
		# [:PUNCT:] is only available in Perl 5.6 or later:
		$punct_class = "[!\"#\\$\\%'()*+,-.\\/:;<=>?\\@\\[\\\\\]\\^_`{|}~]";

		# Special case if the very first character is a quote
		# followed by punctuation at a non-word-break. Close the quotes by brute force:
		$_ = preg_replace(
			array("/^'(?=$punct_class\\B)/", "/^\"(?=$punct_class\\B)/"),
			array($sq_close,                 $dq_close), $_);

		# Special case for double sets of quotes, e.g.:
		#   <p>He said, "'Quoted' words in a larger quote."</p>
		$_ = preg_replace(
			array("/\"'(?=\w)/",     "/'\"(?=\w)/"),
			array($dq_open.$sq_open, $sq_open.$dq_open), $_);

		# Special case for decade abbreviations (the '80s):
		$_ = preg_replace("/'(?=\\d{2}s)/", $sq_close, $_);

		$close_class = '[^\ \t\r\n\[\{\(\-]';
		$dec_dashes = '&\#8211;|&\#8212;';

		# Get most opening single quotes:
		$_ = preg_replace("{
			(
				\\s          |   # a whitespace char, or
				&nbsp;      |   # a non-breaking space entity, or
				--          |   # dashes, or
				&[mn]dash;  |   # named dash entities
				$dec_dashes |   # or decimal entities
				&\\#x201[34];    # or hex
			)
			'                   # the quote
			(?=\\w)              # followed by a word character
			}x", '\1'.$sq_open, $_);
		# Single closing quotes:
		$_ = preg_replace("{
			($close_class)?
			'
			(?(1)|          # If $1 captured, then do nothing;
			  (?=\\s | s\\b)  # otherwise, positive lookahead for a whitespace
			)               # char or an 's' at a word ending position. This
							# is a special case to handle something like:
							# \"<i>Custer</i>'s Last Stand.\"
			}xi", '\1'.$sq_close, $_);

		# Any remaining single quotes should be opening ones:
		$_ = str_replace("'", $sq_open, $_);


		# Get most opening double quotes:
		$_ = preg_replace("{
			(
				\\s          |   # a whitespace char, or
				&nbsp;      |   # a non-breaking space entity, or
				--          |   # dashes, or
				&[mn]dash;  |   # named dash entities
				$dec_dashes |   # or decimal entities
				&\\#x201[34];    # or hex
			)
			\"                   # the quote
			(?=\\w)              # followed by a word character
			}x", '\1'.$dq_open, $_);

		# Double closing quotes:
		$_ = preg_replace("{
			($close_class)?
			\"
			(?(1)|(?=\\s))   # If $1 captured, then do nothing;
							   # if not, then make sure the next char is whitespace.
			}x", '\1'.$dq_close, $_);

		# Any remaining quotes should be opening ones.
		$_ = str_replace('"', $dq_open, $_);

		return $_;
	}


	function educateCommaQuotes($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with ,,comma,, -style double quotes
	#               translated into HTML curly quote entities.
	#
	#   Example input:  ,,Isn't this fun?,,
	#   Example output: &#8222;Isn't this fun?&#8222;
	#
	# Note: this is meant to be used alongside with backtick quotes; there is 
	# no language that use only lower quotations alone mark like in the example.
	#
		$_ = str_replace(",,", '&#8222;', $_);
		return $_;
	}


	function educateGuillemets($_) {
	#
	#   Parameter:  String.
	#   Returns:    The string, with << guillemets >> -style quotes
	#               translated into HTML guillemets entities.
	#
	#   Example input:  << Isn't this fun? >>
	#   Example output: &#8222; Isn't this fun? &#8222;
	#
		$_ = preg_replace("/(?:<|&lt;){2}/", '&#171;', $_);
		$_ = preg_replace("/(?:>|&gt;){2}/", '&#187;', $_);
		return $_;
	}


	function spaceFrenchQuotes($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				inside french-style quotes, only french quotes.
	#
	#	Example input:  Quotes in « French », »German« and »Finnish» style.
	#	Example output: Quotes in «_French_», »German« and »Finnish» style.
	#
		$opt = ( $this->do_space_frenchquote ==  2 ? '?' : '' );
		$chr = ( $this->do_space_frenchquote != -1 ? $this->space_frenchquote : '' );

		# Characters allowed immediatly outside quotes.
		$outside_char = $this->space . '|\s|[.,:;!?\[\](){}|@*~=+-]|¡|¿';

		$_ = preg_replace(
			"/(^|$outside_char)(&#171;|«|&#8250;|‹)$this->space$opt/",
			"\\1\\2$chr", $_);
		$_ = preg_replace(
			"/$this->space$opt(&#187;|»|&#8249;|›)($outside_char|$)/", 
			"$chr\\1\\2", $_);
		return $_;
	}


	function spaceColon($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				before colons.
	#
	#	Example input:  Ingredients : fun.
	#	Example output: Ingredients_: fun.
	#
		$opt = ( $this->do_space_colon ==  2 ? '?' : '' );
		$chr = ( $this->do_space_colon != -1 ? $this->space_colon : '' );

		$_ = preg_replace("/$this->space$opt(:)(\\s|$)/m",
						  "$chr\\1\\2", $_);
		return $_;
	}


	function spaceSemicolon($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				before semicolons.
	#
	#	Example input:  There he goes ; there she goes.
	#	Example output: There he goes_; there she goes.
	#
		$opt = ( $this->do_space_semicolon ==  2 ? '?' : '' );
		$chr = ( $this->do_space_semicolon != -1 ? $this->space_semicolon : '' );

		$_ = preg_replace("/$this->space(;)(?=\\s|$)/m", 
						  " \\1", $_);
		$_ = preg_replace("/((?:^|\\s)(?>[^&;\\s]+|&#?[a-zA-Z0-9]+;)*)".
						  " $opt(;)(?=\\s|$)/m", 
						  "\\1$chr\\2", $_);
		return $_;
	}


	function spaceMarks($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				around question and exclamation marks.
	#
	#	Example input:  ¡ Holà ! What ?
	#	Example output: ¡_Holà_! What_?
	#
		$opt = ( $this->do_space_marks ==  2 ? '?' : '' );
		$chr = ( $this->do_space_marks != -1 ? $this->space_marks : '' );

		// Regular marks.
		$_ = preg_replace("/$this->space$opt([?!]+)/", "$chr\\1", $_);

		// Inverted marks.
		$imarks = "(?:¡|&iexcl;|&#161;|&#x[Aa]1;|¿|&iquest;|&#191;|&#x[Bb][Ff];)";
		$_ = preg_replace("/($imarks+)$this->space$opt/", "\\1$chr", $_);

		return $_;
	}


	function spaceEmDash($_) {
	#
	#	Parameters: String, two replacement characters separated by a hyphen (`-`),
	#				and forcing flag.
	#
	#	Returns:    The string, with appropriates spaces replaced 
	#				around dashes.
	#
	#	Example input:  Then — without any plan — the fun happend.
	#	Example output: Then_—_without any plan_—_the fun happend.
	#
		$opt = ( $this->do_space_emdash ==  2 ? '?' : '' );
		$chr = ( $this->do_space_emdash != -1 ? $this->space_emdash : '' );
		$_ = preg_replace("/$this->space$opt(&#8212;|—)$this->space$opt/", 
			"$chr\\1$chr", $_);
		return $_;
	}


	function spaceEnDash($_) {
	#
	#	Parameters: String, two replacement characters separated by a hyphen (`-`),
	#				and forcing flag.
	#
	#	Returns:    The string, with appropriates spaces replaced 
	#				around dashes.
	#
	#	Example input:  Then — without any plan — the fun happend.
	#	Example output: Then_—_without any plan_—_the fun happend.
	#
		$opt = ( $this->do_space_endash ==  2 ? '?' : '' );
		$chr = ( $this->do_space_endash != -1 ? $this->space_endash : '' );
		$_ = preg_replace("/$this->space$opt(&#8211;|–)$this->space$opt/", 
			"$chr\\1$chr", $_);
		return $_;
	}


	function spaceThousandSeparator($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced 
	#				inside numbers (thousand separator in french).
	#
	#	Example input:  Il y a 10 000 insectes amusants dans ton jardin.
	#	Example output: Il y a 10_000 insectes amusants dans ton jardin.
	#
		$chr = ( $this->do_space_thousand != -1 ? $this->space_thousand : '' );
		$_ = preg_replace('/([0-9]) ([0-9])/', "\\1$chr\\2", $_);
		return $_;
	}


	var $units = '
		### Metric units (with prefixes)
		(?:
			p |
			µ | &micro; | &\#0*181; | &\#[xX]0*[Bb]5; |
			[mcdhkMGT]
		)?
		(?:
			[mgstAKNJWCVFSTHBL]|mol|cd|rad|Hz|Pa|Wb|lm|lx|Bq|Gy|Sv|kat|
			Ω | Ohm | &Omega; | &\#0*937; | &\#[xX]0*3[Aa]9;
		)|
		### Computers units (KB, Kb, TB, Kbps)
		[kKMGT]?(?:[oBb]|[oBb]ps|flops)|
		### Money
		¢ | &cent; | &\#0*162; | &\#[xX]0*[Aa]2; |
		M?(?:
			£ | &pound; | &\#0*163; | &\#[xX]0*[Aa]3; |
			¥ | &yen;   | &\#0*165; | &\#[xX]0*[Aa]5; |
			€ | &euro;  | &\#0*8364; | &\#[xX]0*20[Aa][Cc]; |
			$
		)|
		### Other units
		(?: ° | &deg; | &\#0*176; | &\#[xX]0*[Bb]0; ) [CF]? | 
		%|pt|pi|M?px|em|en|gal|lb|[NSEOW]|[NS][EOW]|ha|mbar
		'; //x

	function spaceUnit($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced
	#				before unit symbols.
	#
	#	Example input:  Get 3 mol of fun for 3 $.
	#	Example output: Get 3_mol of fun for 3_$.
	#
		$opt = ( $this->do_space_unit ==  2 ? '?' : '' );
		$chr = ( $this->do_space_unit != -1 ? $this->space_unit : '' );

		$_ = preg_replace('/
			(?:([0-9])[ ]'.$opt.') # Number followed by space.
			('.$this->units.')     # Unit.
			(?![a-zA-Z0-9])  # Negative lookahead for other unit characters.
			/x',
			"\\1$chr\\2", $_);

		return $_;
	}


	function spaceAbbr($_) {
	#
	#	Parameters: String, replacement character, and forcing flag.
	#	Returns:    The string, with appropriates spaces replaced
	#				around abbreviations.
	#
	#	Example input:  Fun i.e. something pleasant.
	#	Example output: Fun i.e._something pleasant.
	#
		$opt = ( $this->do_space_abbr ==  2 ? '?' : '' );

		$_ = preg_replace("/(^|\s)($this->abbr_after) $opt/m",
			"\\1\\2$this->space_abbr", $_);
		$_ = preg_replace("/( )$opt($this->abbr_sp_before)(?![a-zA-Z'])/m", 
			"\\1$this->space_abbr\\2", $_);
		return $_;
	}


	function stupefyEntities($_) {
	#
	#   Adding angle quotes and lower quotes to SmartyPants's stupefy mode.
	#
		$_ = parent::stupefyEntities($_);

		$_ = str_replace(array('&#8222;', '&#171;', '&#187'), '"', $_);

		return $_;
	}


	function processEscapes($_) {
	#
	#   Adding a few more escapes to SmartyPants's escapes:
	#
	#               Escape  Value
	#               ------  -----
	#               \,      &#44;
	#               \<      &#60;
	#               \>      &#62;
	#
		$_ = parent::processEscapes($_);

		$_ = str_replace(
			array('\,',    '\<',    '\>',    '\&lt;', '\&gt;'),
			array('&#44;', '&#60;', '&#62;', '&#60;', '&#62;'), $_);

		return $_;
	}
}


/*

PHP SmartyPants Typographer
===========================

Version History
---------------

1.0 (28 Jun 2006)

*   First public release of PHP SmartyPants Typographer.


Bugs
----

To file bug reports or feature requests (other than topics listed in the
Caveats section above) please send email to:

<michel.fortin@michelf.com>

If the bug involves quotes being curled the wrong way, please send example
text to illustrate.


### Algorithmic Shortcomings ###

One situation in which quotes will get curled the wrong way is when
apostrophes are used at the start of leading contractions. For example:

	'Twas the night before Christmas.

In the case above, SmartyPants will turn the apostrophe into an opening
single-quote, when in fact it should be a closing one. I don't think
this problem can be solved in the general case -- every word processor
I've tried gets this wrong as well. In such cases, it's best to use the
proper HTML entity for closing single-quotes (`&#8217;`) by hand.


Copyright and License
---------------------

PHP SmartyPants & Typographer  
Copyright (c) 2004-2006 Michel Fortin  
<http://www.michelf.com>  
All rights reserved.

Original SmartyPants  
Copyright (c) 2003-2004 John Gruber  
<http://daringfireball.net/>  
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

*	Redistributions of source code must retain the above copyright
	notice, this list of conditions and the following disclaimer.

*	Redistributions in binary form must reproduce the above copyright
	notice, this list of conditions and the following disclaimer in the
	documentation and/or other materials provided with the distribution.

*	Neither the name "SmartyPants" nor the names of its contributors may
	be used to endorse or promote products derived from this software
	without specific prior written permission.

This software is provided by the copyright holders and contributors "as is"
and any express or implied warranties, including, but not limited to, the 
implied warranties of merchantability and fitness for a particular purpose 
are disclaimed. In no event shall the copyright owner or contributors be 
liable for any direct, indirect, incidental, special, exemplary, or 
consequential damages (including, but not limited to, procurement of 
substitute goods or services; loss of use, data, or profits; or business 
interruption) however caused and on any theory of liability, whether in 
contract, strict liability, or tort (including negligence or otherwise) 
arising in any way out of the use of this software, even if advised of the
possibility of such damage.

*/
?>