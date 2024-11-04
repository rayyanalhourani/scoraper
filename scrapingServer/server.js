const express = require("express");
const puppeteer = require("puppeteer");

async function getScores(date) {
  try {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.goto(`https://www.espn.in/football/scoreboard/_/date/${date}`, {
      waitUntil: "networkidle0",
      timeout: 0,
    });

    const MainContainer = await page.waitForSelector(
      '::-p-xpath(//*[@role="main"]/div/div)'
    );

    date = await MainContainer.$eval(
      "::-p-xpath(./header/div/h3)",
      (element) => element.innerHTML
    );

    let allLeagues = await MainContainer.$$("::-p-xpath(./div)");

    let allScores = {};

    for (let league of allLeagues) {
      let leagueParts = await league.$$("::-p-xpath(./section)");

      for (league of leagueParts) {
        let leagueName = await league.$eval(
          "::-p-xpath(./header/a/div/h3)",
          (el) => el.innerHTML
        );

        let matches = await league.$$("::-p-xpath(./div/section)");

        allScores[leagueName]=[]
        for (match of matches) {
          let matchDetails = [];
          matchDetails["date"] = date;

          let gameDetails = await match.$$("::-p-xpath(./div)");
          gameDetails = await gameDetails[0].$$("::-p-xpath(./div/div)");

          let placeDetils = await gameDetails[0];
          let teamsDetails = await gameDetails[1];

          // extractLocationDetails();
          // extractTimeOrStates();
          // extractTeamsDetails();
          
          allScores[leagueName].push(matchDetails);
        }
      }
    }

    await browser.close();
    return allScores;
  } catch (error) {
    console.log(error);
    res.status(500).json({ error });
  }
}

const app = express();
const PORT = 3000;

app.get("/scores/:date", async (req, res) => {
  try {
    const date = req.params.date;
    const scores = await getScores(date);
    res.json(scores);
  } catch (error) {
    res.status(500).json({ error: "Failed to scrape data" });
  }
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
