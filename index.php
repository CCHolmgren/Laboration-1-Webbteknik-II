<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-05
 * Time: 14:18
 */
set_time_limit(100);
/*
 * Handles redirections and now I do not need to write all the ugly C-style initialization of curl
 */
try{
    if(file_exists("result.json")){
        $previous_result_file = file_get_contents("result.json");
        $previous_result = json_decode($previous_result_file, true);
        if($previous_result["donewhen"]+600>time()){
            echo "Minimum time until next scrape " . 600-(time() - $previous_result["donewhen"]);
            var_dump($previous_result);
            exit;
        }
    }
}catch(Exception $e){
}

function curl($url, $user_agent, $retry = 0) {
    if ($retry > 5) {
        print "Maximum 5 retries are done, skipping!\n";

        return "in loop!";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');
    curl_setopt($ch, CURLOPT_COOKIEFILE, "./cookie.txt");
    curl_setopt($ch, CURLOPT_COOKIEJAR, "./cookie.txt");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    // handling the follow redirect
    if (preg_match("|Location: (https?://\S+)|", $result, $m)) {
        print "Manually doing follow redirect!\n$m[1]\n";

        return curl($m[1], $user_agent, $retry + 1);
    }

    // add another condition here if the location is like Location: /home/products/index.php

    return $result;
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

$scrapeCoursePageArray = ["courseName"=>COURSE_NAME_XPATH,
                          //"courseURL"=>COURSE_URL_XPATH,
                          "courseCode"=>COURSE_CODE_XPATH,
                          "coursePlan"=>COURSE_PLAN_XPATH,
                          "courseFirst"=>COURSE_FIRST_XPATH,
                          "courseHeading"=>COURSE_HEADING_XPATH,
                          "courseAuthor"=>COURSE_AUTHOR_XPATH,
                          "coursePublished"=>COURSE_PUBLISHED_XPATH
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


echo "<!DOCTYPE html><html><body>";

$start_path = "https://coursepress.lnu.se/kurser/";
$pageBase = "https://coursepress.lnu.se";
$nextPageLink = "";
$coursePageLinks = [];
$courseNames = [];
$times = 0;
$stop = false;

/*do {
    $page = curl($start_path, "PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv");

    $domFirstPage = new DOMDocument();

    //Because fuck HTML 5, right?
    libxml_use_internal_errors(true);
    $domFirstPage->loadHTML($page);
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($domFirstPage);

    foreach ($scrapeListPageArray as $xpath_name => $xpath_string) {
        if ($xpath_name === "currentPath") {
            continue;
        } else if ($xpath_name === "nextPage") {
            foreach ($xpath->query($xpath_string) as $x) {
                $nextPageLink = $pageBase . $x->nodeValue;
                echo $nextPageLink . "<br>" . $start_path . "<br>";

                if ($nextPageLink === $start_path) {
                    $stop = true;
                }
            }
        } else if ($xpath_name === "courseLinks") {
            foreach ($xpath->query($xpath_string) as $x) {
                //Exclude the links that aren't courses
                if(strpos($x->nodeValue, $pageBase."/kurs/") !== false){
                    $coursePageLinks[] = $x->nodeValue;
                }
            }
        } else if ($xpath_name === "courseNames") {
            foreach ($xpath->query($xpath_string) as $x) {
                $courseNames[] = $x->nodeValue;
            }
        }
    }
    if($start_path === $nextPageLink)
        break;
    $start_path = $nextPageLink;
} while (true);*/
$coursePageLinks = [];
$courseNames = [];
scrape_courseList("https://coursepress.lnu.se/kurser/", $scrapeListPageArray, $pageBase, $coursePageLinks, $courseNames);

function scrape_courseList($url, $scrapeListPageArray, $pageBase, &$coursePageLinks, &$courseNames){
    if($url==="")
        return;
    $page = curl($url, "PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv");
    $nextPageLink = "";
    $domFirstPage = new DOMDocument();


    //Because fuck HTML 5, right?
    libxml_use_internal_errors(true);
    $domFirstPage->loadHTML($page);
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($domFirstPage);
    foreach ($scrapeListPageArray as $xpath_name => $xpath_string) {
        if ($xpath_name === "currentPath") {
            continue;
        } else if ($xpath_name === "nextPage") {
            foreach ($xpath->query($xpath_string) as $x) {
                $nextPageLink = $pageBase . $x->nodeValue;
                echo $nextPageLink . "<br>" . $url . "<br>";

                if ($nextPageLink === $url) {
                    $stop = true;
                }
            }
        } else if ($xpath_name === "courseLinks") {
            foreach ($xpath->query($xpath_string) as $x) {
                //Exclude the links that aren't courses
                if(strpos($x->nodeValue, $pageBase."/kurs/") !== false){
                    $coursePageLinks[] = $x->nodeValue;
                }
            }
        }
        /*else if ($xpath_name === "courseNames") {
            foreach ($xpath->query($xpath_string) as $x) {
                $courseNames[] = $x->nodeValue;
            }
        }*/
    }
    if($url === $nextPageLink)
        return;

    scrape_courseList($nextPageLink, $scrapeListPageArray, $pageBase, $coursePageLinks, $courseNames);
}
/*foreach($courseNames as $cn){
    //echo $cn . "<br>";
}*/
foreach ($coursePageLinks as $cpl) {
    echo $cpl . "<br>";
}
$courses = [];
$courses["courses"]=[];
foreach($coursePageLinks as $cpl){
    $courses["courses"][] = scrape_coursePage($cpl, $scrapeCoursePageArray);

}
$courses["donewhen"] = time();
$courses["amount_of_courses"] = count($courses["courses"])-1;
file_put_contents("result.json",json_encode($courses));

function scrape_coursePage($url, $scrapeCoursePageArray){
    $object = [];
    $object["courseURL"] = $url;

    $page = curl($url, "PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv");
    $domFirstPage = new DOMDocument();

    //Because fuck HTML 5, right?
    libxml_use_internal_errors(true);
    $domFirstPage->loadHTML($page);
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($domFirstPage);

    foreach ($scrapeCoursePageArray as $xpath_name => $xpath_string) {
        foreach($xpath->query($xpath_string) as $x){
            $object[$xpath_name] = trim($x->nodeValue);
        }
    }
    /*foreach($scrapeCoursePageArray as $xpath_name=>$xpath_string){
        if(!isset($object[$xpath_name])){
            $object[$xpath_name] = "no information";
        }
    }*/
    return $object;
}







/*
$response = curl("https://coursepress.lnu.se/kurs/brukarorienterad-design/", "PHP cURL testing ch222kv");

$dom = new DOMDocument();

//Because fuck HTML 5, right?
libxml_use_internal_errors(true);
$dom->loadHTML($response);
libxml_use_internal_errors(false);
$dom->preserveWhiteSpace = false;
$xpath = new DOMXPath($dom);

foreach ($scrapeCoursePageArray as $xpath_string) {
    foreach ($xpath->query($xpath_string) as $x) {
        echo "<pre>";
        var_export(trim($x->nodeValue));
        echo "</pre><br>";
    }
}*/
echo "</body></html>";