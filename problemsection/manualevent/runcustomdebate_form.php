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
        $actionid = required_param('action', PARAM_INT);

        $FORUM_TYPES = array();

        if($actionid == 1){
            $getsectionsfromcourse = $DB->get_records('course_sections', array('course'=>$courseid));
            foreach($getsectionsfromcourse as $getsectiondata){
                if($getsectiondata->name != null) {$FORUM_TYPES[$getsectiondata->id] = $getsectiondata->name;}
            }
            $mform->addElement('select', 'returncustomdebate', get_string('sectionid', 'local_problemsection'), $FORUM_TYPES, "yes");
        }
        elseif($actionid == 2){
            $getsectionsfromcourse = $DB->get_records('forum', array('course'=>$courseid));
            foreach($getsectionsfromcourse as $getsectiondata){
                if($getsectiondata->name != null) {$FORUM_TYPES[$getsectiondata->id] = $getsectiondata->name;}
            }
            $mform->addElement('select', 'returncustomdebate', "Selecionar fórum", $FORUM_TYPES, "yes");
        }

        $this->add_action_buttons();
    }
}