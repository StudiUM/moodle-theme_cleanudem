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
 * Workshop module renderering methods are defined here
 *
 * @package   theme_cleanudem
 * @copyright 2017 Université de Montréal
 * @author    Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/workshop/renderer.php');

/**
 * Clean UdeM Workshop module renderer class
 *
 * @copyright 2017 Université de Montréal
 * @author    Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_cleanudem_mod_workshop_renderer extends mod_workshop_renderer {

    /** @var boolean If the grading grade are displayed */
    private $showgradinggrade = true;


    /**
     * Renders the workshop grading report.
     * Used only to retrieve the max grading grade value;
     *
     * @param workshop_grading_report $gradingreport
     * @return string html code
     */
    protected function render_workshop_grading_report(workshop_grading_report $gradingreport) {
        $data = $gradingreport->get_data();
        $this->showgradinggrade = !empty($data->maxgradinggrade);
        return parent::render_workshop_grading_report($gradingreport);
    }

    /**
     * Grading report assessment helper method.
     *
     * @param stdClass|null $assessment
     * @param bool $shownames
     * @param array $userinfo The information of the user
     * @param string $separator between the grade and the reviewer/author
     * @return string
     */
    protected function helper_grading_report_assessment($assessment, $shownames, array $userinfo, $separator) {
        global $CFG;

        $nullgrade = get_string('nullgrade', 'workshop');

        if (is_null($assessment)) {
            return $nullgrade;
        }

        $items = array();
        $items['grade'] = is_null($assessment->grade) ? $nullgrade : $assessment->grade;

        if ($assessment->weight != 1) {
            $items['weight'] = $assessment->weight;
        }

        if ($this->showgradinggrade) {
            $gradinggrade = is_null($assessment->gradinggrade) ? get_string('nullgrade', 'workshop') : $assessment->gradinggrade;
            if (!is_null($assessment->gradinggradeover)) {
                $gradinggrade = html_writer::tag('del', $gradinggrade) .
                        " / " . html_writer::tag('ins', $assessment->gradinggradeover);
            }
            $items['gradinggrade'] = $gradinggrade;
        }

        $content = '';
        $url = new moodle_url('/mod/workshop/assessment.php', array('asid' => $assessment->assessmentid));

        foreach ($items as $key => $value) {
            $label = get_string('workshoppeer' . $key . 'label', 'theme_cleanudem');
            $grade = html_writer::tag('span', $value, array('class' => $key));
            $content .= html_writer::tag('span', $label, array('class' => 'gradelabel'));
            $content .= html_writer::link($url, $grade, array('class' => 'grade'));
        }

        $name   = '';
        $userpicture = '';

        if ($shownames) {
            $userid = $assessment->userid;
            $params = array('courseid' => $this->page->course->id, 'size' => 35);
            $userpicture = $this->output->user_picture($userinfo[$userid], $params);
            $name = html_writer::tag('span', fullname($userinfo[$userid]), array('class' => 'fullname'));
            $name = html_writer::tag('span', $name, array('class' => 'user'));
        }

        $grades = $this->output->container($content, 'grades');
        $assessmentdetailsinner = $this->output->container($name . $grades, 'assessmentdetails-inner');

        return $this->output->container($userpicture . $assessmentdetailsinner, 'assessmentdetails');
    }

}
