<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
 
/**
 * This is a one-line short description of the file.
 *
 *
 * @package    local
 * @category   academicyear
 * @copyright  2013 Cathal O'Riordan, Waterford Institute of Technology 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot . "/lib/coursecatlib.php");
require_once($CFG->libdir.'/clilib.php');

/**
 * Handles academic year rollover 
 *
 * Category and enrolment plugin management for new academic year rollover
 */
class academic_year_cli {
    
    private $title;
    private $startyear;
    private $categoryyear;
    
    function __construct($title, $startyear, $currentcategoryyear) {
        $this->title = $title;
        $this->startyear = $startyear;
        $this->currentcategoryyear = $currentcategoryyear;
    }
    
    public function perform_academic_year_rollover() {
        
        mtrace("Processing academic year rollover ...");
        
        if (!$currentacademicyearcategory = $this->get_current_academic_year_category()) {
             cli_error("academic year doesn't exist");
        }
        
        mtrace("Creating new academic year category for {$this->title}");
        $newacademicyearcategory = $this->create_academic_year_category();
        
        mtrace("Copying category structure from {$currentacademicyearcategory->name}");
        $this->copy_category_structure_into_new_category($currentacademicyearcategory, $newacademicyearcategory);
        
        mtrace("All done");
    }
    
    /**
     * Create a new academic year category
     * @return stdClass return new category
     */
    private function create_academic_year_category() {
        global $DB;

        $category = new stdClass();
        $category->name = $this->title;
        $category->idnumber = $this->startyear;

        if ($existing = $DB->get_record('course_categories', array('idnumber' => $category->idnumber))) {
            $existing = coursecat::get($existing->id); // get coursecat object
            return $existing;
        }

        $category = coursecat::create($category);
        
        fix_course_sortorder();

        return $category;
    }
    
    /**
     * Returns the category for a given academic year
     * @param string $categoryyear 
     * @return stdClass return category
     */
    private function get_current_academic_year_category() {
        global $DB;
    
        if (!$category = $DB->get_record('course_categories', array('idnumber' => $this->currentcategoryyear))) {
            return false;
        }
    
        return $category;
    }
    
    /**
     * Copy category structure (all child categories) from previous academic year category into new one
     * @param stdClass $sourcecategory 
     * @param stdClass $destinationcategory 
     */
    private function copy_category_structure_into_new_category($sourcecategory, $destinationcategory) {
        global $DB;
	
    	if ($categoriestocopy = $DB->get_recordset_select('course_categories', 'parent = ?', array($sourcecategory->id))) {
            foreach ($categoriestocopy as $cat) {
                mtrace("copying {$cat->name} to {$destinationcategory->name}");
                if ($this->does_category_already_exist_in_destination($cat, $destinationcategory)) { 
                    mtrace("skipping {$cat->name} as it already exists");
                } else {
                    $this->category_copy_to($cat, $destinationcategory);
                }
            }
    	}
    }
    
    /**
     * Check if category being copied already exists in destination category
     * @param stdClass $cat 
     * @param stdClass $destinationcategory 
     */
    private function does_category_already_exist_in_destination($cat, $destinationcategory) {
        $destinationcategorychildren = $destinationcategory->get_children();
        
        foreach ($destinationcategorychildren as $child) {
            if ($cat->name === $child->name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Recursively copy a category including its subcategories. Does not copy courses within categories!
     * @param stdClass $category
     * @param int $newparentid the id of parent category under which new categories will be copied
     * @return stdClass return new category
     */
    private function category_copy_to($category, $newparentcat) {
        global $DB;

        $newcategory = new stdClass();

        try {
            mtrace("copying {$category->name} to {$newparentcat->name}");
            $newcategory = coursecat::create($category);
        } catch (moodle_exception $e) {
            // we can recover from a duplicate category id
            if ($e->errorcode == 'categoryidnumbertaken') {
                $category->idnumber = $category->name . '.' . $this->startyear;
                $newcategory = coursecat::create($category);
            } else {
                $info = get_exception_info($e);
                mtrace('Error creating category: ' . var_export($newcategory, true) . ' Exception ' . get_class($e), $info->message, $info->backtrace);
                exit(1);
            }
        }

        $newcategory->change_parent($newparentcat);
  
        if ($children = $DB->get_records('course_categories', array('parent'=>$category->id), 'sortorder ASC')) {
            foreach ($children as $childcat) {
                $this->category_copy_to($childcat, $newcategory);
            }
        }
    }
    
    /**
     * Create new term entry for lmb enrolment plugin
     * @param stdClass $academiccategory
     * @return stdClass return enrolment term
     */
    private function create_enrolment_term($academiccategory) {
        global $DB;
        
        if ($lmbterm = $DB->get_record('enrol_lmb_terms', array('sourcedid' => $academiccategory->idnumber))){
            return $lmbterm;
        }
        
        $lmbterm = new stdClass();
        $lmbterm->sourcedid = $academiccategory->idnumber;
        $lmbterm->sourcedidsource = 'WIT Moodle Academic Year Rollover';
        $lmbterm->title = $academiccategory->name;
        $lmbterm->starttime = make_timestamp($academiccategory->idnumber, 9, 1); // September 1st
        $lmbterm->endtime = make_timestamp($academiccategory->idnumber + 1, 8, 31); // August 31st
        $lmbterm->timemodified = time();    
        
        $lmbterm->id = $DB->insert_record('enrol_lmb_terms', $lmbterm, true);        
        
        return $lmbterm;
    }
    
    
    /**
     * Create new term category entry for lmb enrolment plugin
     * @param stdClass $academiccategory
     * @param stdClass $enrolmentterm
     */
    private function create_enrolment_term_category($academiccategory, $enrolmentterm) {
        global $DB;
    
        if ($lmbtermcat = $DB->get_record('enrol_lmb_categories', array('termsourcedid' => $academiccategory->idnumber, 'cattype' => 'term'))){
            return $lmbtermcat;
        }
    
        $lmbtermcat = new stdClass();
        $lmbtermcat->categoryid = $academiccategory->id;
        $lmbtermcat->termsourcedid = $enrolmentterm->sourcedid;
        $lmbtermcat->sourcedidsource = $enrolmentterm->sourcedidsource;
        $lmbtermcat->cattype = 'term';

        $lmbtermcat->id = $DB->insert_record('enrol_lmb_categories', $lmbtermcat, true);
    
        return $lmbtermcat;        
    }
    
    
    /**
     * Establishes links between enrolment category and course category
     * so that enrolment plugin knows which category new courses belong to 
     * @param stdClass $currentacademiccategory
     * @param stdClass $newacademiccategory
     */
    private function link_enrolment_categories($currentacademicyearcategory, $newacademicyearcategory) {
        global $DB;
        
        $categories = $DB->get_records_select('course_categories', 'depth = ?', array(5), 'sortorder ASC', 'id, name, path, sortorder');
        
        foreach($categories as $cat) {
            $parentcatids = explode('/', trim($cat->path, '/'));    
            // if this category's root is new academic year category
            if (!empty($parentcatids) && $newacademicyearcategory->id == $parentcatids[0]) {
                $lmbcat = new stdClass();
                $lmbcat->termsourcedid = $newacademicyearcategory->idnumber;
                $lmbcat->dept = $cat->name;
                $lmbcat->categoryid = $cat->id;
                $lmbcat->cattype = 'termdept';
                
                $DB->insert_record('enrol_lmb_categories', $lmbcat);
            }
            
        }        
    }
}