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
 * Moodle's Clean UdeM theme.
 *
 * @package   theme_cleanudem
 * @copyright 2014 Universite de Montreal
 * @author    Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Clean UdeM core renderer.
 *
 * @copyright 2014 Universite de Montreal
 * @author    Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_cleanudem_core_renderer extends theme_bootstrapbase_core_renderer {

    /**
     * The name of the variable that differentiates the courses that are hidden compared to other.
     */
    const HIDDEN_COURSE = 'hiddencourse';

    /**
     * The identifier of the course Help StudiUM.
     */
    const HELP_STUDIUM_COURSE_ID = 11;

    /**
     * The key word and the name of the class divider for adding a divider in the menu.
     */
    const DIVIDER = 'divider';

    /**
     * Gets the HTML for the frontpage heading button.
     *
     * @since 2.5.1 2.6
     * @return string HTML.
     */
    public function frontpage_heading_button() {
        global $OUTPUT, $SITE;
        if ($this->page->user_allowed_editing()) {
            return $OUTPUT->edit_button(new moodle_url('course/view.php', array('id' => $SITE->id)));
        }
        return $this->page->button;
    }

    /**
     * Gets the HTML for the studium logo box div.
     *
     * @param string $suffix The suffix used in the image (ex. white for the navigation logo box).
     * @return string html The generated html fragment of the logo box.
     */
    public function studium_logobox($suffix = '') {
        return theme_cleanudem_renderer_helper::studium_logobox($suffix);
    }

    /**
     * Add the favicon to the header of a page.
     *
     * @return string the html required to add the favicon.
     */
    public static function favicon_links() {
        return theme_cleanudem_renderer_helper::favicon_links();
    }

    /**
     * Add the msapplication meta tags required for the windows 8 start screen tiles to the header of a page.
     *
     * @return string the html required to add the meta tags.
     */
    public static function msapplication_metas() {
        return theme_cleanudem_renderer_helper::msapplication_metas();
    }

    /**
     * Override the JS require function to hide a block.
     * This is required to call a custom YUI3 module.
     *
     * @param block_contents $bc A block_contents object
     */
    protected function init_block_hider_js(block_contents $bc) {
        if (!empty($bc->attributes['id']) and $bc->collapsible != block_contents::NOT_HIDEABLE) {
            $config = new stdClass;
            $config->id = $bc->attributes['id'];
            $config->title = strip_tags($bc->title);
            $config->preference = 'block' . $bc->blockinstanceid . 'hidden';
            $config->tooltipVisible = get_string('hideblocka', 'access', $config->title);
            $config->tooltipHidden = get_string('showblocka', 'access', $config->title);

            $this->page->requires->yui_module(
                'moodle-theme_cleanudem-blockhider',
                'M.theme_cleanudem.init_block_hider',
                array($config)
            );

            user_preference_allow_ajax_update($config->preference, PARAM_BOOL);
        }
    }

    /**
     * Add the javascript who control the behavior of an item who have a dropdown menu.
     *
     * @param string $custommenuitems The menu items definition in syntax required by {@link convert_text_to_menu_nodes()}
     * @return string the rendered custom menu.
     */
    public function custom_menu($custommenuitems = '') {
        $this->page->requires->yui_module(
            'moodle-theme_cleanudem-navdropdownbehavior',
            'M.theme_cleanudem.init_nav_dropdown_behavior'
        );
        return parent::custom_menu($custommenuitems);
    }

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method produces makes use of the YUI3 menunav widget
     * and requires very specific html elements and classes.
     *
     * @param custom_menu $menu The custom menu to render.
     * @return string The html fragment of the menu.
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $USER;

        $helpstring = get_string('help');

        // Find "Help" menu and add it if not exists.
        foreach ($menu->get_children() as $child) {
            if ($child->get_text() == $helpstring) {
                $help = $child;
                break;
            }
        }

        if (!isset($help)) {
            $help = $menu->add($helpstring, new moodle_url('#'), $helpstring, 0);
        }

        // Add "Home" in the menu.
        $menu->add(get_string('home'), new moodle_url('/?redirect=0'), get_string('home'), -5);

        // Add "My courses" in the menu.
        $menu->add(get_string('frontpagecourselist'), new moodle_url('/course'), get_string('frontpagecourselist'), -2);

        if (isloggedin() && !isguestuser()) {
            // Add "My courses" items in the menu.
            $branchtitle = get_string('mycourses');
            $branchurl = new moodle_url('/my/index.php');
            $branch = $menu->add($branchtitle, $branchurl, $branchtitle, -3);
            if ($my = udem_enrol_get_my_courses_sorted_by_session()) {
                static $maxitems = 10;
                $itemid = 0;
                foreach ($my as $mycourse) {
                    $param = array('id' => $mycourse->id);
                    if (!$mycourse->visible) {
                        $param[self::HIDDEN_COURSE] = 1;
                    }
                    $branch->add($mycourse->shortname, new moodle_url('/course/view.php', $param), $mycourse->fullname);
                    $itemid++;
                    if ($itemid >= $maxitems) {
                        $showall = get_string('showallmycourses', 'theme_cleanudem');
                        $branch->add(self::DIVIDER);
                        $branch->add($showall, $branchurl, $showall);
                        break;
                    }
                }
                if ($itemid > 0 && !theme_cleanudem_is_default_device_type()) {
                    $menu->add(get_string('myhome'), new moodle_url('/my/index.php'), get_string('myhome'), -3);
                }
            }

            // Add "Help StudiUM" items in the menu.
            if (get_course(self::HELP_STUDIUM_COURSE_ID)) {
                $examplesstring = get_string('examples', 'theme_cleanudem');
                $teacherforumstring = get_string('teacher_forum', 'theme_cleanudem');
                $studentforumstring = get_string('student_forum', 'theme_cleanudem');
                $help->add($examplesstring, new moodle_url('/mod/page/view.php?id=30870'), $examplesstring);
                $help->add($teacherforumstring, new moodle_url('/mod/forum/view.php?id=249'), $teacherforumstring);
                $help->add($studentforumstring, new moodle_url('/mod/forum/view.php?id=250'), $studentforumstring);
            }
            $requestcoursesitestring = get_string('requestcoursesite', 'theme_cleanudem');
            $help->add($requestcoursesitestring, new moodle_url('/course/index.php?categoryid=1'), $requestcoursesitestring);
        }

        return parent::render_custom_menu($menu);
    }

    /**
     * This code renders the custom menu items for the
     * bootstrap dropdown menu.
     *
     * @param custom_menu_item $menunode The menu node containing the item to render.
     * @param int $level The position where render the menu.
     * @return string The html fragment of the menu item.
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0) {
        global $USER;

        static $submenucount = 0;

        $target = theme_cleanudem_get_target($menunode->get_url());

        if ($menunode->has_children()) {

            if ($level == 1) {
                $class = 'dropdown';
                if ($isusermenu = $menunode->get_text() == fullname($USER)) {
                    $class .= ' usermenu';
                }
            } else {
                $class = 'dropdown-submenu';
            }

            if ($menunode === $this->language) {
                $class .= ' langmenu';
            }
            $content = html_writer::start_tag('li', array('class' => $class));
            // If the child has menus render it as a sub menu.
            $submenucount++;

            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_' . $submenucount;
            }

            $datatoggle = 'dropdown';

            if (theme_cleanudem_is_default_device_type() && $menunode->get_title() != get_string('language')) {
                $datatoggle = '';
            }

            $content .= html_writer::start_tag('a', array('href' => $url, 'target' => $target , 'class' => 'dropdown-toggle',
                'data-toggle' => $datatoggle, 'title' => $menunode->get_title()));
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= html_writer::tag('b', '', array('class' => 'caret'));
                if ($isusermenu) {
                    $size = 30;
                    if (!theme_cleanudem_is_default_device_type()) {
                        $size *= 2;
                    }
                    $content .= $this->user_picture($USER, array('link' => false, 'size' => $size, 'alttext' => true));
                }
            }
            $content .= html_writer::end_tag('a');
            $content .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= html_writer::end_tag('ul');
        } else {
            if ($menunode->get_text() == self::DIVIDER) {
                return html_writer::start_tag('li', array('class' => self::DIVIDER));
            }
            $content = html_writer::start_tag('li');
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
                $title = $menunode->get_text();
                $class = '';
                if (array_key_exists(self::HIDDEN_COURSE, $url->params())) {
                    $class = 'dimmed';
                    $title = udem_add_unavailable_course_suffix($title);
                    $url->remove_params(self::HIDDEN_COURSE);
                }
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $title, array('title' => $menunode->get_title(),
                'target' => $target, 'class' => $class));
        }
        return $content;
    }

    /**
     * Add a user menu.
     *
     * @return string The html fragment of the user menu.
     */
    public function user_menu() {
        $usermenu = new custom_menu('', current_language());
        return $this->render_user_menu($usermenu);
    }

    /**
     * Renders a custom menu object for the user menu.
     *
     * @param custom_menu $menu The custom menu used for adding items.
     * @return string The html fragment of the user menu.
     */
    protected function render_user_menu(custom_menu $menu) {
        global $USER;
        $content = '';
        if (isloggedin() && !isguestuser()) {
            // Add The user menu.
            $fullname = fullname($USER);
            $usermenu = $menu->add($fullname, new moodle_url('#'), $fullname, 10001);

            // View profile.
            $viewprofile = get_string('viewprofile');
            $usermenu->add($viewprofile, new moodle_url('/user/profile.php', array('id' => $USER->id)), $viewprofile);

            // Edit profile.
            $editmyprofile = get_string('editmyprofile');
            $usermenu->add($editmyprofile, new moodle_url('/user/edit.php', array('id' => $USER->id)), $editmyprofile);

            $usermenu->add(self::DIVIDER);

            // My home.
            $my = get_string('myhome');
            $usermenu->add($my, new moodle_url('/my/index.php'), $my);

            // Forum posts.
            $forumpost = get_string('forumposts', 'forum');
            $usermenu->add($forumpost, new moodle_url('/mod/forum/user.php', array('id' => $USER->id)), $forumpost);

            // Messages.
            $message = get_string('messages', 'message');
            $usermenu->add($message, new moodle_url('/message/index.php', array('user1' => $USER->id)), $message);

            // My files.
            $myfiles = get_string('myfiles');
            $usermenu->add($myfiles, new moodle_url('/user/files.php'), $myfiles);

            // My badges.
            $mybadges = get_string('mybadges', 'badges');
            $usermenu->add($mybadges, new moodle_url('/badges/mybadges.php'), $mybadges);

            $usermenu->add(self::DIVIDER);

            // Logout.
            $logout = get_string('logout');
            $usermenu->add($logout, new moodle_url('/login/logout.php', array('sesskey' => sesskey(), 'alt' => 'logout')), $logout);

            $content .= html_writer::start_tag('ul', array('class' => 'nav'));
            foreach ($menu->get_children() as $item) {
                $content .= $this->render_custom_menu_item($item, 1);
            }

            return $content . html_writer::end_tag('ul');
        }
        return $content;
    }

    /**
     * Add the login buttons, CAS and No CAS.
     */
    public function login_buttons() {
        global $OUTPUT;
        $content = '';
        if (!isloggedin()) {
            $loginpage = ((string)$this->page->url === get_login_url());
            if ($loginpage) {
                $content .= html_writer::div(get_string('loggedinnot', 'moodle'), 'navbar-text logininfo pull-right');
            } else {
                $url = new moodle_url(get_login_url());
                $method = 'get';
                $content .= html_writer::start_div('login-buttons pull-right');
                $url->param('authCAS', 'CAS');
                $content .= $OUTPUT->single_button($url, get_string('acceslogincas', 'auth_cas'), $method,
                        array('class' => 'login login-cas buttonemphasis',
                        'tooltip' => get_string('acceslogincastitle', 'auth_cas')));
                if (udem_is_multiauth_cas()) {
                    $url->param('authCAS', 'NOCAS');
                    $content .= $OUTPUT->single_button($url, get_string('accesloginnocas', 'auth_cas'), $method,
                            array('class' => 'login login-nocas', 'tooltip' => get_string('accesloginnocastitle', 'auth_cas')));
                    $content .= $OUTPUT->help_icon('accesloginnocas', 'auth_cas');
                }
                $content .= html_writer::end_div();
            }
        }
        return $content;
    }

    /**
     * Add a fullscreen button.
     *
     * @param boolean $state The state of the fullscreen button.
     * @return string $content The html fragment of the button.
     */
    public function fullscreen_button($state = false) {
        $content = '';
        if (isloggedin() && !isguestuser()) {
            $string = 'enablefullscreenmode';
            $statestring = 'true';
            if ($state) {
                $string = 'disablefullscreenmode';
                $statestring = 'false';
            }
            $enable = html_writer::span('', 'fa fa-compress');
            $disable = html_writer::span('', 'fa fa-expand');
            $url = new moodle_url($this->page->url, array('fullscreenmodestate' => $statestring));
            $content = html_writer::link($url, $enable . $disable, array('title' => get_string($string, 'theme_cleanudem'),
                'class' => 'navbar-text fullscreen-toggle-btn'));
        }
        return $content;
    }

    /**
     * Gets HTML for the page heading.
     * If the heading is a course title and the course is not visible,
     * add an suffix to specify it.
     *
     * @since 2.5.1 2.6
     * @param string $tag The tag to encase the heading in. h1 by default.
     * @return string HTML.
     */
    public function page_heading($tag = 'h1') {
        global $COURSE, $USER;
        $heading = $this->page->heading;
        $iscoursehomepage = strpos($this->page->pagetype, 'course') === 0;
        if ($iscoursehomepage && empty($COURSE->visible)) {
            $heading = udem_add_unavailable_course_suffix($heading, true, $COURSE->id, $USER->id);
        }
        return html_writer::tag($tag, $heading);
    }

    /**
     * Layout elements.
     *
     * This renderer does not override any existing renderer but provides a way of including
     * portion of files into your layout pages. Those portions are called 'elements' and are
     * located in the directory layout/elements of your theme.
     *
     * To include one of those elements in your layout (or other elements), use this:
     *
     *   <?php echo $OUTPUT->element('elementNameWithoutDotPHP'); ?>
     *
     * You can also pass some variables to your elements, by passing an array as the second argument.
     *
     *   $myvars = array('var1' => 'Hello', 'var2' => 'World');
     *   echo $OUTPUT->element('elementNameWithoutDotPHP', $myvars);
     *
     * Then, you can simply use the variables in your element, in our example your element could be:
     *
     *   <h1><?php echo $var1; ?> <?php echo $var2; ?></h1>
     *
     * You do not need to pass $CFG, $OUTPUT or $VARS, they are made available for you.
     *
     * @param string $name of the element, without .php.
     * @param array $vars associative array of variables.
     * @return string
     */
    public function element($name, $vars = array()) {
        $OUTPUT = $this;
        $PAGE = $this->page;
        $COURSE = $this->page->course;

        $element = $name . '.php';
        $candidate = $this->page->theme->dir . '/layout/elements/' . $element;
        if (!is_readable($candidate)) {
            debugging("Could not include element $name.");
            return '';
        }

        ob_start();
        include($candidate);
        $output = ob_get_clean();
        return $output;
    }
}
