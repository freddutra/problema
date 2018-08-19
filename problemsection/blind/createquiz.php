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
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);
require_capability('local/problemsection:addinstance', $context);

$courseid = required_param('id', PARAM_INT);
$paramgroupid = optional_param('groupid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$changedgroupid = optional_param('changed', 0, PARAM_INT);

//$pageurl = new moodle_url('/local/problemsection/debateadm.php', array('id' => $courseid));
$course = $DB->get_record('course', array('id' => $courseid));
$PAGE->set_pagelayout('admin');

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);
require_capability('local/problemsection:addinstance', $context);

// Header code.
//$pageurl = new moodle_url('/local/problemsection/problemsection.php', array('id' => $courseid));
$pageurl = new moodle_url('/local/problemsection/blind/createstatus.php', array('id' => $courseid));
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_course($course);

$pagetitle = "Criar quiz de debate";
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
try{
    $data = new stdClass();
    $data->course = $courseid;
    $data->name = "Quiz para Debate crítico 2";
    $data->intro = "";
    $data->introformat = 1;
    $data->display = 0;
    $data->includeinactive = 0;
    $data->timemodified = time();
    $data->id = $DB->insert_record('choice', $data);

    // Options
    $DB->insert_record('choice_options', array('choiceid'=>$data->id, 'text'=>"A favor do tema.", "timemodified"=>time()));
    $DB->insert_record('choice_options', array('choiceid'=>$data->id, 'text'=>"Contra o tema.", "timemodified"=>time()));

    $mod = new stdClass();
    $mod->course = $courseid;
    $mod->module = 5;
    $mod->instance = $data->id;
    $mod->section = 0;
    $mod->added = time();
    $mod->id = add_course_module($mod);
    print_r($mod);

    // arbitrário. primeira seção do curso
    $sectionid = course_add_cm_to_section($courseid, $mod->id, 0);
    
    $DB->set_field("course_modules", "section", $sectionid, array("id" => $mod->id));
    rebuild_course_cache($courseid);

    $getmodulestatusid = $DB->get_record('local_problemsection_status', array('courseid'=>$courseid));
    $DB->update_record('local_problemsection_status', array('id'=>$getmodulestatusid->id, 'presentationcreated'=>1));

    header("Location: ../manage.php?id=$courseid&psid=");
}
catch(\Exception $e) {
    echo("Fail" . $e->getMessage());
}