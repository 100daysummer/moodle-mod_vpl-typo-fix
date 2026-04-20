<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for activity modes.
 *
 * @package mod_vpl
 * @copyright  Juan Carlos Rodrí­guez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos RodrÃ­guez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

namespace mod_vpl;

use mod_vpl\util\activity_mode;
use mod_vpl\tests\testable_vpl;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/vpl/lib.php');
require_once($CFG->dirroot . '/mod/vpl/locallib.php');

/**
 * Unit tests for activity modes.
 *
 * @group mod_vpl
 * @group mod_vpl_activity_modes
 */
final class activity_modes_test extends \advanced_testcase {
    /** @var \stdClass Course used in tests. */
    private $course;

    /** @var \stdClass Student user. */
    private $student;

    /** @var \stdClass Teacher user (editing teacher). */
    private $teacher;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, 'student');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');
    }

    /**
     * Helper to create a VPL instance with a given mode.
     *
     * @param int $mode Activity mode constant.
     * @param array $extra Extra parameters for the instance.
     * @return testable_vpl
     */
    private function create_instance(int $mode, array $extra = []): testable_vpl {
        $params = array_merge([
            'name' => "VPL mode $mode",
            'course' => $this->course->id,
            'mode' => $mode,
            'grade' => 10,
            'visible' => 1,
            'visiblegrade' => 1,
            'duedate' => time() + DAYSECS,
            'maxfiles' => 3,
        ], $extra);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_vpl');
        $instance = $generator->create_instance($params);
        $this->assertEquals($mode, $instance->mode, "Created instance should have mode $mode");
        $cm = get_coursemodule_from_instance(VPL, $instance->id);
        $vpl = new testable_vpl($cm->id);
        $this->assertEquals($mode, $vpl->get_instance()->mode, "Created instance should have mode $mode");
        return $vpl;
    }

    /**
     * Test activity_mode static helper: mode_prevents_viewing.
     */
    public function test_mode_prevents_viewing_static(): void {
        $preventview = [activity_mode::NOSTUDENTS, activity_mode::BASEDON, activity_mode::VPLQUESTIONNOSTUDENTS];
        $allowview = [activity_mode::NORMAL, activity_mode::EXAMPLE, activity_mode::STUDENTSREADONLY, activity_mode::VPLQUESTION];

        foreach ($preventview as $mode) {
            $this->assertTrue(
                activity_mode::mode_prevents_viewing($mode),
                "Mode $mode should prevent viewing"
            );
        }
        foreach ($allowview as $mode) {
            $this->assertFalse(
                activity_mode::mode_prevents_viewing($mode),
                "Mode $mode should not prevent viewing"
            );
        }
    }

    /**
     * Test activity_mode static helper: mode_prevents_modification.
     */
    public function test_mode_prevents_modification_static(): void {
        $preventmod = [
            activity_mode::EXAMPLE, activity_mode::NOSTUDENTS, activity_mode::BASEDON,
            activity_mode::STUDENTSREADONLY, activity_mode::VPLQUESTION, activity_mode::VPLQUESTIONNOSTUDENTS,
        ];
        $allowmod = [activity_mode::NORMAL];

        foreach ($preventmod as $mode) {
            $this->assertTrue(
                activity_mode::mode_prevents_modification($mode),
                "Mode $mode should prevent modification"
            );
        }
        foreach ($allowmod as $mode) {
            $this->assertFalse(
                activity_mode::mode_prevents_modification($mode),
                "Mode $mode should not prevent modification"
            );
        }
    }

    /**
     * Test activity_mode::string_name returns valid strings.
     */
    public function test_string_name(): void {
        $modes = [
            activity_mode::NORMAL, activity_mode::EXAMPLE, activity_mode::NOSTUDENTS,
            activity_mode::BASEDON, activity_mode::STUDENTSREADONLY,
            activity_mode::VPLQUESTION, activity_mode::VPLQUESTIONNOSTUDENTS,
        ];
        foreach ($modes as $mode) {
            $name = activity_mode::string_name($mode);
            $this->assertNotEmpty($name);
            $this->assertIsString($name);
        }
    }

    /**
     * Test activity_mode::string_name throws exception for invalid mode.
     */
    public function test_string_name_invalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        activity_mode::string_name(99);
    }

    /**
     * Test vpl_update_mode sets grade to 0 for EXAMPLE mode.
     */
    public function test_update_mode_example(): void {
        $instance = (object)['mode' => activity_mode::EXAMPLE, 'grade' => 10, 'visible' => 1];
        vpl_update_mode($instance);
        $this->assertEquals(0, $instance->grade);
    }

    /**
     * Test vpl_update_mode sets grade=0, visible=0 for BASEDON mode.
     */
    public function test_update_mode_basedon(): void {
        $instance = (object)['mode' => activity_mode::BASEDON, 'grade' => 10, 'visible' => 1];
        vpl_update_mode($instance);
        $this->assertEquals(0, $instance->grade);
        $this->assertEquals(0, $instance->visible);
    }

    /**
     * Test vpl_update_mode sets visiblegrade=0, visible=0 for NOSTUDENTS mode.
     */
    public function test_update_mode_nostudents(): void {
        $instance = (object)['mode' => activity_mode::NOSTUDENTS, 'visiblegrade' => 1, 'visible' => 1];
        vpl_update_mode($instance);
        $this->assertEquals(0, $instance->visiblegrade);
        $this->assertEquals(0, $instance->visible);
    }

    /**
     * Test vpl_update_mode sets visible=1, visiblegrade=1 for STUDENTSREADONLY mode.
     */
    public function test_update_mode_studentsreadonly(): void {
        $instance = (object)['mode' => activity_mode::STUDENTSREADONLY, 'visible' => 0, 'visiblegrade' => 0];
        vpl_update_mode($instance);
        $this->assertEquals(1, $instance->visible);
        $this->assertEquals(1, $instance->visiblegrade);
    }

    /**
     * Test vpl_update_mode for VPLQUESTION mode.
     */
    public function test_update_mode_vplquestion(): void {
        $instance = (object)[
            'mode' => activity_mode::VPLQUESTION,
            'duedate' => time(),
            'maxfiles' => 1,
            'run' => 0,
            'evaluate' => 0,
            'grade' => 10,
        ];
        vpl_update_mode($instance);
        $this->assertEquals(0, $instance->duedate);
        $this->assertEquals(1000, $instance->maxfiles);
        $this->assertEquals(1, $instance->run);
        $this->assertEquals(1, $instance->evaluate);
        $this->assertEquals(0, $instance->grade);
    }

    /**
     * Test vpl_update_mode for VPLQUESTIONNOSTUDENTS mode.
     */
    public function test_update_mode_vplquestionnostudents(): void {
        $instance = (object)[
            'mode' => activity_mode::VPLQUESTIONNOSTUDENTS,
            'visible' => 1,
            'duedate' => time(),
            'maxfiles' => 1,
            'run' => 0,
            'evaluate' => 0,
            'grade' => 10,
        ];
        vpl_update_mode($instance);
        $this->assertEquals(0, $instance->visible);
        $this->assertEquals(0, $instance->duedate);
        $this->assertEquals(1000, $instance->maxfiles);
        $this->assertEquals(1, $instance->run);
        $this->assertEquals(1, $instance->evaluate);
        $this->assertEquals(0, $instance->grade);
    }

    /**
     * Test vpl_update_mode does not modify NORMAL mode instance.
     */
    public function test_update_mode_normal(): void {
        $instance = (object)['mode' => activity_mode::NORMAL, 'grade' => 10, 'visible' => 1, 'visiblegrade' => 1];
        $clone = clone $instance;
        vpl_update_mode($instance);
        $this->assertEquals($clone, $instance);
    }

    /**
     * Test is_mode and is_example on VPL instances.
     */
    public function test_is_mode_and_is_example(): void {
        $vplnormal = $this->create_instance(activity_mode::NORMAL);
        $this->assertTrue($vplnormal->is_mode(activity_mode::NORMAL));
        $this->assertFalse($vplnormal->is_example());

        $vplexample = $this->create_instance(activity_mode::EXAMPLE);
        $this->assertTrue($vplexample->is_mode(activity_mode::EXAMPLE));
        $this->assertTrue($vplexample->is_example());
    }

    /**
     * Test is_vpl_question_mode on VPL instances.
     */
    public function test_is_vpl_question_mode(): void {
        $vplq = $this->create_instance(activity_mode::VPLQUESTION);
        $this->assertTrue($vplq->is_vpl_question_mode());

        $vplqns = $this->create_instance(activity_mode::VPLQUESTIONNOSTUDENTS);
        $this->assertTrue($vplqns->is_vpl_question_mode());

        $vplnormal = $this->create_instance(activity_mode::NORMAL);
        $this->assertFalse($vplnormal->is_vpl_question_mode());
    }

    /**
     * Test mode_prevents_viewing on VPL instance for students vs teachers.
     */
    public function test_instance_mode_prevents_viewing(): void {
        $this->setUser($this->student);

        $vplnormal = $this->create_instance(activity_mode::NORMAL);
        $this->assertFalse($vplnormal->mode_prevents_viewing($this->student->id));

        $vplnostudents = $this->create_instance(activity_mode::NOSTUDENTS);
        $this->assertTrue($vplnostudents->mode_prevents_viewing($this->student->id));
        $this->assertFalse($vplnostudents->mode_prevents_viewing($this->teacher->id));

        $vplbasedon = $this->create_instance(activity_mode::BASEDON);
        $this->assertTrue($vplbasedon->mode_prevents_viewing($this->student->id));
        $this->assertFalse($vplbasedon->mode_prevents_viewing($this->teacher->id));

        $vplqns = $this->create_instance(activity_mode::VPLQUESTIONNOSTUDENTS);
        $this->assertTrue($vplqns->mode_prevents_viewing($this->student->id));
        $this->assertFalse($vplqns->mode_prevents_viewing($this->teacher->id));
    }

    /**
     * Test mode_prevents_modification on VPL instance for students vs teachers.
     */
    public function test_instance_mode_prevents_modification(): void {
        $this->setUser($this->student);

        $vplnormal = $this->create_instance(activity_mode::NORMAL);
        $this->assertFalse($vplnormal->mode_prevents_modification($this->student->id));

        $vplexample = $this->create_instance(activity_mode::EXAMPLE);
        $this->assertTrue($vplexample->mode_prevents_modification($this->student->id));
        $this->assertFalse($vplexample->mode_prevents_modification($this->teacher->id));

        $vplreadonly = $this->create_instance(activity_mode::STUDENTSREADONLY);
        $this->assertTrue($vplreadonly->mode_prevents_modification($this->student->id));
        $this->assertFalse($vplreadonly->mode_prevents_modification($this->teacher->id));

        $vplnostudents = $this->create_instance(activity_mode::NOSTUDENTS);
        $this->assertTrue($vplnostudents->mode_prevents_modification($this->student->id));
        $this->assertFalse($vplnostudents->mode_prevents_modification($this->teacher->id));
    }

    /**
     * Test NO_GRADE constant lists correct modes.
     */
    public function test_no_grade_modes(): void {
        $expected = [
            activity_mode::EXAMPLE,
            activity_mode::BASEDON,
            activity_mode::VPLQUESTION,
            activity_mode::VPLQUESTIONNOSTUDENTS,
        ];
        $this->assertEquals($expected, activity_mode::NO_GRADE);
    }

    /**
     * Test CONTROL_VIEW constant lists correct modes.
     */
    public function test_control_view_modes(): void {
        $expected = [
            activity_mode::BASEDON,
            activity_mode::NOSTUDENTS,
            activity_mode::STUDENTSREADONLY,
            activity_mode::VPLQUESTIONNOSTUDENTS,
        ];
        $this->assertEquals($expected, activity_mode::CONTROL_VIEW);
    }

    /**
     * Test is_visible respects mode for students and teachers.
     */
    public function test_is_visible_by_mode(): void {
        // NORMAL mode: student can see.
        $vplnormal = $this->create_instance(activity_mode::NORMAL);
        $this->setUser($this->student);
        $this->assertTrue($vplnormal->is_visible($this->student->id));

        // STUDENTSREADONLY mode: student can see.
        $vplreadonly = $this->create_instance(activity_mode::STUDENTSREADONLY);
        $this->assertTrue($vplreadonly->is_visible($this->student->id));

        // NOSTUDENTS mode: student cannot see, teacher can.
        $vplnostudents = $this->create_instance(activity_mode::NOSTUDENTS);
        $this->assertFalse($vplnostudents->is_visible($this->student->id));
        $this->assertTrue($vplnostudents->is_visible($this->teacher->id));
    }

    /**
     * Test is_submit_able respects mode for students and teachers.
     */
    public function test_is_submit_able_by_mode(): void {
        // NORMAL mode: student can submit.
        $vplnormal = $this->create_instance(activity_mode::NORMAL);
        $this->setUser($this->student);
        $this->assertTrue($vplnormal->is_submit_able($this->student->id));

        // EXAMPLE mode: student cannot submit, teacher can.
        $vplexample = $this->create_instance(activity_mode::EXAMPLE);
        $this->assertFalse($vplexample->is_submit_able($this->student->id));
        $this->assertTrue($vplexample->is_submit_able($this->teacher->id));

        // STUDENTSREADONLY mode: student cannot submit, teacher can.
        $vplreadonly = $this->create_instance(activity_mode::STUDENTSREADONLY);
        $this->assertFalse($vplreadonly->is_submit_able($this->student->id));
        $this->assertTrue($vplreadonly->is_submit_able($this->teacher->id));
    }
}
