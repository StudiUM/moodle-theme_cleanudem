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
 * 2 columns layout.
 *
 * Do not remove/add block regions (columns) from this file, instead edit config.php
 * to match the corresponding page types with another layout file.
 *
 * @package   theme_cleanudem
 * @copyright 2014 Universite de Montreal
 * @author    Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Get the HTML for the settings bits.
$html = theme_cleanudem_get_html_for_settings($OUTPUT, $PAGE);

$left = (!right_to_left());  // To know if to add 'pull-right' and 'desktop-first-column' classes in the layout for LTR.

?>

<?php echo $OUTPUT->element('head', array('additionalclasses' => 'two-column', 'fontlinks' => $html->fontlinks)); ?>

<?php echo $OUTPUT->element('header'); ?>

<div id="page" class="container-fluid">

    <?php $vars = array('heading' => $OUTPUT->page_heading(), 'button' => $OUTPUT->page_heading_button()); ?>
    <?php echo $OUTPUT->element('page-header', $vars); ?>

    <div id="page-content" class="row-fluid">
    
<?php 
$classextra = 'span9';
if ($left) {
    $classextra .= ' pull-right';
}
?>

        <section id="region-main" class="<?php echo $classextra; ?>">
            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
        
<?php
$classextra = '';
if ($left) {
    $classextra = ' desktop-first-column';
}
echo $OUTPUT->blocks('side-pre', 'span3'.$classextra);
?>

    </div>

    <?php echo $OUTPUT->element('page-footer', array('footernav' => $html->footernav, 'footnote' => $html->footnote)); ?>

</div>

<?php echo $OUTPUT->element('foot');
