<?php

/*KAYNAK:
https://github.com/mudrd8mz/moodle-mod_newmodule
*/

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
 * Prints a particular instance of newmodule
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_newmodule
 * @copyright 2010 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace newmodule with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $DB, $USER, $CFG, $COURSE;

require_once($CFG->libdir.'/completionlib.php');
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/querylib.php';

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // newmodule instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('newmodule', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newmodule  = $DB->get_record('newmodule', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $newmodule  = $DB->get_record('newmodule', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $newmodule->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('newmodule', $newmodule->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

/*AKTIVITE GORULDUGUNDE COMPLETED OLMASI ICIN.
BU SAYEDE RESTRICTED ILE BAGLANAN SONRAKI AKTIVITE ACILIYOR.*/
$completion = new completion_info($course);
$completion->set_module_viewed($cm);
$completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);

/*ADDTOLOG DEPRECATED*/
//add_to_log($course->id, 'newmodule', 'view', "view.php?id=$cm->id", $newmodule->name, $cm->id);
/*PLUGININ ILK CALISMASINI KAYDET
SONRA OGRENCI YENIDEN TIKLARSA TEKRAR TEKRAR EPOSTA GONDERILMEMESI ICIN*/
try{
    $clickrecord=$DB->get_record('newmodule', array('course' => $course->id, 'name' => $USER->id));
    $is_first_click=$clickrecord->intro;
}catch(Exception $e){
    $is_first_click="norecordfound";
}
/*EGER DAHA ONCE TIKLAMA KAYDI YAPILMADIYSA O ZAMAN KAYIT YAP*/
if ($is_first_click!='firstclickdone'){
    $record = new stdClass();
    $record->course = $course->id;
    $record->name = $USER->id;
    $record->intro = "firstclickdone";
    $record->introformat = 1;
    $record->introformat = 1;
    $record->timecreated = 1;
    $record->timemodified = 1;
    $DB->insert_record('newmodule', $record);
}

/// Print the page header
$PAGE->set_url('/mod/newmodule/view.php', array('id' => $cm->id));
$PAGE->set_title($newmodule->name);
$PAGE->set_heading($course->shortname);
/*BU KOD HATA VERDI*/
//$PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'newmodule')));
// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
// Output starts here
echo $OUTPUT->header();

$courseid= $course->id;
$userid=$USER->id;

/*ENROLLMENT BILGILERI*/
/*BURADAKI DB TABLOSUNDA ENROLMENT BILGILERI VAR
FAKAT TABLODAKI KAYITLAR KURS ID SINE GORE KAYITLI DEGIL. 
IKI KURSA ENROL OLMUS OLSA
HANGISINI ALMAMIZ GEREKIYOR NASIL AYIRT EDERIZ BELLI DEGIL.
*/
$user_enroll_data = $DB->get_record('user_enrolments', array('userid'=>$USER->id));
try{
    $enrollment_start_date=date('m/d/Y', $user_enroll_data->timestart);//course start date
}catch(Exception $e){
   $enrollment_start_date=date('m/d/Y');
}


//$normal_end_date=date('Y-m-d',$user_enroll_data->timeend);//course end date
//$enrollment_end_date=date('m/d/Y', json_decode(json_encode(json_decode(json_encode($USER->enrol))->enrolled))->$courseid);

/*KURSUN KURUMCA ILK BASLATILDIGI VE SONLANDIRILMA ZAMANLARI*/
//$start=date('m/d/Y', $COURSE->startdate);
//$end=date('m/d/Y', $COURSE->enddate);

/*OGRENCININ KURSU BITIRME ZAMANI OLARAK PLUGINE TIKLADIGI 
TARIHI GOSTERIYORUZ.*/
//$end=date("m/d/Y");
/*ESKIDEN TIKLANILAN TARIHTI. SIMDI VERITABANINDAN COURSE COMPLETION TARIHINI GOSTERIYORUZ.*/
$user_complete_data = $DB->get_record('course_completions', array('userid'=>$USER->id));
$end=date('m/d/Y', $user_complete_data->timecompleted);

/*OGRENCININ EPOSTA, LISANS VE GSM BILGILERI*/
$licencenumber=json_decode(json_encode($USER->profile))->licence_number;
$gsmnumber=json_decode(json_encode($USER->profile))->gsm_number;
$middlename=json_decode(json_encode($USER->profile))->middle_name;
$email=$USER->email;

