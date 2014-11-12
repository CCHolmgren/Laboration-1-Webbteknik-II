<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-12
 * Time: 11:45
 */
require_once "CoursePage.php";
class CourseList {
    private $coursepages;
    private $pageNumber;
    private $pageLink;
    private $nextPageLink;

    public function __construct(){
        $this->coursepages = new CoursePageList();
    }
    public function addCoursePage(CoursePage $coursePage) {
        $this->coursepages->addCoursePage($coursePage);
    }
}