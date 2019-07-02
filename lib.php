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
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function uploadavatar_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_RATE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_PLAGIARISM:
            return false;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the uploadavatar into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $uploadavatar An object from the form in mod_form.php
 * @param mod_uploadavatar_mod_form $mform
 * @return int The id of the newly inserted uploadavatar record
 */
function uploadavatar_add_instance(stdClass $uploadavatar, mod_uploadavatar_mod_form $mform = null) {
    global $DB, $USER;

    $uploadavatar = uploadavatar_formfield_transform($uploadavatar);
    $uploadavatar->timecreated = time();
    $uploadavatar->userid = $USER->id;

    $uploadavatar->id = $DB->insert_record('uploadavatar', $uploadavatar);

    if (isset($uploadavatar->mctags)) {
        $collection = new \mod_uploadavatar\collection($uploadavatar);
        $collection->set_tags($uploadavatar->mctags);
    }

    return $uploadavatar->id;
}

/**
 * Updates an instance of the uploadavatar in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $uploadavatar An object from the form in mod_form.php
 * @param mod_uploadavatar_mod_form $mform
 * @return boolean Success/Fail
 */
function uploadavatar_update_instance(stdClass $uploadavatar, mod_uploadavatar_mod_form $mform = null) {
    global $DB, $USER;
    if (empty($uploadavatar->enforcedefaults)) {
        $uploadavatar->enforcedefaults = 0;
    }

    // We never change the mode once set.
    unset($uploadavatar->mode);

    $uploadavatar = uploadavatar_formfield_transform($uploadavatar);
    $uploadavatar->timemodified = time();
    $uploadavatar->id = $uploadavatar->instance;

    $result = $DB->update_record('uploadavatar', $uploadavatar);

    $collection = new \mod_uploadavatar\collection($uploadavatar);
    $collection->set_tags($uploadavatar->mctags);

    return $result;
}

/**
 * Transform the optgroups in the form to the relevant format for storing in the DB.
 *
 * @param stdClass $uploadavatar An object from the form in mod_form.php
 */
function uploadavatar_formfield_transform(stdClass $uploadavatar) {
    $uploadavatar->carousel = 0;
    $uploadavatar->grid = 0;
    $uploadavatar->galleryfocus = 1; // Locallib not necessairily included here.
    if (isset($uploadavatar->galleryviewoptions['carousel'])) {
        $uploadavatar->carousel = $uploadavatar->galleryviewoptions['carousel'];
    }
    if (isset($uploadavatar->galleryviewoptions['grid'])) {
        $uploadavatar->grid = $uploadavatar->galleryviewoptions['grid'];
    }
    unset($uploadavatar->galleryviewoptions);

    if (isset($uploadavatar->gallerytypeoptions['focus'])) {
        $uploadavatar->galleryfocus = $uploadavatar->gallerytypeoptions['focus'];
    }
    unset($uploadavatar->gallerytypeoptions);

    if (!isset($uploadavatar->colltype)) {
        $uploadavatar->colltype = 'peerreviewed';
    }
    if ($uploadavatar->colltype == 'instructor') {
        $uploadavatar->maxitems = 0;
        $uploadavatar->maxgalleries = 0;
    }

    return $uploadavatar;
}

/**
 * Removes an instance of the uploadavatar from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function uploadavatar_delete_instance($id) {
    global $DB;

    if (! $uploadavatar = $DB->get_record('uploadavatar', array('id' => $id))) {
        return false;
    }

    $collection = new \mod_uploadavatar\collection($uploadavatar);
    $collection->delete();

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $uploadavatar
 * @return stdClass|null
 */
function uploadavatar_user_outline($course, $user, $mod, $uploadavatar) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $uploadavatar the module instance record
 * @return void, is supposed to echp directly
 */
function uploadavatar_user_complete($course, $user, $mod, $uploadavatar) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in uploadavatar activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param object $course
 * @param bool $viewfullnames capability
 * @param int $timestart
 * @return boolean
 */
function uploadavatar_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link uploadavatar_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @return void adds items into $activities and increases $index
 */
function uploadavatar_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0) {
}

/**
 * Prints single activity item prepared by uploadavatar_get_recent_mod_activity.
 *
 * @see uploadavatar_get_recent_mod_activity()
 * @param stdClass $activity
 * @param int $courseid
 * @param bool $detail
 * @param array $modnames
 * @param object $viewfullnames
 * @return void
 */
function uploadavatar_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function uploadavatar_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 */
function uploadavatar_get_extra_capabilities() {
    return array();
}

// Gradebook API.

