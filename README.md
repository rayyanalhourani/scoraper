# Scorapper

Scoraper is a real-time sports score tracking app that uses web scraping to pull the latest scores from various websites. It features a chatbot to answer user questions about scores, upcoming games, and team info. With live updates and an easy-to-use interface, Scoraper makes staying on top of your favorite sports effortless and interactive.

## Requirements
- PHP 7.4 or higher
- Composer 2.x
- Node.js: 14.x or higher
- npm: 6.x or higher
- Redis server

## Tech Stack
- Backend: PHP (native PHP and Yii2 framework)
- Scraping: Node.js with Puppeteer
- Real-Time Communication: OpenSwoole and phrity for WebSocket
- Chatbot: Rasa
- Database & Caching: MySQL and Redis

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/rayyanalhourani/scoraper.git
    ```

2. Navigate into the project directory:
    ```bash
    cd scoraper
    ```
    
4.  Install dependencies in server
    ```bash
    cd server
    composer install
    ```
6.  Install dependencies in client
    ```bash
    cd client
    composer install
    ```

7.  Install dependencies in scraping server
    ```bash
    cd scrapingServer
    npm install
    ```

7. Start all application using shell script:
    ```bash
    ./start.sh
    ```

## Usage
1. Open your browser and navigate to `localhost:8080/site/score`.
