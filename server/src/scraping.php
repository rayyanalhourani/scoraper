<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Exception\NoSuchElementException;

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

    foreach ($allLeagues as $league) {
        $league = $league->findElement(WebDriverBy::xpath('./section'));
        $leagueName = $league->findElement(WebDriverBy::xpath('./header'))->getText();

        $matches = $league->findElements(WebDriverBy::xpath('./div/section'));

        foreach ($matches as $match) {
            $match = $match->findElements(WebDriverBy::xpath('./div/div/div'));
            $teamsDetails = $match[0];
            $placeDetails = $match[1];

            $matchTimeOrStatus = $teamsDetails->findElement(WebDriverBy::xpath('./div/div/div'))->getText();

            $teams = $teamsDetails->findElements(WebDriverBy::xpath('./div/div/ul/li'));

            foreach ($teams as $team) {
                $name = $team->findElement(WebDriverBy::xpath('./div/div'))->getText();

                try {
                    $img = $team->findElement(WebDriverBy::xpath('./a/img'))->getAttribute("src");
                } catch (NoSuchElementException $e) {
                    $img = null;
                }


                $divs = $team->findElements(WebDriverBy::xpath('./div'));
                
                $score="-";
                if(sizeof($divs)==3){
                    $score=$divs[2]->getText();
                }

                echo "$name $score <br>";
            }
        }
    }

    // echo $matchTimeOrStatus."<br>";
    // echo "<pre>";
    // var_dump($allMatches->getText());
    // echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
