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

namespace mod_uploadavatar\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the uploadavatar activity module.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin stores personal data.
    \core_privacy\local\metadata\provider,
    // This plugin is a core_user_data_provider.
    \core_privacy\local\request\plugin\provider,
    // This plugin has some sitewide user preferences to export.
    \core_privacy\local\request\user_preference_provider {

    use subcontext_info;

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items) : collection {

        $items->add_database_table(
            'uploadavatar',
            [
                'id' => 'privacy:metadata:uploadavatar:id',
                'name' => 'privacy:metadata:uploadavatar:name',
                'userid' => 'privacy:metadata:uploadavatar:userid',
            ],
            'privacy:metadata:uploadavatar'
        );

        $items->add_database_table(
            'uploadavatar_gallery',
            [
                'instanceid' => 'privacy:metadata:uploadavatar_gallery:instanceid',
                'name' => 'privacy:metadata:uploadavatar_gallery:name',
                'userid' => 'privacy:metadata:uploadavatar_gallery:userid',
                'groupid' => 'privacy:metadata:uploadavatar_gallery:groupid',
            ],
            'privacy:metadata:uploadavatar_gallery'
        );

        $items->add_database_table(
            'uploadavatar_item',
            [
                'galleryid' => 'privacy:metadata:uploadavatar_item:galleryid',
                'userid' => 'privacy:metadata:uploadavatar_item:userid',
                'caption' => 'privacy:metadata:uploadavatar_item:caption',
                'description' => 'privacy:metadata:uploadavatar_item:description',
                'moralrights' => 'privacy:metadata:uploadavatar_item:moralrights',
                'originalauthor' => 'privacy:metadata:uploadavatar_item:originalauthor',
                'productiondate' => 'privacy:metadata:uploadavatar_item:productiondate',
                'medium' => 'privacy:metadata:uploadavatar_item:medium',
                'publisher' => 'privacy:metadata:uploadavatar_item:publisher',
                'broadcaster' => 'privacy:metadata:uploadavatar_item:broadcaster',
                'reference' => 'privacy:metadata:uploadavatar_item:reference',
                'externalurl' => 'privacy:metadata:uploadavatar_item:externalurl',
                'timecreated' => 'privacy:metadata:uploadavatar_item:timecreated',
            ],
            'privacy:metadata:uploadavatar_item'
        );

        $items->add_database_table(
            'uploadavatar_userfeedback',
            [
                'itemid' => 'privacy:metadata:uploadavatar_userfeedback:itemid',
                'userid' => 'privacy:metadata:uploadavatar_userfeedback:userid',
                'liked' => 'privacy:metadata:uploadavatar_userfeedback:liked',
                'rating' => 'privacy:metadata:uploadavatar_userfeedback:rating',
            ],
            'privacy:metadata:uploadavatar_userfeedback'
        );

        $items->add_subsystem_link('core_files', [], 'privacy:metadata:core_files');
        $items->add_subsystem_link('core_comment', [], 'privacy:metadata:core_comments');
        $items->add_subsystem_link('core_tag', [], 'privacy:metadata:core_tag');

        $items->add_user_preference('mod_uploadavatar_mediasize', 'privacy:metadata:preference:mediasize');

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        // Fetch all uploadavatar comments.
        $sql = "SELECT c.id
                FROM {context} c
                JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                JOIN {modules} m ON m.id = cm.module AND m.name = 'uploadavatar'
                JOIN {uploadavatar} mg ON mg.id = cm.instance
                LEFT JOIN {uploadavatar_gallery} mgg ON mgg.instanceid = mg.id
                LEFT JOIN {uploadavatar_item} mgi ON mgi.galleryid = mgg.id
                LEFT JOIN {uploadavatar_userfeedback} mgu ON mgu.itemid = mgi.id
                LEFT JOIN {comments} comg ON comg.commentarea = 'gallery' AND comg.itemid = mgg.id
                LEFT JOIN {comments} comi ON comi.commentarea = 'item' AND comi.itemid = mgi.id
                WHERE mgg.userid = :userid1 OR mgi.userid = :userid2 OR mgu.userid = :userid3";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'userid1'      => $userid,
            'userid2'      => $userid,
            'userid3'      => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT c.id AS contextid,
                       mg.id,
                       cm.id AS cmid
                FROM {context} c
                JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                JOIN {modules} m ON m.id = cm.module AND m.name = 'uploadavatar'
                JOIN {uploadavatar} mg ON mg.id = cm.instance
                JOIN {uploadavatar_gallery} g ON g.instanceid = mg.id
                LEFT JOIN {uploadavatar_item} i ON i.galleryid = g.id
                LEFT JOIN {uploadavatar_userfeedback} u ON u.itemid = i.id AND u.userid = :userid1
                WHERE c.id {$contextsql} AND (
                    g.userid = :userid2 OR i.userid = :userid3 OR u.id IS NOT NULL
                )
                GROUP BY c.id, mg.id, cm.id";

        $params = [
            'userid1' => $user->id,
            'userid2' => $user->id,
            'userid3' => $user->id,
            'contextlevel' => CONTEXT_MODULE
        ] + $contextparams;
        $collections = $DB->get_recordset_sql($sql, $params);

        $mappings = [];

        foreach ($collections as $collection) {
            $mappings[$collection->id] = $collection->contextid;
            $context = \context_module::instance($collection->cmid);

            $data = helper::get_context_data($context, $user);
            writer::with_context($context)->export_data([], $data);
            helper::export_context_files($context, $user);
        }
        $collections->close();

        if (!empty($mappings)) {
            static::export_gallery_data($user->id, $mappings);
            static::export_all_items($user->id, $mappings);
        }
    }

    protected static function export_gallery_data(int $userid, array $mappings) {
        global $DB;
        // Find all galleries the user owns, or has added items to.
        list($collinsql, $collparams) = $DB->get_in_or_equal(array_keys($mappings), SQL_PARAMS_NAMED);

        $sql = "SELECT g.*
                FROM {uploadavatar} m
                JOIN {uploadavatar_gallery} g ON g.instanceid = m.id
                LEFT JOIN {uploadavatar_item} i ON i.galleryid = g.id
                LEFT JOIN {uploadavatar_userfeedback} u ON u.itemid = i.id AND u.userid = :userid1
                WHERE m.id {$collinsql} AND (
                    g.userid = :userid2 OR i.userid = :userid3 OR u.id IS NOT NULL
                )";

        $params = ['userid1' => $userid, 'userid2' => $userid, 'userid3' => $userid] + $collparams;
        $galleries = $DB->get_recordset_sql($sql, $params);

        foreach($galleries as $gallery) {
            $context = \context::instance_by_id($mappings[$gallery->instanceid]);

            $gallerydata = (object) [
                'name' => format_string($gallery->name, true),
                'creator_was_you' => transform::yesno($gallery->userid == $userid),
            ];

            $galleryarea = static::get_gallery_area($gallery);
            writer::with_context($context)
                ->export_data($galleryarea, $gallerydata);

            \core_tag\privacy\provider::export_item_tags($userid, $context, $galleryarea, 'mod_uploadavatar', 'gallery', $gallery->id);
        }
        $galleries->close();
    }

    protected static function export_all_items(int $userid, array $mappings) {
        global $DB;
        // Find all galleries the user owns, or has added items to.
        list($collinsql, $collparams) = $DB->get_in_or_equal(array_keys($mappings), SQL_PARAMS_NAMED);

        $sql = "SELECT g.id AS id,
                       m.id AS uploadavatarid,
                       g.name
                FROM {uploadavatar} m
                JOIN {uploadavatar_gallery} g ON g.instanceid = m.id
                LEFT JOIN {uploadavatar_item} i ON i.galleryid = g.id
                LEFT JOIN {uploadavatar_userfeedback} u ON u.itemid = i.id AND u.userid = :userid1
                WHERE m.id {$collinsql} AND (
                    g.userid = :userid2 OR i.userid = :userid3 OR u.id IS NOT NULL
                )
                GROUP BY m.id, g.id, g.name";
        $params = ['userid1' => $userid, 'userid2' => $userid, 'userid3' => $userid] + $collparams;
        $galleries = $DB->get_records_sql($sql, $params);
        foreach ($galleries as $gallery) {
            $context = \context::instance_by_id($mappings[$gallery->uploadavatarid]);
            static::export_all_items_in_gallery($userid, $context, $gallery);
        }
    }

    /**
     * Store all information about all item that we have detected this user to has uploaded or provided feedback to.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   \context    $context The instance of the uploadavatar context.
     * @param   \stdClass   $discussion The gallery whose data is being exported.
     */
    protected static function export_all_items_in_gallery($userid, $context, $gallery) {
        global $DB;
        $sql = "SELECT i.*
                FROM {uploadavatar_gallery} g
                JOIN {uploadavatar_item} i ON i.galleryid = g.id
                LEFT JOIN {uploadavatar_userfeedback} u ON u.itemid = i.id AND u.userid = :userid1
                WHERE g.id = :galleryid AND (
                    i.userid = :userid2 OR u.id IS NOT NULL
                )";
        $params = [
          'galleryid' => $gallery->id,
          'userid1' => $userid,
          'userid2' => $userid,
        ];
        $items = $DB->get_records_sql($sql, $params);

        $galleryarea = static::get_gallery_area($gallery);
        $itemdata = [];
        foreach ($items as $item) {
            $itemdata[] = static::export_item_data($userid, $context, $galleryarea, $item);
        }

        $itemarea = array_merge($galleryarea, static::get_item_area());
        writer::with_context($context)
            ->export_data($itemarea, (object)$itemdata);
    }

    /**
     * Export all data in the item.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   \context    $context The instance of the forum context.
     * @param   array       $galleryarea The subcontext of the gallery.
     * @param   \stdClass   $item The post structure and all of its children
     */
    protected static function export_item_data(int $userid, \context $context, $galleryarea, $item) {
        $itemdata = (object) [
            'id' => $item->id,
            'caption' => format_string($item->caption, true),
            'timecreated' => transform::datetime($item->timecreated),
            'creator_was_you' => transform::yesno($item->userid == $userid),
        ];

        $itemarea = array_merge($galleryarea, static::get_item_area());
        writer::with_context($context)
            ->export_area_files($itemarea, 'mod_uploadavatar', 'item', $item->id)
            ->export_area_files($itemarea, 'mod_uploadavatar', 'lowres', $item->id)
            ->export_area_files($itemarea, 'mod_uploadavatar', 'thumbnail', $item->id);

        \core_tag\privacy\provider::export_item_tags($userid, $context, $itemarea, 'mod_uploadavatar', 'item', $item->id);
        return $itemdata;
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

        $DB->delete_records_select('uploadavatar_userfeedback',
            "itemid IN (
                SELECT i.id
                FROM {uploadavatar_item} i
                JOIN {uploadavatar_gallery} g ON i.galleryid = g.id
                WHERE g.instanceid = :instanceid
            )", ['instanceid' => $instanceid]);

        $DB->delete_records_select('uploadavatar_item',
            "galleryid IN (
                SELECT id
                FROM {uploadavatar_gallery} g
                WHERE g.instanceid = :instanceid
            )", ['instanceid' => $instanceid]);

        $DB->delete_records('uploadavatar_gallery', ['instanceid' => $instanceid]);

        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_uploadavatar', 'item');
        $fs->delete_area_files($context->id, 'mod_uploadavatar', 'lowres');
        $fs->delete_area_files($context->id, 'mod_uploadavatar', 'thumbnail');

        \core_comment\privacy\provider::delete_comments_for_all_users($context, 'mod_uploadavatar', 'gallery');
        \core_comment\privacy\provider::delete_comments_for_all_users($context, 'mod_uploadavatar', 'item');

        \core_tag\privacy\provider::delete_item_tags($context, 'mod_uploadavatar', 'uploadavatar_gallery');
        \core_tag\privacy\provider::delete_item_tags($context, 'mod_uploadavatar', 'uploadavatar_item');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $fs = get_file_storage();

        $galleryidsql = "SELECT g.id
                         FROM {uploadavatar_gallery} g
                         WHERE userid = :userid AND instanceid = :instanceid";
        $itemidsql = "SELECT i.id
                      FROM {uploadavatar_item} i
                      WHERE userid = :userid AND galleryid IN (
                        SELECT id
                        FROM {uploadavatar_gallery} 
                        WHERE instanceid = :instanceid
                      )";
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

            $params = [
                'userid' => $userid,
                'instanceid' => $instanceid,
            ];

            $fs->delete_area_files_select($context->id, 'mod_uploadavatar', 'item', "IN ($itemidsql)", $params);
            $fs->delete_area_files_select($context->id, 'mod_uploadavatar', 'lowres', "IN ($itemidsql)", $params);
            $fs->delete_area_files_select($context->id, 'mod_uploadavatar', 'thumbnail', "IN ($itemidsql)", $params);

            \core_tag\privacy\provider::delete_item_tags_select($context, 'mod_uploadavatar', 'gallery',
                "IN ($galleryidsql)", $params);
            \core_tag\privacy\provider::delete_item_tags_select($context, 'mod_uploadavatar', 'item',
                "IN ($itemidsql)", $params);

            // We delete these last as the deletes above depend on these records.

            $DB->delete_records_select('uploadavatar_userfeedback', "itemid IN ($itemidsql)", $params);
            $DB->delete_records_select('uploadavatar_item', "galleryid IN ($galleryidsql)", $params);
            $DB->delete_records('uploadavatar_gallery', $params);
        }

        \core_comment\privacy\provider::delete_comments_for_user($contextlist, 'mod_uploadavatar', 'gallery');
        \core_comment\privacy\provider::delete_comments_for_user($contextlist, 'mod_uploadavatar', 'item');

    }

    public static function export_user_preferences(int $userid) {
        $pref = get_user_preferences('mod_uploadavatar_mediasize', \mod_uploadavatar\output\gallery\renderable::MEDIASIZE_MD, $userid);
        $string = 'mediasizemd';
        if ($pref == \mod_uploadavatar\output\gallery\renderable::MEDIASIZE_SM) {
          $string = 'mediasizesm';
        } else if ($pref == \mod_uploadavatar\output\gallery\renderable::MEDIASIZE_LG) {
          $string = 'mediasizelg';
        }
        writer::export_user_preference('mod_uploadavatar', 'mod_uploadavatar_mediasize', $pref, get_string($string, 'mod_uploadavatar'));
    }
}
