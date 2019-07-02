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

use \mod_uploadavatar\collection,
    \mod_uploadavatar\gallery,
    \mod_uploadavatar\item,
    \mod_uploadavatar\base as mcbase;
use \mod_uploadavatar\output\collection\renderable as rencollection;
use \mod_uploadavatar\output\gallery\renderable as rengallery;

/**
 * Main renderer.
 *
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_uploadavatar_renderer extends plugin_renderer_base {

    /**
     * Setup the basic page settings.
     *
     * @param \mod_uploadavatar\viewcontroller $controller
     * @return void
     */
    public function setup_page($controller) {
        $this->page->set_context($controller->context);
        $this->page->set_url($controller->pageurl);
        $this->page->set_title(format_string($controller->collection->name));
        $this->page->set_heading(format_string($controller->course->fullname));
    }

    /**
     * Setup the required js/css for the page and return the header html.
     *
     * @param \mod_uploadavatar\viewcontroller $controller
     * @return string
     */
    public function view_header($controller) {
        $collmode = !empty($controller->collection->objectid) ? 'thebox' : 'standard';
        $jsoptions = new stdClass();
        $jsoptions->mode = $collmode;
        if ($controller->gallery) {
            $jsoptions->enablecomments = $controller->gallery->can_comment();
            $jsoptions->enablelikes = $controller->gallery->can_like();
            $jsoptions->mode = $controller->gallery->mode;
        }

        $galleryid = !empty($controller->gallery) ? $controller->gallery->id : 0;
        $this->page->requires->css('/mod/uploadavatar/css/fontawesome.min.css');
        $this->page->requires->js('/mod/uploadavatar/js/screenfull.min.js');
                $this->page->requires->js('/mod/uploadavatar/js/script.js');

        $this->page->requires->yui_module('moodle-mod_uploadavatar-base', 'M.mod_uploadavatar.base.init',
            array(
                $controller->course->id,
                $controller->collection->id,
                $controller->options['viewcontrols'],
                $controller->options['editing'],
                $galleryid, $jsoptions));

        if (!$controller->options['editing'] && $controller->options['action'] == 'viewgallery') {
            $this->setup_mediabox($controller->collection, $controller->gallery);
        }
        $this->page->requires->jquery();

        $jsstrs = array('confirmgallerydelete', 'confirmitemdelete', 'deletegallery',
            'deleteitem', 'like', 'likedby', 'comments', 'unlike', 'others', 'other',
            'addsamplegallery', 'uploadavatar', 'information', 'caption',
            'moralrights', 'originalauthor', 'productiondate', 'medium', 'collection',
            'publisher', 'galleryname', 'creator', 'filename', 'filesize', 'datecreated',
            'download', 'you', 'togglesidebar', 'close', 'togglefullscreen', 'tags',
            'reference', 'broadcaster', 'confirmcollectiondelete',
            'deleteorremovecollection', 'deleteorremovecollectionwarn',
            'deleteorremovegallery', 'deleteorremovegallerywarn',
            'deleteorremoveitem', 'deleteorremoveitemwarn',
            'removecollectionconfirm', 'removegalleryconfirm', 'removeitemconfirm',
            'youmusttypedelete', 'copyright', 'mediainformation');
        $this->page->requires->strings_for_js($jsstrs, 'mod_uploadavatar');
        $this->page->requires->strings_for_js(array(
            'move', 'add', 'description', 'no', 'yes', 'group', 'fullnameuser', 'username', 'next', 'previous', 'submit',
        ), 'moodle');

        $canedit = $controller->gallery && $controller->gallery->user_can_contribute();
        if ($controller->gallery && $canedit) {
            if (!$controller->options['editing']) {
                $url = new moodle_url('/mod/uploadavatar/view.php', array('g' => $controller->gallery->id, 'editing' => 1));
                $this->page->set_button($this->output->single_button($url, get_string('turneditingon', 'core', 'get')));
            } else {
                $url = new moodle_url('/mod/uploadavatar/view.php', array('g' => $controller->gallery->id));
                $this->page->requires->yui_module('moodle-mod_uploadavatar-dragdrop', 'M.mod_uploadavatar.dragdrop.init');
            }
        }
        return $this->output->header();
    }

    /**
     * Setup and init the mediabox JS.
     *
     * @param \mod_uploadavatar\collection $collection
     * @param \mod_uploadavatar\gallery $gallery
     * @return void
     */
    protected function setup_mediabox($collection, $gallery) {
        $mediaboxparams = array(
            'metainfouri' => (new moodle_url('/mod/uploadavatar/rest.php'))->out(),
            'metainfodata' => array(
                'sesskey' => sesskey(),
                'm' => $collection->id,
                'class' => 'item',
            ),
        );
        if ($gallery) {
            $mediaboxparams['enablecomments'] = $gallery->can_comment();
            $mediaboxparams['enablelikes'] = $gallery->can_like();
        }
        $this->page->requires->yui_module('moodle-mod_uploadavatar-mediabox', 'M.mod_uploadavatar.init_mediabox',
            array($mediaboxparams));
    }

    /**
     * Render a list of galleries for the user to browse through.
     *
     * @param rencollection $renderable
     * @return string
     */
    public function render_collection(rencollection $renderable) {
        $column = 1;
        $row = 1;
        $rowopen = false;
        $count = 0;

        $o = $this->output->heading($renderable->collection->name);
        $o .= html_writer::start_tag('div', array('class' => 'gallery_list'));
        foreach ($renderable->galleries as $gallery) {
            if ($renderable->thumbnailsperrow > 0 && $column > $renderable->thumbnailsperrow) {
                // Row complete.
                $o .= html_writer::end_tag('div');
                $rowopen = false;
                $column = 1;
                $row++;
            }
            if ($column == 1) {
                $o .= html_writer::start_tag('div', array('class' => 'row clearfix'));
                $rowopen = true;
            }
            if ($renderable->thumbnailsperpage > 0 && $count > $renderable->thumbnailsperpage) {
                break;
            }

            $o .= $this->gallery_list_item($gallery);
            $column++;
            $count++;
        }
        if ($rowopen) {
            $o .= html_writer::end_tag('div');
        }
        $o .= html_writer::end_tag('div');

        if (!empty($renderable->tags)) {
            $tagtitle = html_writer::span(get_string('tags', 'uploadavatar').': ', 'tagheading');
            $o .= html_writer::div($tagtitle.$renderable->tags, 'taglist');
        }
        $o .= html_writer::div('', 'clearfix');

        $o .= $this->collection_editing_actions($renderable);

        return $o;
    }

    /**
     * Render a gallery for display on a collection page.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @return string
     */
    public function gallery_list_item($gallery) {
        global $COURSE, $USER;
        $o = html_writer::start_tag('div',
            array('class' => 'gallery_list_item', 'data-title' => $gallery->name, 'data-id' => $gallery->id));

        $url = new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id));
        $items = $gallery->get_items();
        if(empty($items)){
        $img = html_writer::empty_tag('img', array('src' => $gallery->get_thumbnail()));
         }else{
        foreach($items as $itemvalue){
            $item = $itemvalue;
        }
        $img = html_writer::empty_tag('img', array('src' => $item->get_image_url_by_type('item')));
        }
        $link = html_writer::link($url, $img);
        $o .= html_writer::tag('div', $link, array('class' => 'gthumbnail'));
        $o .= html_writer::start_tag('div', array('class' => 'title'));
        $heading = format_string($gallery->name);
        $o .= $this->output->heading($heading, 6);
        $o .= html_writer::tag('span', $heading, ['class' => 'tooltiptext']);
        $o .= html_writer::end_tag('div');

        $o .= html_writer::start_tag('div', array('class' => 'controls'));

        $actions = $this->gallery_list_item_actions($gallery);
        $o .= $this->action_menu($actions);

        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Render the action icons for a gallery.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @return string
     */
    public function gallery_list_item_actions($gallery) {
        $actions = array();

        if ($gallery->user_can_edit()) {
            $url = new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id, 'editing' => 1));
            $actions['edit'] = $this->iconlink(get_string('editgallery', 'uploadavatar'), $url, 'pencil-square-o', '', true);
        }

        if ($gallery->user_can_remove()) {
            $url = new moodle_url('/mod/uploadavatar/gallery.php', array('g' => $gallery->id, 'action' => 'delete'));
            $isowner = $gallery->is_thebox_creator_or_agent() ? ' owner' : '';
            $actions['delete'] = $this->iconlink(get_string('deletegallery', 'uploadavatar'),
                $url, 'trash-o', "delete$isowner", true);
        }

        return $actions;
    }

    /**
     * Generate an action menu from a list of actions.
     *
     * @param array $actions
     * @return string
     */
    protected function action_menu($actions) {
        if (empty($actions)) {
            return '';
        }
        $menu = new action_menu();
        $menu->set_alignment(action_menu::TR, action_menu::BR);
        foreach ($actions as $action) {
            $menu->add($action);
        }

        // Prioritise the menu ahead of all other actions.
        $menu->prioritise = true;
        $output = $this->output->render($menu);
        return str_replace('iconsmall', '', $output);
    }

    /**
     * Heading for gallery pages.
     *
     * @param gallery $gallery
     * @return string
     */
    public function gallery_heading(gallery $gallery) {
        $name = format_string($gallery->get_collection()->name).' '.$this->output->rarrow().' '.format_string($gallery->name);
        $head = $this->output->heading($name);
        return html_writer::div($head, 'heading');
    }

    /**
     * Render the media size selector, lets users select thumbnail size to
     * display.
     *
     * @param int $currentsize
     * @return string
     */
    private function mediasize_selector($currentsize = rengallery::MEDIASIZE_MD) {
        $options = array(
            rengallery::MEDIASIZE_SM => get_string('mediasizesm', 'uploadavatar'),
            rengallery::MEDIASIZE_MD => get_string('mediasizemd', 'uploadavatar'),
            rengallery::MEDIASIZE_LG => get_string('mediasizelg', 'uploadavatar'),
        );
        $label = html_writer::label(get_string('mediasize', 'mod_uploadavatar'), 'mediasize');
        $select = html_writer::select($options, 'mediasize', $currentsize, array());
        return html_writer::div($label.$select, 'mediasize_selector');
    }

    /**
     * Render a gallery.
     *
     * @param rengallery $renderable Gallery renderable details.
     * @return void
     */
    public function render_gallery(rengallery $renderable) {
        $gallery = $renderable->gallery;
        $o = $this->gallery_heading($gallery);

        if (!$renderable->nosample) {
            $class = '';
            $pix = 't/check';
            if (!$gallery->moral_rights_asserted()) {
                $class = ' no';
                $pix = 'i/invalid';
            }
        }

        if ($renderable->galleryview == gallery::VIEW_GRID && !$renderable->editing) {
            $o .= $this->mediasize_selector($renderable->mediasize);
        }

        if ($renderable->editing) {
            $o .= $this->gallery_editing_page($gallery);
        } else {
            $o .= $this->gallery_viewing_page($renderable);
        }

        $tags = $gallery->get_tags();
        if (!empty($tags)) {
            $tagtitle = html_writer::span(get_string('tags', 'uploadavatar').': ', 'tagheading');
            $o .= html_writer::div($tagtitle.$tags, 'taglist');
        }

        if ($renderable->editing) {
            $o .= $this->gallery_editing_actions($gallery);
            if ($gallery->mode == 'thebox' && !empty($renderable->syncstamp)) {
                $o .= $this->last_synced($renderable->syncstamp);
            }

        }

        if (!empty($renderable->comments) && !$renderable->editing) {
            $o .= html_writer::div($renderable->comments->output(true), 'commentarea');
        }
        // If the user normally could edit, but can't currently due to read-only time or submission, display export link.
        if ($gallery->user_can_edit(null, true) && !$gallery->user_can_edit()) {
            $exporturl = new moodle_url('/mod/uploadavatar/export.php', array('g' => $gallery->id));
            $o .= html_writer::div(html_writer::link($exporturl, get_string('exportgallery', 'uploadavatar')), 'exportlink');
        }
        $o .= html_writer::div('', 'clearfix');
        return $o;

    }

    /**
     * Get the display of items for a gallery when not in editing mode.
     *
     * @param rengallery $renderable
     * @return string
     */
    protected function gallery_viewing_page(rengallery $renderable) {
        $o = html_writer::start_tag('div', array('class' => 'gallery'.$renderable->mediasizeclass));
        $items = $renderable->gallery->get_items();
        if (empty($items)) {
            $o .= get_string('noitemsadded', 'uploadavatar');
        } else if ($renderable->galleryview == gallery::VIEW_GRID) {
            $o .= $this->view_grid($renderable->gallery, $renderable->options);
        } else {
            $o .= $this->view_carousel($renderable->gallery, $renderable->options);
        }
        $o .= html_writer::end_tag('div');
        if ($otheritems = $renderable->gallery->get_items_by_type(false)) {
            $o .= $this->output->heading(get_string('otherfiles', 'uploadavatar'), 3);
            $o .= $this->list_other_items($otheritems, $renderable->gallery);
        }
        return $o;
    }

    /**
     * Render editing interface for a specific gallery.
     *
     * @param gallery $gallery The gallery to display.
     */
    public function gallery_editing_page(gallery $gallery) {
        global $COURSE, $DB;
        $sql = "SELECT u.id FROM {user} u INNER JOIN {role_assignments} ra ON ra.userid = u.id INNER JOIN {context} ct ON ct.id = ra.contextid INNER JOIN {course} c ON c.id = ct.instanceid INNER JOIN {role} r ON r.id = ra.roleid INNER JOIN {course_categories} cc ON cc.id = c.category WHERE r.id =5 and c.id = $COURSE->id";
        $enrolled_student = $DB->get_records_sql($sql);
        $o =  "<br><div class='titleN'><div class='togleBtn'><span class='grid active'>Grid <i class='fa fa-th-large'></i></span><span class='slide'>Slide <i class='fa fa-arrows-h' aria-hidden='true'></i></span></div>Teachers: </div></br>"; 
        $o .= html_writer::start_tag('div', array('class' => 'gallery_items editing grid-view'));
        foreach ($gallery->get_items() as $item) {
        if (array_key_exists($item->userid, $enrolled_student)) { 
            continue;
        }
            $o .= $this->item_editing($item, $gallery);
        }


              $o .= html_writer::end_tag('div');
            $o .= html_writer::start_tag('div', array('class' => 'gallery_items editing slider'));
        foreach ($gallery->get_items() as $item) {
        if (array_key_exists($item->userid, $enrolled_student)) { 
            continue;
        }
            $o .= $this->item_editing($item, $gallery);
        }



        $o .= html_writer::end_tag('div');
        $o.=  "<br><div class='titleN'> Students: </div>";
        $o .= html_writer::start_tag('div', array('class' => 'gallery_items editing grid-view'));
         
        foreach ($gallery->get_items() as $item) {
         if(!array_key_exists($item->userid, $enrolled_student)) { 
            continue;
        }
            $o .= $this->item_editing($item, $gallery);
        }

          $o .= html_writer::end_tag('div');   
         $o .= html_writer::start_tag('div', array('class' => 'gallery_items editing slider'));
         
        foreach ($gallery->get_items() as $item) {
         if(!array_key_exists($item->userid, $enrolled_student)) { 
            continue;
        }
            $o .= $this->item_editing($item, $gallery);
        }
        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Render the editing actions for a collection.
     *
     * @param rencollection $renderable
     * @return string
     */
    public function collection_editing_actions(rencollection $renderable) {
        $links = $this->collection_editing_actions_list($renderable);
        $content = implode(' &nbsp; ', $links);

        $o = html_writer::div($content, 'actions collection');
        $o .= html_writer::div('', 'clearfix');
        return $o;
    }

    /**
     * Build the editing actions list for a collection.
     *
     * @param rencollection $renderable
     * @return array A list of actions.
     */
    public function collection_editing_actions_list(rencollection $renderable) {
        $links = array();
        global $DB;
        if ($renderable->normallycanadd && !$renderable->readonly) {
            if ($renderable->maxreached) {
                $links['maxgalleries'] = $this->iconlink(get_string('maxgalleriesreached', 'uploadavatar'), null);
            } else {
                if(empty($renderable->galleries)){
                $url = new moodle_url('/mod/uploadavatar/gallery.php', array('m' => $renderable->id));
                $links['addgallery'] = $this->iconlink(get_string('addagallery', 'uploadavatar'), $url, 'plus');
            }
            }
        }

        if ($renderable->linkedassigncmid && $renderable->userorgrouphasgallery) {
            $url = new moodle_url('/mod/assign/view.php',
                array('id' => $renderable->linkedassigncmid, 'action' => 'editsubmission'));
            if ($renderable->submissionsopen) {
                $str = $renderable->hassubmitted ? 'assignedit' : 'assignsubmit';
                $links['submitassign'] = $this->iconlink(get_string($str, 'uploadavatar'), $url, 'check-square');
            } else if ($renderable->hassubmitted) {
                $url->param('action', 'viewsubmission');
                $links['submitassign'] = $this->iconlink(get_string('assignsubmitted', 'uploadavatar'), $url, 'check-square');
            }
        }

        // $url = new moodle_url('/mod/uploadavatar/view.php', array('id' => $this->page->context->instanceid, 'action' => 'search'));
        // $links['search'] = $this->iconlink(get_string('search', 'uploadavatar'), $url, 'search');

        return $links;
    }

    /**
     * Render a sync link for an external service.
     *
     * @return string
     */
    protected function sync_link() {
        $url = $this->page->url;
        $url->param('sync', true);
        return $this->iconlink(get_string('syncwiththebox', 'uploadavatar'), $url, 'refresh');
    }

    /**
     * Render the last synced timestamp for an external service.
     *
     * @param int $timestamp
     * @return string
     */
    public function last_synced($timestamp) {
        if (!$timestamp) {
            return '';
        }
        $lastcompleted = get_string('synclastcompleted', 'uploadavatar').' - ';
        $lastcompleted .= userdate($timestamp);
        return html_writer::div($lastcompleted, 'clearfix lastsync');
    }

    /**
     * Action links shown when editing a gallery.
     *
     * @param gallery $gallery
     * @return string
     */
    public function gallery_editing_actions(gallery $gallery) {
        $actions = $this->gallery_editing_actions_list($gallery);

        $o = html_writer::start_tag('div', array('class' => 'actions'));
        $o .= implode(' &nbsp; ', $actions);
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /**
     * Build a list of gallery editing actions.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @return array A list of actions.
     */
    protected function gallery_editing_actions_list($gallery) {
        global $DB, $USER;
        $additemurl = new moodle_url('/mod/uploadavatar/item.php', array('g' => $gallery->id));
        $addbulkitemurl = new moodle_url('/mod/uploadavatar/item.php', array('g' => $gallery->id, 'bulk' => 1));
        $viewurl = new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id));
        $editurl = new moodle_url('/mod/uploadavatar/gallery.php', array('g' => $gallery->id));
        $exporturl = new moodle_url('/mod/uploadavatar/export.php', array('g' => $gallery->id));
        $actions = array();

        $maxitems = $gallery->get_collection()->maxitems;
        if ($maxitems == 0 || count($gallery->get_items()) < $maxitems) {
           $already_added = $DB->get_field('uploadavatar_item','id',array('galleryid' => $gallery->id, 'userid' => $USER->id));
            if(!$already_added){
            $actions['add'] = $this->iconlink(get_string('addanitem', 'uploadavatar'), $additemurl, 'plus');
            }
            // if ($gallery->mode != 'youtube') {
            //     $actions['addbulk'] = $this->iconlink(get_string('addbulkitems', 'uploadavatar'), $addbulkitemurl, 'plus');
            // }
        } else {
            $actions['maxitems'] = html_writer::span(get_string('maxitemsreached', 'uploadavatar'));
        }
        // $actions['view'] = $this->iconlink(get_string('viewgallery', 'uploadavatar'), $viewurl, 'eye');
        // if ($gallery->user_can_edit()) {
        //     $actions['edit'] = $this->iconlink(get_string('editgallerysettings', 'uploadavatar'), $editurl, 'pencil-square-o');
        // }
        // if ($gallery->mode == 'standard' && $gallery->user_can_edit(null, true)) {
        //     $actions['export'] = $this->iconlink(get_string('exportgallery', 'uploadavatar'), $exporturl, 'share');
        // }

        return $actions;
    }

    /**
     * Render an action icon.
     *
     * @param string $text
     * @param moodle_url $link
     * @param string $fa A fontawesome icon string.
     * @param string $linkclass Any classes to add to the tag.
     * @param boolean $actionmenu Is this an actionmenu?
     * @return string
     */
    protected function iconlink($text, $link = null, $fa = null, $linkclass = '', $actionmenu = false) {
        $o = '';
        if ($fa) {
            $icon = html_writer::tag('i', '', array('class' => 'mgfa mgfa-fw mgfa-lg mgfa-'.$fa));
        }

        if ($link) {
            $class = $actionmenu ? 'action-menu' : 'maction';
            $linkclass = trim($linkclass.' '.$class);
            $o = html_writer::link($link, $icon.$text, array('class' => $linkclass));
        } else {
            $o = html_writer::span($text);
        }
        return $o;
    }

    /**
     * Render an item edit card.
     *
     * @param item $item
     * @param gallery $gallery
     * @return string
     */
    public function item_editing(item $item, $gallery) {
        global $USER;
        $o = html_writer::start_tag('div', array('class' => 'item', 'data-id' => $item->id, 'data-title' => $item->caption));
        $img = html_writer::empty_tag('img', array('src' => $item->get_image_url_by_type('item')));
        $link = html_writer::link(null, $img);
        $o .= html_writer::tag('div', $link, array('class' => 'gthumbnail'));
        $o .= html_writer::start_tag('div', array('class' => 'title'));
        $heading = format_string($item->caption);
        $o .= $this->output->heading($heading, 6);
        $o .= html_writer::tag('span', $heading, ['class' => 'tooltiptext']);
        $o .= html_writer::end_tag('div');

        $o .= html_writer::start_tag('div', array('class' => 'controls'));

        $actions = $this->item_editing_actions_list($item, $gallery);
        $o .= $this->action_menu($actions);

        $o .= html_writer::end_tag('div');

        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Build a list of item editing actions.
     *
     * @param item $item
     * @param gallery $gallery
     * @return array A list of actions.
     */
    protected function item_editing_actions_list($item, $gallery) {
        $actions = array();

        $type = $item->type(true);
        $boxcreatoragent = $gallery->get_collection()->mode == 'thebox'
            && ($gallery->is_thebox_creator_or_agent() || $gallery->get_collection()->is_thebox_creator_or_agent());
        if ($item->user_can_edit() || $boxcreatoragent) {
            $url = new moodle_url('/mod/uploadavatar/item.php', array('i' => $item->id));
            $str = is_null($type) ? 'edititem' : 'edititemtype';
            $actions['edit'] = $this->iconlink(get_string($str, 'uploadavatar', $type),
                $url, 'pencil-square-o', "edit", true);
        }

        $isowner = $item->is_thebox_creator_or_agent() ? ' owner' : '';
        if (($gallery->mode != 'thebox' && $item->user_can_remove()) || $gallery->is_thebox_creator_or_agent()) {
            $url = new moodle_url('/mod/uploadavatar/item.php', array('i' => $item->id, 'action' => 'delete'));
            $str = is_null($type) ? 'deleteitem' : 'deleteitemtype';
            $actions['delete'] = $this->iconlink(get_string($str, 'uploadavatar', $type),
                $url, 'trash-o', "delete$isowner", true);
        }

        return $actions;
    }

    /**
     * Render a list of the items not displayed in the main area.
     *
     * @param array $items
     * @param gallery $gallery
     * @return string
     */
    protected function list_other_items($items, $gallery) {
        $o = html_writer::start_tag('ul');
        foreach ($items as $item) {
            if (!$item->display) {
                continue;
            }
            $image = $this->output->pix_icon($item->file_icon(), $item->caption, 'moodle', array('class' => 'icon'));
            if ($gallery->mode != 'thebox' || $item->thebox_processed()) {
                $entry = html_writer::link($item->get_embed_url(), $image.$item->caption);
            } else {
                $processstring = get_string('beingprocessed', 'uploadavatar');
                $entry = html_writer::span($image.$item->caption." ($processstring)");
            }
            $o .= html_writer::tag('li', $entry);
        }
        $o .= html_writer::end_tag('ul');
        return $o;
    }

    /**
     * Render a carousel of items.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @param array $options
     * @return string
     */
    public function view_carousel(gallery $gallery, array $options = array()) {
        $o = html_writer::start_tag('div', array('class' => 'jcarousel-wrapper'));
        $o .= html_writer::start_tag('div',
            array('class' => 'jcarousel type_'.$gallery->type(true), 'data-jcarousel' => 'true', 'data-wrap' => 'circular'));

        $o .= html_writer::start_tag('ul');
        foreach ($gallery->get_items_by_type() as $item) {
            if (!$item->display) {
                continue;
            }
            $itemhtml = html_writer::empty_tag('img', array('src' => $item->get_image_url_by_type('thumbnail')));
            $attribs = $this->linkattribs($gallery, $item);
            if (!empty($options['filter'])) {
                $attribs['href'] = new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id));
            } else {
                $attribs['href'] = $item->get_image_url_by_type('lowres');
            }
            if ($gallery->get_display_settings()->galleryfocus == mcbase::TYPE_AUDIO) {
                $itemhtml .= $this->embed_html($item);
            }
            $o .= html_writer::tag('li', html_writer::tag('a', $itemhtml, $attribs));
        }
        $o .= html_writer::end_tag('ul');

        $o .= html_writer::end_tag('div');
        $o .= html_writer::tag('a', '&lsaquo;', array('data-jcarousel-control' => 'true', 'data-target' => '-=1',
            'href' => '#', 'class' => 'jcarousel-control-prev'));
        $o .= html_writer::tag('a', '&rsaquo;', array('data-jcarousel-control' => 'true', 'data-target' => '+=1',
            'href' => '#', 'class' => 'jcarousel-control-next'));
        $o .= html_writer::tag('p', '', array('data-jcarouselpagination' => 'true', 'class' => 'jcarousel-pagination'));
        $o .= html_writer::end_tag('div');

        // For whatever reason, including the JS earlier doesn't work.
        $o .= html_writer::tag('script', '', array(
            'type' => 'text/javascript',
            'src' => new moodle_url('/theme/jquery.php/mod_uploadavatar/jcarousel/jquery.jcarousel.v2.js'),
        ));

        return $o;
    }

    /**
     * Get the embed url using the mediarenderer.
     *
     * @param \mod_uploadavatar\item $item
     * @return string
     */
    public function embed_html($item) {
        $mediarenderer = $this->page->get_renderer('core', 'media');
        return $mediarenderer->embed_url(new moodle_url($item->get_embed_url()), '', 670, 377);
    }

    /**
     * Build a list of attributes to attach to a displayed item.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @param \mod_uploadavatar\item $item
     * @return array A list of attributes.
     */
    protected function linkattribs($gallery, $item) {
        $type = $item->type();
        $player = $type == mcbase::TYPE_AUDIO || ($type == mcbase::TYPE_VIDEO && $item->externalurl == '') ? $type : 1;
        $attribs = array(
            'data-mediabox' => 'gallery_'.$gallery->id,
            'title' => $item->caption,
            'data-id' => $item->id,
            'data-type' => $item->get_source(),
            'data-player' => $player,
            'data-url' => $item->get_embed_url(),
            'data-objectid' => $item->objectid,
        );
        return $attribs;
    }

    /**
     * Render a grid of items.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @param array $options
     * @return string
     */
    protected function view_grid(gallery $gallery, array $options) {
        $o = '';

        $column = 1;
        $row = 1;
        $rowopen = false;
        $view = $gallery->get_display_settings();
        $perpage = $view->gridcolumns * $view->gridrows;
        $offset = $perpage * $options['page'];

        $cappos = $gallery->get_collection()->captionposition;

        $items = $gallery->get_items_by_type();
        foreach ($items as $item) {
            if (!$item->display) {
                continue;
            }
            if ($offset) {
                $offset--;
                continue;
            }
            if ($column > $view->gridcolumns && $view->gridcolumns != 0) {
                // Row complete.
                $o .= html_writer::end_tag('div');
                $rowopen = false;
                $column = 1;
                $row++;
            }
            if ($column == 1) {
                $o .= html_writer::start_tag('div', array('class' => 'grid_row clearfix'));
                $rowopen = true;
            }
            if ($row > $view->gridrows && $view->gridrows != 0) {
                // Grid is now full.
                break;
            }

            $url = new moodle_url('/mod/uploadavatar/item.php', array('i' => $item->id, 'action' => 'info'));
            $infoicon = html_writer::tag('div',
                $this->output->action_icon($url, new pix_icon('i/info', get_string('information', 'uploadavatar')), null,
                    array('class' => 'action-icon info')),
                array('class' => 'info')
            );

            $caption = html_writer::tag('div', $infoicon.$item->caption, array('class' => 'caption'));
            $img = html_writer::empty_tag('img', array('src' => $item->get_image_url_by_type('thumbnail')));
            $linkattribs = $this->linkattribs($gallery, $item);
            $link = html_writer::link($item->get_image_url_by_type('lowres'), $img, $linkattribs);

            $itemframe = '';
            if ($cappos == mcbase::POS_TOP) {
                $itemframe .= $caption;
            }
            $itemframe .= html_writer::tag('div', $link, array('class' => 'item-thumb'));
            if ($gallery->get_display_settings()->galleryfocus == mcbase::TYPE_AUDIO) {
                $itemframe .= $this->embed_html($item);
            }
            if ($cappos == mcbase::POS_BOTTOM) {
                $itemframe .= $caption;
            }
            $itemframe = html_writer::tag('div', $itemframe, array('class' => 'item-wrapper'));

            $o .= html_writer::tag('div', $itemframe, array('class' => 'item grid_item', 'data-id' => $item->id,
                'data-title' => $item->caption, 'id' => 'gallery_item_'.$item->id));
            $this->page->requires->yui_module('moodle-mod_uploadavatar-base', 'M.mod_uploadavatar.base.add_item_info_modal',
                array($item->get_metainfo()), null, true);

            $column++;
        }
        if ($rowopen) {
            $o .= html_writer::end_tag('div');
        }
        $count = count($items);
        if ($count > $perpage && $perpage != 0) {
            $url = new moodle_url('/mod/uploadavatar/view.php', array('g' => $gallery->id, 'page' => $options['page']));
            $o .= $this->output->paging_bar($count, $options['page'], $perpage, $url);
        }

        return $o;
    }



    /**
     * Render the tagselector module.
     *
     * @param array $tags
     * @return string
     */
    public function tags($tags) {
        $this->page->requires->yui_module('moodle-mod_uploadavatar-tagselector', 'M.mod_uploadavatar.tagselector.init',
            array('tagentry', $tags), null, true);

        $tagfields = html_writer::span(get_string('tags', 'uploadavatar').': ');
        $tagfields .= html_writer::empty_tag('input', array('id' => 'tagentry'));
        $o = html_writer::div($tagfields, 'tagcontainer');
        return $o;
    }
}

/**
 * Overrides for standard galleries.
 *
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_uploadavatar_standard_renderer extends mod_uploadavatar_renderer {

    /**
     * Render the action icons for a gallery.
     *
     * @param \mod_uploadavatar\gallery $gallery
     * @return string
     */
    public function gallery_list_item_actions($gallery) {
        $actions = parent::gallery_list_item_actions($gallery);

        $order = array_flip(array('edit', 'delete'));
        uksort($actions, function($a, $b) use ($order) {
            return $order[$a] - $order[$b];
        });
        return $actions;
    }
}
