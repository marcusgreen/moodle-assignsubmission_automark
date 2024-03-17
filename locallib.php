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
 * Main class for Automark submission plugin
 *
 * @package    assignsubmission_automark
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_automark extends assign_submission_plugin {

    /**
     * Should return the name of this plugin type.
     *
     * @return string - the name
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_automark');
    }

    /**
     * Remove all data stored in this plugin that is associated with the given submission.
     *
     * @param stdClass $submission record from assign_submission table
     * @return boolean
     */
    public function remove(stdClass $submission) {
        global $DB;

        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $DB->delete_records('assignsubmission_automark', ['submission' => $submissionid]);
        }
        return true;
    }

    /**
     * Add form elements for settings
     *
     * @param null|stdClass $submission record from assign_submission table or null if it is a new submission
     * @param MoodleQuickForm $mform
     * @param stdClass $data form data that can be modified
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $DB;
        $mform->addElement('text', 'automark', $this->get_name());
        $mform->setType('automark', PARAM_TEXT);
        if ($submission) {
            $currentsubmission = $DB->get_record('assignsubmission_automark', ['submission' => $submission->id]);
            $data->automark = $currentsubmission ? $currentsubmission->value : '';
        }
        return true;
    }

    /**
     * Save data to the database and trigger plagiarism plugin,
     * if enabled, to scan the uploaded content via events trigger
     *
     * @param stdClass $submission record from assign_submission table
     * @param stdClass $data data from the form
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $currentsubmission = $DB->get_record('assignsubmission_automark', ['submission' => $submission->id]);

        if ($currentsubmission) {
            $currentsubmission->value = $data->automark;
            $updatestatus = $DB->update_record('assignsubmission_automark', $currentsubmission);
            // TODO trigger event if applicable.
            return $updatestatus;
        } else {
            $currentsubmission = (object)[
                'value' => $data->automark,
                'submission' => $submission->id,
                'assignment' => $this->assignment->get_instance()->id,
            ];
            $currentsubmission->id = $DB->insert_record('assignsubmission_automark', $currentsubmission);
            // TODO trigger event if applicable.
            return $currentsubmission->id > 0;
        }
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data data from the form
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        return trim($data->automark ?? '') === '';
    }

    /**
     * Is this assignment plugin empty? (ie no submission or feedback)
     *
     * @param stdClass $submission record from assign_submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        global $DB;
        $currentsubmission = $DB->get_record('assignsubmission_automark', ['submission' => $submission->id]);
        return !$currentsubmission || trim($currentsubmission->value ?? '') === '';
    }

    /**
     * Display value in the submission status table
     *
     * @param stdClass $submission record from assign_submission table
     * @param bool $showviewlink Modifed to return whether or not to show a link to the full submission/feedback
     * @return string
     */
    public function view_summary(stdClass $submission, &$showviewlink) {
        global $DB;
        $currentsubmission = $DB->get_record('assignsubmission_automark', ['submission' => $submission->id]);
        return $currentsubmission ? s($currentsubmission->value) : '';
    }

    /**
     * Return a description of external params suitable for uploading an feedback comment from a webservice.
     *
     * Used in WebService mod_assign_save_submission
     *
     * @return array
     */
    public function get_external_parameters() {
        global $CFG;
        require_once($CFG->dirroot . '/lib/externallib.php');

        return ['automark' => new external_value(PARAM_RAW, 'The value for this submission.')];
    }
}
