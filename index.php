<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-05
 * Time: 14:18
 */

/*
 * Handles redirections and now I do not need to write all the ugly C-style initialization of curl
 */
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

$scrapeCoursePageArray = [COURSE_NAME_XPATH,
                          COURSE_URL_XPATH,
                          COURSE_CODE_XPATH,
                          COURSE_PLAN_XPATH,
                          COURSE_FIRST_XPATH,
                          COURSE_HEADING_XPATH,
                          COURSE_AUTHOR_XPATH,
                          COURSE_PUBLISHED_XPATH];

/*
 * Constant xpaths for the course list scraping
 */
const PAGE_CURRENT_XPATH = "//div[@id='pag-top']/div/span[contains(@class, 'current')]/text()";
const PAGE_NEXT_XPATH = "//div[@id='pag-top']/div/a[contains(@class, 'next')]/@href";
const PAGE_ALL_COURSES_LINKS_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/@href";
const PAGE_ALL_COURSES_NAMES_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/text()";

$scrapeListPageArray = [PAGE_CURRENT_XPATH,
                        PAGE_NEXT_XPATH,
                        PAGE_ALL_COURSES_LINKS_XPATH,
                        PAGE_ALL_COURSES_NAMES_XPATH];


echo "<!DOCTYPE html><html><body>";

$response = curl("https://coursepress.lnu.se/kurs/brukarorienterad-design/", "PHP cURL testing ch222kv");

$dom = new DOMDocument();

//Because fuck HTML 5, right?
libxml_use_internal_errors(true);
$dom->loadHTML($response);
libxml_use_internal_errors(false);
$dom->preserveWhiteSpace = false;
$xpath = new DOMXPath($dom);

foreach($scrapeCoursePageArray as $xpath_string){
    foreach($xpath->query($xpath_string) as $x)
    {
        echo "<pre>";
        var_export(trim($x->nodeValue));
        echo "</pre><br>";
    }
}
/*
//Kursens namn
foreach ($xpath->query(COURSE_NAME_XPATH) as $x) {
    echo "<pre>";
    var_export($x->nodeValue);
    echo "</pre><br>";
}
//Kurswebbplatsens URL
foreach ($xpath->query(COURSE_URL_XPATH) as $x) {
    echo "<pre>";
    var_export(trim($x->nodeValue));
    echo "</pre><br>";
}
//kurskod
foreach ($xpath->query(COURSE_CODE_XPATH) as $x) {
    echo "<pre>";
    var_export($x->nodeValue);
    echo "</pre><br>";
}
//Kursplan
foreach ($xpath->query(COURSE_PLAN_XPATH) as $x) {
    echo "<pre>";
    echo "<a href=";
    var_export($x->nodeValue);
    echo ">Kursplan</a>";
    echo "</pre><br>";
}
//Inledande text
foreach ($xpath->query(COURSE_FIRST_XPATH) as $x) {
    echo "<pre>";
    var_export(trim($x->nodeValue));
    echo "</pre><br>";
}
//Rubrik
foreach ($xpath->query(COURSE_HEADING_XPATH) as $x) {
    echo "<pre>";
    var_export($x->nodeValue);
    echo "</pre><br>";
}
//Författare
foreach ($xpath->query(COURSE_AUTHOR_XPATH) as $x) {
    echo "<pre>";
    var_export($x->nodeValue);
    echo "</pre><br>";
}
//Publicerad av
foreach ($xpath->query(COURSE_PUBLISHED_XPATH) as $x) {
    echo "<pre>";
    var_export(trim($x->nodeValue));
    echo "</pre><br>";
}
*/
echo "</body></html>";