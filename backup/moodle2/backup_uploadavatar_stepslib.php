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
 * Define the complete uploadavatar structure for backup, with file and id annotations
 */
class backup_uploadavatar_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $uploadavatar = new backup_nested_element('uploadavatar', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified',
            'thumbnailsperpage', 'thumbnailsperrow', 'displayfullcaption',
            'captionposition', 'galleryfocus', 'carousel', 'grid', 'gridrows',
            'gridcolumns', 'enforcedefaults', 'readonlyfrom', 'readonlyto',
            'maxbytes', 'maxitems', 'maxgalleries', 'allowcomments', 'allowlikes',
            'colltype', 'objectid', 'source', 'mode', 'creator', 'userid',
        ));

        $userfeedbacks = new backup_nested_element('userfeedback');
        $userfeedback = new backup_nested_element('feedback', array('id'), array(
            'itemid', 'userid', 'liked', 'rating'
        ));

        $gallerys = new backup_nested_element('gallerys');
        $gallery = new backup_nested_element('gallery', array('id'), array(
            'instanceid', 'name', 'userid', 'nameposition', 'exportable', 'galleryview',
            'gridrows', 'gridcolumns', 'visibleinstructor', 'visibleother', 'thumbnail',
            'galleryfocus', 'groupid', 'mode', 'objectid', 'source', 'creator',
            'contributable',
        ));

        $items = new backup_nested_element('items');
        $item = new backup_nested_element('item', array('id'), array(
            'galleryid', 'userid', 'caption', 'description', 'sortorder', 'display', 'moralrights',
            'originalauthor', 'productiondate', 'medium', 'publisher', 'reference', 'externalurl',
            'timecreated', 'broadcaster', 'objectid', 'source', 'processing_status', 'creator',
        ));

        // Build the tree.

        $uploadavatar->add_child($gallerys);
        $gallerys->add_child($gallery);
        $gallery->add_child($items);
        $items->add_child($item);
        $userfeedbacks->add_child($userfeedback);
        $item->add_child($userfeedbacks);

        // Define sources.
        $uploadavatar->set_source_table('uploadavatar', array('id' => backup::VAR_ACTIVITYID));
        $gallery->set_source_table('uploadavatar_gallery', array('instanceid' => backup::VAR_PARENTID));
        $item->set_source_table('uploadavatar_item', array('galleryid' => backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $userfeedback->set_source_table('uploadavatar_userfeedback', array('itemid' => backup::VAR_PARENTID));
        }

        // Define file annotations.
        $uploadavatar->annotate_files('mod_uploadavatar', 'item', null);
        $uploadavatar->annotate_files('mod_uploadavatar', 'lowres', null);
        $uploadavatar->annotate_files('mod_uploadavatar', 'thumbnail', null);

        $userfeedback->annotate_ids('user', 'userid');
        $gallery->annotate_ids('user', 'userid');
        $item->annotate_ids('user', 'userid');

        // Return the root element (uploadavatar), wrapped into standard activity structure.
        return $this->prepare_activity_structure($uploadavatar);
    }
}
