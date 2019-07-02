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
require_once(dirname(__FILE__).'/item_form.php');
// require_once(dirname(__FILE__).'/item_bulk_form.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot.'/repository/lib.php');

$g = optional_param('g', 0, PARAM_INT); // The gallery id.
$i = optional_param('i', 0, PARAM_INT); // An item id.
$bulk = optional_param('bulk', false, PARAM_BOOL);

if (!$g && !$i) {
    print_error('missingparameter');
}


$item = false;
if ($i) {
    $item = new \mod_uploadavatar\item($i);
    $g = $item->galleryid;
    if (!$item->user_can_edit()) {
        print_error('nopermissions', 'error', null, 'edit item');
    }
}

$gallery = new \mod_uploadavatar\gallery($g);
$uploadavatar = $gallery->get_collection();
$course = $DB->get_record('course', array('id' => $uploadavatar->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('uploadavatar', $uploadavatar->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$pageurl = new moodle_url('/mod/uploadavatar/item.php', array('g' => $gallery->id));
if (!$gallery->user_can_contribute()) {
    print_error('nopermissions', 'error', $pageurl, 'edit gallery');
}

$PAGE->set_url($pageurl);
$PAGE->set_title(format_string($uploadavatar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('uploadavatar-mode-'.$gallery->mode);

if ($gallery) {
    $pageurl = new moodle_url('/mod/uploadavatar/view.php', array('g' => $g));

    $navnode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
    if (empty($navnode)) {
        $navnode = $PAGE->navbar;
    }
    $node = $navnode->add(format_string($gallery->name), $pageurl);
    $node->make_active();
}

$fmoptions = uploadavatar_filepicker_options($gallery);

$formclass = $bulk ? 'mod_uploadavatar_item_bulk_form' : 'mod_uploadavatar_item_form';
$tags = \mod_uploadavatar\item::get_tags_possible();
$mform = new $formclass(null,
    array('gallery' => $gallery, 'firstitem' => !$gallery->has_items(), 'tags' => $tags, 'item' => $item));

$fs = get_file_storage();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id, 'editing' => 1)));
} else if ($data = $mform->get_data()) {
    if ($bulk) {
        $draftid = file_get_submitted_draft_itemid('content');
        $files = $fs->get_area_files(context_user::instance($USER->id)->id, 'user', 'draft', $draftid, 'id DESC', false);
        $storedfile = reset($files);
        \mod_uploadavatar\item::create_from_archive($gallery, $storedfile, $data);
    } else {
        $data->thumnail = 1;
        $data->display = 1;
        $data->description =  '';
        $data->galleryid = $gallery->id;

        if (!empty($data->id)) {
            $item = new \mod_uploadavatar\item($data->id);
            $item->update($data);
        } else {
            $item = \mod_uploadavatar\item::create($data);
        }

        if (!empty($data->content)) {
            $info = file_get_draft_area_info($data->content);
            file_save_draft_area_files($data->content, $context->id, 'mod_uploadavatar', 'item', $item->id, $fmoptions);

            $storedfile = null;
            $regenthumb = false;
            if ($gallery->galleryfocus != \mod_uploadavatar\base::TYPE_IMAGE && $gallery->mode != 'thebox') {
                $draftid = file_get_submitted_draft_itemid('customthumbnail');
                if ($files = $fs->get_area_files(
                    context_user::instance($USER->id)->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                    $storedfile = reset($files);
                    $regenthumb = true;
                }
            }
            // if ($gallery->mode != 'thebox') {
            //     $item->generate_image_by_type('lowres', $regenthumb, $storedfile);
            //     $item->generate_image_by_type('thumbnail', $regenthumb, $storedfile);
            // }
            $params = array(
                'context' => $context,
                'objectid' => $item->id,
                'other' => array(
                    'copyright_id' => $data->copyright_id,
                    'theme_id' => $data->theme_id,
                ),
            );
            $event = \mod_uploadavatar\event\item_updated::create($params);
            $event->add_record_snapshot('uploadavatar_item', $item->get_record());
            $event->trigger();
        }
    }

    redirect(new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id, 'editing' => 1)));
} else if ($item) {
    $data = $item->get_record();

    $draftitemid = file_get_submitted_draft_itemid('content');
    file_prepare_draft_area($draftitemid, $context->id, 'mod_uploadavatar', 'item', $data->id);

    if ($gallery->galleryfocus == \mod_uploadavatar\base::TYPE_AUDIO) {
        $draftitemidthumb = file_get_submitted_draft_itemid('customthumbnail');
        $data->customthumbnail = $draftitemidthumb;
    }

    $draftideditor = file_get_submitted_draft_itemid('description');
    $currenttext = file_prepare_draft_area($draftideditor, $context->id, 'mod_uploadavatar',
            'description', empty($data->id) ? null : $data->id,
            array('subdirs' => 0), empty($data->description) ? '' : $data->description);

    $data->content = $draftitemid;
    $data->description = array('text' => $currenttext,
                           'format' => editors_get_preferred_format(),
                           'itemid' => $draftideditor);

    $data->tags = $item->get_tags();
    $mform->set_data($data);
}

$maxitems = $uploadavatar->maxitems;
if (!$item && $maxitems != 0 && count($gallery->get_items()) >= $maxitems) {
    print_error('errortoomanyitems', 'uploadavatar', '', $maxitems);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
