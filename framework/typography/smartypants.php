<?php

class SmartyPants {
	const SMARTYPANTS_ATTR = 1;

	# Openning and closing smart double-quotes. 
	# ---default &#8220; &#8221;
	const SMARTYPANTS_SMART_DOUBLEQUOTE_OPEN = "&#8220;";
	const SMARTYPANTS_SMART_DOUBLEQUOTE_CLOSE = "&#8221;";

	# Space around em-dashes.  "He_—_or she_—_should change that."
	const SMARTYPANTS_SPACE_EMDASH = " ";

	# Space around en-dashes.  "He_–_or she_–_should change that."
	const SMARTYPANTS_SPACE_ENDASH = " ";

	# Space before a colon. "He said_: here it is."
	const SMARTYPANTS_SPACE_COLON = "&#160;";

	# Space before a semicolon. "That's what I said_; that's what he said."
	const SMARTYPANTS_SPACE_SEMICOLON = "&#160;";

	# Space before a question mark and an exclamation mark: "¡_Holà_! What_?"
	const SMARTYPANTS_SPACE_MARKS = "&#160;";

	# Space inside french quotes. "Voici la «_chose_» qui m'a attaqué."
	const SMARTYPANTS_SPACE_FRENCHQUOTE = "&#160;";

	# Space as thousand separator. "On compte 10_000 maisons sur cette liste."
	const SMARTYPANTS_SPACE_THOUSAND = "&#160;";

	# Space before a unit abreviation. "This 12_kg of matter costs 10_$."
	const SMARTYPANTS_SPACE_UNIT = "&#160;";

	# SmartyPants will not alter the content of these tags:
	const SMARTYPANTS_TAGS_TO_SKIP = 'pre|code|kbd|script|math';

	# Options to specify which transformations to make:
	public $do_nothing   = 0;
	public $do_quotes    = 0;
	public $do_backticks = 0;
	public $do_dashes    = 0;
	public $do_ellipses  = 0;
	public $do_stupefy   = 0;
	public $convert_quot = 0; # should we translate &quot; entities into normal quotes?

	public function __construct($attr = self::SMARTYPANTS_ATTR) 
	{
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
		$text = preg_replace('/&quot;/', '"', $text);
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

?>