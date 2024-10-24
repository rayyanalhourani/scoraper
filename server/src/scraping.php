<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;

try {
    $host = 'http://10.123.41.244:4444/';
    $capabilities = DesiredCapabilities::chrome();
    $chromeOptions = new ChromeOptions();
    $chromeOptions->addArguments(['--headless']); // <- comment out for testing
    $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

    $driver = RemoteWebDriver::create($host, $capabilities);
    $driver->manage()->window()->maximize();
} catch (Exception $e) {
    echo $e->getMessage();
}

function getScores(int $year, int $month, int $day)
{
    global $driver;

    $driver->get("https://www.espn.in/football/scoreboard/_/date/$year$month$day");
    $MainContainer = $driver->findElement(WebDriverBy::xpath('//*[@role="main"]/div/div'));

    $date = $MainContainer->findElement(WebDriverBy::xpath('./header/div/h3'))->getText();
    $allLeagues = $MainContainer->findElements(WebDriverBy::xpath('./div'));

    $allScores = [];

    foreach ($allLeagues as $leagues) {
        $leagueParts = $leagues->findElements(WebDriverBy::xpath('./section'));

        foreach ($leagueParts as $league) {
            $leagueName = $league->findElement(WebDriverBy::xpath('./header'))->getText();
            $matches = $league->findElements(WebDriverBy::xpath('./div/section'));

            foreach ($matches as $match) {
                $gameDetails = $match->findElements(WebDriverBy::xpath('./div'))[0]
                    ->findElements(WebDriverBy::xpath('./div/div'));

                $placeDetails = $gameDetails[1];
                $teamsDetails = $gameDetails[0];

                extractLocationDetails($placeDetails, $matchDetails);
                extractTimeOrStates($teamsDetails, $matchDetails);
                extractTeamsDetails($teamsDetails, $matchDetails);

                $matchDetails["date"] = $date;
                $allScores[$leagueName][] = $matchDetails;
            }
        }
    }

    return $allScores;
}

function extractLocationDetails($placeDetails, &$matchDetails)
{
    $divs = $placeDetails->findElements(WebDriverBy::xpath('./div/div/div/div'));

    $stadium = "";
    $city = "";

    if (sizeof($divs) > 0) {
        $stadium = $divs[0]->getText();
        $city = $divs[1]->getText();
    }

    $matchDetails["stadium"] = $stadium;
    $matchDetails["city"] =  $city;
}

function extractTimeOrStates($teamsDetails, &$matchDetails)
{
    $matchTimeOrStatus = $teamsDetails->findElement(WebDriverBy::xpath('./div/div/div'))->getText();
    $matchDetails["TimeOrStatus"] = $matchTimeOrStatus;
}

function extractTeamsDetails($teamsDetails, &$matchDetails)
{
    $teams = $teamsDetails->findElements(WebDriverBy::xpath('./div/div/ul/li'));

    foreach ($teams as $team) {
        extractTeamDetails($team, $matchDetails);
    }
}

function extractTeamDetails($team, &$matchDetails)
{
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

    $matchDetails["teams"][] = [
        "name" => $name,
        "img"  => $img,
        "score" => $score
    ];
}