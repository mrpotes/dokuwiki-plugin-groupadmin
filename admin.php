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
    function admin_plugin_groupadmin(){
        global $auth;

        $this->setupLocale();

        if (!isset($auth)) {
            $this->disabled = $this->lang['noauth'];
        } else if (!$auth->canDo('getUsers')) {
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
        $info = parent::getInfo();
        $info['desc'] = $info['desc'].' '.$this->disabled;
        return $info;
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

    public function forAdminOnly() { return true; }

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
            case 'save' : 
                $this->_group_name = $_REQUEST['groupname']; 
                $this->_saveGroup($param);
                $this->_group_name = null; 
                break;
            case 'load' : 
                $this->_group_name = $_REQUEST['groupname']; 
                break;
        }
    }

    public function html() {
        $all_users = $this->_auth->retrieveUsers();
        if ($this->_auth->canDo("getGroups")) {
            $group_list = $this->_auth->retrieveGroups();
        } else {
            $group_list = array();
            foreach ($all_users as $user => $userinfo) {
                extract($userinfo);
                $group_list = array_merge($group_list, $grps);                
            }
            $group_list = array_unique($group_list);
        }

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
            if ($this->_group_name == $group) {
                ptln('    <option value="'.$group.'" selected="selected">'.$group.'</option>');
            } else {
                ptln('    <option value="'.$group.'">'.$group.'</option>');
            }
        }

        ptln('  </select>');
        ptln('  <input type="submit" name="cmd[load]"  value="'.$this->getLang('btn_load').'" /><br/><br/>');
        if ($this->_group_name) {
            ptln('  <div>');
            $filter = array();
            $filter['grps'] = $this->_group_name;
            $users_in_group = $this->_auth->retrieveUsers(0, -1, $filter);

            ptln('<table><tr><td>');
            ptln('    <select id="allusers" class="groupadminselection" multiple="multiple" size="20" ondblclick="add()">');
            foreach ($all_users as $user => $userinfo) {
                extract($userinfo);
                if (!in_array($userinfo, $users_in_group)) {
                    ptln('    <option value="'.$user.'">'.$userinfo['name'].' ('.$user.')</option>');
                }
            }
            ptln('    </select>');
            ptln('</td><td>');
            ptln('    <button type="button" class="groupadminselection" onclick="add()">'.$this->getLang('btn_add').'</button><br/>');
            ptln('    <button type="button" class="groupadminselection" onclick="addall()">'.$this->getLang('btn_addall').'</button><br/>');
            ptln('    <button type="button" class="groupadminselection" onclick="remove()">'.$this->getLang('btn_remove').'</button><br/>');
            ptln('    <button type="button" class="groupadminselection" onclick="removeall()">'.$this->getLang('btn_removeall').'</button>');
            ptln('</td><td>');
            ptln('    <select id="groupusers" class="groupadminselection" multiple="multiple" size="20" ondoubleclick="remove()">');
            foreach ($users_in_group as $user => $userinfo) {
                ptln('    <option value="'.$user.'">'.$userinfo['name'].' ('.$user.')</option>');
            }
            ptln('    </select>');
            ptln('</td></tr></table>');
            
            foreach ($all_users as $user => $userinfo) {
                extract($userinfo);
                if (in_array($userinfo, $users_in_group)) {
                    ptln('  <input type="hidden" name="users[]" id="users.'.$user.'" value="'.$user.'"/>');
                } else {
                    ptln('  <input type="hidden" id="users.'.$user.'" value="'.$user.'"/>');
                }
            }
            
            ptln('  <noscript>');
            ptln('  <style>table { display:none; }</style>');
            foreach ($all_users as $user => $userinfo) {
                extract($userinfo);
                if (in_array($userinfo, $users_in_group)) {
                    ptln('  <input type="checkbox" name="noscriptusers[]" id="noscript.'.$user.'" value="'.$user.'" checked="checked"/>');
                } else {
                    ptln('  <input type="checkbox" name="noscriptusers[]" id="noscript.'.$user.'" value="'.$user.'" />');
                                    }
                ptln('<label for="noscript.'.$user.'">'.$userinfo['name'].' ('.$user.')</label><br/>');
            }
            ptln('  </noscript>');
            
            ptln('  <input type="submit" name="cmd[save]"  value="'.$this->getLang('btn_save').'" />');
            ptln('  <div>');
        }
        ptln('</form>');
    }

    function _saveGroup() {
        if ($_POST['noscriptusers']) {
            $usernames = $_POST['noscriptusers'];
        } else {
            $usernames = $_POST['users'];
        }
        
        $group_filter = array();
        $group_filter['grps'] = $this->_group_name;
        $oldusersinfo = $this->_auth->retrieveUsers(0, -1, $group_filter);
        $oldusers = array_keys($this->_auth->retrieveUsers(0, -1, $group_filter));
        $removed_users = array();
        foreach ($oldusers as $olduser) {
            if (!in_array($olduser, $usernames)) {
                $newgrps = array();
                extract($oldusersinfo[$olduser]);
                foreach($grps as $grpname) {
                    if ($grpname != $this->_group_name) {
                        array_push($newgrps, $grpname);
                    }
                }
                $this->_modifyUser($olduser, $newgrps);
            }
        }

        foreach ($usernames as $newuser) {
            if (!in_array($newuser, $oldusers)) {
                $newuserinfo = $this->_auth->getUserData($newuser);
                array_push($newuserinfo['grps'],$this->_group_name);
                $this->_modifyUser($newuser, $newuserinfo['grps']);
            }
        }
        msg($this->lang['update_ok'],1);
    }

    function _modifyUser($username, $newgrps) {
        $changes = array();
        $changes['grps'] = $newgrps;
        $this->_auth->triggerUserMod('modify', array($username, $changes));
    }
}
