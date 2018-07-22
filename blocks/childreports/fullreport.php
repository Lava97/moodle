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
 * @copyright  1999 Martin Dougiamas  http://dougiamas.com
 * @copyright  2018 Lalit Chandwani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/'.$CFG->grade_profilereport.'/lib.php');
global $DB;

require_login();
$course = required_param('id', PARAM_INT); // Course id
$user = required_param('user', PARAM_INT); // User id
$user = $DB->get_record("user", array("id" => $user, 'deleted' => 0), '*', MUST_EXIST); // Updating user variable with user object.

// Navbar creation
$userurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course)); // Creating url to user.
$struser = get_string('user');
$PAGE->navbar->add($struser);
$PAGE->navbar->add(fullname($user), $userurl, navigation_node::TYPE_SETTING);
$PAGE->navbar->add('Full Report of ' . fullname($user));

// Setting up the page.

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');
$PAGE->set_title("Full Report: " . fullname($user));
$PAGE->set_heading("Full Report of " . fullname($user));
$PAGE->set_url($CFG->wwwroot.'/blocks/childreports/fullreport.php', array('id' => $course, 'user' => $user->id));

/**
 * Getting all course ids in which user(student) is enrolled in
 *
 * @param object $user The user.
 */
function get_course_ids($user) {
    global $DB;
    $courseids = $DB->get_records_sql("SELECT c.instanceid
                                       FROM {course} crs, {user} u, {context} c, {role_assignments} ra, {role} r
                                       WHERE u.id = ? AND
                                       r.id = 5 AND
                                       r.id = ra.roleid AND
                                       ra.userid = u.id AND
                                       ra.contextid = c.id AND
                                       c.instanceid = crs.id AND
                                       c.contextlevel = ".CONTEXT_COURSE, array($user->id));

    return $courseids;
}
// Retrieving array with all course ids.
$courseids = get_course_ids($user);

// Output starts here.
echo $OUTPUT->header();

foreach ($courseids as $courseid){
    $course = $DB->get_record('course', array('id' => $courseid->instanceid), '*', MUST_EXIST);
    if (empty($CFG->grade_profilereport) or !file_exists($CFG->dirroot . '/grade/report/' . $CFG->grade_profilereport . '/lib.php')) {
        $CFG->grade_profilereport = 'user';
    }

    $viewasuser = true;
    $functionname = 'grade_report_'.$CFG->grade_profilereport.'_profilereport';
    if (function_exists($functionname)) {
        $functionname($course, $user, $viewasuser);
    }
}

echo $OUTPUT->footer();
