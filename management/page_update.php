<?php
// This file is part of MuTMS suite of plugins for Moodle™ LMS.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.

// phpcs:disable moodle.Files.BoilerplateComment.CommentEndedTooSoon
// phpcs:disable moodle.Files.LineLength.TooLong
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch

/**
 * Update home page.
 *
 * @package    tool_muhome
 * @copyright  2025 Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;
use tool_muhome\local\page;
use tool_mulib\muform\handler;

/** @var moodle_database $DB */
/** @var moodle_page $PAGE */
/** @var core_renderer $OUTPUT */

require('../../../../config.php');

$id = required_param('id', PARAM_INT);

$page = $DB->get_record('tool_muhome_page', ['id' => $id], '*', MUST_EXIST);
$context = context::instance_by_id($page->contextid);

require_login();
require_capability('tool/muhome:manage', $context);

$currenturl = new url('/admin/tool/muhome/management/page_update.php', ['id' => $page->id]);
$returnurl = new url('/admin/tool/muhome/management/index.php', ['contextid' => $context->id]);

$PAGE->set_context($context);
$PAGE->set_url($currenturl);
$title = get_string('page_update', 'tool_muhome');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$handler = handler::from_request();

$page->cohortvisible = array_keys(page::get_cohortvisible_menu($page->id));

$form = new \tool_muhome\local\form\page_update($currenturl, $page);

if ($form->is_cancelled()) {
    $handler->cancelled($returnurl);
}

if ($data = $form->get_data()) {
    $data->id = $page->id;
    page::update($data);
    $handler->submitted($returnurl);
}

$handler->render($form);
