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
 * Renderers for uploadavatar.
 *
 * @package    mod_uploadavatar
 * @author     Developed by IDS Logic
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_uploadavatar_activity_task
 */

/**
 * Structure step to restore one uploadavatar activity
 */
class restore_uploadavatar_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $uploadavatar = new restore_path_element('uploadavatar', '/activity/uploadavatar');
        $paths[] = $uploadavatar;

        $gallery = new restore_path_element('uploadavatar_gallery', '/activity/uploadavatar/gallerys/gallery');
        $paths[] = $gallery;

        $item = new restore_path_element('uploadavatar_item', '/activity/uploadavatar/gallerys/gallery/items/item');
        $paths[] = $item;

        if ($userinfo) {
            $userfeedback = new restore_path_element('uploadavatar_userfeedback',
                '/activity/uploadavatar/gallerys/gallery/items/item/userfeedback/feedback');
            $paths[] = $userfeedback;
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_uploadavatar($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (isset($data->gallerytype)) {
            $types = explode(',', $data->gallerytype);
            $focus = !empty($types) ? $types[0] : \mod_uploadavatar\collection::TYPE_IMAGE;
            if (empty($focus)) {
                $focus = \mod_uploadavatar\collection::TYPE_IMAGE;
            }
            $data->galleryfocus = $focus;
        }
        // Insert the uploadavatar record.
        $newitemid = $DB->insert_record('uploadavatar', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_uploadavatar_userfeedback($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->itemid = $this->get_new_parentid('uploadavatar_item');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $newfeedbackid = $DB->insert_record('uploadavatar_userfeedback', $data);
    }

    protected function process_uploadavatar_gallery($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->instanceid = $this->get_new_parentid('uploadavatar');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        if (isset($data->gallerytype)) {
            $data->galleryfocus = $data->gallerytype;
        }
        $newitemid = $DB->insert_record('uploadavatar_gallery', $data);
        $this->set_mapping('uploadavatar_gallery', $oldid, $newitemid);
    }

    protected function process_uploadavatar_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->galleryid = $this->get_new_parentid('uploadavatar_gallery');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (isset($data->collection)) {
            $data->reference = $data->collection;
        }
        $newitemid = $DB->insert_record('uploadavatar_item', $data);
        $this->set_mapping('uploadavatar_item', $oldid, $newitemid, true);
    }

    protected function after_execute() {
        global $DB;

        // Can't do thumbnail mapping before the item is restored, so we do it here.
        $mgid = $this->task->get_activityid();
        if ($records = $DB->get_records('uploadavatar_gallery', array('instanceid' => $mgid))) {
            foreach ($records as $record) {
                if ($record->thumbnail) {
                    $record->thumbnail = $this->get_mappingid('uploadavatar_item', $record->thumbnail);
                    $DB->update_record('uploadavatar_gallery', $record);
                }
            }
        }
        $this->add_related_files('mod_uploadavatar', 'intro', null);
        $this->add_related_files('mod_uploadavatar', 'item', 'uploadavatar_item');
        $this->add_related_files('mod_uploadavatar', 'lowres', 'uploadavatar_item');
        $this->add_related_files('mod_uploadavatar', 'thumbnail', 'uploadavatar_item');
    }
}
