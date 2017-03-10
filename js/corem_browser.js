var corem_browser = {};

(function () {

    var MARGIN = {left: 20, bottom: 50, right: 20, top: 20};
    var COLORS = ['red', 'green', 'blue', 'cyan', 'magenta', 'orange', 'purple',
                  'AquaMarine', 'BlueViolet', 'Brown', 'BurlyWood', 'CadetBlue', 'Chartreuse',
                  'Chocolate', 'Coral', 'CornflowerBlue', 'Crimson', 'DarkBlue', 'DarkCyan',
                  'DarkGoldenrod', 'DarkGreen', 'DarkMagenta', 'DarkOliveGreen', 'DarkOrange',
                  'DarkRed', 'DarkSalmon', 'DarkSeaGreen', 'DarkSlateBlue', 'DarkSlateGray',
                  'DarkTurquoise', 'DeepPink', 'DeepSkyBlue', 'DimGray', 'DodgerBlue', 'FireBrick',
                  'ForestGreen', 'Indianred', 'Indigo', 'Navy', 'Orchid', 'Plum', 'RoyalBlue',
                  'SeaGreen', 'SpringGreen', 'Teal', 'Tomato', 'YellowGreen'
                 ];
    var curves = [];
    var chipPeakCurves = [];
    var greMaxValues = [];
    var greMaxValue = 0;

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
        curves[id] = curve;
    }

    function drawChipseqPeak(chart, options, chipseqPeaks, tf) {
        var color = '#ff0000';
        var line = d3.line()
            .x(function(d) { return xScale(d.pos); })
            .y(function(d) { return yScale(d.value); });
        var id = 'chippeak_' + tf;
        var pos = chipseqPeaks[tf];
        var linedata1 = [
            {pos: pos, value: 0},
            {pos: pos, value: greMaxValue}
        ];
        chipPeakCurves[id] = chart.append("path")
            .datum(linedata1)
            .attr("class", "line")
            .attr("d", line(linedata1))
            .attr('id', id)
            .style('stroke', color)
            .style('stroke-width', '3');
        chart.append('text')
            .attr('x', xScale(pos))
            .attr('y', yScale(greMaxValue))
            .style('stroke', color)
            .text(tf);
    }

    function drawChipSeqPeaks(chart, options, chipseqPeaks) {
        var chipseqTFs = Object.keys(chipseqPeaks), tf, i, curve, id, linedata, position;
        for (i = 0; i < chipseqTFs.length; i++) {
            tf = chipseqTFs[i];
            drawChipseqPeak(chart, options, chipseqPeaks, tf);
        }
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
            'minx': Number.MAX_SAFE_INTEGER, 'maxx': 0,
            'miny': 0, 'maxy': 0
        };
        var gres = Object.keys(data.gres);
        for (var i in gres) {
            var gredata = data.gres[gres[i]];
            domain = findDomain(gredata, domain);
        }
        return domain;
    }

    function showCurve(greId, show) {
        curves[greId].style('opacity', show ? 1 : 0);
    }

    function minGRECountChanged(minGRECount) {
        var visible, greId;
        for (greId in greMaxValues) {
            visible = greMaxValues[greId] >= minGRECount;
            showCurve(greId, visible);
            if (visible) {
                jQuery('#grepanel_' + greId).show();
            } else {
                jQuery('#grepanel_' + greId).hide();
            }
        }
    }

    function makeGREPanel(grePanelSelector, gres, initialMinGRECount) {
        var content = '<ul class="gre-panel">';
        content += '<li><input type="checkbox" id="use_GRE_all">All</li>';
        for (var i = 0; i < gres.length; i++) {
            content += '<li id="grepanel_' + gres[i] + '" style="color: ' + COLORS[i % COLORS.length] + '"><input id="use_' + gres[i] + '" type="checkbox" checked>' + gres[i] + '</li>';
        }
        content += '</ul>';
        content += '<div style=\"margin-bottom: 5px;\"><label for=\"min_count\">Minimum GRE Count:</label> <input id=\"min_count\" type=\"number\" step=\"1\" value=\"' + initialMinGRECount + '\"></input></div>';
        content += "<div></div>";
        jQuery(grePanelSelector).html(content);
        jQuery('#min_count').change(function(e) {
            var minCount = jQuery(this).val();
            minGRECountChanged(minCount);
        });
        jQuery('input[type=checkbox]').change(function(e) {
            var greId = e.target.id.substring(4)
            var checked = jQuery('#' + e.target.id).is(':checked');
            if (greId == 'GRE_all') {
                jQuery('input[type=checkbox]').each(function(elem) {
                    var id = jQuery(this)[0].id.substring(4);
                    if (id != 'GRE_all') {
                        jQuery(this).prop('checked', checked);
                        showCurve(id, checked);
                    }
                });
            } else {
                showCurve(greId, checked);
            }
        });
    }

    function makeCoremInfoPanel(coremPanelSelector, coremInfos) {
        if (coremInfos.length == 0) {
            content = '(no corems found)';
        } else {
            var content = '<ul class="corem-panel">';
            for (var i = 0; i < coremInfos.length; i++) {
                var coremId = 'COREM_' + coremInfos[i].id;
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
        var initialMinGRECount = 100;
        if (typeof options.minGRECount !== "undefined") {
            initialMinGRECount = options.minGRECount;
        }

        jQuery.get(greURL, null,
              function (data, status, jqxhr) {
                  var gene = data.gene;
                  var gres = Object.keys(data.gres);
                  var chipseqPeaks = data.chipseq_peaks;
                  if (gres.length > 0) {

                      makeGREPanel(grePanelSelector, gres, initialMinGRECount);

                      var domain = initDomain(data);
                      drawAxes(chart, options, domain);

                      for (var i in gres) {
                          var gredata = data.gres[gres[i]];
                          drawCurve(chart, options, gres[i], gredata, COLORS[i % COLORS.length]);
                          var counts = gredata.map(function (a) {
                              return a.count;
                          });
                          greMaxValues[gres[i]] = Math.max(...counts)
                          if (greMaxValues[gres[i]] > greMaxValue) {
                              greMaxValue = greMaxValues[gres[i]];
                          }
                      }
                      drawChipSeqPeaks(chart, options, chipseqPeaks);
                  } else {
                      chart.append("text")
                          .attr("x", 100)
                          .attr("y", 100)
                          .style("text-anchor", "middle")
                          .attr("dy", ".1em")
                          .text("no GREs available");
                  }
                  var genebarHeight = 20;
                  var genebarY = options.height + 25;
                  console.debug("genebarY: " + greMaxValue);
                  var genebarX1 = gene.start < gene.stop ? xScale(gene.start) : xScale(gene.stop);
                  var genebarX2 = gene.start < gene.stop ? xScale(gene.stop) : xScale(gene.start);
                  var genebarWidth = Math.abs(genebarX2 - genebarX1);

                  chart.append('rect')
                      .attr("x", genebarX1)
                      .attr("y", genebarY)
                      .attr("width", genebarWidth).attr("height", genebarHeight)
                      .style("fill", "orange")
                      .style("stroke", "black")
                      .append("title").text(function(d) { return gene.name + ' (' + gene.start + '-' + gene.stop + ')'; });
                  chart.append("text")
                      .attr("x", genebarX1)
                      .attr("y", genebarY + genebarHeight / 2)
                      .attr("dy", ".35em")
                      .text(options.gene);

                  // first time should set visibility
                  minGRECountChanged(initialMinGRECount);
              }, "json");

        jQuery.get(coremURL, null,
              function (data, status, jqxhr) {
                  makeCoremInfoPanel(coremPanelSelector, data.corem_infos);
              }, "json");
    };
}());
