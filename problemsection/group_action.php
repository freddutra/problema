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
 * File : groups.php
 * Manage groups for this problem section.
 */

require_once('../../config.php');
require_once('lib.php');
require_once('selector.php');
require_once('groupaction_form.php');
require_once($CFG->dirroot.'/user/selector/lib.php');

//$psid = required_param('psid', PARAM_INT);
$courseid = required_param('id', PARAM_INT);
$paramgroupid = optional_param('groupid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$changedgroupid = optional_param('changed', 0, PARAM_INT);

//$groupsurl = new moodle_url('/local/problemsection/groups.php', array('id' => $courseid, 'psid' => $psid));
//$groupsurlstring = "$CFG->wwwroot/local/problemsection/groups.php?id=$courseid&psid=$psid";
$pageurl = new moodle_url('/local/problemsection/group_action.php', array('id' => $courseid));

$course = $DB->get_record('course', array('id' => $courseid));
$PAGE->set_pagelayout('admin');

$PAGE->set_url('/local/problemsection/manage.php', array('id' => $courseid));
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);
require_capability('local/problemsection:addinstance', $context);

$pagetitle = "Teste?";
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

//echo $OUTPUT->header();
//echo $problemsvisibility;

$mform = new groupaction_form($pageurl);
if ($mform->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) {
    echo $OUTPUT->header();
    
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
    
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    
    $avaliablestudent = array();
    $context = context_course::instance($courseid);
    $students = $DB->get_records("user_enrolments", array('enrolid'=>$courseid));
    foreach($students as $student){
        $isstudent = $DB->count_records("role_assignments", array('contextid'=>$courseid));
        $coursecontext = context_module::instance($course->id);
        if (!has_capability('mod/folder:managefiles', $coursecontext, $student->userid, false)) {
            //echo "is Student > ";
            //echo "membro:: " . $student->userid . "<br>";
            $avaliablestudent[] = array('userid'=>$student->userid);
        }
    }
    
    // Get avaliable groups (previous)
    $selectusersgroups = array();
    $startgroups = $DB->get_records("groups", array('courseid'=>$courseid));
    foreach($startgroups as $startgroup){
        //echo $startgroup->name;
        if(preg_match('/\[DC]\b/', $startgroup->name)){
            
            // Check who is in which group
            $returnuserofgroups = $DB->get_records("groups_members", array('groupid'=>$startgroup->id));
            //$selectusersgroups[] = array('userid'=>$returnuserofgroup->userid, 'groupname'=>$startgroup->name);
            
            $data = array();
            foreach($returnuserofgroups as $returnuserofgroup){
                //echo "membro:: " . $returnuserofgroup->userid ." (" . $startgroup->name .")<br>";
                $data[$returnuserofgroup->userid] = array('userid'=>$returnuserofgroup->userid, 'groupid'=>$startgroup->id, 'groupname'=>groups_get_group_name($startgroup->id));
            }
            $selectusersgroups[$startgroup->id] = $data;
        }
    }
    
    
    // https://docs.moodle.org/dev/Web_service_API_functions
    
    $grouplim = 1; // Limite de alunos / grupo
    $hardsplit = 2; // 50%
    $timestamp = time(); // now
    $freshcreatedgroupsid = array(); // group ids
    
    if(count($avaliablestudent) > $grouplim) {
        //echo count($avaliablestudent)/$grouplim;
        
        $groupnameformat = "Grupo @";
        
        $newgroupscount = count($avaliablestudent)/$grouplim;
        
        // Criação dos grupos OK
        if(is_int($newgroupscount) == true){
            for($i = 0; $newgroupscount > $i; $i++){
                $currentgroupname = str_replace('@', $i+1, $groupnameformat); // string de formatação
                //$randomsecret = rand(); // prefixo. evitar colisão de identificadores
                //$ident = $randomsecret.""; // identificador
                
                if($i % 2 == 0){$currentgroupname = $currentgroupname . " (argumento positivo)"; } //$ident = "dcp_"; // (argumento positivo)
                else{$currentgroupname = $currentgroupname . " (argumento negativo)";} // $ident = "dcn_"; // (argumento negativo)
                    
                $data = new stdClass();
                $data->courseid = $courseid;
                $data->name = $currentgroupname;
                //$data->idnumber = $ident.$i;
                $freshcreatedgroupsid[] = groups_create_group($data);
            }
        }
        else{
            echo "Not an integer.";
        }
        
        /*
        echo $newgroupid = groups_create_group($data); //return id
        
        // Create _group [OK]
        $newgroupiddata = new stdClass();
        $newgroupiddata->courseid = $courseid;
        $newgroupiddata->idnumber;
        $newgroupiddata->name = "teste";
        $newgroupiddata->description = "";
        $newgroupiddata->descriptionformat = 0;;
        $newgroupiddata->enrolmentkey;
        $newgroupiddata->picture = 0;
        $newgroupiddata->hidepicture = 0;
        $newgroupiddata->timecreated = $timestamp;
        $newgroupiddata->timemodified = $timestamp;
        $newgroupid = $DB->insert_record("groups",$newgroupiddata);
        
        // Create _grouping_groups
        $newgroupinggroups = new stdClass();
        $newgroupinggroups->groupingid = ;
        $newgroupinggroups->groupid = $newgroupid;
        $newgroupinggroups->timeadded = $timestamp;
        */
    }

    // alocar alunos em posição oposta
    // A -> B | A <- B
    
    foreach($selectusersgroups as $setnewgroupstudents){
        foreach($setnewgroupstudents as $setnewgroupstudent){
            //echo $setnewgroupstudent['userid'];
            //if($setnewgroupstudent['userid'])
            //(\[DC]\b)(\w+)[\d]
            
            if(preg_match('/(\[DC]\b)(\w+)[1]\b/', $setnewgroupstudent['groupname'])){ // Se 1 == a favor/contra
                //echo $setnewgroupstudent['groupname'] . " was " . $setnewgroupstudent['groupname'] . ". <br>";
                // enroll user oposite group
                
                // se grupo tiver < que o numero de alunos permitido, colocar nele. Senao, proximo.
                foreach($freshcreatedgroupsid as $freshcreatedgroupid){
                    $numberusersingroup = $DB->count_records('groups_members', array("groupid"=>$freshcreatedgroupid));
                    //echo $numberusersingroup . "<br>";
                    if($numberusersingroup < $grouplim){
                        // Add
                        $group = groups_get_group($freshcreatedgroupid, 'id, courseid', MUST_EXIST);
                        $user = $DB->get_record('user', array('id'=>$setnewgroupstudent['userid']));
                        groups_add_member($group, $user);
                        break;
                    }
                }
            }
            else {}
        }
    }

    // groups_remove_member($groupid, $user->id)
    
    echo "<pre>";
    
    print_r($freshcreatedgroupsid);
    print_r($selectusersgroups);
    echo "</pre>";
    
    $mform->display();
    echo $OUTPUT->footer();
}



echo "<a href='manage.php?id=$courseid'><button>".get_string('back')."</button></a>";

echo $OUTPUT->footer();
