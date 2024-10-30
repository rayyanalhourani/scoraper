<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = "Scores";

?>

<div>

    <h1 class="text-center mt-2 mb-4"><?= Html::encode($this->title) ?></h1>


    <div class="d-flex justify-content-around my-5">
        <!-- calender -->
        <div class="d-flex align-items-center">
            <label for="dateInput" class="form-label w-100 mx-3">Select Date</label>
            <input type="date" name="date" id="dateInput" class="form-control" aria-label="Select date" value="<?= date('Y-n-j') ?>">
        </div>

        <!-- search -->
        <div class="input-group w-25" style="height: 20px;">
            <input type="text" class="form-control" placeholder="Search for team name" aria-label="Search for team name" aria-describedby="button-addon2">
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

    <!-- All matches -->
    <div>


        <div>

            <!-- leage name -->
            <div class="bg-secondary bg-opacity-25 w-100 py-3 align-items-center rounded shadow mb-3 ">
                <h1 class="text-center">AFC Champions League Elite</h1>
            </div>
        </div>

        <!-- matches -->

        <div>
            <div class="border border-2 p-3 shadow rounded" style="width:480px;height:350px">
                <!-- teams and score -->
                <div class="d-flex justify-content-between">
                    <!-- first team -->
                    <div class="d-flex flex-column align-items-center">
                        <img
                            src="https://a.espncdn.com/combiner/i?img=/i/teamlogos/soccer/500/22022.png"
                            alt="team 1"
                            class="mb-2"
                            style="width:9rem;height:9rem" />
                        <div class="text-center" style="width:9rem;">
                            <p class="fw-bold text-muted">Estudiantes de La Plata</p>
                        </div>
                    </div>
                    <!-- score and status -->
                    <div class="mt-5">
                        <p class="text-muted fs-4">10:00 pm</p>
                        <div class="d-flex align-items-center justify-content-between mt-4 fs-2 fw-bold text-dark">
                            <p>2</p>
                            <p class="text-muted">:</p>
                            <p>1</p>
                        </div>
                    </div>
                    <!-- second team -->
                    <div class="d-flex flex-column align-items-center">
                        <img
                            src="https://a.espncdn.com/combiner/i?img=/i/teamlogos/soccer/500/22022.png"
                            alt="team 1"
                            class="mb-2"
                            style="width:9rem;height:9rem" />
                        <div class="text-center" style="width:9rem;">
                            <p class="fw-bold text-muted">Estudiantes de La Plata</p>
                        </div>
                    </div>
                </div>
                <!-- place -->
                <div class="d-flex flex-column align-items-center justify-content-center mt-4" style="height: 3.5rem;">
                    <p class="text-secondary fw-medium">Guillermo Laza</p>
                    <p class="text-muted small">Buenos Aires, Argentina</p>
                </div>
            </div>


        </div>





    </div>

</div>


<script>

</script>