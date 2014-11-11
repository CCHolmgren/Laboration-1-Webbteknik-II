<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-05
 * Time: 14:18
 */
const TIME_BETWEEN_SCRAPES = 600;
const TIME_LIMIT = 300;
set_time_limit(TIME_LIMIT);
/*
 * Handles redirections and now I do not need to write all the ugly C-style initialization of curl
 */
function curl($url, $useragent = "PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv", $retry = 0) {
    if ($retry > 5) {
        print "Maximum 5 retries are done, skipping!\n";

        return "in loop!";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_URL, $url);



    $result = curl_exec($ch);


    // handling the follow redirect
    if (preg_match("|Location: (https?://\S+)|", $result, $m)) {
        print "Manually doing follow redirect!\n$m[1]\n";

        return curl($m[1], $useragent, $retry + 1, $ch);
    }

    // add another condition here if the location is like Location: /home/products/index.php
    return $result;
}

function get_xpath($url) {
    $page = get_page($url);

    $domFirstPage = new DOMDocument();

    //Because fuck HTML 5, right?
    libxml_use_internal_errors(true);
    $domFirstPage->loadHTML($page);
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($domFirstPage);

    return $xpath;
}

function get_page($url) {
    $opts = [
        'http' => [
            'method'     => 'GET',
            'user_agent' => 'PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv'
        ]
    ];
    $context = stream_context_create($opts);
    return mb_convert_encoding(curl($url), 'HTML-ENTITIES', "UTF-8");
    //return mb_convert_encoding(file_get_contents($url, null, $context), 'HTML-ENTITIES', "UTF-8");
}
function scrape_courseList($url, $scrapeListPageArray, $pageBase, &$coursePageLinks, &$courseNames) {
    if ($url === "") {
        return;
    }
    global $scrapeCoursePageArray;
    global $courses;
    $nextPageLink = "";

    $xpath = get_xpath($url);
    foreach ($scrapeListPageArray as $xpath_name => $xpath_string) {
        if ($xpath_name === "currentPath") {
            continue;
        } else if ($xpath_name === "nextPage") {
            foreach ($xpath->query($xpath_string) as $x) {
                $nextPageLink = $pageBase . $x->nodeValue;

                if ($nextPageLink === $url) {
                    return;
                }
            }
        } else if ($xpath_name === "courseLinks") {
            foreach ($xpath->query($xpath_string) as $x) {
                //Exclude the links that aren't courses
                if (strpos($x->nodeValue, $pageBase . "/kurs/") !== false) {
                    //Recursive scraping the courses
                    $courses["courses"][] = scrape_coursePage($x->nodeValue, $scrapeCoursePageArray);
                }
            }
        }
    }
    if ($url === $nextPageLink) {
        return;
    }
    scrape_courseList($nextPageLink, $scrapeListPageArray, $pageBase, $coursePageLinks, $courseNames);
}


function scrape_coursePage($url, $scrapeCoursePageArray) {
    $object = [];
    $object["courseURL"] = $url;
    /*$page = get_page($url);
    $domFirstPage = new DOMDocument();

    //Because fuck HTML 5, right?
    libxml_use_internal_errors(true);
    $domFirstPage->loadHTML($page);
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($domFirstPage);*/
    $xpath = get_xpath($url);

    foreach ($scrapeCoursePageArray as $xpath_name => $xpath_string) {
        foreach ($xpath->query($xpath_string) as $x) {
            $object[$xpath_name] = trim($x->nodeValue);
        }
    }

    return $object;
}

$timestarted = time();

