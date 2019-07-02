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

$string['addagallery'] = 'Add an Avtar gallery';
$string['addanitem'] = 'Add Your Avatar';
$string['addbulkitems'] = 'Add items in bulk';
$string['addfiles'] = 'Add file(s)';
$string['addsamplegallery'] = 'Add a sample gallery';
$string['top'] = 'Top';
$string['bottom'] = 'Bottom';
$string['broadcaster'] = 'Broadcaster';
$string['broadcaster_help'] = 'Who was the distributor that broadcasted this work?';
$string['caption'] = 'Caption';
$string['caption_help'] = 'The caption for this item in your gallery. This caption will be displayed alongside the item. If you opt to leave this blank, the filename (or url) will be displayed as the caption instead.';
$string['captionposition'] = 'Caption position';
$string['close'] = 'Close';
$string['collection'] = 'Collection';

// // Collection types.
$string['colltype'] = 'Collection type';
$string['colltype_help'] = 'The collection type determines what level users can interact with the collection and its content.

// <ul>
// <li>Instructor collection: Only users that can grade the collection can add/edit content within it. This is primarily used for instructors to create example collections; or a set of galleries without letting users create their own.</li>
// <li>Contributed collection: Allows users to create their own galleries and items, but the collection cannot be used as part of an assignment.</li>
// <li>Assignment collection: Users are only able to see the galleries they or their group (if in group mode) have created. Can be used as part of an assignment submission.</li>
// <li>Peer reviewed assignment collection: Users are able to view other users/groups galleries and like/comment on them if those features are enabled. Can be used as part of an assignment submission.</li></ul>';
$string['colltypepeerreviewed'] = 'Everybody can view the collection';

$string['comments'] = 'Comments';
$string['confirmcollectiondelete'] = 'Confirm collection deletion';
$string['confirmgallerydelete'] = 'Confirm gallery deletion';
$string['confirmitemdelete'] = 'Confirm item deletion';
$string['content'] = 'Avatar Image';
$string['content_help'] = 'The item you want to add to your gallery.';
$string['copyright'] = 'Copyright';
$string['copyright_help'] = 'This defines which copyright license is set for all the items you upload via this form.';
$string['creator'] = 'Creator';
$string['datecreated'] = 'Date created';
$string['deletegallery'] = 'Delete gallery';
$string['deleteitem'] = 'Delete item';
$string['deleteitemtype'] = 'Delete {$a}';
$string['deleteorremovecollection'] = 'If you wish to remove the link to the collection without deleting the content click submit.<br/><br/>

// If you wish to remove the link to the collection and delete the content within type DELETE in the textbox below and click submit.';
$string['deleteorremovecollectionwarn'] = 'By deleting you acknowledge you are:<br/>
// - removing this link to the Upload avatar<br/>
// - deleting the collection and/or all galleries and all content from theBox<br/>
// - disabling all links made in other courses to this collection or its content
// ';
$string['deleteorremovegallery'] = 'If you wish to remove the link to the gallery without deleting the content click submit.<br/><br/>

// If you wish to remove the link to the gallery and delete the content within type DELETE in the textbox below and click submit.';
$string['deleteorremovegallerywarn'] = 'By deleting you acknowledge you are:<br/>
// - removing this link to the media gallery<br/>
// - deleting the media gallery and all content from theBox<br/>
// - disabling all links made in other courses to this media gallery or its content';
$string['deleteorremoveitem'] = 'If you wish to remove the item from the gallery without deleting the content click submit.<br/><br/>

// If you wish to remove the link to the gallery and delete the content type DELETE in the textbox below and click submit.';
$string['deleteorremoveitemwarn'] = 'By deleting you acknowledge you are:<br/>
// - removing this link to the media item<br/>
// - deleting the media item from theBox<br/>
// - disabling all links made in other courses to this media item';
$string['displayfullcaption'] = 'Display full caption text';
$string['download'] = 'Download';
$string['editgallery'] = 'Add Avatar';
$string['edititem'] = 'Edit item';
$string['edititemtype'] = 'Edit {$a}';
$string['exportgallery'] = 'Export gallery';
$string['filename'] = 'File name';
$string['filesize'] = 'File size';
$string['galleryname'] = 'Avatar Gallery name';

$string['information'] = 'Information';
$string['like'] = 'Like';
$string['likedby'] = 'Liked by';
$string['maxbytes'] = 'Maximum size per item';
$string['maxitems'] = 'Maximum items per gallery';
$string['maxitems_help'] = 'The maximum number of items a user can put in a gallery in this collection.

// Note: for Instructor collections, this is always unlimited.';
$string['modulename'] = 'Upload Avatar';
$string['modulenameplural'] = 'Upload Avatars';
$string['modulename_help'] = 'Use the Upload Avatar module for creating galleries of media content.

// Users can create their own galleries of images, video or audio either on their own or in groups.


// Uploaded content will be presented in either a carousel or grid format as thumbnails. Click on any of the thumbnails brings that image into focus and allows you to browse through the gallery. Users are able to \'like\' and comment on content they can see in their own and other galleries.';
$string['uploadavatar:addinstance'] = 'Add an instance of Upload avatar';
$string['uploadavatarname'] = 'Module Name';
$string['uploadavatarname_help'] = 'The name you want to give your Upload avatar.';
$string['uploadavatar'] = 'Upload avatar';
$string['mediainformation'] = 'Media information';

