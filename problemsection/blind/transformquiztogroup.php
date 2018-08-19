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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Aplicação moficada :
 * xx
 * Criação de debate crítico (2018)
 * Parte integrante do Trabalho de Conclusão de Curso (xx)
 * 
 * Adds to the course a section where the teacher can submit a problem to groups of students
 * and give them various collaboration tools to work together on a solution.
 *
 * @package   local_problemsection
 * @copyright 2016 Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : groups.php
 * Manage groups for this problem section.
 */

global $CFG, $PAGE, $USER, $SITE, $COURSE;

//require_once("../../config.php");
require_once dirname(dirname(dirname(__FILE__))).'/../config.php';
require_once('../lib.php');
require_once($CFG->libdir.'/formslib.php');

// Access control.
$courseid = required_param('id', PARAM_INT);
$quizid = required_param('quizid', PARAM_INT);
$paramgroupid = optional_param('groupid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$changedgroupid = optional_param('changed', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);
require_capability('local/problemsection:addinstance', $context);

//$pageurl = new moodle_url('/local/problemsection/debateadm.php', array('id' => $courseid));
$course = $DB->get_record('course', array('id' => $courseid));

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);
require_capability('local/problemsection:addinstance', $context);

// Header code
$pageurl = new moodle_url('/local/problemsection/blind/transformquiztogroup.php', array('id' => $courseid, 'quizid'=>$quizid));
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_course($course);

$pagetitle = "Converter quiz em grupo";
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
try{
    $returnchoicestudents = $DB->get_records('choice_answers', array('choiceid'=>$quizid));

    //echo "<pre>";
    foreach($returnchoicestudents as $returnchoicestudent){
        
        $getgroups = $DB->get_records("groups", array('courseid'=>$courseid));
        $user = $DB->get_record('user', array('id'=>$returnchoicestudent->userid, 'deleted'=>0, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);
        
        $data = new stdClass();
        $data->courseid = $courseid;

        if($returnchoicestudent->optionid == 1){
            if(count($getgroups) != 0){
                foreach($getgroups as $getgroup){
                    if(preg_match('/(\[DI]\b)(\w+)[1]\b/', $getgroup->name)){
                        $group = groups_get_group($getgroup->id, 'id, courseid', MUST_EXIST);
                        groups_add_member($group, $user);
                        break;
                    }
                }
            }
            else{ $data->name = "[DI]DebateCritico1"; }
        }
        else{
            if(count($getgroups) != 0){
                foreach($getgroups as $getgroup){
                    if(preg_match('/(\[DI]\b)(\w+)[2]\b/', $getgroup->name)){
                        $group = groups_get_group($getgroup->id, 'id, courseid', MUST_EXIST);
                        groups_add_member($group, $user);
                        break;
                    }
                }
            }
            else{ $data->name = "[DI]DebateCritico2"; }
        }

        $creanewgroup = groups_create_group($data);
        $group = groups_get_group($creanewgroup, 'id, courseid', MUST_EXIST);
        $user = $DB->get_record('user', array('id'=>$returnchoicestudent->userid, 'deleted'=>0, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);
        groups_add_member($group, $user);
        
        $getmodulestatusid = $DB->get_record('local_problemsection_status', array('courseid'=>$courseid));
        $DB->update_record('local_problemsection_status', array('id'=>$getmodulestatusid->id, 'initialgroupcreated'=>1));

        header("Location: ../manage.php?id=$courseid&psid=");
    }
}
catch(\Exception $e) {
    echo("Fail" . $e->getMessage());
}