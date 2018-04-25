<?php

/**********************************************************************
 * Custom Short codes
 * Render the custom fields by interfacting with the web service
 **********************************************************************/

function bicluster_genes_shortcode($atts, $content=null)
{
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $use_microformats = $atts['microformats'] == "true";

    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_genes/" . $bicluster_num);
    $genes = json_decode($genes_json)->genes;
    $content = "<ul style=\"font-size: 8pt\">";
    foreach ($genes as $r) {
        $content .= "<li>" . $r->gene_name . "</li>";
    }
    $content .= "</ul>";
    if ($use_microformats) {
        $species = "Species (TODO)";
        $content .= "<div class=\"gaggle-data\" style=\"display:none\">";
        $content .= "  <span class=\"gaggle-name\">Row members cluster " . $bicluster_num . "</span>";
        $content .= "  <span class=\"gaggle-species\">" . $species . "</span>";
        $content .= "  <span class=\"gaggle-namelist\">";
        $content .= "    <ol>";
        foreach ($genes as $r) {
            $content .= "<li>" . $r->gene_name . "</li>";
        }
        $content .= "    </ol>";
        $content .= "  </span>";
        $content .= "</div>";
    }
    return $content;
}

function bicluster_conditions_shortcode($atts, $content=null)
{
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $use_microformats = $atts['microformats'] == "true";

    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $col_membs_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_conditions/" . $bicluster_num);
    $col_membs = json_decode($col_membs_json)->conditions;
    $content = "<ul style=\"font-size: 8pt\">";
    foreach ($col_membs as $m) {
        $content .= "<li>" . $m->name . "</li>";
    }
    $content .= "</ul>";
    if ($use_microformats) {
        $species = "Species (TODO)";
        $content .= "<div class=\"gaggle-data\" style=\"display:none\">";
        $content .= "  <span class=\"gaggle-name\">Column members cluster " . $bicluster_num . "</span>";
        $content .= "  <span class=\"gaggle-species\">" . $species . "</span>";
        $content .= "  <span class=\"gaggle-namelist\">";
        $content .= "    <ol>";
        foreach ($col_membs as $m) {
            $content .= "<li>" . $m->name . "</li>";
        }
        $content .= "    </ol>";
        $content .= "  </span>";
        $content .= "</div>";
    }
    return $content;
}

