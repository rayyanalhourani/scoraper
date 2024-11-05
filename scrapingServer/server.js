import express from "express";
import { WebSocketServer } from "ws";
import { getScores } from "./scrapers/getScores.js";

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
      const { date } = JSON.parse(message);
      const scores = await getScores(date);
      ws.send(JSON.stringify({ scores }));
    } catch (error) {
      ws.send(JSON.stringify({ error: "Failed to scrape data" }));
    }
  });

  ws.on("close", () => {
    console.log("WebSocket connection closed");
  });
});

console.log(`WebSocket server running on ws://localhost:${PORT}`);