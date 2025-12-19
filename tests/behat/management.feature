@tool @tool_muhome @javascript @MuTMS
Feature: Managers may manage custom home pages
  Background:
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
      | Cat 2 | 0        | CAT2     |
      | Cat 3 | CAT2     | CAT3     |
    And the following "cohorts" exist:
      | name       | idnumber | contextlevel | reference |
      | Cohort 1   | CH1      | System       |           |
      | Cohort 2   | CH2      | System       |           |
      | Cohort 3   | CH3      | System       |           |
    And the following "users" exist:
      | username  | firstname | lastname  | email                |
      | manager1  | Manager   | 1         | manager1@example.com |
      | viewer1   | Viewer    | 1         | viewer1@example.com  |
      | viewer2   | Viewer    | 2         | viewer2@example.com  |
    And the following "roles" exist:
      | name         | shortname |
      | Page manager | pmanager  |
      | Page viewer  | pviewer   |
    And the following "permission overrides" exist:
      | capability                       | permission | role     | contextlevel | reference |
      | tool/muhome:view                 | Allow      | pmanager | System       |           |
      | tool/muhome:manage               | Allow      | pmanager | System       |           |
      | moodle/cohort:view               | Allow      | pmanager | System       |           |
      | moodle/site:configview           | Allow      | pmanager | System       |           |
      | tool/muhome:view                 | Allow      | pviewer  | System       |           |
      | moodle/site:configview           | Allow      | pviewer  | System       |           |
    And the following "role assigns" exist:
      | user      | role         | contextlevel | reference |
      | manager1  | pmanager     | System       |           |
      | viewer1   | pviewer      | System       |           |
      | viewer2   | pviewer      | Category     | CAT2      |

  Scenario: Manager may create, configure and delete custom home pages
    Given I log in as "manager1"
    And I navigate to "Appearance > Custom home pages > Home pages management" in site administration
    And I should see "No pages found"

    When I press "Add page"
    And the following fields in the ".modal-dialog" "css_element" match these values:
      | Page priority        | 1000        |
      | Draft                | 1           |
      | Visible to guests    | 0           |
      | Visible to all users | 1           |
    And I set the following fields in the ".modal-dialog" "css_element" to these values:
      | Page name            | Home page 1 |
    # There is a focusing problem when clicking the submit button.
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I click on "Add page" "button" in the ".modal-dialog" "css_element"
    Then the following should exist in the "reportbuilder-table" table:
      | Page priority | Page name       | Management category | Title | Visible to guests | Visible to all users | Visible to cohorts  | Hidden before   | Hidden after   | Status   |
      | 1000          | Home page 1     | System              |       | No                | Yes                  |                     |                 |                | Draft    |

    When I press "Add page"
    And the following fields in the ".modal-dialog" "css_element" match these values:
      | Page priority         | 990         |
    And I set the following fields in the ".modal-dialog" "css_element" to these values:
      | Page name             | Home page 2 |
      | Management category   | Cat 1       |
      | Title                 | HP title 2  |
      | Page priority         | 997         |
      | Active                | 1           |
      | Visible to guests     | 1           |
      | Visible to all users  | 0           |
      | Visible to cohorts    | CH1, CH2    |
      | hiddenbefore[enabled] | 1           |
      | hiddenbefore[day]     | 5           |
      | hiddenbefore[month]   | 11          |
      | hiddenbefore[year]    | 2025        |
      | hiddenbefore[hour]    | 09          |
      | hiddenbefore[minute]  | 00          |
      | hiddenafter[enabled]  | 1           |
      | hiddenafter[day]      | 1           |
      | hiddenafter[month]    | 12          |
      | hiddenafter[year]     | 2035        |
      | hiddenafter[hour]     | 09          |
      | hiddenafter[minute]   | 00          |
    # There is a focusing problem when clicking the submit button.
    And I press the tab key
    And I click on "Add page" "button" in the ".modal-dialog" "css_element"
    Then the following should exist in the "reportbuilder-table" table:
      | Page priority | Page name       | Management category | Title      | Visible to guests | Visible to all users | Visible to cohorts  | Hidden before   | Hidden after   | Status   |
      | 1000          | Home page 1     | System              |            | No                | Yes                  |                     |                 |                | Draft    |
      | 997           | Home page 2     | Cat 1               | HP title 2 | Yes               | No                   | Cohort 1, Cohort 2  | 5/11/25, 09:00  | 1/12/35, 09:00 | Active   |

    When I click on "Actions" "link" in the "Home page 1" "table_row"
    And I click on "Configure page" "link" in the "Home page 1" "table_row"
    And the following fields in the ".modal-dialog" "css_element" match these values:
      | Page name             | Home page 1 |
      | Page priority         | 1000        |
      | Draft                 | 1           |
      | Visible to guests     | 0           |
      | Visible to all users  | 1           |
    And I set the following fields in the ".modal-dialog" "css_element" to these values:
      | Page name             | Home page A |
      | Management category   | Cat 2       |
      | Title                 | HP title A  |
      | Page priority         | 1001        |
      | Archived              | 1           |
      | Visible to guests     | 1           |
      | Visible to all users  | 0           |
    And I press the tab key
    And I set the following fields in the ".modal-dialog" "css_element" to these values:
      | Visible to cohorts    | CH2, CH3    |
      | hiddenbefore[enabled] | 1           |
      | hiddenbefore[day]     | 5           |
      | hiddenbefore[month]   | 11          |
      | hiddenbefore[year]    | 2026        |
      | hiddenbefore[hour]    | 09          |
      | hiddenbefore[minute]  | 00          |
    And I press the tab key
    And I set the following fields in the ".modal-dialog" "css_element" to these values:
      | hiddenafter[enabled]  | 1           |
      | hiddenafter[day]      | 1           |
      | hiddenafter[month]    | 12          |
      | hiddenafter[year]     | 2038        |
      | hiddenafter[hour]     | 09          |
      | hiddenafter[minute]   | 00          |
    And I press the tab key
    And I click on "Update" "button" in the ".modal-dialog" "css_element"
    Then the following should exist in the "reportbuilder-table" table:
      | Page priority | Page name       | Management category | Title      | Visible to guests | Visible to all users | Visible to cohorts  | Hidden before   | Hidden after   | Status   |
      | 1001          | Home page A     | Cat 2               | HP title A | Yes               | No                   | Cohort 2, Cohort 3  | 5/11/26, 09:00  | 1/12/38, 09:00 | Archived |
      | 997           | Home page 2     | Cat 1               | HP title 2 | Yes               | No                   | Cohort 1, Cohort 2  | 5/11/25, 09:00  | 1/12/35, 09:00 | Active   |

    When I click on "Actions" "link" in the "Home page A" "table_row"
    And I click on "Configure page" "link" in the "Home page A" "table_row"
    And I set the following fields in the ".modal-dialog" "css_element" to these values:
      | Page name             | Home page 1 |
      | Management category   | System      |
      | Title                 | HP title 1  |
      | Page priority         | 1000        |
      | Archived              | 1           |
      | Visible to guests     | 0           |
      | Visible to all users  | 1           |
      | hiddenbefore[enabled] | 0           |
      | hiddenafter[enabled]  | 0           |
    And I press the tab key
    And I click on "Update" "button" in the ".modal-dialog" "css_element"
    Then the following should exist in the "reportbuilder-table" table:
      | Page priority | Page name       | Management category | Title      | Visible to guests | Visible to all users | Visible to cohorts  | Hidden before   | Hidden after   | Status   |
      | 1000          | Home page 1     | System              | HP title 1 | No                | Yes                  |                     |                 |                | Archived |
      | 997           | Home page 2     | Cat 1               | HP title 2 | Yes               | No                   | Cohort 1, Cohort 2  | 5/11/25, 09:00  | 1/12/35, 09:00 | Active   |

    When I click on "Actions" "link" in the "Home page 1" "table_row"
    And I click on "Delete page" "link" in the "Home page 1" "table_row"
    And I click on "Delete page" "button" in the ".modal-dialog" "css_element"
    Then the following should exist in the "reportbuilder-table" table:
      | Page priority | Page name       | Management category | Title      | Visible to guests | Visible to all users | Visible to cohorts  | Hidden before   | Hidden after   | Status   |
      | 997           | Home page 2     | Cat 1               | HP title 2 | Yes               | No                   | Cohort 1, Cohort 2  | 5/11/25, 09:00  | 1/12/35, 09:00 | Active   |
    And I should not see "Home page 1"
