<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');

class groupaction_form extends moodleform {
    public function definition() {
        global $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));

        // Título
        $mform->addElement('text', 'name', get_string('name'));
        $mform->addRule('name', get_string('maximumchars', '', 99), 'maxlength', 99);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        // Data de execução
        $mform->addElement('date_time_selector', 'datefrom', get_string('allowsubmissionsfromdate', 'assign'));
        $mform->addHelpButton('datefrom', 'directions', 'local_problemsection');
        $mform->addRule('datefrom', get_string('required'), 'required', null, 'client');

        //Tipo
        $mform->addElement('header', 'communicationhdr', get_string('communicationtools', 'local_problemsection'));
        
        $FORUM_TYPES = array(
            "opengroup"=>"Abrir Grupos (Visível)",
            "closegroup"=>"Fechar Grupos (Invisível)",
            "creategroups"=>"Criar Grupos"
        );

        $mform->addElement('select', 'type', get_string('forumtype', 'forum'), $FORUM_TYPES, "yes");


        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();
    }
}