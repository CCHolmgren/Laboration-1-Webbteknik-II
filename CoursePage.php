<?php

/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-12
 * Time: 11:46
 */
class CoursePage {
    /*
     * Constants
     */
    const COURSE_NAME_XPATH      = '//*[@id="header-wrapper"]/h1/a/text()';
    const COURSE_CODE_XPATH      = '//*[@id="header-wrapper"]/ul/li[3]/a/text()';
    const COURSE_PLAN_XPATH      = "//a[text()='Kursplan']/@href";
    const COURSE_FIRST_XPATH     = '//section/article[contains(@class, "start-page")]/div';
    const COURSE_HEADING_XPATH   = "//article[2]/header/h1/a/text()";
    const COURSE_AUTHOR_XPATH    = "//article[2]/header/p/strong/text()";
    const COURSE_PUBLISHED_XPATH = "//article[2]/header/p/text()[1]";

    const COURSE_NAME_NAME       = "name";
    const COURSE_CODE_NAME       = "coursecode";
    const COURSE_PLAN_NAME       = "courseplan";
    const COURSE_FIRST_NAME      = "latestsubmissiontext";
    const COURSE_HEADING_NAME    = "courseheading";
    const COURSE_AUTHOR_NAME     = "latestsubmissioncreator";
    const COURSE_PUBLISHED_NAME  = "latestsubmissiontime";


    private $scrapeCoursePageArray = [self::COURSE_NAME_NAME      => self::COURSE_NAME_XPATH,
                                      self::COURSE_CODE_NAME      => self::COURSE_CODE_XPATH,
                                      self::COURSE_PLAN_NAME      => self::COURSE_PLAN_XPATH,
                                      self::COURSE_FIRST_NAME     => self::COURSE_FIRST_XPATH,
                                      self::COURSE_HEADING_NAME   => self::COURSE_HEADING_XPATH,
                                      self::COURSE_AUTHOR_NAME    => self::COURSE_AUTHOR_XPATH,
                                      self::COURSE_PUBLISHED_NAME => self::COURSE_PUBLISHED_XPATH
    ];

    const USER_AGENT             = 'PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv';
    const NO_INFORMATION_TEXT    = "no information";

    /*
     * Fields
     */
    private $url;
    private $name;
    private $coursecode;
    private $latestsubmissiontext;
    private $latestsubmissioncreator;
    private $latestsubmissiontime;
    private $courseplan;
    private $courseheading;
    private $scraped;

    public function __construct($url) {
        $this->url = $url;
        $this->scraped = false;
    }
    /*
     * Functions
     */
    public function getResult() {
        return array("url"                  => $this->url,
                     "name"                 => $this->name,
                     "coursecode"           => $this->coursecode,
                     "latestsubmissiontext" => $this->latestsubmissiontext,
                     "submissioncreator"    => $this->latestsubmissioncreator,
                     "submissiontime"       => $this->latestsubmissiontime,
                     "courseplan"           => $this->courseplan,
                     "courseheading"        => $this->courseheading);
    }

    public function scrape() {
        if (!$this->scraped) {
            $xpath = $this->get_xpath();

            foreach ($this->scrapeCoursePageArray as $name => $xstring) {
                foreach ($xpath->query($xstring) as $x) {
                    //The xpath will capture more than the time and date
                    if ($name === self::COURSE_PUBLISHED_NAME) {
                        $this->$name =
                            trim(preg_replace("/.+([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}).+/", "$1",
                                              $x->nodeValue));
                    } else {
                        $this->$name = trim($x->nodeValue);
                    }
                }
            }
            foreach ($this->scrapeCoursePageArray as $name => $xstring) {
                if (empty($this->$name)) {
                    $this->$name = self::NO_INFORMATION_TEXT;
                }
            }
            $this->scraped = true;
        }
    }

    private function get_xpath() {
        $page = $this->get_page();

        $domFirstPage = new DOMDocument();

        //Because fuck HTML 5, right?
        //This is because otherwise DOMDocument loadHTML breaks if the page is of HTML5 type,
        //since it doesn't include a XML declaration really
        libxml_use_internal_errors(true);
        $domFirstPage->loadHTML($page);
        libxml_use_internal_errors(false);

        $xpath = new DOMXPath($domFirstPage);

        return $xpath;
    }

    function get_page($use_curl = true) {
        if ($use_curl) {
            return mb_convert_encoding($this->curl($this->url), 'HTML-ENTITIES', "UTF-8");
        } else {
            $opts = [
                'http' => [
                    'method'     => 'GET',
                    'user_agent' => USER_AGENT
                ]
            ];
            $context = stream_context_create($opts);

            return mb_convert_encoding(file_get_contents($this->url, null, $context), 'HTML-ENTITIES', "UTF-8");
        }
    }

    function curl() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $this->url);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }
}

class CoursePageList implements Iterator {
    private $coursepages;
    private $results = ["courses" => []];

    public function __construct() {
        $this->coursepages = [];
    }

    public function addCoursePageWithUrl($url) {
        $this->coursepages[] = new CoursePage($url);
    }

    public function addCoursePageWithArray(array $urls){
        foreach($urls as $url){
            $this->addCoursePageWithUrl($url);
        }
    }

    public function addCoursePage(CoursePage $coursePage) {
        $this->coursepages[] = $coursePage;
    }

    public function scrapePages($limit = INF) {
        if ($this->coursepages) {
            $count = 0;
            $this->results["startedscrape"] = time();
            /** @var CoursePage $page */
            foreach ($this as $page) {
                if($count < $limit){
                    $page->scrape();
                    $count += 1;
                }
                usleep(mt_rand(10000,40000));
            }
            $this->results["stoppedscrape"] = time();
            $this->results["timetaken"] = $this->results["stoppedscrape"] - $this->results["startedscrape"];
        }
    }

    public function getResults() {
        if ($this->coursepages) {
            foreach ($this as $page) {
                $this->results["courses"][] = $page->getResult();
            }
            $this->results["coursesscraped"] = count($this->results["courses"]);

            return $this->results;
        }
    }

    /*
     * Iterator interface implementation
     */
    public function rewind() {
        reset($this->coursepages);
    }

    public function next() {
        return next($this->coursepages);
    }

    public function current() {
        return current($this->coursepages);
    }

    public function valid() {
        return $this->key() !== null;
    }

    public function key() {
        return key($this->coursepages);
    }
}