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

namespace mod_uploadavatar\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_uploadavatar item created event class.
 *
 * @property-read array $other {
 * }
 *
 * @package    mod_uploadavatar
 * @since      Moodle 2.7
 * @copyright  2014 NetSpot Pty Ltd
 * @author     Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_created extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'uploadavatar_item';
    }

    public function get_description() {
        return "The user with id '$this->userid' has created the item with id '$this->objectid' in the Media collection " .
            "with course module id '$this->contextinstanceid'.";
    }

    public static function get_name() {
        return get_string('eventitemcreated', 'mod_uploadavatar');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/uploadavatar/view.php', array('id' => $this->contextinstanceid));
    }

}
