<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');

class groupaction_form extends moodleform {
    public function definition() {
        global $OUTPUT, $DB;

        $mform =& $this->_form;

        // Check if config exist
        $courseid = required_param('id', PARAM_INT);

        $returnsettingid = $DB->get_record('local_problemsection_config', array('courseid'=>$courseid));
        $mform->setDefault('studentspergroup', $returnsettingid->studentspergroup);
        $mform->setDefault('newgroupnamestype', $returnsettingid->newgroupnamestyle);
        $mform->setDefault('newsubgroupnamestype', $returnsettingid->newsubgroupnamestype);
        $mform->setDefault('newsubgroupnamestype', $returnsettingid->newsubgroupnamestype);
        $mform->setDefault('newpost', $returnsettingid->newpost);
        $mform->setDefault('newsecondpost', $returnsettingid->newsecondpost);
        $mform->setDefault('firstgroupyesno', $returnsettingid->firstgroup);
        $mform->setDefault('secondgroupyesno', $returnsettingid->secondgroup);
        $mform->setDefault('coursesectionid', $returnsettingid->sectionid);

        // -----------------------------

        $mform->addElement('header', 'generalhdr', get_string('general'));

        // aluno por subgrupo
        $mform->addElement('text', 'studentspergroup', get_string('studentspergroup', 'local_problemsection'));
        $mform->addRule('studentspergroup', get_string('maximumchars', '', 99), 'maxlength', 99);
        $mform->setType('studentspergroup', PARAM_INT);
        //$mform->addRule('studentspergroup', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'groupdefinition', get_string('namestyleformat', 'local_problemsection'));
        $mform->setExpanded('groupdefinition');

        // padrao do nome dos subgrupos
        $mform->addElement('text', 'newsubgroupnamestype', get_string('newsubgroupnamestype', 'local_problemsection'));
        $mform->addRule('newsubgroupnamestype', get_string('maximumchars', '', 99), 'maxlength', 99);
        $mform->setType('newsubgroupnamestype', PARAM_TEXT);
        //$mform->addRule('newsubgroupnamestype', get_string('required'), 'required', null, 'client');
        
        // padrão do nome dos tópicos de defesa
        $mform->addElement('text', 'newpost', get_string('newpost', 'local_problemsection'));
        $mform->addRule('newpost', get_string('maximumchars', '', 99), 'maxlength', 99);
        $mform->setType('newpost', PARAM_TEXT);
        //$mform->addRule('newpost', get_string('required'), 'required', null, 'client');

        // padrão do nome dos tópicos de refutação
        $mform->addElement('text', 'newsecondpost', get_string('newsecondpost', 'local_problemsection'));
        $mform->addRule('newsecondpost', get_string('maximumchars', '', 99), 'maxlength', 99);
        $mform->setType('newsecondpost', PARAM_TEXT);
        //$mform->addRule('newsecondpost', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'groupdefinition', get_string('groupdefinition', 'local_problemsection'));
        $mform->setExpanded('groupdefinition');

        // Grupo 1 será a favor ou contra?
        $firstgroup=array();
        $firstgroup[] = $mform->createElement('radio', 'firstgroupyesno', '', get_string('infavorgroup', 'local_problemsection'), 1);
        $firstgroup[] = $mform->createElement('radio', 'firstgroupyesno', '', get_string('againsgroup', 'local_problemsection'), 0);
        $mform->addGroup($firstgroup, 'firstgroup', 'Primeiro grupo', array(' '), false);

        // Grupo 2 será a favor ou contra?
        $secondgroup=array();
        $secondgroup[] = $mform->createElement('radio', 'secondgroupyesno', '', get_string('infavorgroup', 'local_problemsection'), 1);
        $secondgroup[] = $mform->createElement('radio', 'secondgroupyesno', '', get_string('againsgroup', 'local_problemsection'), 0);
        $mform->addGroup($secondgroup, 'secondgroup', 'Segundo grupo', array(' '), false);

        // Selecionar entrada que vai receber grupos, forum e posts
        $mform->addElement('header', 'moduleentry', get_string('moduleentry', 'local_problemsection'));
        $mform->setExpanded('moduleentry');

        // carregar seções
        $FORUM_TYPES = array();
        $getsectionsfromcourse = $DB->get_records('course_sections', array('course'=>$courseid));
        foreach($getsectionsfromcourse as $getsectiondata){
            if($getsectiondata->name != null) {$FORUM_TYPES[$getsectiondata->id] = $getsectiondata->name;}
            //$FORUM_TYPES[$getsectiondata->id] = $getsectiondata->name;
        }
        $mform->addElement('select', 'coursesectionid', get_string('sectionid', 'local_problemsection'), $FORUM_TYPES, "yes");


        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();
    }
}