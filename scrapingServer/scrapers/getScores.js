import puppeteer from "puppeteer";

export async function getScores(date) {
  try {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.goto(`https://www.espn.in/football/scoreboard/_/date/${date}`, {
      waitUntil: "networkidle0",
      timeout: 0,
    });

    const mainContainer = await page.waitForSelector(
      '::-p-xpath(//*[@role="main"]/div/div)'
    );

    date = await mainContainer.$eval(
      "::-p-xpath(./header/div/h3)",
      (element) => element.innerHTML
    );

    let allLeagues = await mainContainer.$$("::-p-xpath(./div)");
    let allScores = {};

    for (let league of allLeagues) {
      let leagueParts = await league.$$("::-p-xpath(./section)");

      for (let league of leagueParts) {
        let leagueName = await league.$eval(
          "::-p-xpath(./header/a/div/h3)",
          (el) => el.innerHTML
        );

        let matches = await league.$$("::-p-xpath(./div/section)");
        allScores[leagueName] = [];

        for (let match of matches) {
          let matchDetails = { date, teams: [] };

          let gameDetails = await match.$$("::-p-xpath(./div)");
          gameDetails = await gameDetails[0].$$("::-p-xpath(./div/div)");

          let placeDetails = await gameDetails[1];
          let teamsDetails = await gameDetails[0];

          let locationDetails = await extractLocationDetails(placeDetails);
          matchDetails["stadium"] = locationDetails.stadium;
          matchDetails["city"] = locationDetails.city;

          matchDetails["TimeOrStatus"] = await extractTimeOrStates(
            teamsDetails
          );

          await extractTeamsDetails(teamsDetails, matchDetails);

          allScores[leagueName].push(matchDetails);
        }
      }
    }

    await browser.close();
    return allScores;
  } catch (error) {
    console.log(error);
    return { error: "Failed to retrieve data" };
  }
}

async function extractLocationDetails(placeDetails) {
  const divs = await placeDetails.$$("::-p-xpath(./div/div/div/div)");
  let stadium = "";
  let city = "";

  if (divs.length > 0) {
    stadium = await divs[0].evaluate((el) => el.textContent.trim());
    city =
      divs.length > 1
        ? await divs[1].evaluate((el) => el.textContent.trim())
        : "";
  }

  return { stadium, city };
}

async function extractTimeOrStates(teamsDetails) {
  const matchTimeOrStatusElement = await teamsDetails.$(
    "::-p-xpath(./div/div/div)"
  );
  return matchTimeOrStatusElement
    ? await matchTimeOrStatusElement.evaluate((el) => el.textContent.trim())
    : "";
}

async function extractTeamsDetails(teamsDetails, matchDetails) {
  const teams = await teamsDetails.$$("::-p-xpath(./div/div/ul/li)");

  for (let team of teams) {
    const teamInfo = await extractTeamDetails(team);
    matchDetails["teams"].push(teamInfo);
  }
}

async function extractTeamDetails(team) {
  const nameElement = await team.$("::-p-xpath(./div/div)");
  const name = nameElement
    ? await nameElement.evaluate((el) => el.textContent.trim())
    : "";

  const imgElement = await team.$("::-p-xpath(./a/img)");
  const img = imgElement
    ? await imgElement.evaluate((el) => el.getAttribute("src"))
    : null;

  const divs = await team.$$("::-p-xpath(./div)");
  const score =
    divs.length === 3
      ? await divs[2].evaluate((el) => el.textContent.trim())
      : "-";

  return { name, img, score };
}
