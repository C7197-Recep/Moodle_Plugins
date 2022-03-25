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
require_once("$CFG->dirroot/user/profile/lib.php");

GLOBAL $COURSE, $DB, $USER;

/*ASIL ALINAN PARAMETRELER*/
$courseid     = optional_param('courseid', 0, PARAM_INT); // This are required.
$groupid     = optional_param('groupid', 0, PARAM_INT); // This are required.
$userid     = optional_param('userid', 0, PARAM_INT); // This are required.


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

echo $OUTPUT->header();
echo $OUTPUT->heading("In-Car Sheet");
echo $CFG->dbname;
/*SECILEN OGRENCI BILGILERINI AL
BURADA BAZI BILGILER MOODLEIN DEFAULT USER TABLOSUNDAN DOGRUDAN,
BAZILARI ISE BU TABLONUN profile SUTUNU ICINDEN ALINIYOR
*/
$user = $DB->get_record('user', array('id' => $userid));
$userprofile=profile_user_record($userid);

$name = $user->firstname . " " . $user->lastname;
$address = $user->address;
$zipcode = $userprofile->postal_code;
$licencenumber=$user_profile->licence_number;
$gsmnumber=$userprofile->gsm_number;
$instructorname = $USER->firstname . " " .  $USER->lastname;
$instructordirlicno = $USER->drivinglicencenumber;

echo json_encode($USER);
echo '
<form action="/action_page.php">
  <label for="name">Name:</label><br>
  <input type="text" id="name" name="name" value="'.$name.'" required><br>
  <label for="address">Address:</label><br>
  <input type="text" id="address" name="address" value="'.$address.'" required><br>
  <label for="zipcode">Zip Code:</label><br>
  <input type="text" id="zipcode" name="zipcode" value="'.$zipcode.'" required><br>
  <label for="licencenumber">Licence Number:</label><br>
  <input type="text" id="licencenumber" name="licencenumber" value="'.$licencenumber.'" required><br>
  <label for="gsmnumber">GSM Number:</label><br>
  <input type="text" id="gsmnumber" name="gsmnumber" value="'.$gsmnumber.'" required><br>
  
  <label for="instructorname">Instructor Name:</label><br>
  <input type="text" id="instructorname" name="instructorname" value="'.$instructorname.'" required><br>
  
  <label for="drivinglicencenumber">Driving Licence #:</label><br>
  <input type="text" id="drivinglicencenumber" name="drivinglicencenumber" value="'.$instructorname.'" required><br>
  
  <label for="instructorlicenceno">Instructor Licence No:</label><br>
  <input type="text" id="instructorlicenceno" name="instructorlicenceno" value="'.$instructorlicenceno.'" required><br>
  
  <label for="instructorlicexpdate">Instructor Lic. Exp. Date:</label><br>
  <input type="text" id="instructorlicexpdate" name="instructorlicexpdate" value="'.$instructorlicexpdate.'" required><br>
  
  <br>
  <input type="submit" value="Submit">
</form> 
';

echo $OUTPUT->footer();
