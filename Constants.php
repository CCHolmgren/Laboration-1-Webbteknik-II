<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-11
 * Time: 13:50
 */
const TIME_BETWEEN_SCRAPES = 300;
const EXECUTION_TIME_LIMIT = 100;
const CURL_RETRY_LIMIT = 5;

const USER_AGENT  = 'PHP cURL scraping Webbteknik II - Laboration 1 - ch222kv';

const RESULT_DONEWHEN = "donewhen";
const NO_INFORMATION_TEXT = "no information";

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
//Kursplan also follows a nice format, but if we assume that it would follow that format, and they changed it
//Then it would be bad. If we use this xpath to get to the kursplan then we can atleast assume we get it correct
//As long as they do not rename the link or remove it
const COURSE_PLAN_XPATH = "//a[text()='Kursplan']/@href";
const COURSE_FIRST_XPATH = '//section/article[contains(@class, "start-page")]/div';
const COURSE_HEADING_XPATH = "//article[2]/header/h1/a/text()";
const COURSE_AUTHOR_XPATH = "//article[2]/header/p/strong/text()";
const COURSE_PUBLISHED_XPATH = "//article[2]/header/p/text()[1]";

const COURSE_NAME_NAME = "courseName";
const COURSE_URL_NAME = "courseURL";
const COURSE_CODE_NAME = "courseCode";
const COURSE_PLAN_NAME = "coursePlan";
const COURSE_FIRST_NAME = "courseFirst";
const COURSE_HEADING_NAME = "courseHeading";
const COURSE_AUTHOR_NAME = "courseAuthor";
const COURSE_PUBLISHED_NAME = "coursePublished";

$scrapeCoursePageArray = [COURSE_NAME_NAME      => COURSE_NAME_XPATH,
                          //"courseURL"=>COURSE_URL_XPATH,
                          COURSE_CODE_NAME      => COURSE_CODE_XPATH,
                          COURSE_PLAN_NAME      => COURSE_PLAN_XPATH,
                          COURSE_FIRST_NAME     => COURSE_FIRST_XPATH,
                          COURSE_HEADING_NAME   => COURSE_HEADING_XPATH,
                          COURSE_AUTHOR_NAME    => COURSE_AUTHOR_XPATH,
                          COURSE_PUBLISHED_NAME => COURSE_PUBLISHED_XPATH
];

/*
 * Constant xpaths for the course list scraping
 */
const PAGE_CURRENT_XPATH = "//div[@id='pag-top']/div/span[contains(@class, 'current')]/text()";
const PAGE_NEXT_XPATH = "//div[@id='pag-top']/div/a[contains(@class, 'next')]/@href";
const PAGE_ALL_COURSES_LINKS_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/@href";
const PAGE_ALL_COURSES_NAMES_XPATH = "//*[@id='blogs-list']/li/div[@class='item']/div[@class='item-title']/a/text()";

const PAGE_CURRENT_NAME = "currentPath";
const PAGE_NEXT_NAME = "nextPage";
const PAGE_ALL_COURSES_LINKS_NAME = "courseLinks";
const PAGE_ALL_COURSES_NAMES_NAME = "courseNames";

$scrapeListPageArray = [PAGE_CURRENT_NAME => PAGE_CURRENT_XPATH, //Current page number
                        PAGE_NEXT_NAME    => PAGE_NEXT_XPATH, //Next page path
                        PAGE_ALL_COURSES_LINKS_NAME => PAGE_ALL_COURSES_LINKS_XPATH, //Links to courses
                        //PAGE_ALL_COURSES_NAMES_NAME => PAGE_ALL_COURSES_NAMES_XPATH //Names of the courses

];

