<?php

/** @var yii\web\View $this */

use app\widgets\LeagueMatchs;
use yii\helpers\Html;

$this->title = "Scores";

?>
<div>
    <h1 class="text-center mt-2 mb-4"><?= Html::encode($this->title) ?></h1>
    <div class="d-flex justify-content-around my-5">
        <!-- calender -->
        <div class="d-flex align-items-center">
            <label for="dateInput" class="form-label w-100 mx-3">Select Date</label>
            <input type="date" name="date" id="dateInput" class="form-control" aria-label="Select date" value="<?= date('Y-n-j') ?>" onchange="submit()">
        </div>
        <!-- search -->
        <div class="input-group w-25" style="height: 20px;">
            <input type="text" class="form-control" placeholder="Search for team or league" aria-label="Search for team name" aria-describedby="button-addon2">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary d-flex align-items-center gap-1" type="button" id="button-addon2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                    <span>Search</span>
                </button>
            </div>
        </div>
    </div>

    <div id="allMatches">

    </div>
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
        socketServer.send(currentDate)
    };

    socketServer.onclose = function() {
        console.log("Connection closed");
    };

    socketServer.onerror = function() {
        console.log("Error happens");
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
            let leagueContainer = document.createElement("div");
            leagueContainer.className = "league-container";
            leagueContainer.appendChild(getBanner(leagueName));

            let matchesContainer = document.createElement("div");
            matchesContainer.className = "d-flex justify-content-around align-items-center";

            matchesData[leagueName].forEach(match => {
                matchesContainer.appendChild(getMatch(match));
            });

            leagueContainer.appendChild(matchesContainer);
            allMatchesContainer.appendChild(leagueContainer);
        }
    }

    function getBanner(leagueName) {
        const bannerDiv = document.createElement('div');
        bannerDiv.className = 'bg-secondary bg-opacity-25 w-100 py-3 align-items-center rounded shadow m-3';

        const title = document.createElement('h1');
        title.className = 'text-center';
        title.textContent = leagueName;

        bannerDiv.appendChild(title);
        return bannerDiv;
    }

    function getMatch(match) {
        const team1 = JSON.parse(match.team1),
            team2 = JSON.parse(match.team2);

        const matchDiv = document.createElement('div');
        matchDiv.className = 'border border-2 p-3 shadow rounded';
        matchDiv.style.width = '480px';
        matchDiv.style.minHeight = '300px';

        const teamScoreDiv = document.createElement('div');
        teamScoreDiv.className = 'd-flex justify-content-between';

        const team1Div = createTeamElement(team1);
        const team2Div = createTeamElement(team2);

        const statusDiv = document.createElement('div');
        statusDiv.className = 'mt-5';
        statusDiv.innerHTML = `
        <p class="text-muted fs-4">${match.status}</p>
        <div class="d-flex align-items-center justify-content-between mt-4 fs-2 fw-bold text-dark">
            <p>${team1.score}</p>
            <p class="text-muted">:</p>
            <p>${team2.score}</p>
        </div>`;

        teamScoreDiv.appendChild(team1Div);
        teamScoreDiv.appendChild(statusDiv);
        teamScoreDiv.appendChild(team2Div);

        const locationDiv = document.createElement('div');
        locationDiv.className = 'd-flex flex-column align-items-center justify-content-center mt-4';
        locationDiv.style.height = '3.5rem';
        locationDiv.innerHTML = `
        <p class="text-secondary fw-medium">${match.stadium}</p>
        <p class="text-muted small">${match.city}</p>`;

        matchDiv.appendChild(teamScoreDiv);
        matchDiv.appendChild(locationDiv);

        return matchDiv;
    }

    function createTeamElement(team) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'd-flex flex-column align-items-center';

        const teamImg = document.createElement('img');
        teamImg.src = team.img;
        teamImg.alt = team.name;
        teamImg.className = 'mb-2';
        teamImg.style.width = '6rem';
        teamImg.style.height = '6rem';

        const teamNameDiv = document.createElement('div');
        teamNameDiv.className = 'text-center';
        teamNameDiv.style.width = '9rem';
        teamNameDiv.innerHTML = `<p class="fw-bold text-muted">${team.name}</p>`;

        teamDiv.appendChild(teamImg);
        teamDiv.appendChild(teamNameDiv);

        return teamDiv;
    }
</script>