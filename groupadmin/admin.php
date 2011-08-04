<?php
/**
 * DokuWiki Plugin groupadmin (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  James Phillpotts <james.dokuwiki@potes.org.uk>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'admin.php';

class admin_plugin_groupadmin extends DokuWiki_Admin_Plugin {

	var $_auth = null;
	var $_group_name = '';

	/**
	 * Constructor
	 */
	function admin_plugin_usermanager(){
		global $auth;

		$this->setupLocale();

		if (!isset($auth)) {
			$this->disabled = $this->lang['noauth'];
		} else if (!$auth->canDo('getGroups')) {
			$this->disabled = $this->lang['nosupport'];
		} else {

			// we're good to go
			$this->_auth = & $auth;

		}
	}


	public function getMenuSort() { return 3; }

	/**
	 * return prompt for admin menu
	 */
	function getMenuText($language) {
		if (!is_null($this->_auth))
		return parent::getMenuText($language);

		return $this->getLang('title').' '.$this->disabled;
	}

	public function forAdminOnly() { return false; }

	public function handle() {
		global $ID;

		if (is_null($this->_auth)) return false;

		if (is_array($fn)) {
			$cmd = key($fn);
			$param = is_array($fn[$cmd]) ? key($fn[$cmd]) : null;
		} else {
			$cmd = $fn;
			$param = null;
		}
		
		switch($cmd){
			case 'save' : $this->_saveGroups($param); break;
			case 'load' : $this->_group_name = $_REQUEST['groupname']; break;
		}
	}

	public function html() {
		ptln('<h1>' . $this->getLang('title') . '</h1>');

	}
}
