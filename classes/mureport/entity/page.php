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

namespace tool_muhome\mureport\entity;

use tool_mureport\mureport\action;
use tool_mureport\mureport\cell;
use tool_mureport\mureport\column;
use tool_mureport\mureport\entity;
use tool_mureport\mureport\export_value;
use tool_mureport\mureport\filter;
use tool_mureport\mureport\row;
use tool_mulib\local\sql;
use core\lang_string;
use core\url;
use stdClass;

/**
 * Custom home page entity, pages in tool_muhome_page.
 *
 * @package     tool_muhome
 * @copyright   2026 Petr Skoda
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class page extends entity {
    #[\Override]
    public function get_default_source(): sql {
        return new sql('{tool_muhome_page}');
    }

    #[\Override]
    protected function get_default_title(): string {
        return get_string('page', 'tool_muhome');
    }

    #[\Override]
    public function get_context_sql(): ?sql {
        return new sql("{$this->alias}.contextid");
    }

    #[\Override]
    public function get_context_for_key(int|string $key): ?\core\context {
        global $DB;
        $contextid = $DB->get_field('tool_muhome_page', 'contextid', ['id' => $key]);
        return $contextid ? (\core\context::instance_by_id($contextid, IGNORE_MISSING) ?: null) : null;
    }

    #[\Override]
    public function get_column_names(): array {
        return [
            'priority', 'name', 'id', 'title', 'status', 'category', 'guestvisible', 'uservisible', 'cohortvisible', 'hiddenbefore',
            'hiddenafter', 'hiddenfromtenants', 'timecreated', 'actions',
        ];
    }

    #[\Override]
    protected function define_columns(string $selector): array {
        $alias = $this->alias;
        $column = match ($selector) {
            'id' => $this->create_id_column(),
            'name' => $this->define_name_column(),
            'status' => $this->create_column(
                'status',
                new lang_string('page_status', 'tool_muhome'),
                column::TYPE_ENUMERATION
            )
                ->add_field('status', "$alias.status")
                ->set_options(fn(): array => \tool_muhome\local\page::get_statuses_menu())
                ->set_sortable(true),
            'category' => $this->define_category_column(),
            'priority' => $this->create_column('priority', new lang_string('page_priority', 'tool_muhome'), column::TYPE_INTEGER)
                ->add_field('priority', "$alias.priority")
                ->set_sortable(true),
            'title' => $this->define_title_column(),
            'hiddenbefore', 'hiddenafter' => $this->create_column(
                $selector,
                new lang_string($selector, 'tool_muhome'),
                column::TYPE_TIMESTAMP
            )
                ->add_field($selector, "$alias.$selector")
                ->set_sortable(true),
            'guestvisible', 'uservisible' => $this->create_column(
                $selector,
                new lang_string($selector, 'tool_muhome'),
                column::TYPE_BOOLEAN
            )
                ->add_field($selector, "$alias.$selector")
                ->set_sortable(true),
            'hiddenfromtenants' => $this->create_column(
                'hiddenfromtenants',
                new lang_string('hiddenfromtenants', 'tool_muhome'),
                column::TYPE_BOOLEAN
            )
                ->add_field('hiddenfromtenants', "$alias.hiddenfromtenants")
                ->set_sortable(true)
                ->set_available(fn(): bool => \tool_mulib\local\mulib::is_mutenancy_active()),
            'cohortvisible' => $this->define_cohortvisible_column(),
            'timecreated' => $this->create_column(
                'timecreated',
                new lang_string('timecreated', 'core_reportbuilder'),
                column::TYPE_TIMESTAMP
            )
                ->add_field('timecreated', "$alias.timecreated")
                ->set_sortable(true),
            'actions' => $this->define_actions_column(),
            default => null,
        };
        return $column ? [$column] : [];
    }

    #[\Override]
    public function get_filter_names(): array {
        return [
            'name', 'id', 'title', 'status', 'category', 'guestvisible', 'uservisible', 'hiddenbefore', 'hiddenafter',
            'hiddenfromtenants', 'timecreated',
        ];
    }

    #[\Override]
    protected function define_filters(string $selector): array {
        $alias = $this->alias;
        $filter = match ($selector) {
            'id' => $this->create_id_filter(),
            'name' => new filter\text($alias, 'name', new lang_string('page_name', 'tool_muhome'), "$alias.name"),
            'status' => (new filter\checkboxes(
                $alias,
                'status',
                new lang_string('page_status', 'tool_muhome'),
                "$alias.status"
            ))
                ->set_options(fn(): array => \tool_muhome\local\page::get_statuses_menu()),
            'category' => (new filter\picker(
                $alias,
                'category',
                new lang_string('page_category', 'tool_muhome'),
                "$alias.contextid"
            ))
                ->set_integer_values()
                ->set_labels(fn(array $values): array => self::get_category_labels($values)),
            'title' => new filter\text($alias, 'title', new lang_string('page_title', 'tool_muhome'), "$alias.title"),
            'hiddenbefore', 'hiddenafter' => new filter\date(
                $alias,
                $selector,
                new lang_string($selector, 'tool_muhome'),
                "$alias.$selector"
            ),
            'guestvisible', 'uservisible' => new filter\boolean(
                $alias,
                $selector,
                new lang_string($selector, 'tool_muhome'),
                "$alias.$selector"
            ),
            'hiddenfromtenants' => (new filter\boolean(
                $alias,
                'hiddenfromtenants',
                new lang_string('hiddenfromtenants', 'tool_muhome'),
                "$alias.hiddenfromtenants"
            ))
                ->set_available(fn(): bool => \tool_mulib\local\mulib::is_mutenancy_active()),
            'timecreated' => new filter\date(
                $alias,
                'timecreated',
                new lang_string('timecreated', 'core_reportbuilder'),
                "$alias.timecreated"
            ),
            default => null,
        };
        return $filter ? [$filter] : [];
    }

    /**
     * Define page name column, linked to the page.
     *
     * @return column
     */
    private function define_name_column(): column {
        $alias = $this->alias;
        $name = fn(stdClass $fields, bool $escape): string => format_string(
            $fields->name,
            true,
            ['context' => \core\context::instance_by_id($fields->contextid), 'escape' => $escape]
        );
        $url = fn(stdClass $fields): url => \tool_muhome\local\page::get_url((int)$fields->id);
        return $this->create_column('name', new lang_string('page_name', 'tool_muhome'), column::TYPE_TEXT)
            ->add_field('name', "$alias.name")
            ->add_field('id', "$alias.id")
            ->add_field('contextid', "$alias.contextid")
            ->set_sortable(true)
            ->add_formatter(
                'name',
                fn(cell $cell): string => $name($cell->get_fields(), true),
                fn(cell $cell): export_value => export_value::text($name($cell->get_fields(), false))
            )
            ->add_formatter(
                'link',
                fn(cell $cell): string => \html_writer::link($url($cell->get_fields()), $name($cell->get_fields(), true)),
                fn(cell $cell): export_value => export_value::link($url($cell->get_fields()), $name($cell->get_fields(), false))
            )
            ->set_default_formatter('link');
    }

    /**
     * Define page category column, linked to page management of the category.
     *
     * @return column
     */
    private function define_category_column(): column {
        $alias = $this->alias;
        $name = fn(stdClass $fields): string
            => \core\context::instance_by_id($fields->contextid)->get_context_name(false, false, false);
        $url = fn(stdClass $fields): url
            => new url('/admin/tool/muhome/management/index.php', ['contextid' => $fields->contextid]);
        return $this->create_column('category', new lang_string('page_category', 'tool_muhome'), column::TYPE_TEXT)
            ->add_field('contextid', "$alias.contextid")
            ->add_formatter(
                'name',
                fn(cell $cell): string => s($name($cell->get_fields())),
                fn(cell $cell): export_value => export_value::text($name($cell->get_fields()))
            )
            ->add_formatter(
                'link',
                function (cell $cell) use ($name, $url): string {
                    $fields = $cell->get_fields();
                    if (!has_capability('tool/muhome:view', \core\context::instance_by_id($fields->contextid))) {
                        return s($name($fields));
                    }
                    return \html_writer::link($url($fields), s($name($fields)));
                },
                fn(cell $cell): export_value => export_value::text($name($cell->get_fields()))
            )
            ->set_default_formatter('link');
    }

    /**
     * Define page title column.
     *
     * @return column
     */
    private function define_title_column(): column {
        $alias = $this->alias;
        $title = fn(stdClass $fields, bool $escape): string => $fields->title === null ? '' : format_string(
            $fields->title,
            true,
            ['context' => \core\context::instance_by_id($fields->contextid), 'escape' => $escape]
        );
        return $this->create_column('title', new lang_string('page_title', 'tool_muhome'), column::TYPE_TEXT)
            ->add_field('title', "$alias.title")
            ->add_field('contextid', "$alias.contextid")
            ->set_sortable(true)
            ->add_formatter(
                'title',
                fn(cell $cell): string => $title($cell->get_fields(), true),
                fn(cell $cell): export_value => export_value::text($title($cell->get_fields(), false))
            )
            ->set_default_formatter('title');
    }

    /**
     * Define cohorts column, cohorts are used only when the page is not visible to all users.
     *
     * @return column
     */
    private function define_cohortvisible_column(): column {
        $alias = $this->alias;
        $cohortids = function (stdClass $fields): array {
            if ($fields->uservisible) {
                return [];
            }
            return array_keys(\tool_muhome\local\page::get_cohortvisible_menu((int)$fields->id));
        };
        return $this->create_column('cohortvisible', new lang_string('cohortvisible', 'tool_muhome'), column::TYPE_TEXT)
            ->add_field('id', "$alias.id")
            ->add_field('uservisible', "$alias.uservisible")
            ->add_formatter(
                'list',
                fn(cell $cell): string => implode(', ', \tool_mulib\local\cohort_util::format_cohort_names(
                    $cohortids($cell->get_fields())
                )),
                fn(cell $cell): export_value => export_value::list(array_map(
                    fn(string $name): string => html_to_text($name, 0, false),
                    \tool_mulib\local\cohort_util::format_cohort_names($cohortids($cell->get_fields()), false)
                ))
            )
            ->set_default_formatter('list');
    }

    /**
     * Define page actions column.
     *
     * @return column
     */
    private function define_actions_column(): column {
        $alias = $this->alias;
        $column = $this->create_actions_column('actions', new lang_string('actions'))
            ->add_field('id', "$alias.id")
            ->add_field('status', "$alias.status");
        $manage = function (row $row) use ($alias): bool {
            $context = $row->get_context($alias);
            return $context && has_capability('tool/muhome:manage', $context);
        };
        $url = fn(string $script): \Closure => fn(stdClass $fields): url
            => new url("/admin/tool/muhome/management/$script.php", ['id' => $fields->id]);

        $column->add_action(new action('update', new lang_string('page_update', 'tool_muhome'), 'i/settings'))
            ->set_available(fn(stdClass $fields, row $row): bool => $manage($row))
            ->set_url($url('page_update'))
            ->set_dialog('lg');
        $column->add_action(new action('move', new lang_string('page_move', 'tool_muhome'), 'i/folder'))
            ->set_available(fn(stdClass $fields, row $row): bool => $manage($row))
            ->set_url($url('page_move'))
            ->set_dialog('sm');
        $column->add_action(new action('delete', new lang_string('page_delete', 'tool_muhome'), 'i/delete'))
            ->set_available(fn(stdClass $fields, row $row): bool
                => $fields->status != \tool_muhome\local\page::STATUS_ACTIVE && $manage($row))
            ->set_url($url('page_delete'))
            ->set_dialog('sm')
            ->set_destructive();

        return $column;
    }

    /**
     * Returns names of management contexts, the system context or course categories.
     *
     * @param string[] $contextids
     * @return array context id => plain text name
     */
    public static function get_category_labels(array $contextids): array {
        global $DB;
        if (!$contextids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal(array_map('intval', $contextids), SQL_PARAMS_NAMED, 'ctx');
        $params['catlevel'] = CONTEXT_COURSECAT;
        $sql = "SELECT ctx.id, ctx.contextlevel, cc.name
                  FROM {context} ctx
             LEFT JOIN {course_categories} cc ON cc.id = ctx.instanceid AND ctx.contextlevel = :catlevel
                 WHERE ctx.id $insql";
        $result = [];
        $syscontext = \core\context\system::instance();
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            if ((int)$record->contextlevel === CONTEXT_SYSTEM) {
                $result[$record->id] = $syscontext->get_context_name(false, false, false);
            } else if ($record->name !== null) {
                $result[$record->id] = format_string($record->name, true, ['context' => $syscontext, 'escape' => false]);
            }
        }
        return $result;
    }
}
