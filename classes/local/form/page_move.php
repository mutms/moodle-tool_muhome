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

namespace tool_muhome\local\form;

use tool_mulib\muform\element\autocomplete;
use tool_mulib\muform\element\buttons;
use tool_mulib\muform\element\cancel;
use tool_mulib\muform\element\info;
use tool_mulib\muform\element\submit;
use tool_mulib\muform\form;
use tool_muhome\muform\autocomplete\page_contextid;

/**
 * Move page to a different context.
 *
 * @package    tool_muhome
 * @copyright  2026 Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class page_move extends form {
    #[\Override]
    protected function definition(): void {
        $page = $this->get_current_data();

        $this->add(new info('name', get_string('page_name', 'tool_muhome')));

        $this->add(new info('title', get_string('page_title', 'tool_muhome')));

        $source = new page_contextid((int)$page['contextid']);
        $contextid = new autocomplete('contextid', get_string('page_category', 'tool_muhome'), $source);
        $contextid->set_required(true);
        $this->add($contextid);

        $this->add(new buttons('buttons'));
        $this->add(new submit('submit', get_string('page_move', 'tool_muhome')), 'buttons');
        $this->add(new cancel(), 'buttons');
    }
}
