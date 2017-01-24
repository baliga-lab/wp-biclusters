var corem_browser = {};

(function () {

    var MARGIN = {left: 20, bottom: 20, right: 20, top: 20};
    var COLORS = ['red', 'green', 'blue', 'cyan', 'magenta', 'orange', 'purple'];
    var curves = [];

    // module global chart scale, we might want to switch to an instance
    var xScale, yScale, xAxis, yAxis;

    function drawAxes(chart, options, domain) {
        xScale = d3.scaleLinear().domain([domain.minx - 1, domain.maxx + 1]).range([0, options.width]);
        yScale = d3.scaleLinear().domain([domain.miny, domain.maxy + 1]).range([options.height, 0]);
        xAxis = d3.axisBottom(xScale).ticks(10);
        yAxis = d3.axisLeft(yScale).ticks(5);

        chart.append("g").attr("class", "axis").attr("id", "coremBrowserAxisX")
            .attr('transform', 'translate(0, ' + options.height + ')')
            .call(xAxis);
        chart.append("g").attr("class", "axis").attr("id", "coremBrowserAxisY")
            .attr('transform', 'translate(' + options.width + ', 0)')
            .call(yAxis);

        chart.append("text")
            .attr("class", "axis-label")
            .attr("x", -(options.height / 2)).attr("y", options.width + MARGIN.right / 2 + 2)
            .attr("transform", "rotate(-90)")
            .style("text-anchor", "middle")
            .attr("dy", ".1em")
            .text("GRE count");
    }

    function drawCurve(chart, options, id, data, color) {
        var line = d3.line()
            .x(function(d) { return xScale(d.pos); })
            .y(function(d) { return yScale(d.count); });

        var curve = chart.append("path").datum(data).attr("class", "line").attr("d", line)
            .attr('id', id)
            .style('stroke', color);
        //curve.style('opacity', 0);
        curves[id] = curve;
    }

    function findDomain(data, initDomain) {
        for (var i in data) {
            var d = data[i];
            if (d.pos > initDomain.maxx) initDomain.maxx = d.pos;
            if (d.pos < initDomain.minx) initDomain.minx = d.pos;
            if (d.count > initDomain.maxy) initDomain.maxy = d.count;
        }
        return initDomain;
    }

    function initDomain(data) {
        var domain = {
            'minx': 1000000, 'maxx': 0,
            'miny': 0, 'maxy': 0
        };
        var gres = Object.keys(data.gres);
        for (var i in gres) {
            var gredata = data.gres[gres[i]];
            domain = findDomain(gredata, domain);
        }
        return domain;
    }

    function makeGREPanel(grePanelSelector, gres) {
        var content = '<ul class="gre-panel">';
        content += '<li><input type="checkbox" id="use_GRE_all">All</li>';
        for (var i = 0; i < gres.length; i++) {
            content += '<li style="color: ' + COLORS[i % COLORS.length] + '"><input id="use_' + gres[i] + '" type="checkbox" checked>' + gres[i] + '</li>';
        }
        content += '</ul>';
        jQuery(grePanelSelector).html(content);
        jQuery('input[type=checkbox]').change(function(e) {
            var greId = e.target.id.substring(4)
            var checked = jQuery('#' + e.target.id).is(':checked');
            if (greId == 'GRE_all') {
                jQuery('input[type=checkbox]').each(function(elem) {
                    var id = jQuery(this)[0].id.substring(4);
                    if (id != 'GRE_all') {
                        jQuery(this).prop('checked', checked);
                        // toggle visibility by setting their opacity
                        var curve = curves[id];
                        curve.style('opacity', checked ? 1 : 0);
                    }
                });
            } else {
                var curve = curves[greId];
                // toggle visibility by setting their opacity
                curve.style('opacity', checked ? 1 : 0);
            }
        });
    }

    function makeCoremInfoPanel(coremPanelSelector, coremInfos) {
        if (coremInfos.length == 0) {
            content = '(no corems found)';
        } else {
            var content = '<ul class="corem-panel">';
            for (var i = 0; i < coremInfos.length; i++) {
                var coremId = 'COREM_' + coremInfos[i].corem_id;
                content += '<li><input type="radio" name="coremsel" value="' + coremId + '">' + coremId + '</li>';
            }
            content += '</ul>';
        }
        jQuery(coremPanelSelector).html(content);
    }


    corem_browser.init = function(svgSelector, grePanelSelector, coremPanelSelector, options) {
        var greURL = options.apiURL + "/api/v1.0.0/gene_gres/" + options.gene;
        var coremURL = options.apiURL + "/api/v1.0.0/corems_with_gene/" + options.gene;
        var chart = d3.select(svgSelector).attr('width', options.width + MARGIN.left + MARGIN.right)
            .attr('height', options.height + MARGIN.bottom + MARGIN.top)
            .append("g")
            .attr("transform", "translate(" + MARGIN.left + ", " + MARGIN.top + ")");

        jQuery.get(greURL, null,
              function (data, status, jqxhr) {
                  var gene = data.gene;
                  var gres = Object.keys(data.gres);
                  makeGREPanel(grePanelSelector, gres);

                  var domain = initDomain(data);
                  drawAxes(chart, options, domain);

                  for (var i in gres) {
                      var gredata = data.gres[gres[i]];
                      drawCurve(chart, options, gres[i], gredata, COLORS[i % COLORS.length]);
                  }
              }, "json");

        jQuery.get(coremURL, null,
              function (data, status, jqxhr) {
                  makeCoremInfoPanel(coremPanelSelector, data.corem_infos);
              }, "json");
    };
}());
