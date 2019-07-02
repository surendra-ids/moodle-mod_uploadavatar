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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/gallery_form.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // A gallery id.
$m = optional_param('m', 0, PARAM_INT); // The uploadavatar id.
$g = optional_param('g', $id, PARAM_INT); // A gallery id.

if (!$m && !$g) {
    print_error('missingparameter');
}

$gallery = false;
if ($g) {
    $gallery = new \mod_uploadavatar\gallery($g);
    $m = $gallery->instanceid;
}

$uploadavatar = $DB->get_record('uploadavatar', array('id' => $m), '*', MUST_EXIST);
$uploadavatar = new \mod_uploadavatar\collection($uploadavatar);
$course = $DB->get_record('course', array('id' => $uploadavatar->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('uploadavatar', $uploadavatar->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$maxgalleries = $uploadavatar->maxgalleries;
if (!$gallery && !$uploadavatar->user_can_add_children()) {
    print_error('errortoomanygalleries', 'uploadavatar', '', $maxgalleries);
}

$pageurl = new moodle_url('/mod/uploadavatar/gallery.php', array('m' => $uploadavatar->id));
$PAGE->set_url($pageurl);
$PAGE->set_title(format_string($uploadavatar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$groupmode = groups_get_activity_groupmode($cm);
if (has_capability('moodle/site:accessallgroups', $context) && $groupmode != NOGROUPS) {
    $groupmode = 'aag';
    $groups = groups_get_all_groups($cm->course, null, $cm->groupingid);
} else {
    $groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
}

$tags = \mod_uploadavatar\gallery::get_tags_possible();
$mform = new mod_uploadavatar_gallery_form(null, array('uploadavatar' => $uploadavatar,
    'groups' => $groups, 'groupmode' => $groupmode, 'context' => $context, 'tags' => $tags, 'gallery' => $gallery));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uploadavatar/view.php', array('m' => $uploadavatar->id, 'editing' => 1)));
} else if ($data = $mform->get_data()) {
        $data->contributable = 1; //everybosy can contribute in the gallery
    if (!empty($data->id)) {
        $gallery = new \mod_uploadavatar\gallery($data->id);
        $gallery->update($data);
    } else {
        $data->instanceid = $data->m;
        unset($data->m);
        $data->userid = $USER->id;

        if ($uploadavatar->enforcedefaults) {
            $data->galleryfocus = $uploadavatar->galleryfocus;
            $data->gridcolumns = $uploadavatar->gridcolumns;
            $data->gridrows = $uploadavatar->gridrows;
            if ($uploadavatar->grid && !$uploadavatar->carousel) {
                $data->galleryview = \mod_uploadavatar\gallery::VIEW_GRID;
            } else if (!$uploadavatar->grid && $uploadavatar->carousel) {
                $data->galleryview = \mod_uploadavatar\gallery::VIEW_CAROUSEL;
            }
        }

        $gallery = \mod_uploadavatar\gallery::create($data);
    }
    redirect(new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id, 'editing' => 1)));
} else if ($gallery) {
    if (!$gallery->user_can_edit()) {
        print_error('nopermissions', 'error', $pageurl, 'edit gallery');
    }
    $data = $gallery->get_record();
    $data->tags = $gallery->get_tags();
    foreach ($gallery->get_display_settings() as $key => $value) {
        $data->$key = $value;
    }
    $mform->set_data($data);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
