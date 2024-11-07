import express from "express";
import { WebSocketServer } from "ws";
import { getScores } from "./src/getters/scores.js";
import { getLastMatch } from "./src/getters/lastMatch.js";
import { getNextMatch } from "./src/getters/nextMatch.js";

const app = express();
const PORT = 3000;

const server = app.listen(PORT, () => {
  console.log(`HTTP server running on http://localhost:${PORT}`);
});

const wss = new WebSocketServer({ server });

wss.on("connection", (ws) => {
  console.log("New WebSocket connection");

  ws.on("message", async (message) => {
    try {
      const { type, value } = JSON.parse(message);
      let result = null;
      if (type == "scores") {
        result = await getScores(value);
      } else if (type == "lastMatch") {
        result = await getLastMatch(value);
      } else if (type == "nextMatch") {
        result = await getNextMatch(value);
      }
      ws.send(JSON.stringify({ result }));
    } catch (error) {
      ws.send(JSON.stringify({ error: "Failed to scrape data" }));
    }
  });

  ws.on("close", () => {
    console.log("WebSocket connection closed");
  });
});

console.log(`WebSocket server running on ws://localhost:${PORT}`);
