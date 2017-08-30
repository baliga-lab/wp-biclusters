var corem_browser = {};

(function () {

    var MARGIN = {left: 20, bottom: 60, right: 20, top: 20};
    var GENEBAR_HEIGHT = 20;
    /* at least this much of the gene bar has to be visible at all times*/
    var GENEBAR_MINX_VISIBLE = 50;
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

    function drawAxes(chart, options, domain, gene) {
        var minGeneX = Math.min(gene.start, gene.stop);
        var maxGeneX = Math.max(gene.start, gene.stop);
        var minx = domain.minx, maxx = domain.maxx;
        var geneLeft = true;
        /* the start of the gene is too far right off the scale => adjust */
        if (minGeneX >= maxx - GENEBAR_MINX_VISIBLE) {
            maxx = minGeneX + GENEBAR_MINX_VISIBLE;
        }
        /* the end of the gene is too far left off the scale => adjust */
        if (maxGeneX <= minx + GENEBAR_MINX_VISIBLE) {
            minx = maxGeneX - GENEBAR_MINX_VISIBLE;
        }
        /* only the right portion of the gene is displayed */
        if (minGeneX < minx) {
            geneLeft = false;
        }

        xScale = d3.scaleLinear().domain([minx - 1, maxx + 1]).range([0, options.width]);
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
        return geneLeft;
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

    function drawChipseqPeak(chart, options, chipseqPeaks, tf, i) {
        var color = '#ff0000';
        var line = d3.line()
            .x(function(d) { return xScale(d.pos); })
            .y(function(d) { return yScale(d.value); });
        var id = 'chippeak_' + tf;
        var pos = chipseqPeaks[tf];
        var y = greMaxValue - ((greMaxValue / 15) * i);
        var linedata1 = [
            {pos: pos, value: 0},
            {pos: pos, value: y}
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
            .attr('y', yScale(y))
            .style('stroke', color)
            .text(tf);
    }

    function drawChipSeqPeaks(chart, options, chipseqPeaks) {
        var chipseqTFs = Object.keys(chipseqPeaks), tf, i, curve, id, linedata, position;
        for (i = 0; i < chipseqTFs.length; i++) {
            tf = chipseqTFs[i];
            drawChipseqPeak(chart, options, chipseqPeaks, tf, i);
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

    function drawGeneBar(chart, options, gene, geneLeft) {
        var genebarY = options.height + (MARGIN.bottom - (GENEBAR_HEIGHT + 5));
        var genebarX1 = gene.start < gene.stop ? xScale(gene.start) : xScale(gene.stop);
        var genebarX2 = gene.start < gene.stop ? xScale(gene.stop) : xScale(gene.start);
        var genebarWidth = Math.abs(genebarX2 - genebarX1);
        var geneLabelX = geneLeft ? genebarX1 : genebarX2 - 80;

        chart.append('rect')
            .attr("x", genebarX1)
            .attr("y", genebarY)
            .attr("width", genebarWidth).attr("height", GENEBAR_HEIGHT)
            .style("fill", "orange")
            .style("stroke", "black")
            .append("title").text(function(d) { return gene.name + ' (' + gene.start + '-' + gene.stop + ')'; });
        chart.append("text")
            .attr("x", geneLabelX)
            .attr("y", genebarY + GENEBAR_HEIGHT / 2)
            .attr("dy", ".35em")
            .text(options.gene);
    }

    function drawTSSSite(chart, options, tss) {
        var tssX1 = tss.start < tss.stop ? xScale(tss.start) : xScale(tss.stop);
        var tssX2 = tss.stop > tss.start ? xScale(tss.stop) : xScale(tss.start);
        var tssY = options.height + (GENEBAR_HEIGHT + 5);
        chart.append("line").attr("x1", tssX1).attr("y1", tssY).attr("x2", tssX2).attr("y2", tssY)
            .style("stroke", "green")
            .append("title").text(function(d) { return 'TSS (' + tss.start + '-' + tss.stop + ')'; });
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
                      var geneLeft = drawAxes(chart, options, domain, gene);

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
                  drawGeneBar(chart, options, gene, geneLeft);
                  drawTSSSite(chart, options, data.tss);

                  // first time should set visibility
                  minGRECountChanged(initialMinGRECount);
              }, "json");

        jQuery.get(coremURL, null,
              function (data, status, jqxhr) {
                  makeCoremInfoPanel(coremPanelSelector, data.corem_infos);
              }, "json");
    };
}());
