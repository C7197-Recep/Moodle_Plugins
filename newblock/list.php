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
 * Lists all the users within a given course.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/notes/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;

GLOBAL $COURSE;

define('DEFAULT_PAGE_SIZE', 20);

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('courseid', 0, PARAM_INT); // This are required.
$newcourse    = optional_param('newcourse', false, PARAM_BOOL);
$roleid       = optional_param('roleid', 0, PARAM_INT);
$urlgroupid   = optional_param('group', 0, PARAM_INT);

$PAGE->set_url('/blocks/newblock/list.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'contextid' => $contextid,
        'id' => $courseid,
        'newcourse' => $newcourse));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
// Not needed anymore.
unset($contextid);
unset($courseid);

require_login($course);

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    course_require_view_participants($systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
    course_require_view_participants($context);
}

// Trigger events.
user_list_view($course, $context);

$bulkoperations = has_capability('moodle/course:bulkmessaging', $context);

$PAGE->set_title("$course->shortname: ".get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->set_docs_path('enrol/users');
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

// Expand the users node in the settings navigation when it exists because those pages
// are related to this one.
$node = $PAGE->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
if ($node) {
    $node->force_open();
}


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('participants'));


/*USER TABLO STILI*/
echo '<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>';

$table_html='<table>
  <tr>
    <th>Name</th>
    <th>Email</th>
    <th>Group</th>
    <th>Status</th>
    <th>In-car Sheet</th>
  </tr>';
  
$usergroups=groups_get_user_groups($course->id, $USER->id)[0];

foreach ($usergroups as $groupid) {
    //$members=groups_get_members($groupid, $fields='u.*', $sort='lastname ASC');
    /*AKTIF ENROL OLANLARI GOSTERIR*/
    $members=get_enrolled_users($context, $withcapability = '', $groupid = $groupid, $userfields = 'u.*', $orderby = '', $limitfrom = 0, $limitnum = 0, $onlyactive = true);
    
    foreach ($members as $member) {
        $table_html = $table_html .
                      '<tr>
                        <th>'.$member->firstname.' '.$member->lastname .'</th>
                        <th>'.$member->email.' </th>
                        <th>'.groups_get_group_name($groupid).'</th>
                        <th>Active</th>
                        <th><a href="incarsheet.php?courseid='.$COURSE->id.'&groupid='.$groupid.'&userid='.$member->id.'">Create</a></th>
                      </tr>';
    }
}
$table_html = $table_html . '</table>';

echo $table_html;

echo $OUTPUT->footer();
