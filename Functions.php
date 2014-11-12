<?php
/**
 * Created by PhpStorm.
 * User: Chrille
 * Date: 2014-11-11
 * Time: 15:37
 */

require_once("Constants.php");
/*
 * Handles redirections and now I do not need to write all the ugly C-style initialization of curl
 */
function curl($url, $retry = 0) {
    if ($retry > CURL_RETRY_LIMIT) {
        print "Maximum " . CURL_RETRY_LIMIT . " retries are done, skipping!\n";

        return "in loop!";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_URL, $url);

    $result = curl_exec($ch);

    // handling the follow redirect
    if (preg_match("|Location: (https?://\S+)|", $result, $m)) {
        print "Manually doing follow redirect!\n$m[1]\n";

        return curl($m[1], $retry + 1, $ch);
    }

    // add another condition here if the location is like Location: /home/products/index.php
    return $result;
}
function multiRequest($data, $options = array()) {
    // array of curl handles
    $curly = array();
    // data to be returned
    $result = array();
    // multi handle
    $mh = curl_multi_init();
    // loop through $data and create curl handles
    // then add them to the multi-handle
    foreach ($data as $id => $d) {
        $curly[$id] = curl_init();
        $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
        curl_setopt($curly[$id], CURLOPT_URL,            $url);
        curl_setopt($curly[$id], CURLOPT_HEADER,         0);
        curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
        // post?
        if (is_array($d)) {
            if (!empty($d['post'])) {
                curl_setopt($curly[$id], CURLOPT_POST,       1);
                curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
            }
        }
        // extra options?
        if (!empty($options)) {
            curl_setopt_array($curly[$id], $options);
        }
        curl_multi_add_handle($mh, $curly[$id]);
    }
    // execute the handles
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while($running > 0);
    // get content and remove handles
    foreach($curly as $id => $c) {
        $result[$id] = curl_multi_getcontent($c);
        curl_multi_remove_handle($mh, $c);
    }
    // all done
    curl_multi_close($mh);
    return $result;
}

function get_xpath($url) {
    $page = get_page($url);

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

function get_page($url, $use_curl = true) {
    if ($use_curl) {
        return mb_convert_encoding(curl($url), 'HTML-ENTITIES', "UTF-8");
    } else {
        $opts = [
            'http' => [
                'method'     => 'GET',
                'user_agent' => USER_AGENT
            ]
        ];
        $context = stream_context_create($opts);

        return mb_convert_encoding(file_get_contents($url, null, $context), 'HTML-ENTITIES', "UTF-8");
    }
}

/**
 * @param $url
 * @param $scrapeListPageArray
 * @param $pageBase
 * @param $courses array It will contain the result of the scraping,
 * or nothing if there was some problem with the scraping
 * @param $scrapeCoursePageArray
 */
function scrape_courseList($url, $scrapeListPageArray, $pageBase, &$courses, $scrapeCoursePageArray) {
    if ($url === "") {
        return "";
    }
    $nextPageLink = "";
    $pages = [];
    $xpath = get_xpath($url);
    foreach ($scrapeListPageArray as $xpath_name => $xpath_string) {
        if ($xpath_name === PAGE_CURRENT_NAME) {
            continue;
        } else if ($xpath_name === PAGE_NEXT_NAME) {
            foreach ($xpath->query($xpath_string) as $x) {
                $nextPageLink = $pageBase . $x->nodeValue;

                if ($nextPageLink === $url) {
                    return "";
                }
            }
        } else if ($xpath_name === PAGE_ALL_COURSES_LINKS_NAME) {
            foreach ($xpath->query($xpath_string) as $x) {
                //Exclude the links that aren't courses
                if (strpos($x->nodeValue, $pageBase . "/kurs/") !== false) {
                    //Scrape the courses at the same time.
                    /*echo "<p>".$x->nodeValue."";
                    ob_flush();
                    flush();
                    $pages[] = $x->nodeValue;*/
                    $courses["courses"][] = scrape_course_page($x->nodeValue, $scrapeCoursePageArray);
                    usleep(mt_rand(10, 350));
                    //sleep(1);
                    /*echo "Scraped!</p>";
                    ob_flush();
                    flush();*/
                }
            }
        }
    }
    /*
    $r = multiRequest($pages);
    var_dump($r);
    foreach($r as $page){
        $courses["courses"][] = scrape_course_page($page, $scrapeCoursePageArray);
    }*/

    //echo "<h1>" . "Page done" . "</h1>";

    if ($url === $nextPageLink) {
        return "";
    }
    return scrape_courseList($nextPageLink, $scrapeListPageArray, $pageBase, $courses, $scrapeCoursePageArray);
}

function recursive($url, $scrapeListPageArray, $pageBase, $scrapeCoursePageArray, $result=["courses"=>[]]) {
    if ($url === "") {
        return;
    }
    $nextPageLink = "";

    $xpath = get_xpath($url);
    foreach ($scrapeListPageArray as $xpath_name => $xpath_string) {
        if ($xpath_name === PAGE_CURRENT_NAME) {
            continue;
        } else if ($xpath_name === PAGE_NEXT_NAME) {
            foreach ($xpath->query($xpath_string) as $x) {
                $nextPageLink = $pageBase . $x->nodeValue;

                if ($nextPageLink === $url) {
                    return "";
                }
            }
        } else if ($xpath_name === PAGE_ALL_COURSES_LINKS_NAME) {
            foreach ($xpath->query($xpath_string) as $x) {
                //Exclude the links that aren't courses
                if (strpos($x->nodeValue, $pageBase . "/kurs/") !== false) {
                    //Scrape the courses at the same time.
                    echo "<p>".$x->nodeValue."";
                    ob_flush();
                    flush();
                    $result["courses"][] = scrape_course_page($x->nodeValue, $scrapeCoursePageArray);
                    echo "Scraped!</p>";
                    ob_flush();
                    flush();
                }
            }
        }
    }
    if ($url === $nextPageLink) {
        return $result;
    }
    var_dump($result);
    return recursive($nextPageLink, $scrapeListPageArray, $pageBase,$scrapeCoursePageArray, $result);
}

function scrape_course_page($url, $scrapeCoursePageArray) {
    $object = [];
    $object["courseURL"] = $url;

    $xpath = get_xpath($url);

    foreach ($scrapeCoursePageArray as $xpath_name => $xpath_string) {
        foreach ($xpath->query($xpath_string) as $x) {
            //The xpath will capture more than the time and date
            if ($xpath_name === COURSE_PUBLISHED_NAME) {
                $object[$xpath_name] =
                    trim(preg_replace("/.+([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}).+/", "$1", $x->nodeValue));
            } else {
                $object[$xpath_name] = trim($x->nodeValue);
            }
        }
    }
    foreach ($scrapeCoursePageArray as $xpath_name => $xpath_string) {
        if (!isset($object[$xpath_name])) {
            $object[$xpath_name] = NO_INFORMATION_TEXT;
        }
    }

    return $object;
}

function save_result($result, $encode = true) {
    if($encode){
        $contents = json_encode($result);
    } else {
        $contents = $result;
    }
    file_put_contents(RESULT_FILENAME, $contents);
}

function get_result($decoded = true, $assoc = true) {
    if ($decoded) {
        return json_decode(file_get_contents(RESULT_FILENAME), $assoc);
    } else {
        return file_get_contents(RESULT_FILENAME);
    }

}

function result_file_exists() {
    return file_exists(RESULT_FILENAME);
}

function save_scraping_started_file($time = null) {
    file_put_contents(SCRAPING_STARTED_FILENAME, $time === null ? time() : $time);
}