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
 * Layout for embedded content.
 *
 * @package   theme_cleanudem
 * @copyright 2014 Universite de Montreal
 * @author    Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Get the HTML for the settings bits.
$html = theme_cleanudem_get_html_for_settings($OUTPUT, $PAGE);
?>

<?php echo $OUTPUT->element('head', array('fontlinks' => $html->fontlinks)); ?>

<div id="page">
    <div id="page-content" class="clearfix">
        <?php echo $OUTPUT->main_content(); ?>
    </div>
</div>

<?php echo $OUTPUT->element('foot');
