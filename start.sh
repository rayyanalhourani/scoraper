#!/bin/bash

export TITLE="scraping Server"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; node scrapingServer/server.js c; exec bash"

export TITLE="Server"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; php server/index.php; exec bash"

export TITLE="Client"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; php client/yii serve; exec bash"

export TITLE="Rasa api"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; cd chatbot; source venv/bin/activate; rasa run actions; exec bash"

export TITLE="Rasa action server"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; cd chatbot; source venv/bin/activate; rasa run -m models --enable-api --cors "*" --debug; exec bash"