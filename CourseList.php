<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-12
 * Time: 11:45
 */
require_once "CoursePage.php";

class CourseList {
    /*
     * Constants
     */
    const PAGE_CURRENT_XPATH           = "//div[@id='pag-top']/div/span[contains(@class, 'current')]/text()";
    const PAGE_NEXT_XPATH              = "//div[@id='pag-top']/div/a[contains(@class, 'next')]/@href";
    const PAGE_ALL_COURSES_LINKS_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/@href";
    const PAGE_ALL_COURSES_NAMES_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/text()";
    const PAGE_CURRENT_NAME            = "currentPath";
    const PAGE_NEXT_NAME               = "nextPageLink";
    const PAGE_ALL_COURSES_LINKS_NAME  = "courseLinks";
    const PAGE_ALL_COURSES_NAMES_NAME  = "courseNames";

    private $scrapeListPageArray = [self::PAGE_CURRENT_NAME           => self::PAGE_CURRENT_XPATH, //Current page number
                                    self::PAGE_NEXT_NAME              => self::PAGE_NEXT_XPATH, //Next page path
                                    self::PAGE_ALL_COURSES_LINKS_NAME => self::PAGE_ALL_COURSES_LINKS_XPATH,

    ];

    const USER_AGENT             = 'PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv';
    /*
     * Fields
     */
    private $coursepages;
    private $pageNumber;
    private $pageLink;
    private $nextPageLink;
    private $currentUrl;
    private $coursePageLinks;
    private $starturl;
    private $limit;
    private $countScraped;
    private $listScraped;

    public function __construct($starturl, $limit = -1) {
        $this->coursepages = new CoursePageList();
        $this->coursePageLinks = [];
        $this->starturl = $starturl;
        $this->currentUrl = $starturl;
        $this->listScraped = false;
        if($limit > -1){
            $this->limit = $limit;
        } else {
            $this->limit = INF;
        }
        $this->countScraped = 0;
    }
    /*
     * Functions
     */
    public function addCoursePage(CoursePage $coursePage) {
        $this->coursepages->addCoursePage($coursePage);
    }

    public function addCoursePageWithUrl($url) {
        $this->coursepages->addCoursePageWithUrl($url);
    }

    public function scrapeList() {
        $xpath = $this->get_xpath();

        foreach ($this->scrapeListPageArray as $name => $xstring) {
            if ($name === self::PAGE_ALL_COURSES_LINKS_NAME) {
                foreach ($xpath->query($xstring) as $x) {
                    if ($this->countScraped < $this->limit && strpos($x->nodeValue, "/kurs/") !== false) {
                        var_dump($x->nodeValue);
                        ob_flush();
                        flush();
                        $this->countScraped += 1;
                        $this->coursePageLinks[] = $x->nodeValue;
                    }
                }
            } else if ($name === self::PAGE_NEXT_NAME) {
                foreach ($xpath->query($xstring) as $x) {
                    $this->nextPageLink = $x->nodeValue;
                }
            }
        }
        if ($this->currentUrl === "http://coursepress.lnu.se" . $this->nextPageLink || $this->countScraped> $this->limit) {
            $this->listScraped = true;
            return;
        }
        $this->currentUrl = "http://coursepress.lnu.se" . $this->nextPageLink;
        $this->scrapeList();
    }
    public function getListResult($forceScrape = false){
        if($this->listScraped){
            return $this->coursePageLinks;
        } else if ($forceScrape && !$this->listScraped){
            $this->scrapeList();
            return $this->coursePageLinks;
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
            return mb_convert_encoding($this->curl($this->currentUrl), 'HTML-ENTITIES', "UTF-8");
        } else {
            $opts = [
                'http' => [
                    'method'     => 'GET',
                    'user_agent' => USER_AGENT
                ]
            ];
            $context = stream_context_create($opts);

            return mb_convert_encoding(file_get_contents($this->currentUrl, null, $context), 'HTML-ENTITIES', "UTF-8");
        }
    }

    function curl() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $this->currentUrl);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function scrapePages($limit = INF) {
        $this->coursepages->addCoursePageWithArray($this->coursePageLinks);
        $this->coursepages->scrapePages($limit);
    }

    public function getResults() {
        return $this->coursepages->getResults();
    }
}