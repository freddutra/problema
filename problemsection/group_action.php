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

require_once('../../config.php');
require_once('lib.php');
require_once('selector.php');
require_once('groupaction_form.php');
require_once($CFG->dirroot.'/user/selector/lib.php');

$courseid = required_param('id', PARAM_INT);
$paramgroupid = optional_param('groupid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$changedgroupid = optional_param('changed', 0, PARAM_INT);

$pageurl = new moodle_url('/local/problemsection/group_action.php', array('id' => $courseid));

$course = $DB->get_record('course', array('id' => $courseid));
$PAGE->set_pagelayout('admin');

$PAGE->set_url('/local/problemsection/manage.php', array('id' => $courseid));
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);
require_capability('local/problemsection:addinstance', $context);

$pagetitle = "Gerenciador de ações";
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$mform = new groupaction_form($pageurl);
if ($mform->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) {
    try{
        global $DB;
        $problemsection = new stdClass();
        $problemsection->name = $submitteddata->name;
        $problemsection->courseid = $submitteddata->courseid;
        $problemsection->runtime = $submitteddata->datefrom;
        $problemsection->action = $submitteddata->type;
        $problemsection->status = 1;
        $problemsection->id = $DB->insert_record('local_problemsection_groups', $problemsection);
    }
    catch(\Exception $e) {
        echo("Fail" . $e->getMessage());
    }
    header("Location: manage.php?id=$courseid&psid=");
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}