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

use \mod_uploadavatar\gallery as gallery;

/**
 * Module instance settings form
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_uploadavatar_gallery_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mg = $this->_customdata['uploadavatar'];
        $gallery = $this->_customdata['gallery'];
        $groupmode = $this->_customdata['groupmode'];
        $groups = $this->_customdata['groups'];
        $context = $this->_customdata['context'];
        $tags = $this->_customdata['tags'];

        $lockfields = false;
        if ($gallery && $gallery->mode == 'thebox' && !$gallery->is_thebox_creator_or_agent()) {
            $lockfields = true;
        }

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('galleryname', 'uploadavatar'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'uploadavatarname', 'uploadavatar');

        if ($groupmode != NOGROUPS || $groupmode === 'aag') {
            if (count($groups) > 1) {
                $opts = array();
                $counts = $mg->get_group_gallery_counts();
                foreach ($groups as $group) {
                    $manage = has_capability('mod/uploadavatar:manage', $context);
                    if (!isset($counts[$group->id])
                        || $counts[$group->id]->count < $mg->maxgalleries || $mg->maxgalleries == 0 || $manage) {
                        $opts[$group->id] = $group->name;
                    }
                }
                $mform->addElement('select', 'groupid', get_string('group'), $opts);
                $mform->addHelpButton('groupid', 'group', 'uploadavatar');
            } else {
                $groupkeys = array_keys($groups);
                $groupid = !empty($groupkeys) ? $groupkeys[0] : 0;
                $mform->addElement('hidden', 'groupid', $groupid);
                $mform->setType('groupid', PARAM_INT);
            }
        }

        $mform->addElement('hidden', 'm', $mg->id);
        $mform->setType('m', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'source', 'moodle');
        $mform->setType('source', PARAM_ALPHA);
        $mform->hardFreeze('source');

        $this->add_action_buttons();
    }

    /**
     * Pre-process form data.
     *
     * @param array $toform
     * @return void
     */
    public function data_preprocessing(&$toform) {
        $toform['galleryviewoptions'] = array();
        $toform['galleryviewoptions']['carousel'] = $toform['carousel'];
        $toform['galleryviewoptions']['grid'] = $toform['grid'];
    }

    /**
     * Set the forms data.
     *
     * @param array $data
     * @return void
     */
    public function set_data($data) {
        if (!empty($data->mode)) {
            $this->_form->hardFreeze('mode');
            if ($data->mode == 'youtube') {
                $data->galleryfocus = \mod_uploadavatar\base::TYPE_VIDEO;
                $this->_form->freeze('galleryfocus');
            }
        }
        parent::set_data($data);
    }

    /**
     * Validate form input.
     *
     * @param array $data
     * @param array $files
     * @return array List of errors, if any.
     */
    public function validation($data, $files) {
        $errors = array();
        $collection = new \mod_uploadavatar\collection($data['m']);
        return $errors;
    }
}
