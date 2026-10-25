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


namespace tool_muhome\callback;

/**
 * Hook callbacks for tool_mucohort.
 *
 * @package    tool_muhome
 * @copyright  2026 Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class tool_mucohort {
    /**
     * List places where the cohort is used.
     *
     * @param \tool_mucohort\hook\cohort_usage $hook
     */
    public static function cohort_usage(\tool_mucohort\hook\cohort_usage $hook): void {
        global $DB;

        $cohort = $hook->get_cohort();
        $sql = "SELECT p.id, p.name, p.contextid
                  FROM {tool_muhome_page} p
                  JOIN {tool_muhome_page_cohortvisible} cv ON cv.pageid = p.id
                 WHERE cv.cohortid = ?
              ORDER BY p.name ASC, p.id ASC";
        $section = get_string('cohort_usage_page', 'tool_muhome');
        foreach ($DB->get_records_sql($sql, [$cohort->id]) as $record) {
            $context = \context::instance_by_id($record->contextid);
            $visible = has_capability('tool/muhome:view', $context);
            $url = $visible ? new \core\url('/admin/tool/muhome/management/index.php', ['contextid' => $context->id]) : null;
            $name = format_string($record->name, true, ['context' => $context, 'escape' => false]);
            $hook->add_item($section, $name, $url, null, $visible);
        }
    }
}
