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
 * Adds to the course a section where the teacher can submit a problem to groups of students
 * and give them various collaboration tools to work together on a solution.
 *
 * @package   local_problemsection
 * @copyright 2016 Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : lib.php
 * Library functions
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->dirroot/course/lib.php");
require_once($CFG->dirroot.'/group/lib.php');

function open_forum($courseid){
    /* 
     * Forçar curso a ser público (grupos visiveis).
     * Fórum acompanha a privacidade do curso
     */ 
    global $COURSE, $DB;
    $DB->update_record("course", array(
        'id'=>$courseid,
        'groupmode'=>1,
        'groupmodeforce'=>1
    ));
}

function close_forum($courseid){
    /* 
     * Forçar curso a ser privado (grupos visiveis).
     * Fórum acompanha a privacidade do curso
     */ 
    global $COURSE, $DB;
    $DB->update_record("course", array(
        'id'=>$courseid,
        'groupmode'=>2,
        'groupmodeforce'=>1
    ));
}