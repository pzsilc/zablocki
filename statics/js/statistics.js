class Statistics{
    constructor(){
        this.totalNum = null;
        this.startAverage = null;
        this.endAverage = null;
        this.startHist = null;
        this.endHist = null;
        this.plotOfTotals = null;
        this.labelsHist = null;
        this.startTimes = [];
        this.endTimes = [];
        this.ctxStart = document.getElementById('start-chart');
        this.ctxEnd = document.getElementById('end-chart');
        this.ctxTotal = document.getElementById('total-chart');    
    }

    fetch(){
        const _this = this;
        return new Promise(resolve => {
            $.get(
                '/zablocki/dashboard/statistics/generate',
                {},
                (data, textStatus, jqXHR) => {
                    data = JSON.parse(data);
                    _this.startHist = data.start_hist;
                    _this.endHist = data.end_hist;
                    _this.plotOfTotals = data.plot_of_totals;
                    _this.labelsHist = data.hist_labels;
                    _this.totalNum = data.total_num;
                    _this.startAverage = data.start_average;
                    _this.endAverage = data.end_average;
                    _this.startTimes = data.start_times;
                    _this.endTimes = data.end_times;
                    resolve(true);
                }
            )
        })
    }

    render(){
        new Chart(this.ctxStart, {
            type: 'bar',
            data: {
                labels: this.labelsHist,
                datasets: [{
                    label: "Ilość zamówień (w dniach)",
                    data: this.startHist,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(120, 159, 30, 0.7)',
                        'rgba(200, 112, 230, 0.7)',
                        'rgba(45, 34, 56, 0.7)'
                    ],
                }]
            }
        });

        new Chart(this.ctxEnd, {
            type: 'bar',
            data: {
                labels: this.labelsHist,
                datasets: [{
                    label: "Ilość zamówień (w dniach)",
                    data: this.endHist,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(120, 159, 30, 0.7)',
                        'rgba(200, 112, 230, 0.7)',
                        'rgba(45, 34, 56, 0.7)'
                    ],
                }]
            }
        });
        
        new Chart(this.ctxTotal, {
            type: 'line',
            data: {
                labels: Object.keys(this.plotOfTotals).reverse(),
                datasets: [{ 
                    data: this.plotOfTotals,
                    label: "średni czas trwania zamówień (w dniach)",
                    borderColor: "#639cff",
                    fill: false
                }]
            }
        });

        let invisibleClassName = 'd-none';
        $('#content').removeClass(invisibleClassName);
        $('#loader').addClass(invisibleClassName);
        $('#additional-info').html(
            `<h2>Dodatkowe informacje</h2><br/>` + 
            `<p>Całkowita liczba zamówień: <b>${this.totalNum}</b></p>` +
            `<p>Średni czas rozpoczęcia: <b>${this.startAverage}</b> dni</p>` + 
            `<p>Średni czas zakończenia: <b>${this.endAverage}</b> dni</p>`
        )
    }
}



if(document.getElementById('statistics')){
    window.onload = async () => {
        let statistics = new Statistics();
        await statistics.fetch();
        statistics.render();
    }
}



/*
<script>

        new Chart(ctxStart, {
            type: 'bar',
            data: {
                labels: labelsHist,
                datasets: [{
                    label: "Ilość zamówień (w dniach)",
                    data: startHist,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(120, 159, 30, 0.7)',
                        'rgba(200, 112, 230, 0.7)',
                        'rgba(45, 34, 56, 0.7)'
                    ],
                }]
            }
        });

        new Chart(ctxEnd, {
            type: 'bar',
            data: {
                labels: labelsHist,
                datasets: [{
                    label: "Ilość zamówień (w dniach)",
                    data: endHist,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(120, 159, 30, 0.7)',
                        'rgba(200, 112, 230, 0.7)',
                        'rgba(45, 34, 56, 0.7)'
                    ],
                }]
            }
        });
        
        new Chart(ctxTotal, {
            type: 'line',
            data: {
                labels: Object.keys(plotOfTotals).reverse(),
                datasets: [{ 
                    data: plotOfTotals,
                    label: "średni czas trwania zamówień (w dniach)",
                    borderColor: "#639cff",
                    fill: false
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'World population per region (in millions)'
                }
            }
        });
    }
</script>
*/