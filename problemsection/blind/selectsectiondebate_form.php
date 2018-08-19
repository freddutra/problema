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

        $FORUM_TYPES = array();
        $getsectionsfromcourse = $DB->get_records('course_sections', array('course'=>$courseid));
        foreach($getsectionsfromcourse as $getsectiondata){
            if($getsectiondata->name != null) {$FORUM_TYPES[$getsectiondata->id] = $getsectiondata->name;}
            //$FORUM_TYPES[$getsectiondata->id] = $getsectiondata->name;
        }
        $mform->addElement('select', 'coursesectionid', get_string('sectionid', 'local_problemsection'), $FORUM_TYPES, "yes");
        
        $this->add_action_buttons();
    }
}