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
 * File : manage.php
 * To manage the problem sections in this course.
 */

require_once("../../config.php");
require_once("lib.php");

// Arguments.
$courseid = required_param('id', PARAM_INT);
$deletedproblemsectionid = optional_param('delete', 0, PARAM_INT);
$deletedproblemsectionaction = optional_param('mode', null, PARAM_RAW);

// Access control.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);
require_capability('local/problemsection:addinstance', $context);

// Header code.
$manageurl = new moodle_url('/local/problemsection/manage.php', array('id' => $courseid));
if ($deletedproblemsectionid) {
    $pageurl = new moodle_url('/local/problemsection/manage.php',
            array('id' => $courseid, 'delete' => $deletedproblemsectionid));
} else {
    $pageurl = $manageurl;
}
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_course($course);
$title = get_string('manage', 'local_problemsection');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Deleting the problem section.
if ($deletedproblemsectionid && confirm_sesskey()) {
    $deletionparams = array('id' => $deletedproblemsectionid, 'courseid' => $courseid);
    
    if($deletedproblemsectionaction == "task"){
        $deletedproblemsection = $DB->get_record('local_problemsection', $deletionparams);
        if ($deletedproblemsection) {
            $deletedsection = $DB->get_record('course_sections', array('id' => $deletedproblemsection->sectionid));
            // Get section_info object with all availability options.
            $sectionnum = $deletedsection->section;
            $sectioninfo = get_fast_modinfo($course)->get_section_info($sectionnum);

            if (course_can_delete_section($course, $sectioninfo)) {
                $confirm = optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey();
                if ($confirm) {
                    local_problemsection_delete($deletedproblemsection, $course, $sectioninfo);
                    redirect($manageurl);
                } else {
                    $strdelete = get_string('deleteproblemsection', 'local_problemsection');
                    $PAGE->navbar->add($strdelete);
                    $PAGE->set_title($strdelete);
                    $PAGE->set_heading($course->fullname);
                    echo $OUTPUT->header();
                    echo $OUTPUT->box_start('noticebox');
                    $optionsyes = array('id' => $courseid, 'confirm' => 1,
                        'delete' => $deletedproblemsectionid, 'sesskey' => sesskey());
                    $deleteurl = new moodle_url('/local/problemsection/manage.php', $optionsyes);
                    $formcontinue = new single_button($deleteurl, get_string('deleteproblemsection', 'local_problemsection'));
                    $formcancel = new single_button($manageurl, get_string('cancel'), 'get');
                    echo $OUTPUT->confirm(get_string('warningdelete', 'local_problemsection',
                        $deletedproblemsection->name), $formcontinue, $formcancel);
                    echo $OUTPUT->box_end();
                    echo $OUTPUT->footer();
                    exit;
                }
            } else {
                notice(get_string('nopermissions', 'error', get_string('deletesection')), $manageurl);
            }
        }
    }
    
    if($deletedproblemsectionaction == "action"){
        $deletedproblemsection = $DB->get_record('local_problemsection_groups', $deletionparams);
        if ($deletedproblemsection) {
            $confirm = optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey();
            if ($confirm) {
                local_problemsection_deleteaction($deletedproblemsectionid);
                redirect($manageurl);
            } else {
                $strdelete = get_string('deleteproblemsection', 'local_problemsection');
                $PAGE->navbar->add($strdelete);
                $PAGE->set_title($strdelete);
                $PAGE->set_heading($course->fullname);
                echo $OUTPUT->header();
                echo $OUTPUT->box_start('noticebox');
                $optionsyes = array('id' => $courseid, 'confirm' => 1,
                    'delete' => $deletedproblemsectionid, 'sesskey' => sesskey());
                $deleteurl = new moodle_url('/local/problemsection/manage.php', $optionsyes);
                $formcontinue = new single_button($deleteurl, get_string('deleteproblemsection', 'local_problemsection'));
                $formcancel = new single_button($manageurl, get_string('cancel'), 'get');
                echo $OUTPUT->confirm(get_string('warningdelete', 'local_problemsection',
                    $deletedproblemsection->name), $formcontinue, $formcancel);
                echo $OUTPUT->box_end();
                echo $OUTPUT->footer();
                exit;
            }
        } else {
            notice(get_string('nopermissions', 'error', get_string('deletesection')), $manageurl);
        }
    }
}

$problemsections = $DB->get_records('local_problemsection', array('courseid' => $courseid));
$addurl = "problemsection.php?id=$courseid";
$commongroupsurl = "groups.php?id=$courseid&psid=";
$groupactionsurl = "group_action.php?id=$courseid&psid=";
$debateadminurl = "debateadm.php?id=$courseid&psid=";
$commonsubmissionsurl = "$CFG->wwwroot/mod/assign/view.php?action=grading&id=";
$commondeleteurl = "manage.php?id=$courseid&mode=task&sesskey=".s(sesskey())."&delete=";
$commondeleteactionurl = "manage.php?id=$courseid&mode=action&sesskey=".s(sesskey())."&delete=";

