<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = "Scores";
?>
<div class="container my-4">
    <h1 class="text-center mt-2 mb-4 display-5 fw-bold text-primary"><?= Html::encode($this->title) ?></h1>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="position-absolute start-50 translate-middle d-flex align-items-center gap-4 d-none">
        <h5 class="mt-2">Loading data, please wait...</h5>
        <div class="spinner-border" role="status"></div>
    </div>

    <!-- Calendar and Search -->
    <div class="row justify-content-center my-5 align-items-center w-100">
        <div class="col-md-4 mb-3">
            <label for="dateInput" class="form-label fw-bold fs-5">Select Date</label>
            <input type="date" id="dateInput" class="form-control fw-bold" aria-label="Select date" value="<?= date('Y-n-j') ?>" onchange="submitDate()">
        </div>
        <div class="col-md-4 mt-3">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" placeholder="Search for team, league, time, status, location">
                <span class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <!-- Matches Container -->
    <div id="allMatches" class="row g-4"></div>
</div>

<!--  Local Storage for Date -->
<script>
    document.getElementById("dateInput").value = localStorage.getItem("date") ?
        localStorage.getItem("date").replace(/(\d{4})(\d{2})(\d{2})/, '$1-$2-$3') :
        new Date().toLocaleDateString("en-CA");
</script>

<!-- WebSocket Initialization and Events -->
<script>
    const socketServer = new WebSocket("ws://127.0.0.1:8085");
    let allMatchesData = {};

    socketServer.onopen = () => {
        console.log("Connected to WebSocket!");
        const currentDate = localStorage.getItem("date") || new Date().toLocaleDateString("en-CA").replace(/-/g, "");
        socketServer.send(currentDate);
        toggleLoadingIndicator(true);
    };

    socketServer.onmessage = (event) => {
        const data = event.data;
        if (!isJson(data)) return console.log(data);

        toggleLoadingIndicator(false);
        const response = JSON.parse(data);
        allMatchesData = formatDataByLeague(response);
        displayMatches(allMatchesData);
    };

    socketServer.onclose = () => console.log("Connection closed");
    socketServer.onerror = () => console.log("Error occurred");
</script>

<!-- Helpers and Event Handlers -->
<script>
    function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    function submitDate() {
        const date = document.getElementById("dateInput").value.replace(/-/g, "");
        localStorage.setItem("date", date);
        socketServer.send(date);
        toggleLoadingIndicator(true);
    }

    function toggleLoadingIndicator(show) {
        document.getElementById('loadingIndicator').classList.toggle('d-none', !show);
    }
</script>

<!-- Formatting and Rendering -->
<script>
    function formatDataByLeague(data) {
        return Object.entries(data).reduce((formatted, [key, value]) => {
            const leagueName = key.split(":")[1];
            if (!formatted[leagueName]) formatted[leagueName] = [];
            formatted[leagueName].push(value);
            return formatted;
        }, {});
    }

    function displayMatches(matchesData) {
        const allMatchesContainer = document.getElementById("allMatches");
        allMatchesContainer.innerHTML = "";

        Object.entries(matchesData).forEach(([leagueName, matches]) => {
            if (!matches.length) return;

            allMatchesContainer.appendChild(createLeagueTitle(leagueName));
            const leagueRow = document.createElement("div");
            leagueRow.className = "row g-3";

            matches.forEach(match => leagueRow.appendChild(createMatchCard(match)));
            allMatchesContainer.appendChild(leagueRow);
        });

        if (!Object.values(matchesData).some(matches => matches.length)) {
            allMatchesContainer.innerHTML = '<p class="text-muted">No matches found for the selected criteria.</p>';
        }
    }

    function createLeagueTitle(leagueName) {
        const leagueTitle = document.createElement("h2");
        leagueTitle.className = "text-secondary my-3 border-bottom pb-2";
        leagueTitle.textContent = leagueName;
        return leagueTitle;
    }

    function createMatchCard(match) {
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

        teamRow.append(createTeamElement(team1), createScoreElement(team1.score, team2.score), createTeamElement(team2));

        cardBody.append(teamRow, createStatusElement(match.status), createLocationElement(match.stadium, match.city));

        cardDiv.appendChild(cardBody);
        colDiv.appendChild(cardDiv);

        return colDiv;
    }

    function createTeamElement(team) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'text-center';

        const teamImg = document.createElement('img');
        teamImg.src = team.img || "<?= Yii::getAlias('@web/images/defaultTeam.png'); ?>";
        teamImg.alt = team.name;
        teamImg.className = 'img-fluid mb-2';
        teamImg.style.width = '4rem';

        const teamName = document.createElement('p');
        teamName.className = 'fw-bold text-dark small';
        teamName.textContent = team.name;

        teamDiv.style = 'width:6rem';
        teamDiv.append(teamImg, teamName);

        return teamDiv;
    }

    function createScoreElement(score1, score2) {
        const scoreDiv = document.createElement('div');
        scoreDiv.className = 'fw-bold text-primary fs-4';
        scoreDiv.innerHTML = `<span>${score1}</span> : <span>${score2}</span>`;
        return scoreDiv;
    }

    function createStatusElement(status) {
        const statusText = document.createElement('p');
        statusText.className = 'text-muted my-2';
        statusText.textContent = status;
        return statusText;
    }

    function createLocationElement(stadium, city) {
        const locationDiv = document.createElement('div');
        locationDiv.classList = "col text-secondary small mt-3";

        const stadiumText = document.createElement('span');
        stadiumText.textContent = stadium;
        stadiumText.style.display = "block";

        const cityText = document.createElement('span');
        cityText.textContent = city;
        cityText.style.display = "block";

        locationDiv.append(stadiumText, cityText);
        return locationDiv;
    }
</script>

<!--  Search Functionality-->
<script>
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
                return [
                    team1.name, team2.name, league, match.time, match.status, match.city, match.stadium
                ].some(field => field?.toLowerCase().includes(query));
            });
        }
        displayMatches(filteredData);
    }
</script>