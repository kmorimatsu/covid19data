/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

// Check if data/pcrdata exist
try{
	dummy=data.length;
} catch(e){
	var data=pcrdata;
}

try{
	dummy=pcrdata.length;
} catch(e){
	var pcrdata=data;
}

// Validate prefdata
if (0<=get.pref && get.pref<data.length) {
	var prefdata=data[parseInt(get.pref)];
	var prefpcrdata=pcrdata[parseInt(get.pref)];
} else {
	var prefdata=data[0];
	var prefpcrdata=pcrdata[0];
}

// Set canvas background color
Chart.pluginService.register({
    beforeDraw: function(c){
        if (c.config.options.chartArea && c.config.options.chartArea.backgroundColor) {
            var ctx = c.chart.ctx;
            var chartArea = c.chartArea;
            ctx.fillStyle = "rgba(255, 255, 255, 1)";
            ctx.fillRect(0, 0, c.chart.width, c.chart.height);
            ctx.save();
            ctx.fillStyle = c.config.options.chartArea.backgroundColor;
            ctx.fillRect(chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top);
            ctx.restore();
        }
    }
});
