<?php

/** @var yii\web\View $this */

use app\widgets\LeagueMatchs;
use yii\helpers\Html;

$this->title = "Scores";

?>
<div class="container my-4">
    <h1 class="text-center mt-2 mb-4 display-5 fw-bold text-primary"><?= Html::encode($this->title) ?></h1>
    
    <div class="row justify-content-center my-5">
        <!-- Calendar -->
        <div class="col-md-4 mb-3">
            <label for="dateInput" class="form-label">Select Date</label>
            <input type="date" name="date" id="dateInput" class="form-control" aria-label="Select date" value="<?= date('Y-n-j') ?>" onchange="submit()">
        </div>
        
        <!-- Search -->
        <div class="col-md-5">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search for team or league" aria-label="Search for team name">
                <button class="btn btn-primary" type="button" id="button-addon2">
                    <i class="bi bi-search me-2"></i>Search
                </button>
            </div>
        </div>
    </div>

    <div id="allMatches" class="row g-4"></div>
</div>

<script>
    let socketServer;
    const currentDate = new Date().toLocaleDateString("en-CA").replace(/-/g, "");

    socketServer = new WebSocket("ws://127.0.0.1:8085");

    socketServer.onmessage = function(event) {
        let data = event.data;

        if (!isJson(data)) {
            console.log(data);
            return;
        }

        let response = JSON.parse(data);
        let formatedData = getFormatedDate(response);
        printData(formatedData);
    };

    socketServer.onopen = function() {
        console.log("Connected to WebSocket!");
        socketServer.send(currentDate);
    };

    socketServer.onclose = function() {
        console.log("Connection closed");
    };

    socketServer.onerror = function() {
        console.log("Error occurred");
    };

    function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    function submit() {
        let input = document.getElementById("dateInput").value;
        const date = input.replace(/-/g, "");
        socketServer.send(date);
    }

    function getFormatedDate(data) {
        let formatedData = [];
        Object.entries(data).forEach(([key, value]) => {
            let parts = key.split(":");
            let leagueName = parts[1];

            if (!formatedData[leagueName]) {
                formatedData[leagueName] = [];
            }

            formatedData[leagueName].push(value);
        });
        return formatedData;
    }

    function printData(matchesData) {
        const allMatchesContainer = document.getElementById("allMatches");
        allMatchesContainer.innerHTML = "";

        for (let leagueName in matchesData) {
            let leagueTitle = document.createElement("h2");
            leagueTitle.className = "text-secondary my-3 border-bottom pb-2";
            leagueTitle.textContent = leagueName;
            allMatchesContainer.appendChild(leagueTitle);

            let leagueRow = document.createElement("div");
            leagueRow.className = "row g-3";

            matchesData[leagueName].forEach(match => {
                let matchCard = getMatch(match);
                leagueRow.appendChild(matchCard);
            });

            allMatchesContainer.appendChild(leagueRow);
        }
    }

    function getMatch(match) {
        const team1 = JSON.parse(match.team1),
              team2 = JSON.parse(match.team2);

        const colDiv = document.createElement('div');
        colDiv.className = "col-md-6 col-lg-4";

        const cardDiv = document.createElement('div');
        cardDiv.className = 'card shadow-sm h-100';

        const cardBody = document.createElement('div');
        cardBody.className = 'card-body text-center';

        const teamRow = document.createElement('div');
        teamRow.className = 'd-flex justify-content-between align-items-center';

        const team1Div = createTeamElement(team1);
        const team2Div = createTeamElement(team2);

        const scoreDiv = document.createElement('div');
        scoreDiv.className = 'fw-bold text-primary fs-4';
        scoreDiv.innerHTML = `<span>${team1.score}</span> : <span>${team2.score}</span>`;

        const statusText = document.createElement('p');
        statusText.className = 'text-muted my-2';
        statusText.textContent = match.status;

        const locationText = document.createElement('p');
        locationText.className = 'text-secondary small';
        locationText.textContent = `${match.stadium}, ${match.city}`;

        teamRow.appendChild(team1Div);
        teamRow.appendChild(scoreDiv);
        teamRow.appendChild(team2Div);

        cardBody.appendChild(teamRow);
        cardBody.appendChild(statusText);
        cardBody.appendChild(locationText);

        cardDiv.appendChild(cardBody);
        colDiv.appendChild(cardDiv);

        return colDiv;
    }

    function createTeamElement(team) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'text-center';

        const teamImg = document.createElement('img');
        teamImg.src = team.img ?? "<?= Yii::getAlias('@web/images/defaultTeam.png'); ?>"
        teamImg.alt = team.name;
        teamImg.className = 'img-fluid mb-2';
        teamImg.style.width = '3rem'; // Adjust size as needed

        const teamName = document.createElement('p');
        teamName.className = 'fw-bold text-dark small';
        teamName.textContent = team.name;

        teamDiv.appendChild(teamImg);
        teamDiv.appendChild(teamName);

        return teamDiv;
    }
</script>