ob_end_clean();
ob_start();
header('Content-type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'> <title>Scrapy scrapy</title></head><body>";


echo "Time that this page was generated: " . date("Y-m-d h:i:s", $timestarted) . "<br>";

if (file_exists("result.json")) {
    $previous_result_file = file_get_contents("result.json");
    $previous_result = json_decode($previous_result_file, true);
    if ($previous_result["donewhen"] + TIME_BETWEEN_SCRAPES > time()) {
        $timenow = time();
        echo "Minimum time until next scrape: " . (TIME_BETWEEN_SCRAPES - ($timenow - $previous_result["donewhen"])) . " seconds.";
        var_dump($previous_result);
        exit;
    }
} else if (file_exists("started_scraping.txt")) {
    echo "The scraping has already started. It might take a while, please return later to find out when it is loaded";
    exit;
}


/*
 * Methodology:
 * Scrape the course list page, get all the courses
 * Then we go through the list of the links and scrape those
 * We can do this because doing it recursively doesn't help us. The courses are finite and as such we know its going to
 * end sometime
 * We create some form of object and then create a json object of those objects in an dictionary or something
 */

/*
 * constant xpaths for the course page scraping
 * Now I need the pagination scraping
 * Maybe use an object later? So that I can create a lni kfrom the PLAN_XPATH without hardcoding it
 */
const COURSE_NAME_XPATH = '//*[@id="header-wrapper"]/h1/a/text()';
const COURSE_URL_XPATH = '//*[@id="header-wrapper"]/h1/a/@href';
const COURSE_CODE_XPATH = '//*[@id="header-wrapper"]/ul/li[3]/a/text()';
const COURSE_PLAN_XPATH = "//a[text()='Kursplan']/@href";
const COURSE_FIRST_XPATH = '//section/article[contains(@class, "start-page")]/div';
const COURSE_HEADING_XPATH = "//article[2]/header/h1/a/text()";
const COURSE_AUTHOR_XPATH = "//article[2]/header/p/strong/text()";
const COURSE_PUBLISHED_XPATH = "//article[2]/header/p/text()[1]";

$scrapeCoursePageArray = ["courseName"      => COURSE_NAME_XPATH,
                          //"courseURL"=>COURSE_URL_XPATH,
                          "courseCode"      => COURSE_CODE_XPATH,
                          "coursePlan"      => COURSE_PLAN_XPATH,
                          "courseFirst"     => COURSE_FIRST_XPATH,
                          "courseHeading"   => COURSE_HEADING_XPATH,
                          "courseAuthor"    => COURSE_AUTHOR_XPATH,
                          "coursePublished" => COURSE_PUBLISHED_XPATH
];

/*
 * Constant xpaths for the course list scraping
 */
const PAGE_CURRENT_XPATH = "//div[@id='pag-top']/div/span[contains(@class, 'current')]/text()";
const PAGE_NEXT_XPATH = "//div[@id='pag-top']/div/a[contains(@class, 'next')]/@href";
const PAGE_ALL_COURSES_LINKS_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/@href";
const PAGE_ALL_COURSES_NAMES_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/text()";

$scrapeListPageArray = ["currentPath" => PAGE_CURRENT_XPATH, //Current page number
                        "nextPage"    => PAGE_NEXT_XPATH, //Next page path
                        "courseLinks" => PAGE_ALL_COURSES_LINKS_XPATH, //Links to courses
                        //"courseNames" => PAGE_ALL_COURSES_NAMES_XPATH //Names of the courses

];


$start_path = "http://coursepress.lnu.se/kurser/";
$pageBase = "http://coursepress.lnu.se";
$nextPageLink = "";
$times = 0;
$stop = false;

$coursePageLinks = [];
$courseNames = [];
$courses = ["courses"=>[]];

header("Connection: close");

echo "Starting the scraping of course list";
file_put_contents("started_scraping.txt", "");

echo "</body></html>";
$size = ob_get_length();

header("Content-Length: $size");
ob_end_flush();
flush();
session_write_close();

scrape_courseList($start_path, $scrapeListPageArray, $pageBase, $coursePageLinks, $courseNames);
$courses["donewhen"] = time();
$courses["timestarted"] = $timestarted;
$courses["timetaken"] = $courses["donewhen"] - $timestarted;
$courses["amount_of_courses"] = count($courses["courses"]) - 1;

file_put_contents("result.json", json_encode($courses));
unlink("started_scraping.txt");