function bicluster_motifs_shortcode($atts, $content=null)
{
    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $motifs_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_pssms/" . $bicluster_num);

    $content = "<div id=\"canvas_1\"></div>";
    $content .= "<div id=\"canvas_2\"></div>";

    $content .= "<script>";
    $content .= "  var canvasOptions = {";
    $content .= "    width: 300,";
    $content .= "    height: 150,";
    $content .= "    glyphStyle: '20pt Helvetica'";
    $content .= "  };";
    $content .= "  var motifs = " . $motifs_json . ";\n";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    seqlogo.makeLogo('canvas_1', motifs['motifs'][0], canvasOptions);";
    $content .= "    seqlogo.makeLogo('canvas_2', motifs['motifs'][1], canvasOptions);";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function model_overview_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $summary_json = file_get_contents($source_url . "/api/v1.0.0/summary");
    $summary = json_decode($summary_json);
    $content = "<h2>Model Overview</h2>";
    $content .= "<table id=\"summary\" class=\"row-border\">";
    $content .= "  <thead><tr><th>#</th><th>Description</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td><a href=\"index.php/genes/\">" . $summary->num_genes . "</a></td><td>Genes</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/conditions/\">" . $summary->num_conditions . "</a></td><td>Conditions</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/corems/\">" . $summary->num_corems . "</a></td><td>Corems <img id=\"corem_help\" style=\"width: 18px\" src=\"" . esc_url(plugins_url('images/help.png', __FILE__)). "\"></td></tr>";
    $content .= "    <tr><td><a href=\"index.php/biclusters/\">" . $summary->num_biclusters . "</a></td><td>Biclusters <img id=\"bicluster_help\" style=\"width: 18px\" src=\"" . esc_url(plugins_url('images/help.png', __FILE__)). "\"></td></tr>";
    $content .= "    <tr><td><a href=\"index.php/gres/\">" . $summary->num_gres . "</a></td><td>GREs <img id=\"gre_help\" style=\"width: 18px\" src=\"" . esc_url(plugins_url('images/help.png', __FILE__)). "\"></td></tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";

    $content .= "    jQuery('#summary').DataTable({";
    $content .= "      'paging': false,";
    $content .= "      'info': false,";
    $content .= "      'searching': false";
    $content .= "    });";
    $content .= "    jQuery('#bicluster_help').qtip({ content: 'Bicluster: Output of integrated biclustering algorithm, cMonkey (Reiss et al., 2006), that identifies groups of genes with (1) similar patterns of differential expression over subsets of conditions, (2) similar de novo detected <i>cis</i>-regulatory motifs in their promoters, and (3) related functions, inferred from functional association networks (e.g., EMBL STRING (Szklarczyk et al., 2011)).' });";
    $content .= "    jQuery('#corem_help').qtip({ content: 'A <strong>corem</strong> or <i>conditionally co-regulated module</i> is a set of genes that are <strong>co-regulated</strong> in <strong>specific environments</strong>. Often (but not always) genes in a corem share common regulatory mechanisms (<i>GREs</i>). A gene can belong to multiple corems. Corems were discovered by applying <strong>EGRIN 2.0</strong> to large gene expression data sets.' });";
    $content .= "    jQuery('#gre_help').qtip({ content: 'GRE: Gene Regulatory Element, a cluster of similar <i>cis</i>-regulatory motifs' });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function corems_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $corems_json = file_get_contents($source_url . "/api/v1.0.0/corems");
    $corems = json_decode($corems_json)->corems;

    $content = "<table id=\"corems\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Corem ID</th><th># Genes</th><th># Conditions</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($corems as $c) {
        $content .= "    <tr><td><a href=\"index.php/corem/?corem=" . $c->id . "\">" . $c->id . "</a></td><td>". $c->num_genes . "</td><td>" . $c->num_conds . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#corems').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function conditions_table_html($conditions)
{
    $content = "<table id=\"conditions\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Name</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($conditions as $c) {
        $content .= "    <tr><td><a href=\"index.php/condition/?condition=" . $c->id . "\">". $c->name . "</a></td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#conditions').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function conditions_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $conds_json = file_get_contents($source_url . "/api/v1.0.0/conditions");
    $conds = json_decode($conds_json)->conditions;

    return conditions_table_html($conds);
}

function corem_conditions_table_shortcode($attr, $content=null)
{
    $corem_id = get_query_var('corem');
    $source_url = get_option('source_url', '');
    $conds_json = file_get_contents($source_url . "/api/v1.0.0/corem_conditions/" . $corem_id);
    $conds = json_decode($conds_json)->conditions;
    if (count($conds) == 1) {
        $content = '<h4>Enriched in 1 Condition</h4>';
    } else {
        $content = '<h4>Enriched in ' . count($conds) . ' Conditions</h4>';
    }

    return $content . conditions_table_html($conds);
}

function categories_table_html($categories)
{
    $num_categories = count($categories);
    if ($num_categories == 1) {
        $content = "<h4>Enriched in 1 Functional Category</h4>";
    } else {
        $content = "<h4>Enriched in " . count($categories) . " Functional Categories</h4>";
    }
    $content .= "<table id=\"categories\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Name</th><th>q-value</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($categories as $c) {
        $content .= "    <tr><td>" . $c->category . "</td><td>" . $c->p_adj . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#categories').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function corem_categories_table_shortcode($attr, $content=null)
{
    $corem_id = get_query_var('corem');
    $source_url = get_option('source_url', '');
    $cats_json = file_get_contents($source_url . "/api/v1.0.0/corem_categories/" . $corem_id);
    $cats = json_decode($cats_json)->categories;

    return categories_table_html($cats);
}

function genes_ajax_table_html($ajax_action, $user_params)
{
    // $user_params -> Javascript parameters
    $js_params = '';
    foreach ($user_params as $key => $value) {
        $js_params .= ",'" . $key . "': '" . $value . "'";
    }

    $content = "<table id=\"genes\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Name</th><th>Common Name</th><th>Links</th><th>Description</th><th>Start</th><th>Stop</th><th>Strand</th><th>Chromosome</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    jQuery('#genes').DataTable({\n";
    $content .= "      'processing': true,\n";
    $content .= "      'serverSide': true,\n";
    $content .= "      'columns': [\n";
    $content .= "        {'data': 'gene_name'},\n";
    $content .= "        {'data': 'common_name'},\n";
    $content .= "        {'data': 'links'},\n";
    $content .= "        {'data': 'description'},\n";
    $content .= "        {'data': 'start'},\n";
    $content .= "        {'data': 'stop'},\n";
    $content .= "        {'data': 'strand'},\n";
    $content .= "        {'data': 'chromosome'}\n";
    $content .= "      ],\n";
    $content .= "      'ajax': {\n";
    $content .= "         'url': ajax_dt.ajax_url,\n";
    $content .= "         'type': 'GET',\n";
    $content .= "         'data': {'action': '" . $ajax_action . "'" . $js_params .  "}\n";
    $content .= "     }\n";
    $content .= "    });\n";
    $content .= "  });\n";
    $content .= "</script>";
    return $content;
}

function genes_table_shortcode($attr, $content)
{
    return genes_ajax_table_html('genes_dt', array());
}

function gene_info_shortcode($attr, $content=null)
{
    $gene_name = get_query_var('gene');
    if (!$gene_name) return "(no gene name provided)";
    $source_url = get_option('source_url', '');
    $gene_json = file_get_contents($source_url . "/api/v1.0.0/gene_info/" . $gene_name);
    $gene = json_decode($gene_json)->gene;
    $content = "<div>";
    $content .= "Common Name: " . $gene->common_name . "<br>";
    $content .= "Accession: <a target=\"_blank\" href=\"https://www.ncbi.nlm.nih.gov/protein/" . $gene->accession . "\">" . $gene->accession . "</a><br>";
    $content .= "Description: " . $gene->description . "<br>";
    $content .= "Chromosome: " . $gene->chromosome . "<br>";
    $content .= "Strand: " . $gene->strand . "<br>";
    $content .= "Position: " . $gene->start . "-" .  $gene->stop . "<br>";
    $content .= "-&gt; <a target=\"_blank\" href=\"http://tuberculist.epfl.ch/quicksearch.php?gene+name=" . $gene_name . "\">Tuberculist</a><br>";


    $content .= "</div>";
    return $content;
}

function corem_genes_table_shortcode($attr, $content)
{
    $corem_id = get_query_var('corem');
    return genes_ajax_table_html('corem_genes_dt', array('corem_id' => $corem_id));
}

function corem_title_shortcode($attr, $content)
{
    $corem_id = get_query_var('corem');
    return "<h3>Corem " . $corem_id . "</h3>";
}

function corem_coexpressions_graph_shortcode($attr, $content)
{
    $corem_id = get_query_var('corem');

    $source_url = get_option('source_url', '');
    $blocks_json = file_get_contents($source_url . "/api/v1.0.0/corem_condition_enrichment/" . $corem_id);
    $blocks = json_decode($blocks_json)->condition_blocks;

    $content = "<div style=\"width: 100%;\"><img id=\"coexp_help\" style=\"width: 18px; float: right\" src=\"" . esc_url(plugins_url('images/help.png', __FILE__)). "\"></div>\n";
    $content .= '<h4>Condition Blocks</h4>';
    $content .= '<ul style="list-style-type: none">';
    $content .= '  <li><input id="ccb_0" type="checkbox" value="0" checked></input> All</li>';
    foreach ($blocks as $i=>$b) {
        $content .= '  <li><input id="ccb_' . $b->id . '" type="checkbox" value="' . $b->id . '"></input> ' . $b->name . '</li>';
    }
    $content .= '</ul>';
    $content .= '<div id="corem_coexps" style="width: 100%; height: 300px"></div>';
    $content .= "<script>\n";
    $content .= "    function makeCoCoExpChart(data, conds) {";
    $content .= "      var x, chart = Highcharts.chart('corem_coexps', {\n";
    $content .= "        chart: { type: 'line' },";
    $content .= "        title: { text: 'Co-expression' },\n";
    $content .= "        xAxis: { title: { text: 'Conditions' }, categories: conds,\n";
    $content .= "                 labels: {\n";
    $content .= "                   formatter: function() {\n";
    $content .= "                     return this.axis.categories.indexOf(this.value);\n";
    $content .= "                   }}},\n";
    $content .= "        yAxis: { title: { text: 'Standardized expression'} },\n";
    $content .= "        series: data\n";
    $content .= "     })\n";
    $content .= "   }\n";

    $content .= "  function loadCoremCoexpressions(coremId, blocks) {\n";
    $content .= "    jQuery.ajax({\n";
    $content .= "      url: ajax_dt.ajax_url,\n";
    $content .= "      method: 'GET',\n";
    $content .= "      data: {'action': 'corem_coexps_dt', 'corem': " . $corem_id .", 'blocks': blocks }\n";
    $content .= "    }).done(function(data) {\n";
    $content .= "      makeCoCoExpChart(data.expressions, data.conditions);\n";
    $content .= "    });\n";
    $content .= "  };\n";


    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    jQuery('#coexp_help').qtip({ content: 'Hover over data points to see condition and value information' });\n";
    $blocks = array(0);
    $content .= "    loadCoremCoexpressions(" . $corem_id . ", [" . implode(",", $blocks) . "]);\n";
    $content .= "    jQuery('input[id^=\"ccb_\"]').click(function() {\n";
    $content .= "        if (this.id == 'ccb_0') {\n";
    $content .= "          jQuery('input[id^=\"ccb_\"]').attr('checked', false);\n";
    $content .= "          jQuery('#ccb_0').attr('checked', true);\n";
    $content .= "        } else {\n";
    $content .= "          jQuery('#ccb_0').attr('checked', false);\n";
    $content .= "        }\n";
    $content .= "        var checked = jQuery('input[id^=\"ccb_\"]:checked');\n";
    $content .= "        var selectedArr = [];\n";
    $content .= "        jQuery.each(checked, function(i, e) { selectedArr.push(e.id.substring(4)); });\n";
    $content .= "        loadCoremCoexpressions(" . $corem_id . ", selectedArr);";
    // reload the graph with changed
    $content .= "    });\n";
    $content .= "  });\n";
    $content .= "</script>\n";
    return $content;
}

function corem_condition_blocks_shortcode($attr, $content)
{
    $corem_id = get_query_var('corem');
    if (!$corem_id) return "(no corem provided)";
    $source_url = get_option('source_url', '');
    $blocks_json = file_get_contents($source_url . "/api/v1.0.0/corem_condition_enrichment/" . $corem_id);
    $blocks = json_decode($blocks_json)->condition_blocks;
    $num_blocks = count($blocks);
    if ($num_blocks == 1) {
        $content = "<h4>Enriched in 1 Condition Block</h4>";
    } else {
        $content = "<h4>Enriched in " . count($blocks) . " Condition Blocks</h4>";
    }
    $content .= '<table id="corem_condition_blocks" class="stripe row-border">';
    $content .= '  <thead><tr><th>Name</th><th>q-value</th></tr></thead>';
    $content .= '  <tbody>';
    foreach ($blocks as $b) {
        $content .= "  <tr><td>" . $b->name . "</td><td>" . $b->q_value.  "</td></tr>";
    }
    $content .= '  </tbody>';
    $content .= '</table>';
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#corem_condition_blocks').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function corem_gres_shortcode($attr, $content)
{
    $corem_id = get_query_var('corem');
    if (!$corem_id) return "(no corem provided)";
    $source_url = get_option('source_url', '');
    $gres_json = file_get_contents($source_url . "/api/v1.0.0/corem_gres/" . $corem_id);
    $gres = json_decode($gres_json)->gres;
    if (count($gres) == 1) {
        $content = '<h4>Enriched in 1 GRE</h4>';
    } else {
        $content = '<h4>Enriched in ' . count($gres) . ' GREs</h4>';
    }
    $content .= '<table id="corem_gres" class="stripe row-border">';
    $content .= '  <thead><tr><th>GRE</th><th>Motif</th><th>Motif e-value</th><th>q-value</th></tr></thead>';
    $content .= '  <tbody>';
    foreach ($gres as $g) {
        if (!get_object_vars($g->pssm)) {
            $content .= '<tr><td>' . $g->gre . '</td><td>N/A</td><td>-</td><td>' . $g->q_value .'</td></tr>';
        } else {
            $content .= '<tr><td>' . $g->gre . '</td><td><span id="gre_pssm_' . $g->gre . '"></span></td><td>' . $g->motif_evalue . '</td><td>' . $g->q_value .'</td></tr>';
        }
    }
    $content .= '  </tbody>';
    $content .= '</table>';
    $content .= "<script>";
    foreach ($gres as $g) {
        if (get_object_vars($g->pssm)) {
            $content .= 'var gre_pssm_' . $g->gre . ' = ' . json_encode($g->pssm) . ';';
        }
    }
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#corem_gres').DataTable({";
    $content .= "      'columnDefs': [ {'width': '10%', 'targets': 0}, {'width': '70%', 'targets': 1}, {'width': '10%', 'targets': 2} ]";
    $content .= "    });";

    foreach ($gres as $g) {
        if (get_object_vars($g->pssm)) {
            $content .= '  seqlogo.makeLogo("gre_pssm_' . $g->gre . '", gre_pssm_' . $g->gre . ', {width: 400, height: 120, glyphStyle: "20pt Helvetica"});';
        }
    }
    $content .= "  });";
    $content .= "</script>";
    return $content;
}


function condition_name_shortcode($attr, $content=null)
{
    $condition_id = get_query_var('condition');
    if (!$condition_id) return "(no condition provided)";
    $source_url = get_option('source_url', '');
    $cond_json = file_get_contents($source_url . "/api/v1.0.0/condition_info/" . $condition_id);
    return json_decode($cond_json)->condition->name;
}

function condition_blocks_shortcode($attr, $content=null)
{
    $condition_id = get_query_var('condition');
    if (!$condition_id) return "(no condition provided)";
    $source_url = get_option('source_url', '');
    $cond_json = file_get_contents($source_url . "/api/v1.0.0/condition_info/" . $condition_id);
    $cond_blocks = get_object_vars(json_decode($cond_json)->blocks);

    $content = "<div>";
    $block_num = 0;
    foreach ($cond_blocks as $b => $bconds) {
        $content .= "  <span>" . $b . " <a title=\"click to view conditions\" style=\"text-decoration:none;\" class=\"expand_block_members\" href=\"javascript:void(0)\" id=\"block_" . $block_num . "\">+ </a></span>";
        $content .= "  <div id=\"block_conds_" . $block_num . "\" style=\"font-size: xx-small; display: none\">";
        foreach ($bconds as $sub_cond) {
            $content .= "<a href=\"../../index.php/condition/?condition=\">" . $sub_cond . "</a> ";
        }
        $content .= "  </div>";
        $block_num++;
    }
    $content .= "</div>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('.expand_block_members').click(function() {";
    $content .= "      var block_conds_id = 'block_conds_' + jQuery(this).attr('id').substring(6);";
    $content .= "      jQuery('#' + block_conds_id).toggle();";
    $content .= "      if (jQuery(this).text()[0] == '+') jQuery(this).text('- ');";
    $content .= "      else jQuery(this).text('+ ');";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function biclusters_table_html($clusters)
{
    $num_clusters = count($clusters);
    if ($num_clusters == 1) {
        $content = "<h4>Contained in 1 Bicluster</h4>";
    } else {
        $content = "<h4>Contained in " . $num_clusters . " Biclusters</h4>";
    }
    $content .= "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Bicluster ID</th><th># Genes</th><th># Conditions</th><th>Residual</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($clusters as $c) {
        $content .= "    <tr><td><a href=\"index.php/bicluster/?bicluster=" . $c->id . "\">" . $c->id . "</a></td><td>". $c->num_genes . "</td><td>" . $c->num_conditions . "</td><td>" . $c->residual . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#biclusters').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}


function biclusters_table_shortcode($attr, $content)
{
    $content = "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Bicluster ID</th><th># Genes</th><th># Conditions</th><th>Residual</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    jQuery('#biclusters').DataTable({\n";
    $content .= "      'processing': true,\n";
    $content .= "      'serverSide': true,\n";
    $content .= "      'columns': [\n";
    $content .= "        {'data': 'id'},\n";
    $content .= "        {'data': 'num_genes'},\n";
    $content .= "        {'data': 'num_conditions'},\n";
    $content .= "        {'data': 'residual'}\n";
    $content .= "      ],\n";
    $content .= "      'ajax': {\n";
    $content .= "         'url': ajax_dt.ajax_url,\n";
    $content .= "         'type': 'GET',\n";
    $content .= "         'data': {'action': 'biclusters_dt'}\n";
    $content .= "     }\n";
    $content .= "    });\n";
    $content .= "  });\n";
    $content .= "</script>";
    return $content;
}

function gres_table_shortcode($attr, $content)
{
    $content = "<table id=\"gres\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>GRE</th><th>Motif</th><th>Motif e-value</th><th># corems</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    var table = jQuery('#gres').DataTable({\n";
    $content .= "      'processing': true,\n";
    $content .= "      'serverSide': true,\n";
    $content .= "      'columns': [\n";
    $content .= "        {'data': 'gre'},\n";
    $content .= "        {'data': 'pssm_tag'},\n";
    $content .= "        {'data': 'motif_evalue'},\n";
    $content .= "        {'data': 'corems'}\n";
    $content .= "      ],\n";
    $content .= "      'ajax': {\n";
    $content .= "         'url': ajax_dt.ajax_url,\n";
    $content .= "         'type': 'GET',\n";
    $content .= "         'data': {'action': 'gres_dt'}\n";
    $content .= "     }\n";
    $content .= "    });\n";
    $content .= "    table.on('draw.dt', function() {\n";
    $content .= "       var i, numRows = table.rows().data().length;\n";
    $content .= "       for (i = 0; i < numRows; i++) {\n";
    $content .= "         var greObj = table.rows(i).data()[0];\n";
    $content .= '         seqlogo.makeLogo("gre_pssm_" + greObj.gre, greObj.pssm, {width: 400, height: 120, glyphStyle: "20pt Helvetica"});';
    $content .= "       }\n";
    $content .= "     });\n";
    $content .= "  });\n";
    $content .= "</script>";
    return $content;
}


function gene_biclusters_table_shortcode($attr, $content=null)
{
    $gene = get_query_var('gene');
    $source_url = get_option('source_url', '');
    $clusters_json = file_get_contents($source_url . "/api/v1.0.0/gene_biclusters/" . $gene);
    $clusters = json_decode($clusters_json)->biclusters;
    $num_clusters = count($clusters);
    return $content . biclusters_table_html($clusters);
}

function corems_table_html($corems)
{
    $content = "<table id=\"corems\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Corem ID</th><th># Genes</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($corems as $c) {
        $content .= "    <tr><td><a href=\"index.php/corem/?corem=" . $c->id . "\">" . $c->id . "</a></td><td>". count($c->genes) . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#corems').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function corems_table_html2($corems)
{
    $content = "<table id=\"corems\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Corem ID</th><th># Genes</th><th># Conditions</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($corems as $c) {
        $content .= "    <tr><td><a href=\"index.php/corem/?corem=" . $c->id . "\">" . $c->id . "</a></td><td>". $c->num_genes . "</td><td>" . $c->num_conditions . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#corems').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}


function gene_title_shortcode($attr, $content)
{
    $gene = get_query_var('gene');
    return "<h3>" . $gene . "</h3>";
}

function gene_corems_table_shortcode($attr, $content=null)
{
    $gene = get_query_var('gene');
    $source_url = get_option('source_url', '');
    $corems_json = file_get_contents($source_url . "/api/v1.0.0/corems_with_gene/" . $gene);
    $corems = json_decode($corems_json)->corem_infos;
    $num_corems = count($corems);
    if ($num_corems == 1) {
        $content = "<h4>Contained in 1 Corem</h4>";
    } else {
        $content = "<h4>Contained in " . count($corems) . " Corems</h4>";
    }
    return $content . corems_table_html($corems);
}

function condition_biclusters_table_shortcode($attr, $content=null)
{
    $condition_id = get_query_var('condition');
    $source_url = get_option('source_url', '');
    $clusters_json = file_get_contents($source_url . "/api/v1.0.0/condition_biclusters/" . $condition_id);
    $clusters = json_decode($clusters_json)->biclusters;

    return biclusters_table_html($clusters);
}

function bicluster_info_shortcode($attr, $content=null)
{
    $bicluster_id = get_query_var('bicluster');
    if (!$bicluster_id) return "(no bicluster id provided)";
    $source_url = get_option('source_url', '');
    $bicluster_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_info/" . $bicluster_id);
    $bicluster = json_decode($bicluster_json)->bicluster;
    $content = "<div>";
    $content .= "ID: " . $bicluster->id . "<br>";
    $content .= "# Conditions: " . $bicluster->num_conditions . "<br>";
    $content .= "# Genes: " . $bicluster->num_genes . "<br>";
    $content .= "Residual: " . $bicluster->residual . "<br>";
    $content .= "</div>";
    return $content;
}

function gene_gre_browser_shortcode($attr, $content)
{
    $gene = get_query_var('gene');
    if (!$gene) return "(no gene provided)";
    $source_url = get_option('source_url', '');
    $content = "<h4>Promotor architecture for " . $gene . "</h4>";
    $content .= "<h5>Corems</h5>";
    $content .= '<div id="corem-panel"></div>';
    $content .= "<h5>GREs</h5>";
    $content .= '<div id="gre-panel"></div>';
    $content .= '<svg id="gene_gre_browser"></svg>';
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= '    corem_browser.init("#gene_gre_browser", "#gre-panel", "#corem-panel",';
    $content .= '                       { width: 640, height: 220, minGRECount: 10, apiURL: "' . $source_url . '", gene: "' . $gene . '" });';
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function search_box_shortcode($attr, $content)
{
    $content = "<form action=\"" . esc_url(admin_url('admin-post.php')) .  "\" method=\"post\">";
    $content .= "Search Term: <input name=\"search_term\" type=\"text\"></input>";
    $content .= "<div style=\"margin-top: 5px;\"><input type=\"submit\" value=\"Search\"></input></div>";
    $content .= "<input type=\"hidden\" name=\"action\" value=\"search_biclusters\">";
    $content .= "</form>";
    return $content;
}

function search_results_shortcode($attr, $content)
{
    $search_term = $_GET['search_term'];
    $content = "<div>Search Term: " . $search_term . "</div>";
    $solr_server = "http://garda:8983/solr";
    $core1 = "mtb_corems";
    $core2 = "mtb_clusters";

    $results_json = file_get_contents($solr_server . "/" . $core1 . "/select?indent=on&q=" .
                                      $search_term . "&wt=json&rows=1000");
    $results = json_decode($results_json);
    $num_found = $results->response->numFound;
    if ($num_found > 0) {
        $content .= "<div># corems found: " . $num_found . "</div>";
        $corems = array();
        foreach ($results->response->docs as $doc) {
            $corems []= (object) array('id' => $doc->id,
                                       'num_genes' => count($doc->genes),
                                       'num_conditions' => count($doc->conditions));
        }
        $content .= corems_table_html2($corems);
    } else {
        $results_json = file_get_contents($solr_server . "/" . $core2 . "/select?indent=on&q=" .
                                          $search_term . "&wt=json&rows=1000");
        $results = json_decode($results_json);
        $num_found = $results->response->numFound;
        $content .= "<div># biclusters found: " . $num_found . "</div>";
        $biclusters = array();
        foreach ($results->response->docs as $doc) {
            $biclusters []= (object) array('id' => $doc->id,
                                           'num_genes' => count($doc->genes),
                                           'num_conditions' => count($doc->conditions),
                                           'residual' => $doc->residual[0]);
        }
        $content .= biclusters_table_html($biclusters);
    }
    return $content;
}


function biclusters_add_shortcodes()
{
    add_shortcode('bicluster_genes', 'bicluster_genes_shortcode');
    add_shortcode('bicluster_conditions', 'bicluster_conditions_shortcode');

    add_shortcode('corem_genes_table', 'corem_genes_table_shortcode');
    add_shortcode('corem_conditions_table', 'corem_conditions_table_shortcode');
    add_shortcode('corem_categories_table', 'corem_categories_table_shortcode');
    add_shortcode('corem_coexpressions_graph', 'corem_coexpressions_graph_shortcode');
    add_shortcode('corem_condition_blocks', 'corem_condition_blocks_shortcode');
    add_shortcode('corem_gres', 'corem_gres_shortcode');
    add_shortcode('corem_title', 'corem_title_shortcode');

    add_shortcode('gene_title', 'gene_title_shortcode');
    add_shortcode('gene_info', 'gene_info_shortcode');
    add_shortcode('gene_biclusters_table', 'gene_biclusters_table_shortcode');
    add_shortcode('gene_corems_table', 'gene_corems_table_shortcode');
    add_shortcode('gene_gre_browser', 'gene_gre_browser_shortcode');

    add_shortcode('bicluster_motifs', 'bicluster_motifs_shortcode');
    add_shortcode('model_overview', 'model_overview_shortcode');
    add_shortcode('condition_name', 'condition_name_shortcode');
    add_shortcode('condition_blocks', 'condition_blocks_shortcode');
    add_shortcode('bicluster_info', 'bicluster_info_shortcode');
    add_shortcode('condition_biclusters_table', 'condition_biclusters_table_shortcode');

    add_shortcode('biclusters_search_box', 'search_box_shortcode');
    add_shortcode('biclusters_search_results', 'search_results_shortcode');

    // EGRIN2 specific
    add_shortcode('corems_table', 'corems_table_shortcode');
    add_shortcode('conditions_table', 'conditions_table_shortcode');
    add_shortcode('genes_table', 'genes_table_shortcode');
    add_shortcode('biclusters_table', 'biclusters_table_shortcode');
    add_shortcode('gres_table', 'gres_table_shortcode');
}

?>