echo $OUTPUT->header();
echo "<a href='$addurl'><button>".get_string('problemsection:addinstance', 'local_problemsection')."</button></a>";
echo "<a href='$groupactionsurl'><button>Adicionar ação</button></a>";
echo "<a href='$debateadminurl'><button>Administrar modulo</button></a>";

echo "<h4 class='debate-menage-header-style'>Atividades</h4>";
if ($problemsections) {
    echo '<table class="debate-menage-table">';
    echo '<tr>';
    echo '<th>'.get_string('name').'</th>';
    echo '<th>'.get_string('groups').'</th>';
    echo '<th>'.get_string('submissions', 'local_problemsection').'</th>';
    echo '<th>'.get_string('allowsubmissionsfromdate', 'assign').'</th>';
    echo '<th>'.get_string('duedate', 'assign').'</th>';
    echo '<th>Ação</th>';
    echo '</tr>';
    foreach ($problemsections as $problemsection) {
        $nbgroups = $DB->count_records('groupings_groups',
                array('groupingid' => $problemsection->groupingid));
        $groupsurl = $commongroupsurl.$problemsection->id;
        $assigncm = local_problemsection_get_assigncm($problemsection);
        echo '<tr>';
        echo "<td>$problemsection->name</td>";
        echo "<td style='text-align:center'><a href='$groupsurl'>$nbgroups</a></td>";
        if ($assigncm) {
            $submissionsurl = $commonsubmissionsurl.$assigncm->id;
            $assign = $DB->get_record('assign', array('id' => $assigncm->instance));
            $nbsubmissions = $DB->count_records('assign_submission', array('assignment' => $assign->id));
            echo "<td style='text-align:center'><a href='$submissionsurl'>$nbsubmissions</a></td>";
            echo '<td>'.date('d/m/Y H:i:s', $assign->allowsubmissionsfromdate).'</td>';
            echo '<td>'.date('d/m/Y H:i:s', $assign->duedate).'</td>';
        } else {
            echo '<td></td><td></td><td></td>';
        }
        echo "<td><a href='".$commondeleteurl.$problemsection->id."'><button>"
                .get_string('deleteproblemsection', 'local_problemsection')."</button></a></td>";
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>'.get_string('noproblemyet', 'local_problemsection').'</p>';
}

// -------------------------
$problemsectionsactions = $DB->get_records('local_problemsection_groups', array('courseid' => $courseid));
echo "<h4 class='debate-menage-header-style'>Funções</h4>";
if ($problemsectionsactions) {
    echo '<table class="debate-menage-table">';
    echo '<tr>';
    echo '<th>'.get_string('name').'</th>';
    echo '<th>'."Data de execução".'</th>';
    echo '<th>'."Tipo".'</th>';
    echo '<th>Ação</th>';
    echo '</tr>';
    foreach ($problemsectionsactions as $problemsectionsaction) {
        $nbgroups = $DB->count_records('groupings_groups',
                array('groupingid' => $problemsection->groupingid));
        $groupsurl = $commongroupsurl.$problemsection->id;
        $assigncm = local_problemsection_get_assigncm($problemsection);
        echo '<tr>';
        echo "<td>".$problemsectionsaction->name."</td>";
        echo "<td>".date('d/m/Y H:i:s', $problemsectionsaction->runtime)."</td>";
        echo "<td>".$problemsectionsaction->action."</td>";
        
        echo "<td><a href='".$commondeleteactionurl.$problemsectionsaction->id."'><button>"
                .get_string('deleteproblemsection', 'local_problemsection')."</button></a></td>";
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>'.get_string('noproblemyet', 'local_problemsection').'</p>';
}

// ------------------------------------------------------------------
echo "<h4 class='debate-menage-header-style'>Configuração do módulo</h4>";
$moduleconfigadm = $DB->get_record('local_problemsection_config', array('courseid' => $courseid));
if ($moduleconfigadm) {
    echo '<table class="debate-menage-table">';
    echo '<tr>';
    echo '<th> Item </th>';
    echo '<th> valor </th>';
    echo '</tr>';

    echo "<tr><td>Id curso</td><td>".$moduleconfigadm->courseid."</td></tr>";
    echo "<tr><td>".get_string('studentspergroup', 'local_problemsection')."</td><td>".$moduleconfigadm->studentspergroup."</td></tr>";
    echo "<tr><td>".get_string('newgroupnamestype', 'local_problemsection')."</td><td>".$moduleconfigadm->newgroupnamestyle."</td></tr>";
    echo "<tr><td>".get_string('newsubgroupnamestype', 'local_problemsection')."</td><td>".$moduleconfigadm->newsubgroupnamestype."</td></tr>";
    echo "<tr><td>".get_string('newpost', 'local_problemsection')."</td><td>".$moduleconfigadm->newpost."</td></tr>";
    echo "<tr><td>".get_string('newsecondpost', 'local_problemsection')."</td><td>".$moduleconfigadm->newsecondpost."</td></tr>";
    echo "<tr><td>Primeiro grupo</td><td>".$moduleconfigadm->firstgroup."</td></tr>";
    echo "<tr><td>Segundo grupo</td><td>".$moduleconfigadm->secondgroup."</td></tr>";
    echo "<tr><td>".get_string('moduleentry', 'local_problemsection')."</td><td>".$moduleconfigadm->sectionid."</td></tr>";

    echo '</table>';
} else {
    echo '<p>'.get_string('noproblemyet', 'local_problemsection').'</p>';
}

// ------------------------------------------------------------------
$modulestatus = $DB->get_record('local_problemsection_status', array("courseid"=>$courseid));
echo "<h4 class='debate-menage-header-style'>Estatística do sistema</h4>";

$createstatus = "blind/createstatus.php?id=$courseid";
$createpresentation = "createpresentation.php?id=$courseid";
$createquiz = "quizselect.php?id=$courseid";
$createdebate = "selectsectiondebate.php?id=$courseid";

if($modulestatus){
    echo '<table class="debate-menage-table">';
    echo '<tr>';
    echo '<th> Descrição </th>';
    echo '<th> Status </th>';
    echo '<th> Ação </th>';
    echo '</tr>';

    echo "<tr>";
        echo "<td>Criar/editar carta de apresentação do debate.</td>";
        if($modulestatus->presentationcreated == 1){
            echo "<td class='debate-manage-table-running'>Já configurada.</td>";
            echo "<td><a href='$createpresentation'><button>Nova Carta de apresentação</button></a></td>";
        }else{
            echo "<td class='debate-manage-table-not-running'>Não configurada.</td>";
            echo "<td><a href='$createpresentation'><button>Criar Carta de apresentação</button></a></td>";
        }
    echo "</tr>";

    echo "<tr>";
        echo "<td>Criar quiz inicial.</td>";
        if($modulestatus->quizcreated == 1){
            echo "<td class='debate-manage-table-running'>Quiz já configurado.</td>";
        }else{
            echo "<td class='debate-manage-table-not-running'>Não configurada.</td>";
            echo "<td><a href='$createquiz'><button>Criar quiz de seleção</button></a></td>";
        }
    echo "</tr>";

    echo "<tr>";
        echo "<td>Gerar grupos iniciais a partir do quiz criado.";
        if($modulestatus->quizcreated == 0) {echo "<br><b>Não é possível criar os grupos iniciais: Quiz não inicializado.</b>";}
        echo "</td>";
        if($modulestatus->initialgroupcreated == 1){
            echo "<td class='debate-manage-table-running'>Grupos criados com sucesso.</td>";
            echo "<td><a href=''><button>Gerar grupos</button></a></td>";
        }else{
            echo "<td class='debate-manage-table-not-running'>Grupos não criados.</td>";
            if($modulestatus->quizcreated == 1) { echo "<td><a href=''><button>Gerar grupos</button></a></td>";}
            else{echo "<td><a href=''><button>Gerar grupos</button></a></td>";}
        }
    echo "</tr>";

    echo "<tr>";
        echo "<td>Gerar grupos secundários a partir do primeiro (divisão para debate).</td>";
        if($modulestatus->groupscreated == 1){
            echo "<td class='debate-manage-table-running'>Grupos criados com sucesso.</td>";
        }else{
            echo "<td class='debate-manage-table-not-running'>Grupos não criados.</td>";
            echo "<td><a href='$createdebate'><button>Gerar grupos</button></a></td>";
        }
    echo "</tr>";

    echo "<tr>";
        echo "<td>Fórum de debate (defesa e refutação).</td>";
        if($modulestatus->forumcreated == 1){
            echo "<td class='debate-manage-table-running'>Fórum criado com sucesso.</td>";
        }else{
            echo "<td class='debate-manage-table-not-running'>Falha ao criar fórum.</td>";
            echo "<td><a href=''><button>Criar manualmente</button></a></td>";
        }
    echo "</tr>";

    echo "<tr>";
        echo "<td>Posts de debate (defesa).</td>";
        if($modulestatus->postscreated == 1){
            echo "<td class='debate-manage-table-running'>Posts criados com sucesso.</td>";
        }else{
            echo "<td class='debate-manage-table-not-running'>Falha ao criar posts.</td>";
            echo "<td><a href=''><button>Criar manualmente</button></a></td>";
        }
    echo "</tr>";

    echo '</table>';
}
else{
    echo "Oops. Alguma coisa aconteceu de errado. <b>Não existem status a serem exibidos</b>. <br>";
    echo "Os status são importantes para o correto funcionamento da aplicação. Se você não estiver vendo, clique em <i>Gerar status da aplicação</i>. <br>";
    echo "<td><a href='$createstatus'><button>Gerar status da aplicação</button></a></td>";
}


// ------------------------------------------------------------------
//echo "<h4 class='debate-menage-header-style'>Estatística do curso</h4>";
// fazer em formato de tabela. Seguir padrão.
# total de alunos
# total de grupos
# total de grupos a favor
# total de grupos contra
# total de posts (defesa)
# ------------------------
# total de posts (refutação)


//echo "<a href='$CFG->wwwroot/course/view.php?id=$courseid'><button>".get_string('back')."</button></a>";
echo $OUTPUT->footer();
