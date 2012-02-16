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
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_animation extends DokuWiki_Action_Plugin {

    /*
    * return some info
    */
    function getInfo(){
        return array(
            'author' => 'Yihui Xie',
            'email'  => 'xie@yihui.name',
            'date'   => '2011-01-31',
            'name'   => 'Animation Plugin',
            'desc'   => 'Generate an animation from a sequence of images, e.g. 1.png, 2.png, ...',
            'url'    => 'http://animation.yihui.name/wiki:animation_plugin',
                     );
    }

    /*
    * plugin should use this method to register its handlers with the dokuwiki's event controller
    */
    function register(&$controller) {
        $controller->register_hook('TPL_METAHEADER_OUTPUT',
                                   'BEFORE',
                                   $this,
                                   '_hooksh'
                                   );

        $controller->register_hook('TPL_ACT_RENDER',
                                   'AFTER',
                                   $this,
                                   '_hookjsprocessing'
                                   );
    }

    /*
    *  Inject the SyntaxHightlighter files
    *
    *  @author David Shin <dshin@pimpsmart.com>
    *  @param $event object target event
    *  @param $param mixed event parameters passed from register_hook
    *
    *  To add other brushes, add file name(s) to the $brushes array.
    */
    function _hooksh (&$event, $param) {

        // Add stylesheets
        $anistyles = array('shCore', 'shThemeDefault', 'scianimator');
        foreach ($anistyles as $anistyle) {
            $event->data['link'][] = array( 'rel'   => 'stylesheet',
                                            'type'  => 'text/css',
                                            'href'  => DOKU_BASE.'lib/plugins/animation/styles/' . $anistyle . '.css',
                                            );
        }

        // Add JS
        $brushes = array("shCore.js","shAutoloader.js","shBrushR.js", "jquery.scianimator.min.js");

        // Register all brushes.
        foreach ($brushes as $brush) {
            $event->data["script"][] = array ("type"   => "text/javascript",
                                               "src"   => DOKU_BASE."lib/plugins/animation/scripts/".$brush,
                                               "_data" => ""
                                               );
        }
        
        $morecode = array();
        $addjs = '';
        trigger_event('JQUERY_READY', $morecode, NULL, false);
        foreach ($morecode as $id=>$mc) {
            $addjs .= '// BEGIN --- ' . $id . PHP_EOL;
            $addjs .= $mc . PHP_EOL;
            $addjs .= '// END --- ' . $id . PHP_EOL;
        }

        $fulljs = 'jQuery.noConflict();' . PHP_EOL;
        if (!empty($addjs)) {
            $fulljs .= 'jQuery(document).ready(function() {' . PHP_EOL;
            $fulljs .= $addjs . PHP_EOL;
            $fulljs .= '});' . PHP_EOL;
        }
        $event->data['script'][] = array(
            'type' => 'text/javascript',
            'charset' => 'utf-8',
            '_data' => $fulljs,
        );

    }

    /*
    *  Inject the SyntaxHightlighter javascript processing
    *
    *  @author Dominik
    *  @param $event object target event
    *  @param $param mixed event parameters passed from register_hook
    *
    */
    function _hookjsprocessing (&$event, $param) {
        global $ID;
        global $INFO;

        //this ensures that code will be written only on base page
        //not on other inlined wiki pages (e.g. when using monobook template)
        if ($ID != $INFO["id"]) return;

        ptln("");
        ptln("<script type=\"text/javascript\">");
        ptln("  SyntaxHighlighter.defaults[\"toolbar\"] = false;");
        ptln("  SyntaxHighlighter.all();");
        ptln("</script>");

    }
}
