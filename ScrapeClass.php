<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-11
 * Time: 15:37
 */
require_once("Constants.php");
class Scrape {
    private $url;
    private $useragent;
    private $use_curl;
    private $courses;
    private $scrapeCoursePageArray;
    private $pageBase;
    private $coursePageLinks;
    private $courseNames;
    private $scrapeListPageArray;

    public function __construct($url,
                                $useragent = "PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv",
                                $use_curl = true,
                                $pageBase = "",
                                $scrapeCoursePageArray = [],
                                $scrapeListPageArray = []) {
        $this->url = $url;
        $this->useragent = $useragent;
        $this->use_curl = $use_curl;
        $this->pageBase = $pageBase;
        $this->coursePageLinks = [];
        $this->courseNames = [];
        $this->scrapeCoursePageArray = $scrapeCoursePageArray;
        $this->scrapeListPageArray = $scrapeListPageArray;
        $this->courses = ["courses" => []];
    }

    public function start() {
        $timestarted = time();
        $this->scrape_courseList();
        $this->courses["donewhen"] = time();
        $this->courses["timestarted"] = $timestarted;
        $this->courses["timetaken"] = $this->courses["donewhen"] - $timestarted;
        $this->courses["amount_of_courses"] = count($this->courses["courses"]);
        var_dump($this->courses);

        return;
    }

    private function scrape_courseList() {
        if ($this->url === "") {
            return "";
        }
        $nextPageLink = "";

        $xpath = $this->get_xpath();
        foreach ($this->scrapeListPageArray as $xpath_name => $xpath_string) {
            if ($xpath_name === "currentPath") {
                continue;
            } else if ($xpath_name === "nextPage") {
                foreach ($xpath->query($xpath_string) as $x) {
                    $nextPageLink = $this->pageBase . $x->nodeValue;

                    if ($nextPageLink === $this->url) {
                        return "";
                    }
                }
            } else if ($xpath_name === "courseLinks") {
                foreach ($xpath->query($xpath_string) as $x) {
                    //Exclude the links that aren't courses
                    if (strpos($x->nodeValue, $this->pageBase . "/kurs/") !== false) {
                        //Recursive scraping the courses
                        var_dump($x->nodeValue);
                        $this->courses["courses"][] =
                            $this->scrape_coursePage($x->nodeValue, $this->scrapeCoursePageArray);
                        var_dump($this->courses);
                    }
                }
            }
        }
        if ($this->url === $nextPageLink) {
            return "";
        }
        $this->url = $nextPageLink;

        return $this->scrape_courseList();
    }

    private function get_xpath() {
        $page = $this->get_page($this->url);

        $domFirstPage = new DOMDocument();

        //Because fuck HTML 5, right?
        libxml_use_internal_errors(true);
        $domFirstPage->loadHTML($page);
        libxml_use_internal_errors(false);

        $xpath = new DOMXPath($domFirstPage);

        return $xpath;
    }

    private function get_page() {
        if ($this->use_curl) {
            $opts = [
                'http' => [
                    'method'     => 'GET',
                    'user_agent' => 'PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv'
                ]
            ];
            $context = stream_context_create($opts);

            return mb_convert_encoding(file_get_contents($this->url, null, $context), 'HTML-ENTITIES', "UTF-8");
        } else {
            return mb_convert_encoding($this->curl($this->url), 'HTML-ENTITIES', "UTF-8");
        }
    }

    private function curl($retry = 0) {
        if ($retry > 5) {
            print "Maximum 5 retries are done, skipping!\n";

            return "in loop!";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $this->url);

        $result = curl_exec($ch);

        // handling the follow redirect
        if (preg_match("|Location: (https?://\S+)|", $result, $m)) {
            print "Manually doing follow redirect!\n$m[1]\n";
            $this->url = $m[1];

            return curl($retry + 1);
        }

        // add another condition here if the location is like Location: /home/products/index.php
        return $result;
    }

    private function scrape_coursePage() {
        $object = [];
        $object["courseURL"] = $this->url;

        $xpath = $this->get_xpath();

        foreach ($this->scrapeCoursePageArray as $xpath_name => $xpath_string) {
            foreach ($xpath->query($xpath_string) as $x) {
                $object[$xpath_name] = trim($x->nodeValue);
            }
        }
        var_dump($object);

        return $object;
    }

    public function get_result() {
        return $this->courses;
    }
}