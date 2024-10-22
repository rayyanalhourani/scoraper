<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://10.123.41.244:4444/';
$capabilities = DesiredCapabilities::chrome();
$chromeOptions = new ChromeOptions();
$chromeOptions->addArguments(['--headless']); // <- comment out for testing
$capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);
$driver = RemoteWebDriver::create($host, $capabilities);
$driver->manage()->window()->maximize();

$driver->get('https://www.livescore.com/en/');

$allMatches = $driver->findElement(WebDriverBy::cssSelector('[data-test-id="virtuoso-item-list"]'));



