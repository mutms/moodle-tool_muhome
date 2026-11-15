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

namespace tool_muhome\phpunit\muform\autocomplete;

use tool_muhome\muform\autocomplete\page_contextid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Page context autocomplete source tests.
 *
 * @package     tool_muhome
 * @copyright   2026 Petr Skoda
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\tool_muhome\muform\autocomplete\page_contextid::class)]
#[Group('MuTMS')]
final class page_contextid_test extends \advanced_testcase {
    public function test_source(): void {
        $this->resetAfterTest();

        $syscontext = \context_system::instance();

        $category1 = $this->getDataGenerator()->create_category(['name' => 'Kategorie 1']);
        $catcontext1 = \context_coursecat::instance($category1->id);
        $category2 = $this->getDataGenerator()->create_category(['name' => 'Kategorie 2']);
        $catcontext2 = \context_coursecat::instance($category2->id);
        $category3 = $this->getDataGenerator()->create_category(['name' => 'Kategorie 3', 'parent' => $category1->id]);
        $catcontext3 = \context_coursecat::instance($category3->id);

        $user1 = $this->getDataGenerator()->create_user();
        $editorroleid = $this->getDataGenerator()->create_role();
        assign_capability('tool/muhome:manage', CAP_ALLOW, $editorroleid, $syscontext);
        role_assign($editorroleid, $user1->id, $catcontext1->id);

        $this->setUser($user1);
        $source = new page_contextid($catcontext3->id);
        $this->assertSame([$catcontext3->id], $source->get_args());
        $expected = [
            (string)$catcontext1->id => 'Kategorie 1',
            (string)$catcontext3->id => 'Kategorie 1 / Kategorie 3',
        ];
        $this->assertSame($expected, $source->search('', 50, []));
        $this->assertSame([(string)$catcontext3->id => 'Kategorie 1 / Kategorie 3'], $source->search('rie 3', 50, []));
        $this->assertNull($source->search('', 1, []));
        $value = (string)$catcontext1->id;
        $this->assertSame('Kategorie 1', $source->labels([$value])[$value] ?? null);
        $value = (string)$catcontext2->id;
        $this->assertNull($source->labels([$value])[$value] ?? null);
        $value = (string)$syscontext->id;
        $this->assertNull($source->labels([$value])[$value] ?? null);
        $this->assertNull($source->labels(['-1'])['-1'] ?? null);
        $value = (string)\context_user::instance($user1->id)->id;
        $this->assertNull($source->labels([$value])[$value] ?? null);

        $this->setAdminUser();
        $source = new page_contextid($syscontext->id);
        $result = $source->search('', 50, []);
        $defaultcontext = \context_coursecat::instance(\core_course_category::get_default()->id);
        $expected = [$syscontext->id, $defaultcontext->id, $catcontext1->id, $catcontext3->id, $catcontext2->id];
        $this->assertSame($expected, array_keys($result));
        $value = (string)$syscontext->id;
        $this->assertSame('System', $source->labels([$value])[$value] ?? null);

        // The current context stays selectable when the capability is lost.
        $this->setUser($user1);
        role_assign($editorroleid, $user1->id, $catcontext2->id);
        $source = new page_contextid($catcontext2->id);
        role_unassign($editorroleid, $user1->id, $catcontext2->id);
        accesslib_clear_all_caches_for_unit_testing();
        $this->assertArrayNotHasKey((string)$catcontext2->id, $source->search('', 50, []));
        $value = (string)$catcontext2->id;
        $this->assertSame('Kategorie 2', $source->labels([$value])[$value] ?? null);

        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        new page_contextid($catcontext1->id);
    }
}
