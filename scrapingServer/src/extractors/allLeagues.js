import puppeteer from "puppeteer";
import fs from "fs";

export async function getAllLeagues() {
  try {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.goto(`https://www.espn.com/soccer/teams`, {
      waitUntil: "networkidle0",
      timeout: 0,
    });

    const mainContainer = await page.$(
      '::-p-xpath(//*[@id="fittPageContainer"]/div[2]/div[2]/div/div[1]/div[1]/div[1]/div/div)'
    );

    let select = await mainContainer.$$("::-p-xpath(./select)");

    let allLeagues = [];
    let options = await select[0].$$("::-p-xpath(./option)");

    for (let league of options) {
      allLeagues.push(
        await league.evaluate((el) => [
          el.innerHTML,
          el.getAttribute("data-url"),
        ])
      );
    }
    saveArrayToFile("extracedData/allLeagues.txt", allLeagues);
  } catch (error) {
    console.log(error);
    return { error: "Failed to retrieve data" };
  }
}

function saveArrayToFile(filePath, data) {
  fs.writeFile(filePath, JSON.stringify(data), (err) => {
    if (err) {
      console.error("Error writing to file:", err);
    } else {
      console.log("File written successfully!");
    }
  });
}

getAllLeagues();
