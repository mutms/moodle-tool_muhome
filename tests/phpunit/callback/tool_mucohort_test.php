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

namespace tool_muhome\phpunit\callback;

use tool_muhome\callback\tool_mucohort;

/**
 * Tests of tool_mucohort hook callbacks.
 *
 * @group      MuTMS
 * @package    tool_muhome
 * @copyright  2026 Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \tool_muhome\callback\tool_mucohort
 */
final class tool_mucohort_test extends \advanced_testcase {
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        if (!\tool_mulib\local\mulib::is_mucohort_available()) {
            $this->markTestSkipped('tool_mucohort is not installed');
        }
    }

    /**
     * Returns usage items as "section: name|url|visible" strings.
     *
     * @param \stdClass $cohort
     * @return string[]
     */
    private function get_usage(\stdClass $cohort): array {
        $hook = new \tool_mucohort\hook\cohort_usage($cohort);
        tool_mucohort::cohort_usage($hook);
        $result = [];
        foreach ($hook->get_sections() as $section => $items) {
            foreach ($items as $item) {
                $result[] = $section . ': ' . $item->name . '|' . ($item->url ? 'url' : '') . '|' . ($item->visible ? 'visible' : 'hidden');
            }
        }
        return $result;
    }

    public function test_cohort_usage(): void {
        /** @var \tool_muhome_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_muhome');
        $cohort = $this->getDataGenerator()->create_cohort();
        $generator->create_page(['name' => 'Page 1', 'uservisible' => 0, 'cohortvisible' => [$cohort->id]]);

        $section = get_string('cohort_usage_page', 'tool_muhome');
        $this->setAdminUser();
        $this->assertSame([$section . ': Page 1|url|visible'], $this->get_usage($cohort));
        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame([$section . ': Page 1||hidden'], $this->get_usage($cohort));
    }
}
