<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');

class groupaction_form extends moodleform {
    public function definition() {
        global $OUTPUT, $DB;

        $mform =& $this->_form;

        $mform->addElement('editor', 'frontpagedescription', "Apresentação geral");
        $mform->setType('frontpagedescription', PARAM_RAW);
        $mform->addRule('frontpagedescription', get_string('required'), 'required', null, 'client');
        
        $mform->addElement('editor', 'pagecontent', "Instruções do exercício");
        $mform->setType('pagecontent', PARAM_RAW);
        $mform->addRule('pagecontent', get_string('required'), 'required', null, 'client');
        $this->add_action_buttons();
    }
}