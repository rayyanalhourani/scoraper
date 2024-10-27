<?php

namespace Services;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Exception;

class Scraping
{
    public $driver;

    public function __construct($host)
    {
        try {
            $capabilities = DesiredCapabilities::chrome();
            $chromeOptions = new ChromeOptions();
            $chromeOptions->addArguments(['--headless']); // <- comment out for testing
            $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

            $this->driver = RemoteWebDriver::create($host, $capabilities);
            $this->driver->manage()->window()->maximize();
            error_log("Connected to Selenuim server.");
        } catch (Exception $e) {
            error_log("unable to Connected to Selenuim server.");
            echo $e->getMessage();
        }
    }


    public function getScores($date)
    {
        $this->driver->get("https://www.espn.in/football/scoreboard/_/date/$date");
        $MainContainer = $this->driver->findElement(WebDriverBy::xpath('//*[@role="main"]/div/div'));

        $date = $MainContainer->findElement(WebDriverBy::xpath('./header/div/h3'))->getText();
        $allLeagues = $MainContainer->findElements(WebDriverBy::xpath('./div'));

        $allScores = [];

        foreach ($allLeagues as $leagues) {
            $leagueParts = $leagues->findElements(WebDriverBy::xpath('./section'));

            foreach ($leagueParts as $league) {
                $leagueName = $league->findElement(WebDriverBy::xpath('./header'))->getText();
                $matches = $league->findElements(WebDriverBy::xpath('./div/section'));

                foreach ($matches as $match) {
                    $matchDetails = [];
                    $matchDetails["date"] = $date;

                    $gameDetails = $match->findElements(WebDriverBy::xpath('./div'))[0]
                        ->findElements(WebDriverBy::xpath('./div/div'));

                    $placeDetails = $gameDetails[1];
                    $teamsDetails = $gameDetails[0];

                    $this->extractLocationDetails($placeDetails, $matchDetails);
                    $this->extractTimeOrStates($teamsDetails, $matchDetails);
                    $this->extractTeamsDetails($teamsDetails, $matchDetails);

                    $allScores[$leagueName][] = $matchDetails;
                }
            }
        }

        return $allScores;
    }

    private function extractLocationDetails($placeDetails, &$matchDetails)
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

    private function extractTimeOrStates($teamsDetails, &$matchDetails)
    {
        $matchTimeOrStatus = $teamsDetails->findElement(WebDriverBy::xpath('./div/div/div'))->getText();
        $matchDetails["TimeOrStatus"] = $matchTimeOrStatus;
    }

    private function extractTeamsDetails($teamsDetails, &$matchDetails)
    {
        $teams = $teamsDetails->findElements(WebDriverBy::xpath('./div/div/ul/li'));

        foreach ($teams as $team) {
            $this->extractTeamDetails($team, $matchDetails);
        }
    }

    private function extractTeamDetails($team, &$matchDetails)
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
}
