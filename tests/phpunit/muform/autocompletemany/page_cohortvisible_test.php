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

namespace tool_muhome\phpunit\muform\autocompletemany;

use tool_muhome\local\page;
use tool_muhome\muform\autocompletemany\page_cohortvisible;
use tool_mulib\local\mulib;

/**
 * Visible to cohorts autocomplete source tests.
 *
 * @group       MuTMS
 * @package     tool_muhome
 * @copyright   2026 Petr Skoda
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \tool_muhome\muform\autocompletemany\page_cohortvisible
 */
final class page_cohortvisible_test extends \advanced_testcase {
    #[\Override]
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_source(): void {
        global $DB;

        /** @var \tool_muhome_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_muhome');

        $syscontext = \context_system::instance();

        $category1 = $this->getDataGenerator()->create_category();
        $catcontext1 = \context_coursecat::instance($category1->id);

        $page1 = $generator->create_page();
        $page2 = $generator->create_page(['contextid' => $catcontext1->id]);

        $cohort1 = $this->getDataGenerator()->create_cohort(['name' => 'Kohorta 1', 'visible' => 0]);
        $cohort2 = $this->getDataGenerator()->create_cohort(['name' => 'Kohorta 2', 'visible' => 0, 'contextid' => $catcontext1->id]);
        $cohort3 = $this->getDataGenerator()->create_cohort(['name' => 'Kohorta 3', 'visible' => 1, 'contextid' => $catcontext1->id]);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $managerrole = $DB->get_record('role', ['shortname' => 'manager'], '*', MUST_EXIST);
        role_assign($managerrole->id, $user1->id, $syscontext);
        role_assign($managerrole->id, $user2->id, $catcontext1);

        $editorroleid = $this->getDataGenerator()->create_role();
        assign_capability('tool/muhome:manage', CAP_ALLOW, $editorroleid, $syscontext);
        role_assign($editorroleid, $user3->id, $catcontext1->id);

        $this->setUser($user1);

        $source = new page_cohortvisible(0, $syscontext->id);
        $this->assertSame([0, $syscontext->id], $source->get_args());
        $all = [$cohort1->id => 'Kohorta 1', $cohort2->id => 'Kohorta 2', $cohort3->id => 'Kohorta 3'];
        $this->assertSame($all, $source->search('', 50, []));
        $this->assertNull($source->search('', 2, []));
        $this->assertSame([$cohort2->id => 'Kohorta 2', $cohort3->id => 'Kohorta 3'], $source->search('', 50, [(string)$cohort1->id]));

        $source = new page_cohortvisible((int)$page1->id, 0);
        $this->assertSame($all, $source->search('', 50, []));
        $this->assertSame([$cohort1->id => 'Kohorta 1'], $source->search('ta 1', 50, []));
        $this->assertSame([], $source->validate([(string)$cohort1->id, (string)$cohort2->id, (string)$cohort3->id]));
        $this->assertSame([(string)$cohort1->id => 'Kohorta 1'], $source->labels([(string)$cohort1->id, '-1', 'x']));

        $this->setUser($user2);

        $source = new page_cohortvisible((int)$page2->id, 0);
        $this->assertSame([$cohort2->id => 'Kohorta 2', $cohort3->id => 'Kohorta 3'], $source->search('', 50, []));
        $this->assertSame([(string)$cohort1->id => 'Error'], $source->validate([(string)$cohort1->id, (string)$cohort2->id, (string)$cohort3->id]));
        $this->assertSame([], $source->labels([(string)$cohort1->id]));

        $source = new page_cohortvisible(0, $catcontext1->id);
        $this->assertSame([$cohort2->id => 'Kohorta 2', $cohort3->id => 'Kohorta 3'], $source->search('', 50, []));

        $this->setUser($user3);

        $source = new page_cohortvisible((int)$page2->id, 0);
        $this->assertSame([$cohort3->id => 'Kohorta 3'], $source->search('', 50, []));

        // Cohorts already allowed to see the page stay valid.
        $this->setAdminUser();
        page::update((object)['id' => $page2->id, 'uservisible' => 0, 'cohortvisible' => [$cohort1->id]]);
        $this->setUser($user2);
        $source = new page_cohortvisible((int)$page2->id, 0);
        $this->assertSame([], $source->validate([(string)$cohort1->id]));
        $this->assertSame([(string)$cohort1->id => 'Kohorta 1'], $source->labels([(string)$cohort1->id]));

        $this->expectException(\required_capability_exception::class);
        new page_cohortvisible((int)$page1->id, 0);
    }

    public function test_source_tenant(): void {
        global $DB;

        if (!mulib::is_mutenancy_available()) {
            $this->markTestSkipped('tenant support not available');
        }

        \tool_mutenancy\local\tenancy::activate();

        /** @var \tool_mutenancy_generator $tenantgenerator */
        $tenantgenerator = $this->getDataGenerator()->get_plugin_generator('tool_mutenancy');

        /** @var \tool_muhome_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_muhome');

        $syscontext = \context_system::instance();
        $tenant1 = $tenantgenerator->create_tenant();
        $tenant1catcontext = \context_coursecat::instance($tenant1->categoryid);
        $tenant2 = $tenantgenerator->create_tenant();
        $tenant2catcontext = \context_coursecat::instance($tenant2->categoryid);

        $tenantcohort1 = $DB->get_record('cohort', ['id' => $tenant1->cohortid]);
        $tenantcohort2 = $DB->get_record('cohort', ['id' => $tenant2->cohortid]);

        $page0 = $generator->create_page([]);
        $page1 = $generator->create_page(['contextid' => $tenant1catcontext->id]);

        $cohort0 = $this->getDataGenerator()->create_cohort(['visible' => 1]);
        $cohort1 = $this->getDataGenerator()->create_cohort(['visible' => 1, 'contextid' => $tenant1catcontext->id]);
        $cohort2 = $this->getDataGenerator()->create_cohort(['visible' => 1, 'contextid' => $tenant2catcontext->id]);

        $user1 = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager'], '*', MUST_EXIST);
        role_assign($managerrole->id, $user1->id, $syscontext);
        $this->setUser($user1);

        // Tenant cohorts are created in system context, they are available everywhere.
        $source = new page_cohortvisible((int)$page0->id, 0);
        $this->assertEqualsCanonicalizing(
            [$cohort0->id, $cohort1->id, $cohort2->id, $tenantcohort1->id, $tenantcohort2->id],
            array_keys($source->search('', 50, []))
        );

        $source = new page_cohortvisible((int)$page1->id, 0);
        $this->assertEqualsCanonicalizing(
            [$cohort0->id, $cohort1->id, $tenantcohort1->id, $tenantcohort2->id],
            array_keys($source->search('', 50, []))
        );
        $this->assertSame([(string)$cohort2->id => 'Error'], $source->validate([(string)$cohort1->id, (string)$cohort2->id]));

        $source = new page_cohortvisible(0, $syscontext->id);
        $this->assertSame([], $source->validate([(string)$cohort2->id]));
        $source = new page_cohortvisible(0, $tenant1catcontext->id);
        $this->assertSame([(string)$cohort2->id => 'Error'], $source->validate([(string)$cohort2->id]));
    }
}
