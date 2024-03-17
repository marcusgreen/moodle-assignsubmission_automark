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
 * Provides the information to backup Automark submissions
 *
 * @package    assignsubmission_automark
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignsubmission_automark_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to submission element
     *
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

        // TODO: make sure the names of the elements are correct, "value" is just an example of a field name.
        $subpluginelement = new backup_nested_element('submission_automark', null, ['value', 'submission']);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // Set source to populate the data.
        $subpluginelement->set_source_table('assignsubmission_automark', ['submission' => backup::VAR_PARENTID]);

        // TODO: annotate files if necessary. Substitute 'fileareaname' with a correct filearea name.
        $subpluginelement->annotate_files('assignsubmission_automark', 'fileareaname', 'submission');

        return $subplugin;
    }
}
