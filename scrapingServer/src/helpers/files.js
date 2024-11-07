import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

export function saveArrayToFile(filePath, data) {
  fs.writeFile(filePath, JSON.stringify(data), (err) => {
    if (err) {
      console.error("Error writing to file:", err);
    } else {
      console.log("File written successfully!");
    }
  });
}

export function loadArrayFromFile(filePath) {
  let dataArray = [];
  try {
    const __filename = fileURLToPath(import.meta.url);
    const __dirname = path.dirname(__filename);

    const absolutePath = path.resolve(__dirname, filePath);

    const data = fs.readFileSync(absolutePath, "utf-8");
    dataArray = JSON.parse(data);
  } catch (err) {
    console.error("Error reading file:", err);
  }
  return dataArray;
}
