#!/bin/bash
gnome-terminal -- bash -c "java -jar server/selenium-server-4.25.0.jar standalone; exec bash"
sleep 15
gnome-terminal -- bash -c "php server/index.php; exec bash"
gnome-terminal -- bash -c "php client/yii serve; exec bash"