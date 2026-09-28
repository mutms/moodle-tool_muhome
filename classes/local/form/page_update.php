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

use tool_mulib\muform\element\autocompletemany;
use tool_mulib\muform\element\buttons;
use tool_mulib\muform\element\cancel;
use tool_mulib\muform\element\checkbox;
use tool_mulib\muform\element\datetime;
use tool_mulib\muform\element\number;
use tool_mulib\muform\element\radios;
use tool_mulib\muform\element\submit;
use tool_mulib\muform\element\text;
use tool_mulib\muform\form;
use tool_muhome\local\page;
use tool_muhome\muform\autocompletemany\page_cohortvisible;

/**
 * Update a page.
 *
 * @package    tool_muhome
 * @copyright  2025 Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class page_update extends form {
    #[\Override]
    protected function definition(): void {
        $page = $this->get_current_data();

        $name = new text('name', get_string('page_name', 'tool_muhome'), ['maxlength' => 1333, 'width' => 'full']);
        $name->set_required(true);
        $this->add($name);

        $this->add(new text('title', get_string('page_title', 'tool_muhome'), ['maxlength' => 1333, 'width' => 'full']));

        $this->add(new number('priority', get_string('page_priority', 'tool_muhome'), ['width' => 'small']));

        $this->add(new checkbox('guestvisible', get_string('guestvisible', 'tool_muhome')));

        $this->add(new checkbox('uservisible', get_string('uservisible', 'tool_muhome')));

        $source = new page_cohortvisible((int)$page['id'], 0);
        $this->add(new autocompletemany('cohortvisible', get_string('cohortvisible', 'tool_muhome'), $source));
        $this->get_display_manager()->hide_if('cohortvisible', 'uservisible', 'checked');

        $this->add(new datetime('hiddenbefore', get_string('hiddenbefore', 'tool_muhome')));

        $this->add(new datetime('hiddenafter', get_string('hiddenafter', 'tool_muhome')));

        if (\tool_mulib\local\mulib::is_mutenancy_active()) {
            $this->add(new checkbox('hiddenfromtenants', get_string('hiddenfromtenants', 'tool_muhome')));
        }

        $status = new radios('status', get_string('page_status', 'tool_muhome'), page::get_statuses_menu());
        $status->set_required(true);
        $this->add($status);

        $this->add(new buttons('buttons'));
        $this->add(new submit('submit', get_string('update')), 'buttons');
        $this->add(new cancel(), 'buttons');
    }

    #[\Override]
    protected function validation(array $data, array &$allerrors): void {
        if ($data['hiddenbefore'] && $data['hiddenafter'] && $data['hiddenbefore'] > $data['hiddenafter']) {
            $allerrors['hiddenafter'][] = get_string('error');
        }
    }
}
