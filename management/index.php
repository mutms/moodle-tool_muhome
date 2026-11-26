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
// phpcs:disable moodle.Files.RequireLogin.Missing
// phpcs:disable moodle.Files.LineLength.TooLong
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch

/**
 * Custom home pages management.
 *
 * @package    tool_muhome
 * @copyright  2025 Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_muhome\local\management;
use core\url;

/** @var core_renderer $OUTPUT */

require('../../../../config.php');

$contextid = optional_param('contextid', 0, PARAM_INT);

$report = \tool_muhome\mureport\system_report\pages::make_instance($contextid ?: context_system::instance()->id);
$report->require_access();
$context = $report->get_context();

$currenturl = new url('/admin/tool/muhome/management/index.php', ['contextid' => $context->id]);

management::setup_index_page($currenturl, $context);
$report->init_page($currenturl);

echo $OUTPUT->header();
echo $OUTPUT->render(new \tool_mureport\output\report_full($report));
echo $OUTPUT->footer();
