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

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/uploadavatar/locallib.php');

/**
 * Module instance settings form
 *
 * @copyright  NetSpot Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_uploadavatar_mod_form extends moodleform_mod {

    /**
     * @var stdClass The course record.
     */
    protected $course = null;

    /**
     * Initialise activity form.
     *
     * @param mixed $current
     * @param mixed $section
     * @param mixed $cm
     * @param mixed $course
     * @return void
     */
    public function __construct($current, $section, $cm, $course) {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $config = get_config('uploadavatar');

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('uploadavatarname', 'uploadavatar'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'uploadavatarname', 'uploadavatar');

        $this->standard_intro_elements();
        $opts = array(
            'standard' => get_string('modestandard', 'mod_uploadavatar'),
        );
        if (!empty($config->disablestandardgallery) && (empty($this->_instance) || $this->current->mode == 'thebox') && count($opts) > 1) {
            unset($opts['standard']);
        }

        if (count($opts) == 1) {
            $key = key($opts);
            $mform->addElement('hidden', 'mode', $key);
            $mform->setType('mode', PARAM_ALPHA);
            $mform->hardFreeze('mode');
        } else {
            $mform->addElement('select', 'mode', get_string('collmode', 'mod_uploadavatar'), $opts);
            $mform->addHelpButton('mode', 'collmode', 'uploadavatar');
            $mform->disabledIf('mode', 'instance', 'neq', '' );
        }


        $options = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes, 0, $config->maxbytes);
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'uploadavatar'), $options);
        $mform->setDefault('maxbytes', $config->maxbytes);

        $options = array(
            'peerreviewed' => get_string('colltypepeerreviewed', 'uploadavatar'),
        );
        $mform->addElement('select', 'colltype', get_string('colltype', 'uploadavatar'), $options);
        $mform->addHelpButton('colltype', 'colltype', 'uploadavatar');

        $numbers = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 20, 30, 40, 50, 100);
        $options = array_merge(array(0 => get_string('unlimited')), $numbers);
        $mform->addElement('select', 'maxitems', get_string('maxitems', 'uploadavatar'), $options);
        $mform->setType('maxitems', PARAM_INT);
        $mform->setDefault('maxitems', 0);
        $mform->addHelpButton('maxitems', 'maxitems', 'uploadavatar');
        $mform->disabledIf('maxitems', 'colltype', 'eq', 'instructor');
        $mform->disabledIf('maxitems', 'colltype', 'eq', 'single');

        // $options = array(
        //     0 => get_string('unlimited'),
        //     '-1' => 0,
        //     1 => 1,
        //     2 => 2,
        //     3 => 3,
        //     4 => 4,
        //     5 => 5,
        //     6 => 6,
        //     7 => 7,
        //     8 => 8,
        //     9 => 9,
        //     10 => 10,
        //     20 => 20,
        //     30 => 30,
        //     40 => 40,
        //     50 => 50,
        //     100 => 100,
        // );
        // $mform->addElement('select', 'maxgalleries', get_string('maxgalleries', 'uploadavatar'), $options);
        // $mform->setType('maxgalleries', PARAM_INT);
        // $mform->setDefault('maxgalleries', 1);
        // $mform->addHelpButton('maxgalleries', 'maxgalleries', 'uploadavatar');
        // $mform->disabledIf('maxgalleries', 'colltype', 'eq', 'instructor');
        // $mform->disabledIf('maxgalleries', 'colltype', 'eq', 'single');

        // if ($CFG->usecomments) {
        //     $mform->addElement('selectyesno', 'allowcomments', get_string('allowcomments', 'uploadavatar'));
        //     $mform->setDefault('allowcomments', 1);
        //     $mform->addHelpButton('allowcomments', 'allowcomments', 'uploadavatar');
        // }

        // $mform->addElement('selectyesno', 'allowlikes', get_string('allowlikes', 'uploadavatar'));
        // $mform->setDefault('allowlikes', 1);
        // $mform->addHelpButton('allowlikes', 'allowlikes', 'uploadavatar');

        // Display settings.
        // $mform->addElement('header', 'display', get_string('settingsdisplay', 'uploadavatar'));

        // $options = array(
        //     0 => get_string('showall', 'uploadavatar'),
        //     10 => 10,
        //     25 => 25,
        //     50 => 50,
        //     100 => 100,
        //     200 => 200,
        // );
        // $mform->addElement('select', 'thumbnailsperpage', get_string('thumbnailsperpage', 'uploadavatar'), $options);

        // $options = array(2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6);
        // $mform->addElement('select', 'thumbnailsperrow', get_string('thumbnailsperrow', 'uploadavatar'), $options);

        $mform->addElement('selectyesno', 'displayfullcaption', get_string('displayfullcaption', 'uploadavatar'));

        $options = array(
            \mod_uploadavatar\base::POS_BOTTOM => get_string('bottom', 'uploadavatar'),
            \mod_uploadavatar\base::POS_TOP => get_string('top', 'uploadavatar'),
        );
        $mform->addElement('select', 'captionposition', get_string('captionposition', 'uploadavatar'), $options);

        $mform->addElement('hidden', 'source', 'moodle');
        $mform->setType('source', PARAM_ALPHA);
        $mform->hardFreeze('source');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Preprocess form data. Some of our data needs to be structured to match what a moodleform expects.
     *
     * @param array $toform
     * @return void
     */
    public function data_preprocessing(&$toform) {
        $toform['galleryviewoptions'] = array();
        $toform['galleryviewoptions']['carousel'] = isset($toform['carousel']) ? $toform['carousel'] : 1;
        $toform['galleryviewoptions']['grid'] = isset($toform['grid']) ? $toform['grid'] : '';

        $toform['gallerytypeoptions'] = array();
        $toform['gallerytypeoptions']['focus'] = \mod_uploadavatar\base::TYPE_IMAGE;
        if (isset($toform['galleryfocus'])) {
            $toform['gallerytypeoptions']['focus'] = $toform['galleryfocus'];
        }
    }

    /**
     * Set the form data.
     *
     * @param mixed $data Set the form data.
     * @return void
     */
    public function set_data($data) {
        if (!empty($data->id)) {
            $collection = new \mod_uploadavatar\collection($data);
            $data->mctags = $collection->get_tags();
            if ($collection->count_galleries() && $collection->is_assessable()) {
                $this->_form->hardFreeze('colltype');
            }
        }
        parent::set_data($data);
    }

}
