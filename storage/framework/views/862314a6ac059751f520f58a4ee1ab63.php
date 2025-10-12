<script>
    var options =
    {
        chart: {
            type: '<?php echo $chart->type(); ?>',
            height: <?php echo $chart->height(); ?>,
            width: '<?php echo $chart->width(); ?>',
            toolbar: <?php echo $chart->toolbar(); ?>,
            zoom: <?php echo $chart->zoom(); ?>

        },
        plotOptions: {
            bar: <?php echo $chart->horizontal(); ?>

        },
        colors: <?php echo $chart->colors(); ?>,
        series: <?php echo $chart->dataset(); ?>,
        dataLabels: <?php echo $chart->dataLabels(); ?>,
        <?php
            $chartLabels = $chart->labels();
        ?>
        <!--[if BLOCK]><![endif]--><?php if($chartLabels): ?>
            labels: <?php echo json_encode($chartLabels, true); ?>,
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        title: {
            text: "<?php echo $chart->title(); ?>"
        },
        subtitle: {
            text: '<?php echo $chart->subtitle(); ?>',
            align: '<?php echo $chart->subtitlePosition(); ?>'
        },
        xaxis: {
            categories: <?php echo $chart->xAxis(); ?>

        },
        grid: <?php echo $chart->grid(); ?>,
        markers: <?php echo $chart->markers(); ?>,
        <?php
            $chartStroke = $chart->stroke();
        ?>
        <!--[if BLOCK]><![endif]--><?php if($chartStroke): ?>
            stroke: <?php echo $chartStroke; ?>,
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    }

    var chart = new ApexCharts(document.querySelector("#<?php echo $chart->id(); ?>"), options);
    chart.render();

</script>
<?php /**PATH C:\laragon\www\pos-nabila\resources\views/vendor/larapex-charts/chart/script.blade.php ENDPATH**/ ?>