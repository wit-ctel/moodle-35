<?php
namespace mod_turnitintool\event;
defined('MOODLE_INTERNAL') || die();
class add_turnitintool extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['level'] = self::LEVEL_PARTICIPATING; // For 2.6, this appears to have been renamed to 'edulevel' in 2.7
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'turnitintool';
    }

    public static function get_name() {
        return get_string('add_turnitintool', 'mod_turnitintool');
    }

    public function get_description() {
        return $this->other['desc'];
    }

    public function get_url() {
        return new \moodle_url('/mod/turnitintool/view.php', array( 'id' => $this->objectid));
    }

    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, "turnitintool", "add turnitintool", 'view.php?id='.$this->objectid, $this->other['desc'], $this->objectid);
    }

    public static function get_legacy_eventname() {
        // Override ONLY if you are migrating events_trigger() call.
        return 'MYPLUGIN_OLD_EVENT_NAME';
    }

    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['desc'])) {
            throw new \coding_exception('The \'desc\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}