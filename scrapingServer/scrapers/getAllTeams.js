import puppeteer from "puppeteer";
import fs from "fs";

export async function getAllTeams() {
  try {
    // const browser = await puppeteer.launch({ headless: true });
    // const page = await browser.newPage();

    let allLeagues = loadArrayFromFile("extracedData/allLeagues.txt");

    console.log(allLeagues);
    

  } catch (error) {
    console.log(error);
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

getAllTeams();
