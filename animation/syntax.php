<?php
/**
 * Plugin animation: combine a sequence of images to create an animation"
 *
 * Syntax: 
 <ani id url type max interval autoplay | opts>
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Yihui Xie <xie@yihui.name>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_animation extends DokuWiki_Syntax_Plugin {
    function getInfo() {
      return array(
		   'author' => 'Yihui Xie',
		   'email'  => 'xie@yihui.name',
		   'date'   => '2012-02-16',
		   'name'   => 'Animation Plugin',
		   'desc'   => 'Generate an animation from a sequence of images, e.g. 1.png, 2.png, ...',
		   'url'    => 'https://github.com/yihui/dokuwiki',
		   );
    }
    function getType() { return 'substition';}
    function getSort() {
      return 122;
    }
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('<ani.*?>',$mode,'plugin_animation');
    }
    function handle($match, $state, $pos, &$handler) {
      $source = trim(substr($match, 4, -1));
      list($para,$opts) = preg_split('/\|/u',$source,2);
      if (strpos($opts, "': ") === false) $opts = '';
      // 1st version:
      // list($id, $url, $type, $max, $height, $interval) = preg_split('/\s+/u', trim($para), 8);
      // 2nd version:
      // list($id, $url, $type, $max, $width, $height, $interval, $autoplay) = preg_split('/\s+/u', trim($para), 8);
      list($id, $url, $type, $max, $interval, $autoplay, $navigator, $width) = preg_split('/\s+/u', trim($para), 8);
      if (floatval($interval) > 100 & floatval($autoplay) > 0) {
	if (floatval($autoplay) < 10) {
	  // you are using the 1st version
	  $interval = $autoplay;
	  $autoplay = '';
	} else {
	  // you are using the 2nd version
	  $interval = $navigator;
	  $autoplay = $width;
	}
      }
      return array($state, array($id, $url, $type, $max, $interval, $autoplay, $navigator, $width, $opts));
    }
    function render($mode, &$renderer, $data) {
      if($mode == 'xhtml'){
	list($state, $match) = $data;
	list($id, $url, $type, $max, $interval, $autoplay, $navigator, $width, $opts) = $match;
	$id = 'animation_' . str_replace(array("!", '"', "#", "$", "%", "&", "'", "(", ")", "*", "+", ",", ".", "/", ":", ";", "?", "@", "[", "]", "^", "`", "{", "|", "}", "~"), "_", $id);
	if ($autoplay == 'autoplay') {
	  $autoplay = "$('#$id').scianimator('play');";
	} else {
	  $autoplay = '';
	}
	if ($navigator != 'true') $opts = "'controls': ['first', 'previous', 'play', 'next', 'last', 'loop', 'speed'], " . $opts;
	if (floatval($width) > 0) $opts = "'width': $width, " . $opts;
	// if you have your format, just use yours, otherwise use the default %d
	if (preg_match("/%[0-9]+d$/", $url) == 0) $url .= "%d";
	$imglist = '';
	for ($imgnum = 1; $imgnum <= intval($max); $imgnum++) {
		$imglist .= "'" . sprintf($url, $imgnum) . '.' . $type . "', ";
	}
	$renderer->doc .=

	  "   <div class=\"scianimator\"><div id=\"$id\" style=\"display: inline-block;\"></div></div>
    <script type=\"text/javascript\">
    (function($) {
        $(document).ready(function() {

        $('#$id').scianimator({
            'images': [$imglist],
            'delay': ". floatval($interval) * 1000 .",
            $opts
        });
        ".
	  $autoplay
	  . "
        });
    })(jQuery);
    </script>
" .
	  '';
	return true;
      }
      return false;
    }
}
