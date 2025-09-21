<script>
    var options =
    {
        chart: {
            type: '{!! $chart->type() !!}',
            height: {!! $chart->height() !!},
            width: '{!! $chart->width() !!}',
            toolbar: {!! $chart->toolbar() !!},
            zoom: {!! $chart->zoom() !!}
        },
        plotOptions: {
            bar: {!! $chart->horizontal() !!}
        },
        colors: {!! $chart->colors() !!},
        series: {!! $chart->dataset() !!},
        dataLabels: {!! $chart->dataLabels() !!},
        @php
            $chartLabels = $chart->labels();
        @endphp
        @if($chartLabels)
            labels: {!! json_encode($chartLabels, true) !!},
        @endif
        title: {
            text: "{!! $chart->title() !!}"
        },
        subtitle: {
            text: '{!! $chart->subtitle() !!}',
            align: '{!! $chart->subtitlePosition() !!}'
        },
        xaxis: {
            categories: {!! $chart->xAxis() !!}
        },
        grid: {!! $chart->grid() !!},
        markers: {!! $chart->markers() !!},
        @php
            $chartStroke = $chart->stroke();
        @endphp
        @if($chartStroke)
            stroke: {!! $chartStroke !!},
        @endif
    }

    var chart = new ApexCharts(document.querySelector("#{!! $chart->id() !!}"), options);
    chart.render();

</script>