$string['medium'] = 'Medium';
$string['modestandard'] = 'Standard';

$string['moralrights'] = 'Moral rights';
$string['moralrights_help'] = 'Do you wish to assert your moral rights?

// By selecting yes you\'re granting your consent for this item to potentially be used as a sample of work.';
$string['originalauthor'] = 'Original author';
$string['originalauthor_help'] = 'The original author of the item.';
$string['other'] = 'other';
$string['others'] = 'others';
$string['pluginadministration'] = 'Upload avatar administration';
$string['pluginname'] = 'Upload Avatar';


$string['privacy:metadata:core_comments'] = 'Comments associated with Upload avatar galleries or items';
$string['privacy:metadata:core_files'] = 'Tags associated with Upload avatar galleries or items';
$string['privacy:metadata:core_tag'] = 'Tags associated with Upload avatar galleries or items';
$string['privacy:metadata:uploadavatar'] = 'Information about the the media galleries a user has created.';
$string['privacy:metadata:uploadavatar:instanceid'] = 'The ID of the Upload avatar.';
$string['privacy:metadata:uploadavatar:name'] = 'The name of the Upload avatar.';
$string['privacy:metadata:uploadavatar:userid'] = 'The ID of the user who created/owns the Upload avatar activity.';
$string['privacy:metadata:uploadavatar_gallery'] = 'Information about the the media galleries a user has created.';
$string['privacy:metadata:uploadavatar_gallery:instanceid'] = 'The ID of the uploadavatar item the user is providing a feedback for.';
$string['privacy:metadata:uploadavatar_gallery:name'] = 'The name of the gallery.';
$string['privacy:metadata:uploadavatar_gallery:userid'] = 'The ID of the user who created the gallery.';
$string['privacy:metadata:uploadavatar_item'] = 'Information about the the media items a user has created.';
$string['privacy:metadata:uploadavatar_item:galleryid'] = 'The ID of the gallery the item belongs to.';
$string['privacy:metadata:uploadavatar_item:userid'] = 'The ID of the user who created the item.';
$string['privacy:metadata:uploadavatar_item:caption'] = 'The caption the user gave the item.';
$string['privacy:metadata:uploadavatar_item:description'] = 'The desciption the user gave the item.';
$string['privacy:metadata:uploadavatar_item:moralrights'] = 'If the user claimed their moral rights on the item.';
$string['privacy:metadata:uploadavatar_item:originalauthor'] = 'Original author/creator of the work.';
$string['privacy:metadata:uploadavatar_item:productiondate'] = 'Datetime the piece was created.';
$string['privacy:metadata:uploadavatar_item:medium'] = 'Medium used to create the work.';
$string['privacy:metadata:uploadavatar_item:publisher'] = 'Publisher of the work.';
$string['privacy:metadata:uploadavatar_item:broadcaster'] = 'Broadcaster of the work.';
$string['privacy:metadata:uploadavatar_item:reference'] = 'Reference to the collection the work belongs to.';
$string['privacy:metadata:uploadavatar_item:externalurl'] = 'The externalurl, if any, the item references.';
$string['privacy:metadata:uploadavatar_item:timecreated'] = 'The time the user created the item.';
$string['privacy:metadata:uploadavatar_userfeedback'] = 'Information about the user\'s feedback on a given uploadavatar item';
$string['privacy:metadata:uploadavatar_userfeedback:itemid'] = 'The ID of the uploadavatar item the user is providing a feedback for.';
$string['privacy:metadata:uploadavatar_userfeedback:userid'] = 'The user who made the feedback.';
$string['privacy:metadata:uploadavatar_userfeedback:liked'] = 'If the user "liked" the item.';
$string['privacy:metadata:uploadavatar_userfeedback:rating'] = 'What rating the user gave the item (not implemented).';
$string['privacy:metadata:preference:mediasize'] = 'What viewing size the user prefers to see media items in.';
$string['productiondate'] = 'Production date';
$string['productiondate_help'] = 'The date the original work was produced.';
$string['publisher'] = 'Publisher';
$string['publisher_help'] = 'The publisher (if any) of the work.';

$string['reference'] = 'Reference';
$string['reference_help'] = 'Reference to the collection (if any) the work is from.';
$string['removecollectionconfirm'] = 'Are you sure you wish to remove the link to this collection?';
$string['removegalleryconfirm'] = 'Are you sure you wish to remove the link to this gallery?';
$string['removefromgallery'] = 'Remove from gallery';
$string['removeitemconfirm'] = 'Are you sure you wish to remove the link to this item?';

$string['tags'] = 'Tags';
$string['togglefullscreen'] = 'Toggle fullscreen';
$string['togglesidebar'] = 'Toggle sidebar';
$string['you'] = 'you';
$string['youmusttypedelete'] = 'You must type DELETE to confirm deletion.';
$string['togglesidebar'] = 'Toggle sidebar';
$string['unlike'] = 'Unlike';
$string['configmaxbytes'] = 'Default maximum item file size for all media collections on the site (subject to course limits and other local settings)';