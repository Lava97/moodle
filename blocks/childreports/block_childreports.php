<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Child Reports block.
 *
 * @package    block_childreports
 * @copyright  2018 Lalit Chandwani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Child Reports block.
 *
 * @package    block_childreports
 * @copyright  2018 Lalit Chandwani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_childreports extends block_base {
    /**
     * Initialization function
     *
     *
     */
    public function init() {
        $this->title = get_string('childreports', 'block_childreports');
    }

    /**
     * Getting and setting the content of the block
     *
     *
     */
    public function get_content() {
        global $CFG, $USER, $DB;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        // Get all the users who have a child associated with them.
        $allusernames = get_all_user_name_fields(true, 'user');
        if ($usercontexts = $DB->get_records_sql("SELECT context.instanceid, $allusernames
                                                  FROM {role_assignments} roleAssignments, {context} context, {user} user
                                                  WHERE roleAssignments.userid = ?
                                                       AND roleAssignments.contextid = context.id
                                                       AND context.instanceid = user.id
                                                       AND context.contextlevel = ".CONTEXT_USER, array($USER->id))) {

            $this->content->text = '<ul>';
            foreach ($usercontexts as $usercontext) {
                $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/user/view.php?id='.$usercontext->instanceid.'&amp;course='.SITEID.'">'.fullname($usercontext).'</a></li>';
                $this->content->text .= '<ul><li><a href="'.$CFG->wwwroot.'/blocks/childreports/fullreport.php?user='.$usercontext->instanceid.'&amp;id='.SITEID.'">Full report of '.fullname($usercontext).'</a></li></ul>';
            }
            $this->content->text .= '</ul>';
        }

        return $this->content;
    }

    /**
     * Function that gets called every time block settings are brought up, this is setting newly assigned title to block
     *
     *
     */
    public function specialization() {
        $this->title = isset($this->config->title) ? $this->config->title : get_string('newchildreportsblock', 'block_childreports');
    }

    /**
     * Allowing multiple instances of different pages
     *
     *
     */
    public function instance_allow_multiple() {
        return true;
    }
}
