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
 * Activity mode utility class for VPL.
 *
 * @package mod_vpl
 * @copyright 2026 onwards Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jc.rodriguezdelpino@ulpgc.es>
 */
namespace mod_vpl\util;

/**
 * Activity mode utility class for VPL.
 *
 * @copyright 2026 onwards Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jc.rodriguezdelpino@ulpgc.es>
 */
class activity_mode {
    /** @var int Normal activity mode */
    public const NORMAL = 0;
    /** @var int Example activity mode */
    public const EXAMPLE = 1;
    /** @var int No students activity mode */
    public const NOSTUDENTS = 2;
    /** @var int Based on activity mode */
    public const BASEDON = 3;
    /** @var int Students read-only activity mode */
    public const STUDENTSREADONLY = 4;
    /** @var int VPL question activity mode */
    public const VPLQUESTION = 5;
    /** @var int VPL question no students access activity mode */
    public const VPLQUESTIONNOSTUDENTS = 6;
    /** @var array Map of activity modes to their string names for internationalization */
    private const STRINGS = [
        self::NORMAL => 'mode_normal',
        self::EXAMPLE => 'isexample',
        self::NOSTUDENTS => 'mode_no_students',
        self::BASEDON => 'mode_basedon',
        self::STUDENTSREADONLY => 'mode_students_readonly',
        self::VPLQUESTION => 'mode_vplquestion',
        self::VPLQUESTIONNOSTUDENTS => 'mode_vplquestion_no_students',
    ];
    /** @var array List of activity modes that prevent students from viewing the activity */
    private const PREVENT_SHOW_MODES = [
        self::NOSTUDENTS,
        self::BASEDON,
        self::VPLQUESTIONNOSTUDENTS,
    ];

    /** @var array List of activity modes that prevent students from modifying the activity */
    private const PREVENT_MODIFICATION_MODES = [
        self::EXAMPLE,
        self::NOSTUDENTS,
        self::BASEDON,
        self::STUDENTSREADONLY,
        self::VPLQUESTION,
        self::VPLQUESTIONNOSTUDENTS,
    ];

    /** @var array List of activity modes that prevent students from receiving a grade */
    public const NO_GRADE = [
        self::EXAMPLE,
        self::BASEDON,
        self::VPLQUESTION,
        self::VPLQUESTIONNOSTUDENTS,
    ];

    /** @var array List of activity modes that control if student can view the activity */
    public const CONTROL_VIEW = [
        self::BASEDON,
        self::NOSTUDENTS,
        self::STUDENTSREADONLY,
        self::VPLQUESTIONNOSTUDENTS,
    ];

    /**
     * Get the string name for internationalization given activity mode.
     *
     * @param int $mode activity mode
     * @return string string name for internationalization
     */
    public static function string_name($mode) {
        if (isset(self::STRINGS[$mode])) {
            return self::STRINGS[$mode];
        } else {
            throw new \InvalidArgumentException('Invalid activity mode: ' . $mode);
        }
    }
    /**
     * Return if the activity mode prevents students from viewing the activity.
     *
     * @param int $mode activity mode to check
     * @return bool
     */
    public static function mode_prevents_viewing($mode) {
        return in_array($mode, self::PREVENT_SHOW_MODES, true);
    }

    /**
     * Return if the activity mode prevents students from modifying the activity.
     *
     * @param int $mode activity mode to check
     * @return bool
     */
    public static function mode_prevents_modification($mode) {
        return in_array($mode, self::PREVENT_MODIFICATION_MODES, true);
    }
}
