$(function(){   

	$('.dashboard .area-ultimas-compras ul li').last().css('border-bottom','0px solid transparent');
	$('.dashboard .area-ultimas-compras').tinycarousel({
		display: 1,
		scroll: 2,
		axis: 'y',
		duration: 200
	});
	$('.dashboard .area-integracao-MoIP').tinycarousel({
		display: 1,
		scroll: 2,
		axis: 'y',
		duration: 200
	});	
	$('.dashboard .area-ultimas-assinaturas').tinycarousel({
		display: 1,
		scroll: 2,
		axis: 'y',
		duration: 200
	});	
	$('.dashboard .area-ultimas-ofertas').tinycarousel({
		display: 1,
		scroll: 1,
		axis: 'y',
		duration: 200
	});			

	$('.area-linha').find('rect').attr('fill','')

	


	// Inicio de Codigo de Grafico
			var chart;
			chart = new Highcharts.Chart({
            chart: {
                renderTo: 'area-linha',
                type: 'line',
                marginRight: 130,
                marginBottom: 25,
                backgroundColor: 'transparent',
                width: 450,
                height: 100
            },
            title: {
                text: '',
                x: 20,
 				style:{fontSize: '20px'}           
            },
            subtitle: {
                text: '',
                x: 40
            },
            credits:{
            	enabled : false
            },
            exporting:{
            	enabled : false
            },            
            xAxis: {
                categories: ['dom','seg', 'ter', 'qua', 'qui', 'sex', 'sab']
            },
            yAxis: {
                title: {
                    text: ''
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            plotOptions: {
            	series: {
                	lineColor: '#feaa00',
                	color: '#666'
            	}
        	},
            tooltip: {
                formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +' visualizações';
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },
            series: [{
                name: 'Sapatilha Adidas',
                data: [50,20,10,40,55,65,22]
            }]
        });	


	// Inicio de Codigo de Grafico
			var chart2;
			chart2 = new Highcharts.Chart({
            chart: {
                renderTo: 'area-linha2',
                type: 'line',
                marginRight: 130,
                marginBottom: 25,
                backgroundColor: 'transparent',
                width: 450,
                height: 100
            },
            title: {
                text: '',
                x: 20 //center
            },
            subtitle: {
                text: '',
                x: 40
            },
            credits:{
            	enabled : false
            },
            exporting:{
            	enabled : false
            },            
            xAxis: {
                categories: ['dom','seg', 'ter', 'qua', 'qui', 'sex', 'sab']
            },
            yAxis: {
                title: {
                    text: ''
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            plotOptions: {
            	series: {
                	lineColor: '#feaa00',
                	color: '#666'
            	}
        	},
            tooltip: {
                formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +' visualizações';
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },
            series: [{
                name: 'Sapatilha Adidas',
                data: [90,10,15,20,65,32,02]
            }]
        });	



});