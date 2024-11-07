import puppeteer from "puppeteer";
import fs from "fs";

export async function getNextMatch(teamName = "Arsenal") {
  try {
    const browser = await puppeteer.launch({ headless: true });

    let allTeams = loadArrayFromFile("../../data/teams.txt");

    let teamLink = allTeams["English Premier League"][teamName];
    teamLink = teamLink.replace("/soccer/team/", "/football/team/fixtures/");
    const page = await browser.newPage();
    await page.goto(`https://www.espn.com${teamLink}`, {
      waitUntil: "networkidle0",
      timeout: 0,
    });

    const mainContainer = await page.$$(
      '::-p-xpath(//*[@id="fittPageContainer"]/div[2]/div/div[5]/div/div[1]/section/div/section/div[3]/div[1]/div[2]/div/div[2]/table/tbody/tr/td)'
    );
    if (mainContainer.length > 0) {
      let date = await mainContainer[0].evaluate((el) => el.textContent);
      let team1 = await mainContainer[1].evaluate((el) => el.textContent);
      let team2 = await mainContainer[3].evaluate((el) => el.textContent);
      let time = await mainContainer[4].evaluate((el) => el.textContent);
      let league = await mainContainer[5].evaluate((el) => el.textContent);

      await browser.close();
      return [date, team1, time, team2, league];
    } else {
      await browser.close();
      return null;
    }
  } catch (error) {
    console.error(error);
    return { error: "Failed to retrieve data" };
  }
}

function loadArrayFromFile(filePath) {
  let dataArray = [];
  try {
    const data = fs.readFileSync(filePath, "utf-8");
    dataArray = JSON.parse(data);
  } catch (err) {
    console.error("Error reading file:", err);
  }
  return dataArray;
}

getNextMatch()
