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
// phpcs:disable moodle.Commenting.DocblockDescription.Missing

namespace tool_muhome\phpunit\mureport\system_report;

use tool_muhome\mureport\system_report\pages;
use tool_muhome\local\page;
use tool_mureport\mureport\request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Custom home pages report tests.
 *
 * @package     tool_muhome
 * @copyright   2026 Petr Skoda
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(pages::class)]
#[CoversClass(\tool_muhome\mureport\entity\page::class)]
#[Group('MuTMS')]
final class pages_test extends \advanced_testcase {
    #[\Override]
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Returns exported rows.
     *
     * @param int $contextid
     * @param request $request
     * @return array
     */
    private function get_rows(int $contextid, request $request = new request([])): array {
        $report = pages::make_instance($contextid);
        $report->require_access();
        return $report->get_query()->get_page($request)->export_rows();
    }

    /**
     * Returns page names of report rows.
     *
     * @param int $contextid
     * @param request $request
     * @return string[]
     */
    private function get_names(int $contextid, request $request = new request([])): array {
        return array_map(fn(array $row): string => strip_tags($row['cells']['page:name']), $this->get_rows($contextid, $request));
    }

    /**
     * Returns action names of a row.
     *
     * @param array $row
     * @return string[]
     */
    private function get_actions(array $row): array {
        return array_column($row['actions']['page:actions'] ?? [], 'name');
    }

    public function test_rows(): void {
        /** @var \tool_muhome_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_muhome');
        $category1 = $this->getDataGenerator()->create_category();
        $category2 = $this->getDataGenerator()->create_category(['parent' => $category1->id]);
        $catcontext1 = \context_coursecat::instance($category1->id);
        $catcontext2 = \context_coursecat::instance($category2->id);
        $syscontext = \context_system::instance();
        $cohort = $this->getDataGenerator()->create_cohort(['name' => 'Cohort X']);

        $page0 = $generator->create_page(['name' => 'Alpha', 'title' => 'Welcome', 'priority' => 30, 'status' => page::STATUS_ACTIVE,
            'uservisible' => 0, 'cohortvisible' => [$cohort->id]]);
        $generator->create_page(['name' => 'Beta', 'priority' => 20, 'contextid' => $catcontext1->id, 'guestvisible' => 1,
            'status' => page::STATUS_DRAFT]);
        $generator->create_page(['name' => 'Gamma', 'priority' => 10, 'contextid' => $catcontext2->id, 'status' => page::STATUS_ACTIVE]);

        $this->setAdminUser();
        $this->assertSame(['Alpha', 'Beta', 'Gamma'], $this->get_names($syscontext->id));
        $this->assertSame(['Beta', 'Gamma'], $this->get_names($catcontext1->id));
        $this->assertSame(['Gamma'], $this->get_names($catcontext2->id));
        $this->assertSame(['Alpha'], $this->get_names($syscontext->id, new request([], null, 0, null, 'welcome')));
        $this->assertSame(['Beta'], $this->get_names($syscontext->id, new request(['page:guestvisible' => ['value' => 1]])));
        $this->assertSame(['Beta'], $this->get_names($syscontext->id, new request(['page:status' => ['values' => [(string)page::STATUS_DRAFT]]])));
        $category = new request(['page:category' => ['values' => [(string)$catcontext1->id]]]);
        $this->assertSame(['Beta'], $this->get_names($syscontext->id, $category));

        $rows = $this->get_rows($syscontext->id);
        $this->assertStringContainsString(page::get_url((int)$page0->id)->out(), $rows[0]['cells']['page:name']);
        $this->assertSame('Welcome', $rows[0]['cells']['page:title']);
        $this->assertSame('30', $rows[0]['cells']['page:priority']);
        $this->assertStringContainsString('Cohort X', $rows[0]['cells']['page:cohortvisible']);
        $this->assertSame('', $rows[1]['cells']['page:cohortvisible']);
        $this->assertSame('Active', $rows[0]['cells']['page:status']);
        $this->assertArrayNotHasKey('page:hiddenfromtenants', $rows[0]['cells']);
        $this->assertSame([false, true, false], array_column($rows, 'muted'));
        $this->assertSame(['update', 'move'], $this->get_actions($rows[0]));
        $this->assertSame(['update', 'move', 'delete'], $this->get_actions($rows[1]));

        $viewer = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('tool/muhome:view', CAP_ALLOW, $roleid, $syscontext);
        role_assign($roleid, $viewer->id, $catcontext1->id);
        $this->setUser($viewer);
        $this->assertSame(['Beta', 'Gamma'], $this->get_names($catcontext1->id));
        $this->assertSame([], $this->get_actions($this->get_rows($catcontext1->id)[0]));
        $this->expectException(\core\exception\required_capability_exception::class);
        $this->get_rows($syscontext->id);
    }

    public function test_invalid_context(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $this->expectException(\core\exception\invalid_parameter_exception::class);
        pages::make_instance(\context_course::instance($course->id)->id)->require_access();
    }
}
