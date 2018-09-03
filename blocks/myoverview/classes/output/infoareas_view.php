<?php
/**
 * Class containing data for information areas view in the myoverview block.
 *
 * @package    block_myoverview
 * @copyright  2018 WIT <caoriordan@wit.ie>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myoverview\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_course\external\course_summary_exporter;

/**
 * Class containing data for information areas view in the myoverview block.
 *
 * @copyright  2018 WIT <caoriordan@wit.ie>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class infoareas_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const COURSES_PER_PAGE = 20;

    /** @var array $courses List of courses the user is enrolled in. */
    protected $courses = [];

    /**
     * The courses_view constructor.
     *
     * @param array $courses list of courses.
     * @param array $coursesprogress list of courses progress.
     */
    public function __construct($courses) {
        $this->courses = $courses;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        // Build courses view data structure.
        $infoareasview = [
            'hascourses' => !empty($this->courses)
        ];

        // How many courses we have per status?
        $coursesbystatus = ['inprogress' => 0];
        foreach ($this->courses as $course) {
            $courseid = $course->id;
            $context = \context_course::instance($courseid);
            $exporter = new course_summary_exporter($course, [
                'context' => $context
            ]);
            $exportedcourse = $exporter->export($output);
            // Convert summary to plain text.
            $exportedcourse->summary = content_to_text($exportedcourse->summary, $exportedcourse->summaryformat);
            
            $course = new \course_in_list($course);
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                if ($isimage) {
                    $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                    $exportedcourse->courseimage = $url;
                    $exportedcourse->classes = 'courseimage';
                    break;
                }
            }

            $exportedcourse->color = $this->coursecolor($course->id);

            if (!isset($exportedcourse->courseimage)) {
                $pattern = new \core_geopattern();
                $pattern->setColor($exportedcourse->color);
                $pattern->patternbyid($courseid);
                $exportedcourse->classes = 'coursepattern';
                $exportedcourse->courseimage = $pattern->datauri();
            }
            
            // Include course visibility.
            $exportedcourse->visible = (bool)$course->visible;
            
            // Courses that have already ended.
            $inprogresspages = floor($coursesbystatus['inprogress'] / $this::COURSES_PER_PAGE);

            $infoareasview['inprogress']['pages'][$inprogresspages]['courses'][] = $exportedcourse;
            $infoareasview['inprogress']['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
            $infoareasview['inprogress']['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
            $infoareasview['inprogress']['haspages'] = true;
            $coursesbystatus['inprogress']++;
        }

        // Build courses view paging bar structure.
        foreach ($coursesbystatus as $status => $total) {
            $quantpages = ceil($total / $this::COURSES_PER_PAGE);

            if ($quantpages) {
                $infoareasview[$status]['pagingbar']['disabled'] = ($quantpages <= 1);
                $infoareasview[$status]['pagingbar']['pagecount'] = $quantpages;
                $infoareasview[$status]['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
                $infoareasview[$status]['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
                for ($page = 0; $page < $quantpages; $page++) {
                    $infoareasview[$status]['pagingbar']['pages'][$page] = [
                        'number' => $page + 1,
                        'page' => $page + 1,
                        'url' => '#',
                        'active' => ($page == 0 ? true : false)
                    ];
                }
            }
        }

        return $infoareasview;
    }
    
    /**
     * Generate a semi-random color based on the courseid number (so it will always return
     * the same color for a course)
     *
     * @param int $courseid
     * @return string $color, hexvalue color code.
     */
    protected function coursecolor($courseid) {
        // The colour palette is hardcoded for now. It would make sense to combine it with theme settings.
        $basecolors = ['#81ecec', '#74b9ff', '#a29bfe', '#dfe6e9', '#00b894', '#0984e3', '#b2bec3', '#fdcb6e', '#fd79a8', '#6c5ce7'];

        $color = $basecolors[$courseid % 10];
        return $color;
    }
}
