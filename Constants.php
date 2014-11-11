<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-11
 * Time: 13:50
 */
const TIME_BETWEEN_SCRAPES = 600;
const EXECUTION_TIME_LIMIT = 300;
const CURL_RETRY_LIMIT = 5;

const USER_AGENT  = 'PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv';

const RESULT_FILENAME = "result.json";
const SCRAPING_STARTED_FILENAME = "started_scraping.txt";
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

