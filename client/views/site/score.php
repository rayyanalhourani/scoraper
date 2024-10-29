<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = "Scores";

?>

<div class="p-4 bg-gray-100 min-h-screen">

    <h1 class="text-2xl font-bold text-center mb-6"><?= Html::encode($this->title) ?></h1>

    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-md">
        
        <!-- Search -->
        <div class="flex-1 mr-4">
            <input type="text" placeholder="Search..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Calendar -->
        <div class="flex-1">
            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

    </div>

</div>