/**
 * Is a given scale used by the instance of uploadavatar?
 *
 * This function returns if a scale is being used by one uploadavatar
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $uploadavatarid ID of an instance of this module
 * @param int $scaleid
 * @return bool true if the scale is used by the given uploadavatar instance
 */
function uploadavatar_scale_used($uploadavatarid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of uploadavatar.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid
 * @return boolean true if the scale is used by any uploadavatar instance
 */
function uploadavatar_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Creates or updates grade item for the give uploadavatar instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $uploadavatar instance object with extra cmidnumber and modname property
 * @return void
 */
function uploadavatar_grade_item_update(stdClass $uploadavatar) {
    global $CFG;
    return;
}

/**
 * Update uploadavatar grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $uploadavatar instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function uploadavatar_update_grades(stdClass $uploadavatar, $userid = 0) {
    global $CFG, $DB;
    return;
}

// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function uploadavatar_get_file_areas($course, $cm, $context) {
    return array(
        'gallery' => new lang_string('areagallery', 'mod_uploadavatar'),
        'item' => new lang_string('areaitem', 'mod_uploadavatar'),
        'lowres' => new lang_string('arealowres', 'mod_uploadavatar'),
        'thumbnail' => new lang_string('areathumbnail', 'mod_uploadavatar'),
    );
}

/**
 * File browsing support for uploadavatar file areas
 *
 * @package mod_uploadavatar
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function uploadavatar_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    // Moodle <2.6 needs us to require these files manually.
    require_once($CFG->dirroot.'/mod/uploadavatar/classes/file_info.php');
    require_once($CFG->dirroot.'/mod/uploadavatar/classes/file_info_area_gallery.php');

    $urlbase = $CFG->wwwroot . '/pluginfile.php';
    // When itemid is null, we're browsing. Browsing is only supported for getting lists of gallery's and item's.
    if (is_null($itemid)) {
        if ($filearea == 'thumbnail' || $filearea == 'lowres') {
            return null;
        }
        return new mod_uploadavatar_file_info($browser, $course, $cm, $context, $areas, $filearea);
    }

    // Get the list of files within the gallery.
    if ($filearea == 'gallery') {
        $storedfile = new virtual_root_file($context->id, 'mod_uploadavatar', 'gallery', $itemid);
        return new mod_uploadavatar_file_info_area_gallery($browser, $context, $storedfile, $urlbase,
            null, true, true, true, false);
    }

    // If we've gotten to here, we're after a specific file.
    $fs = get_file_storage();

    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!$storedfile = $fs->get_file($context->id, 'mod_uploadavatar', $filearea, $itemid, $filepath, $filename)) {
        if ($filepath === '/' and $filename === '.') {
            $storedfile = new virtual_root_file($context->id, 'mod_uploadavatar', 'collection', 0);
        } else {
            return null;
        }
    }

    return new file_info_stored($browser, $context, $storedfile, $urlbase, $filearea, $itemid, true, true, false);
}

/**
 * Serves the files from the uploadavatar file areas
 *
 * @package mod_uploadavatar
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the uploadavatar's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function uploadavatar_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    $areas = uploadavatar_get_file_areas($course, $cm, $context);
    if (!isset($areas[$filearea])) {
        return false;
    }

    $itemid = (int)array_shift($args);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_uploadavatar/$filearea/$itemid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options); // Download MUST be forced - security!
}

/**
 * Validate comment parameter before perform other comments actions
 *
 * @param stdClass $commentparam {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return boolean
 */
function uploadavatar_comment_validate($commentparam) {
    if ($commentparam->commentarea != 'gallery' && $commentparam->commentarea != 'item') {
        throw new comment_exception('invalidcommentarea');
    }
    if ($commentparam->itemid == 0) {
        throw new comment_exception('invalidcommentitemid');
    }
    return true;
}

/**
 * Running addtional permission check on plugins
 *
 * @package  mod_uploadavatar
 * @category comment
 *
 * @param stdClass $args
 * @return array
 */
function uploadavatar_comment_permissions($args) {
    return array('post' => true, 'view' => true);
}

/**
 * Validate comment data before displaying comments
 *
 * @package  mod_uploadavatar
 * @category comment
 *
 * @param stdClass $comments
 * @param stdClass $args
 * @return boolean
 */
function uploadavatar_comment_display($comments, $args) {
    if ($args->commentarea != 'gallery' && $args->commentarea != 'item') {
        throw new comment_exception('invalidcommentarea');
    }
    if ($args->itemid == 0) {
        throw new comment_exception('invalidcommentitemid');
    }
    return $comments;
}
