<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;

try {
    $host = 'http://10.123.41.244:4444/';
    $capabilities = DesiredCapabilities::chrome();
    $chromeOptions = new ChromeOptions();
    $chromeOptions->addArguments(['--headless']); // <- comment out for testing
    $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);
    $driver = RemoteWebDriver::create($host, $capabilities);
    $driver->manage()->window()->maximize();

    $driver->get('https://www.espn.in/football/scoreboard');

    $wait = new WebDriverWait($driver, 10); // wait up to 10 seconds

    $MainContainer = $driver->findElement(WebDriverBy::xpath('//*[@role="main"]/div/div'));

    $date = $MainContainer->findElement(WebDriverBy::xpath('./header/div/h3'))->getText();

    $allLeagues = $MainContainer->findElements(WebDriverBy::xpath('./div'));
    $all=[];
    foreach ($allLeagues as $leagues) {
        $leagueParts = $leagues->findElements(WebDriverBy::xpath('./section'));

        foreach ($leagueParts as $league) {
            $leagueName = $league->findElement(WebDriverBy::xpath('./header'))->getText();
            $matches = $league->findElements(WebDriverBy::xpath('./div/section'));
            foreach ($matches as $match) {
                $matchDetails=[];

                $gameDetails = $match->findElements(WebDriverBy::xpath('./div'))[0];
                $gameDetails = $gameDetails->findElements(WebDriverBy::xpath('./div/div'));

                $teamsDetails = $gameDetails[0];
                $placeDetails = $gameDetails[1];

                $divs = $placeDetails->findElements(WebDriverBy::xpath('./div/div/div/div'));

                $stadium = "";
                $city = "";
                if (sizeof($divs) > 0) {
                    $stadium = $divs[0]->getText();
                    $city = $divs[1]->getText();
                }

                $matchTimeOrStatus = $teamsDetails->findElement(WebDriverBy::xpath('./div/div/div'))->getText();

                $matchDetails["stadium"]=$stadium;
                $matchDetails["city"]=$city;
                $matchDetails["TimeOrStatus"]=$matchTimeOrStatus;
                $matchDetails["teams"]=[];

                $teams = $teamsDetails->findElements(WebDriverBy::xpath('./div/div/ul/li'));
                foreach ($teams as $team) {
                    $teamDetails=[];
                    $name = $team->findElement(WebDriverBy::xpath('./div/div'))->getText();

                    $imgs = $team->findElements(WebDriverBy::xpath('./a/img'));
                    $img = null;
                    if (sizeof($imgs) > 0) {
                        $img = $imgs[0]->getAttribute("src");
                    }

                    $divs = $team->findElements(WebDriverBy::xpath('./div'));
                    $score = "-";
                    if (sizeof($divs) == 3) {
                        $score = $divs[2]->getText();
                    }

                    $teamDetails["name"]=$name;
                    $teamDetails["img"]=$img;
                    $teamDetails["score"]=$score;

                    $matchDetails["teams"][]=$teamDetails;
                }
                $all[$leagueName][]=$matchDetails;
            }
        }
    }
    echo "<pre>";
    var_dump($all);
    echo "</pre>";

    $driver->close();
} catch (Exception $e) {
    echo $e->getMessage();
}
