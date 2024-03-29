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
define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/uploadavatar/locallib.php');

$class = required_param('class', PARAM_ALPHA);
$m = required_param('m', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHAEXT);
$data = optional_param_array('data', null, PARAM_RAW);

$PAGE->set_url('/mod/uploadavatar/rest.php', array('id' => $id, 'class' => $class, 'm' => $m));

$uploadavatar = $DB->get_record('uploadavatar', array('id' => $m), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $uploadavatar->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('uploadavatar', $uploadavatar->id, $course->id, false, MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$requestmethod = $_SERVER['REQUEST_METHOD'];

echo $OUTPUT->header();

$classname = "\\mod_uploadavatar\\{$class}";

if (!empty($id)) {
    $object = new $classname($id);
}

switch($requestmethod) {

    case 'POST':
        if ($action == 'sortorder') {
            $object->update_sortorder($data);
        } else if ($action == 'sample') {
            $info = $object->copy($data[0]);
            echo json_encode($info);
        }
    break;

    case 'DELETE':
        $success = false;
        $isowner = $object->is_thebox_creator_or_agent();

        if ($class == 'collection') {
            if (!has_capability('mod/uploadavatar:manage', $object->get_context()) && !$object->user_can_remove()) {
                throw new moodle_exception("no permission");
            }
            $success = true;
        } else {
            if (empty($object->objectid)) {
                // Non-box.
                if ($object->user_can_remove() && $object->delete()) {
                    $success = true;
                }
            } else {
                $success = true;
            }

        }
        if ($success) {
            echo json_encode('success');
        } else {
            throw new moodle_exception("failed to delete $class $id");
        }
    break;
}