/*GSM BILGILERI SETINI BIZ OLUSTURDUGUMUZ ICIN, EGER YOKSA TELEFONU
DEFAULT MOODLE SETLERINDEN AL*/
if (strlen($gsmnumber)>0){
    $phone=$gsmnumber;
}
elseif (strlen($USER->phone1)>0){
    $phone=$USER->phone1;
}else{
    $phone="Not provided.";
}

/*OLUSTURULACAK MESAJLAR*/
$informationtext="";
$messageHtml=""; //"<a href='https://korrogo.com'>Test</a>";

/*KURSUN NONEDITINGTEACHER BILGILERINI AL*/
$role = $DB->get_record('role', array('shortname' => 'teacher'));
/*GETCONTEXT DEPRECATED*/
//$context = get_context_instance(CONTEXT_COURSE, $courseid);
//$context = context_module::instance($courseid);
$context = context_course::instance($course->id);
$teachers = get_role_users($role->id, $context);
//$roleuser = $DB->get_record('user', array('id' => $role->id));

/*OGRENCI PLUGINE ILK KEZ MI TIKLIYOR*/
// $clickrecord=$DB->get_record('newmodule', array('course' => $course->id, 'name' => $USER->id));
// $is_first_click=$clickrecord->intro;

/*KURS BITIRME BILGISI*/
$crs = new stdClass();
$crs->id = $course->id;
$cinfo = new completion_info($crs);
$iscomplete = $cinfo->is_course_complete($USER->id);
$headtext="";
// echo $is_first_click;
// echo $iscomplete;

