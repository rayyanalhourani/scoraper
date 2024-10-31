<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = "Scores";
?>
<div class="container my-4">
    <h1 class="text-center mt-2 mb-4 display-5 fw-bold text-primary"><?= Html::encode($this->title) ?></h1>

    <div class="row justify-content-center my-5 align-items-center">
        <!-- Calendar -->
        <div class="col-md-4 mb-3">
            <label for="dateInput" class="form-label fw-bold fs-5">Select Date</label>
            <input type="date" name="date" id="dateInput" class="form-control fw-bold" aria-label="Select date" value="<?= date('Y-n-j') ?>" onchange="submit()">
        </div>

        <!-- Search -->
        <div class="col-md-4 mt-3">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" placeholder="Search for team ,league ,time or status" aria-label="Search for team name">
                <span class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <div id="allMatches" class="row g-4"></div>
</div>

<script>
    let socketServer;
    const currentDate = new Date().toLocaleDateString("en-CA").replace(/-/g, "");
    let allMatchesData = {};

    if (localStorage.getItem("date")!=null) {
        document.getElementById("dateInput").value = localStorage.getItem("date").replace(/(\d{4})(\d{2})(\d{2})/, '$1-$2-$3');
    } else {
        document.getElementById("dateInput").value = new Date().toLocaleDateString("en-CA")
    }

    socketServer = new WebSocket("ws://127.0.0.1:8085");
    socketServer.onmessage = function(event) {
        let data = event.data;

        if (!isJson(data)) {
            console.log(data);
            return;
        }

        let response = JSON.parse(data);
        let formattedData = getFormattedDate(response);
        allMatchesData = formattedData;
        printData(formattedData);
    };

    socketServer.onopen = function() {
        console.log("Connected to WebSocket!");
        socketServer.send(localStorage.getItem("date") || currentDate);
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
        localStorage.setItem("date", date);
        socketServer.send(date);
    }

    function getFormattedDate(data) {
        let formattedData = [];
        Object.entries(data).forEach(([key, value]) => {
            let parts = key.split(":");
            let leagueName = parts[1];

            if (!formattedData[leagueName]) {
                formattedData[leagueName] = [];
            }

            formattedData[leagueName].push(value);
        });
        return formattedData;
    }

    function printData(matchesData) {
        const allMatchesContainer = document.getElementById("allMatches");
        allMatchesContainer.innerHTML = "";

        let leaguesDisplayed = false;

        for (let leagueName in matchesData) {
            if (matchesData[leagueName].length > 0) {
                leaguesDisplayed = true;

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

        if (!leaguesDisplayed) {
            allMatchesContainer.innerHTML = '<p class="text-muted">No matches found for the selected criteria.</p>';
        }
    }

    function getMatch(match) {
        const team1 = JSON.parse(match.team1);
        const team2 = JSON.parse(match.team2);

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

        const locationDiv = document.createElement('div');
        locationDiv.classList = "col text-secondary small mt-3"

        const stadiumText = document.createElement('span');
        stadiumText.textContent = match.stadium;
        stadiumText.style.display = "block";

        const cityText = document.createElement('span');
        cityText.textContent = match.city;
        cityText.style.display = "block";

        locationDiv.appendChild(stadiumText);
        locationDiv.appendChild(cityText);

        teamRow.appendChild(team1Div);
        teamRow.appendChild(scoreDiv);
        teamRow.appendChild(team2Div);

        cardBody.appendChild(teamRow);
        cardBody.appendChild(statusText);
        cardBody.appendChild(locationDiv);

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
        teamImg.style.width = '4rem';

        const nameDiv = document.createElement('p');
        teamDiv.style = 'width:6rem';

        const teamName = document.createElement('p');
        teamName.className = 'fw-bold text-dark small';
        teamName.textContent = team.name;

        nameDiv.appendChild(teamName);

        teamDiv.appendChild(teamImg);
        teamDiv.appendChild(nameDiv);

        return teamDiv;
    }

    document.getElementById("searchInput").addEventListener("input", function() {
        const query = this.value.toLowerCase();
        filterMatches(query);
    });

    function filterMatches(query) {
        const filteredData = {};

        for (let league in allMatchesData) {
            filteredData[league] = allMatchesData[league].filter(match => {
                const team1 = JSON.parse(match.team1);
                const team2 = JSON.parse(match.team2);
                return (
                    team1.name.toLowerCase().includes(query) ||
                    team2.name.toLowerCase().includes(query) ||
                    league.toLowerCase().includes(query) ||
                    match.time?.toLowerCase().includes(query) ||
                    match.status.toLowerCase().includes(query)
                );
            });
        }

        printData(filteredData);
    }
</script>