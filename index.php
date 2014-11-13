<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-05
 * Time: 14:18
 */

require_once "Constants.php";
require_once "Functions.php";
require_once "ScrapeClass.php";
require_once "CoursePage.php";
require_once "CourseList.php";

set_time_limit(EXECUTION_TIME_LIMIT);

/*
 * Methodology:
 * Scrape the course list page, get all the courses
 * Then we go through the list of the links and scrape those
 * We can do this because doing it recursively doesn't help us. The courses are finite and as such we know its going to
 * end sometime
 * We create some form of object and then create a json object of those objects in an dictionary or something
 */

$timestarted = time();

ob_end_clean();
ob_start();
header('Content-type: text/html; charset=utf-8');
header("Connection: close");

echo "
<!DOCTYPE html>
<html>
    <head>
        <meta charset='UTF-8'>
        <title>Scrapy scrapy</title>
    </head>
    <body>
        <p>This page was generated at " . date("Y-m-d h:i:s", $timestarted) . "</p>";
        echo "Result.json exists?";
        var_dump(file_exists("result.json"));
if (file_exists(realpath("") . SCRAPING_STARTED_FILENAME)) {
    echo "<p>The scraping has already started. It might take a while, please return later to find out when it is loaded.</p>";
    echo "<p>Started " . (time() - file_get_contents(SCRAPING_STARTED_FILENAME)) . " seconds ago.</p>";
    exit;
} else if (file_exists(realpath("") . "result.json")) {
    $previous_result = get_result();
    if ($previous_result[RESULT_DONEWHEN] + TIME_BETWEEN_SCRAPES > time()) {
        $timenow = time();
        echo "<p>Minimum time until next scrape: " . (TIME_BETWEEN_SCRAPES - ($timenow - $previous_result[RESULT_DONEWHEN])) . " seconds.</p>";
        var_dump($previous_result);
        exit;
    }
}

echo "<p>Starting the scraping of course list.</p>";
save_scraping_started_file($timestarted);
//file_put_contents("started_scraping.txt", $timestarted);

echo "
    </body>
</html>";

//The browser will load until we tell it that the content has all been loaded, and that is done via this header setting
$size = ob_get_length();
//header("Content-Length: $size");

//Flush everything to the browser, and then end the streaming to the browser
//ob_end_flush();
flush();
session_write_close();

//Initial things to start the scraping
$start_path = "http://coursepress.lnu.se/kurser/";

$courseList = new CourseList($start_path);
$courseList->scrapeList();
$courseList->scrapePages();

save_result($courseList->getResults());
//Who thought that unlink was a good name for removal of files?
unlink(realpath("") . SCRAPING_STARTED_FILENAME);