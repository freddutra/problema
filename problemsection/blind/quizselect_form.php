<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');

class groupaction_form extends moodleform {
    public function definition() {
        global $OUTPUT, $DB;

        $mform =& $this->_form;
        $courseid = required_param('id', PARAM_INT);

        $returnchoicesofcourse = $DB->get_records('choice', array('course'=>$courseid));
        $FORUM_TYPES = array();
        foreach($returnchoicesofcourse as $returnchoice){
            $FORUM_TYPES[$returnchoice->id] = $returnchoice->name;
        }
        $mform->addElement('select', 'selectedchoise', get_string('selectedchoise', 'local_problemsection'), $FORUM_TYPES, "yes");
        $mform->addElement('static', 'Seleção do quiz', "Seleção do quiz", "Selecione um quiz para ser convertido nos grupos iniciais (posteriormente utilizados para filtragem e criação dos grupos de defesa e refutação).\rUma verz selecionado, <b>não será possível alterar</b>.");

        $this->add_action_buttons();
    }
}