#!/bin/bash

export TITLE="Selenium Server"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; java -jar server/selenium-server-4.25.0.jar standalone; exec bash"

sleep 15

export TITLE="Server"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; php server/index.php; exec bash"

export TITLE="Client"
gnome-terminal -- bash -c "echo -ne \"\033]0;${TITLE}\007\"; php client/yii serve; exec bash"
