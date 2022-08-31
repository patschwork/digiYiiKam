<?php

/** @var yii\web\View $this */

$this->title = 'DigiYiiKam';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h2 class="display-4">DigiYiiKam</h2>
    </div>
    <div class="body-content">
        <div class="row">
            <div class="col-lg-4">
            <h3>Images per type</h3>
            <?= \onmotion\apexcharts\ApexchartsWidget::widget([
                'type' => 'pie', // default area
                'height' => '400', // default 350
                'width' => '350', // default 100%
                'chartOptions' => [
                    'chart' => [
                        'toolbar' => [
                            'show' => true,
                            'autoSelected' => 'zoom'
                        ],
                    ],
                    // 'labels' => $labels,
                    'labels' => (new \vendor\digiyiikam\utils())->get_data_pie_chart()['labels'],
                    'xaxis' => [
                        'type' => 'numeric',
                    ],
                    'stroke' => [
                        'show' => true,
                        'colors' => ['transparent']
                    ],
                    'legend' => [
                        'verticalAlign' => 'bottom',
                        'horizontalAlign' => 'left',
                    ],
                ],
                // 'series' => $series,
                'series' => (new \vendor\digiyiikam\utils())->get_data_pie_chart()['series'],
            ]);
            ?>
       </div>
    </div>
</div>
