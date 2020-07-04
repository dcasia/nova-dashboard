<script>

    import { Line } from 'vue-chartjs'

    export default {
        extends: Line,
        data: () => ( {
            chartdata: {
                labels: [ 'Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange' ],
                datasets: [
                    {
                        data: [ 12, 19, 3, 5, 2, 3 ],
                        backgroundColor: [
                            'rgba(255, 255, 255, 0.1)'
                        ],
                        borderColor: [
                            'rgba(255, 255, 255, 0.5)'
                        ],
                        borderWidth: 2,
                        pointBorderColor: 'rgba(0, 0, 0, 0)',
                        pointBackgroundColor: 'rgba(0, 0, 0, 0)',
                        pointHoverBackgroundColor: 'rgb(255, 255, 255)',
                        pointHoverBorderColor: 'rgb(255, 255, 255)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                labels: {
                    hidden: true
                },
                scales: {
                    xAxes: [ { display: false } ],
                    yAxes: [ { display: false } ]
                }
            }
        } ),

        mounted() {
            this.renderChart(this.chartdata, this.options)
        }

    }

</script>
