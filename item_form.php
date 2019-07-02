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

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/uploadavatar/locallib.php');
require_once($CFG->dirroot.'/mod/uploadavatar/classes/quickform/limitedurl.php');
require_once($CFG->dirroot.'/mod/uploadavatar/classes/quickform/uploader.php');
require_once($CFG->dirroot.'/mod/uploadavatar/classes/quickform/uploader_standard.php');

/**
 * Module instance settings form.
 *
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_uploadavatar_item_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $gallery = $this->_customdata['gallery'];
        $tags = $this->_customdata['tags'];
        $item = $this->_customdata['item'];

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'caption', get_string('caption', 'uploadavatar'), array('size' => '64'));
        $mform->setType('caption', PARAM_TEXT);
        $mform->addRule('caption', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('caption', 'caption', 'uploadavatar');

        $options = array(
            'collapsed' => true,
            'maxfiles' => 0,
            'return_types' => null,
        );
            $mform->addElement('filepicker', 'content', get_string('content', 'uploadavatar'), '0',
                uploadavatar_filepicker_options($gallery));
            $mform->addHelpButton('content', 'content', 'uploadavatar');

            $fpoptions = uploadavatar_filepicker_options($gallery);
            $fpoptions['accepted_types'] = array('web_image');
            $fpoptions['return_types'] = FILE_INTERNAL;

        $lockfields = $item && !$item->user_can_edit() ? true : false;

        // uploadavatar_add_tag_field($mform, $tags, false, !$lockfields);

        if ($lockfields) {
            $mform->hardFreeze('caption');
            $mform->hardFreeze('description');
            $mform->hardFreeze('tags');
        }

        // // Advanced settings.
        // $mform->addElement('header', 'advanced', get_string('advanced'));
        // uploadavatar_add_metainfo_fields($mform);

        $mform->addElement('hidden', 'g', $gallery->id);
        $mform->setType('g', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'source', 'moodle');
        $mform->setType('source', PARAM_ALPHA);
        $mform->hardFreeze('source');

        $mform->addElement('hidden', 'extpath', '');
        $mform->setType('extpath', PARAM_RAW);

        $mform->addElement('hidden', 'theme_id', '');
        $mform->setType('theme_id', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'copyright_id', '');
        $mform->setType('copyright_id', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'objectid', '');
        $mform->setType('objectid', PARAM_ALPHANUMEXT);

        $this->add_action_buttons();
    }

    /**
     * Validate user input.
     *
     * @param mixed $data The submitted form data.
     * @param mixed $files The submitted files.
     * @return array List of errors, if any.
     */
    public function validation($data, $files) {
        global $CFG;
        $errors = parent::validation($data, $files);
        $info = isset($data['content']) ? file_get_draft_area_info($data['content']) : array('filecount' => 0);
        $url = isset($data['externalurl']) ? trim($data['externalurl']) : '';

        if (empty($data['externalurl']) && $info['filecount'] == 0 && empty($data['objectid'])) {
            $errors['filecheck'] = get_string('required');
        } else if (!empty($url) && !preg_match('|^/|', $url)) {
            // Links relative to server root are ok - no validation necessary.
            if (preg_match('|^[a-z]+://|i', $url) or preg_match('|^https?:|i', $url) or preg_match('|^ftp:|i', $url)) {
                // Normal URL.
                if (!uploadavatar_appears_valid_url($url)) {
                    $errors['externalurl'] = get_string('invalidurl', 'url');
                }
            } else if (!preg_match('|^[a-z]+:|i', $url)) {
                // The preg_match above has us skip general URI such as
                // teamspeak, mailto, etc. - it may or may not work in all
                // browsers. We do not validate these at all, sorry.

                // Invalid URI, we try to fix it by adding 'http://' prefix.
                // Relative links are NOT allowed because we display the link on different pages!
                require_once($CFG->dirroot."/mod/url/locallib.php");
                if (!url_appears_valid_url('http://'.$url)) {
                    $errors['externalurl'] = get_string('invalidurl', 'url');
                }
            }
        }
        return $errors;
    }

}
