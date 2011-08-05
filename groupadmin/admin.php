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

	/**
	 * return some info
	 */
	function getInfo(){

		return array(
            'author' => 'James Phillpotts',
            'email'  => 'james.dokuwiki@potes.org.uk',
            'date'   => '2011-08-04',
            'name'   => 'Group Admin',
            'desc'   => 'Allows the management of users-groups from a groups focus '.$this->disabled,
            'url'    => 'http://dokuwiki.org/plugin:groupadmin',
		);
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

		if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

		$this->output = 'invalid';
		if (!checkSecurityToken()) return;
		if (!is_array($_REQUEST['cmd'])) return;

		if (is_null($this->_auth)) return false;

		if (is_array($fn)) {
			$cmd = key($fn);
			$param = is_array($fn[$cmd]) ? key($fn[$cmd]) : null;
		} else {
			$cmd = $fn;
			$param = null;
		}

		switch (key($_REQUEST['cmd'])) {
			case 'save' : $this->_saveGroup($param); break;
			case 'load' : $this->_group_name = $_REQUEST['groupname']; break;
		}
	}

	public function html() {
		$group_list = $this->_auth->retrieveGroups();

		ptln('<p>'.htmlspecialchars($this->getLang('description')).'</p>');

		ptln('<form action="'.wl($ID).'" method="post">');

		// output hidden values to ensure dokuwiki will return back to this plugin
		ptln('  <input type="hidden" name="do"   value="admin" />');
		ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
		formSecurityToken();

		ptln('  <label for="groupname">'.$this->getLang('selectgroup').'</label>');
		ptln('  <select id="groupname" name="groupname">');
		ptln('    <option value="">'.$this->getLang('blankgroup').'</option>');
		foreach ($group_list as $group) {
			if ($_group_name == $group) {
				ptln('    <option value="'.$group.'" selected="selected">'.$group.'</option>');
			} else {
				ptln('    <option value="'.$group.'">'.$group.'</option>');
			}
		}
		ptln('  </select>');
		ptln('  <div>');
		if ($this->_edit_group) {
			$filter = array();
			$filter['grps'] = $this->_edit_group;
			$users_in_group = $this->_auth->retrieveUsers(0, -1, $filter);
			$all_users = $this->_auth->retrieveUsers();

			ptln('    <select id="allusers" multiple="multiple" size="20">');
			foreach ($all_users as $user => $userinfo) {
				extract($userinfo);
				if (!in_array($userinfo, $users_in_group)) {
					ptln('    <option value="'.$user.'">'.$user.'</option>');
				}
			}
			ptln('    </select>');
			
			ptln('    <button type="button" onclick="add()">'.$this-getLang('btn_add').'</button>');
			ptln('    <button type="button" onclick="addall()">'.$this-getLang('btn_addall').'</button>');
			ptln('    <button type="button" onclick="remove()">'.$this-getLang('btn_remove').'</button>');
			ptln('    <button type="button" onclick="removeall()">'.$this-getLang('btn_removeall').'</button>');
			
			ptln('    <select id="groupusers" name="users[]" multiple="multiple" size="20">');
			foreach ($users_in_group as $user => $userinfo) {
				extract($userinfo);
				if (!in_array($userinfo, $users_in_group)) {
					ptln('    <option value="'.$user.'">'.$user.'</option>');
				}
			}
			ptln('    </select>');
			
			ptln('  <input type="submit" name="cmd[save]"  value="'.$this->getLang('btn_save').'" />');
		}
		ptln('  <div>');
		ptln('</form>');
	}

	function _saveGroup() {
		$group_filter = array();
		$group_filter['grps'] = $groupName;
		$oldusers = $this->_auth->retrieveUsers(0, -1, $group_filter);

		$removed_users = array();
		foreach ($oldusers as $olduser => $olduserinfo) {
			if (!in_array($olduser, $usernames))
			array_push($removed_users, $olduserinfo);
		}
		msg($this->lang['update_ok'],1);
	}
}
