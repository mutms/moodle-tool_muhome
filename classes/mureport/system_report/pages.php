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

namespace tool_muhome\mureport\system_report;

use tool_mureport\mureport\system_report;
use tool_muhome\mureport\entity\page;
use tool_mulib\local\sql;
use core\exception\invalid_parameter_exception;
use core\lang_string;

/**
 * Custom home pages in a category and its subcategories, or all pages, for page managers.
 *
 * @package     tool_muhome
 * @copyright   2026 Petr Skoda
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class pages extends system_report {
    /**
     * Make report instance.
     *
     * @param int $contextid system or category context
     * @return self
     */
    public static function make_instance(int $contextid): self {
        return self::from_constraints(['contextid' => $contextid]);
    }

    #[\Override]
    public static function get_title(): string {
        return get_string('pages', 'tool_muhome');
    }

    #[\Override]
    public function get_context(): \core\context {
        $context = $this->get_constraint_context();
        if (!$context || !in_array((int)$context->contextlevel, [CONTEXT_SYSTEM, CONTEXT_COURSECAT], true)) {
            throw new invalid_parameter_exception('Invalid page context');
        }
        return $context;
    }

    #[\Override]
    protected function check_access(): void {
        $this->require_capability('tool/muhome:view');
    }

    #[\Override]
    protected function define(): void {
        $this->set_main_entity(new page('page'));
        $this->set_row_muted(
            new sql('page.status <> :mutedstatus', ['mutedstatus' => \tool_muhome\local\page::STATUS_ACTIVE]),
            ['page']
        );
        $this->add_capability_restriction('page', 'tool/muhome:view');

        $this->set_default_columns([
            'page:priority',
            'page:name',
            'page:category',
            'page:title',
            'page:guestvisible',
            'page:uservisible',
            'page:cohortvisible',
            'page:hiddenbefore',
            'page:hiddenafter',
            'page:hiddenfromtenants',
            'page:status',
            'page:actions',
        ]);
        $this->set_default_filters([
            'page:name' => ['multisearch' => true],
            'page:title' => ['multisearch' => true],
            'page:id' => ['multisearch' => true],
            'page:category',
            'page:status',
            'page:guestvisible',
            'page:uservisible',
        ]);
        $this->set_multisearch_label(new lang_string('pagesearch', 'tool_muhome'));
        $this->set_default_sorting(['page:priority' => 'desc']);
    }

    #[\Override]
    protected function apply_constraints(): void {
        $context = $this->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            $this->add_context_restriction('page', $context);
        }
    }
}
