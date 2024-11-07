import puppeteer from "puppeteer";
import fs from "fs";
import PQueue from "p-queue";

export async function getAllTeams() {
  try {
    const browser = await puppeteer.launch({ headless: true });
    const queue = new PQueue({ concurrency: 20 });

    let allLeagues = loadArrayFromFile("../../data/allLeagues.txt");
    let allTeams = {};

    const scrapeLeague = async (league) => {
      let name = league[0];
      let url = league[1];
      console.log(name, " start");

      const page = await browser.newPage();
      await page.goto(`https://www.espn.com${url}`, {
        waitUntil: "networkidle0",
        timeout: 0,
      });

      const mainContainer = await page.$(
        '::-p-xpath(//*[@id="fittPageContainer"]/div[2]/div[2]/div/div[1]/div[1]/div[2])'
      );

      if (mainContainer) {
        let columns = await mainContainer.$$("::-p-xpath(./div)");

        for (let column of columns) {
          let teams = await column.$$("::-p-xpath(./div/div)");
          for (let team of teams) {
            let teamLink = await team.$("::-p-xpath(./div/section/div/a)");
            if (teamLink) {
              let teamDetails = await teamLink.evaluate((el) => [
                el.textContent,
                el.getAttribute("href"),
              ]);
              if (!allTeams[name]) {
                allTeams[name] = {};
              }
              allTeams[name][teamDetails[0]] = teamDetails[1];
            } else {
              console.warn(
                "Warning: teamLink not found for a team in league:",
                name
              );
            }
          }
        }

        await page.close();
      } else {
        console.warn("Error with league:", name);
      }
      console.log(name, " end");
    };

    for (let league of allLeagues) {
      queue.add(() => scrapeLeague(league));
    }

    await queue.onIdle();

    const { updatedLeagues, teamsOnly } = updateLeaguesAndExtractTeams(allTeams);

    saveArrayToFile("../../data/teamsWithLeagues.txt", updatedLeagues);
    saveArrayToFile("../../data/teamsOnly.txt", teamsOnly);

    await browser.close();
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

function saveArrayToFile(filePath, data) {
  fs.writeFile(filePath, JSON.stringify(data, null, 2), (err) => {
    if (err) {
      console.error("Error writing to file:", err);
    } else {
      console.log("File written successfully!");
    }
  });
}

function updateLeaguesAndExtractTeams(data) {
  const updatedLeagues = {};
  const teamsOnly = {};

  for (const [leagueName, teams] of Object.entries(data)) {
    const updatedLeagueName = leagueName.includes("W")
      ? leagueName.replace("W", "Women's")
      : leagueName;

    const updatedTeams = {};

    for (const [teamName, teamUrl] of Object.entries(teams)) {
      const updatedTeamName =
        updatedLeagueName.includes("Women's") && !teamName.includes("Women's")
          ? teamName + " Women's"
          : teamName;
      updatedTeams[updatedTeamName] = teamUrl;
      teamsOnly[updatedTeamName] = teamUrl;
    }
    updatedLeagues[updatedLeagueName] = updatedTeams;
  }

  return { updatedLeagues, teamsOnly };
}

getAllTeams();
