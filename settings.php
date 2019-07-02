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

if ($ADMIN->fulltree) {
    if (isset($CFG->maxbytes)) {
        $maxbytes = get_config('uploadavatar', 'maxbytes');
        $options = get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes);
        $settings->add(new admin_setting_configselect('uploadavatar/maxbytes', new lang_string('maxbytes', 'uploadavatar'),
                            new lang_string('configmaxbytes', 'uploadavatar'), 0, $options));
    }

//     $settings->add(new admin_setting_configcheckbox('uploadavatar/disablestandardgallery',
//                         new lang_string('disablestandardgallery', 'uploadavatar'),
//                         new lang_string('configdisablestandardgallery', 'uploadavatar'), 0));

}
// if ($hassiteconfig) {
//     $ADMIN->add('reports', new admin_externalpage('moduploadavatarstorage',
//         new lang_string('storagereport', 'uploadavatar'), "$CFG->wwwroot/mod/uploadavatar/storage.php", 'moodle/site:config'));
// }
