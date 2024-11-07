import fs from "fs";

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
    const data = fs.readFileSync(filePath, "utf-8");
    dataArray = JSON.parse(data);
  } catch (err) {
    console.error("Error reading file:", err);
  }
  return dataArray;
}