//GRUP (SURUCU KURSU) ADI
$usergroupid=groups_get_user_groups($course->id, $USER->id)[0][0];
$usergroupname = groups_get_group_name($usergroupid);

    /*EGER KURS TAMAMLANDIYSA TAMAMLANDI EPOSTASINI GONDER*/
    if ($iscomplete) {
        if ($grade = grade_get_course_grade($userid, $courseid)) {
            
            $finalgrade=$grade->grade;
            
            $headtext='Congratulations!';
            
            $subject="Course Completion Notification - " . $COURSE->shortname;
            
            $messageText="<p>Dear ".$usergroupname.",</p>".
                         "<p>Your student " . $USER->firstname . " " . $USER->lastname . " has just completed the " . $COURSE->shortname . ".</p>".
                         "<br>".
                         "<p>Please see the details below:</p>".
                         "<br>";
                    
            $messageHtml=$messageText.
                        "<p>User Name : " . $USER->username . "</p>".
                        "<p>First Name : " . $USER->firstname . "</p>".
                        "<p>Last Name : " . $USER->lastname . "</p>".
                        "<p>Middle Name : " . $middlename . "</p>".
                        "<p>Driver's Licence Number : " . $licencenumber."</p>".
                        "<p>Phone : " . $phone."</p>".
                        "<p>Email : " . $email."</p>".
                        "<p>Course Start Date : " . $enrollment_start_date." (mm/dd/yyyy)</p>".
                        "<p>Course Completion Date : " . $end." (mm/dd/yyyy)</p>".
                        "<p>Final Score : " . $finalgrade."</p>".
                        "<br>".
                        "<p>Thank you for choosing GoGo Online BDE Curriculum.</p>".
                        "<br>".
                        "<p>GoGo Driving</p>".
                        "<p>support@gogodriving.com</p>".
                        "<p>(226) 620 1919</p>";
    
            $informationtext="<p>You have completed the 30 hours of the Online Beginner Driver Education Course.</p>".
                             "<p>Please get in touch with your driving school to schedule your in-car training.</p>".
                             "<br>".
                             "<p>Thank you.</p>";
                                     
                                     
            /*HER OGRETMENE GEREKLI SARTLAR SAGLANIYORSA EPOSTA GONDER*/
            foreach ($teachers as $teacher) {
                /*SADECE OGRENCININ BAGLI OLDUGU GRUBUN OGRETMENLERINE BILGI VER*/
                if (json_encode(groups_get_user_groups($course->id, $USER->id))==json_encode(groups_get_user_groups($course->id, $teacher->id))){
                    //echo fullname($teacher);
                    //echo $teacher->email;
                    //echo $USER->firstname;
                    //echo json_encode($USER);
                    /*HER OGRETMEN ICIN*/
                    /*EMAIL ORTAK PARAMETRELERI*/
                    $toUser=$teacher;
                    $fromUser=core_user::get_support_user();
                    email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, '', '', false);
                }
            }
        }
    }
    /*EGER KURS TAMAMLANMADIYSA VE ILK KEZ TIKLANIYORSA
    O ZAMAN ENROL OLDU EPOSTASINI GONDER*/
    elseif ($is_first_click!='firstclickdone'){
        
        $headtext='Congratulations!';
        
        $subject="Student Enrolment Notification - " . $COURSE->shortname;
        
        $messageText="<p>Dear ".$usergroupname.",</p>".
                     "<p>Your student " . $USER->firstname . " " . $USER->lastname . " has just enrolled and started " . $COURSE->shortname . ".</p>".
                     "<br>".
                     "<p>Please see the details below:</p>".
                     "<br>";
        $messageHtml=$messageText.
                    "<p>User Name : " . $USER->username . "</p>".
                    "<p>First Name : " . $USER->firstname . "</p>".
                    "<p>Last Name : " . $USER->lastname . "</p>".
                    "<p>Middle Name : " . $middlename . "</p>".
                    "<p>Driver's Licence Number : " . $licencenumber."</p>".
                    "<p>Course Start Date : " . $enrollment_start_date." (mm/dd/yyyy)</p>".
                    "<p>Phone : " . $phone."</p>".
                    "<p>Email : " . $email."</p>".
                    "<br>".
                    "<p>You will be notified when your student completes the course, too.</p>".
                    "<p>Thank you for choosing GoGo Online BDE Curriculum.</p>".
                    "<br>".
                    "<p>GoGo Driving</p>".
                    "<p>support@gogodriving.com</p>".
                    "<p>(226) 620 1919</p>";
        
        $informationtext="<p>You've just enrolled in the Online Beginner Driver Education Course.</p>" .
                         "<p>Please watch the Tutorial first and proceed with the next activity.</p>".
                         "<br>" .
                         "<p>Have fun and learn as much as you can!</p>";
        
        /*HER OGRETMENE GEREKLI SARTLAR SAGLANIYORSA EPOSTA GONDER*/
        foreach ($teachers as $teacher) {
            /*SADECE OGRENCININ BAGLI OLDUGU GRUBUN OGRETMENLERINE BILGI VER*/
            if (json_encode(groups_get_user_groups($course->id, $USER->id))==json_encode(groups_get_user_groups($course->id, $teacher->id))){
                //echo fullname($teacher);
                //echo $teacher->email;
                //echo $USER->firstname;
                //echo json_encode($USER);
                /*HER OGRETMEN ICIN*/
                /*EMAIL ORTAK PARAMETRELERI*/
                $toUser=$teacher;
                $fromUser=core_user::get_support_user();
                email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, '', '', false);
            }
        }
    }else{
        $informationtext="You have already done this activity.";
    }
    
    // Replace the following lines with you own code
    echo $OUTPUT->heading($headtext);
    
        // foreach ($teachers as $teacher) {
        //     echo "öğretmen : "  . $teacher->firstname . " " . $teacher->lastname;
        //     echo "<br>";
        //     echo "öğretmenin grup id:" .json_encode(groups_get_user_groups($course->id, $teacher->id));
        //     echo "<br>";
        //     echo "öğrencinin grup id:" .json_encode(groups_get_user_groups($course->id, $USER->id));
        //     echo "<br>";
            
        //     if (json_encode(groups_get_user_groups($course->id, $USER->id))==json_encode(groups_get_user_groups($course->id, $teacher->id))){
        //         //echo "eposta gönderilecek öğretmen:" . $teacher->firstname . " " . $teacher->lastname;
        //         //email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, '', '', false);
        //     }
        // }
    
    echo $informationtext;
    
    // echo "<p>Below is the email content sent : </p>";
    // echo "<p>*****************************</p>";
    // echo $messageHtml;
    // echo $iscomplete;
    // echo $course->id;
    // echo '<br>';
    // echo $USER->id;
// Render the activity information.
$cminfo = cm_info::create($cm);
$cmcompletion = \core_completion\cm_completion_details::get_instance($cminfo, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cminfo, $USER->id);
echo $OUTPUT->activity_information($cminfo, $cmcompletion, $activitydates);

// Finish the page  
echo $OUTPUT->footer();
