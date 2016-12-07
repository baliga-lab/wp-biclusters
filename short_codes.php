<?php

/**********************************************************************
 * Custom Short codes
 * Render the custom fields by interfacting with the web service
 **********************************************************************/

function bicluster_genes_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $use_microformats = $atts['microformats'] == "true";

    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_genes/" . $bicluster_num);
    $genes = json_decode($genes_json, true)["genes"];
    $row_membs = [];
    foreach ($genes as $r) {
        $row_membs[] = $r["gene_name"];
    }
    $content .= "<ul style=\"font-size: 8pt\">";
    foreach ($row_membs as $m) {
        $content .= "<li>" . $m . "</li>";
    }
    $content .= "</ul>";
    if ($use_microformats) {
        $species = "Species (TODO)";
        $content .= "<div class=\"gaggle-data\" style=\"display:none\">";
        $content .= "  <span class=\"gaggle-name\">Row members cluster " . $bicluster_num . "</span>";
        $content .= "  <span class=\"gaggle-species\">" . $species . "</span>";
        $content .= "  <span class=\"gaggle-namelist\">";
        $content .= "    <ol>";
        foreach ($row_membs as $m) {
            $content .= "<li>" . $m . "</li>";
        }
        $content .= "    </ol>";
        $content .= "  </span>";
        $content .= "</div>";
    }
    return $content;
}

function bicluster_conditions_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $use_microformats = $atts['microformats'] == "true";

    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $col_membs_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_conditions/" . $bicluster_num);
    $col_membs = json_decode($col_membs_json, true)["conditions"];
    $content .= "<ul style=\"font-size: 8pt\">";
    foreach ($col_membs as $m) {
        $content .= "<li>" . $m["name"] . "</li>";
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
            $content .= "<li>" . $m . "</li>";
        }
        $content .= "    </ol>";
        $content .= "  </span>";
        $content .= "</div>";
    }
    return $content;
}

function bicluster_motifs_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $motifs_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_pssms/" . $bicluster_num);

    $content .= "<div id=\"canvas_1\"></div>";
    $content .= "<div id=\"canvas_2\"></div>";

    $content .= "<script>";
    $content .= "  var canvasOptions = {";
    $content .= "    width: 300,";
    $content .= "    height: 150,";
    $content .= "    glyphStyle: '20pt Helvetica'";
    $content .= "  };";
    $content .= "  var motifs = " . $motifs_json;
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    isblogo.makeLogo('canvas_1', motifs['motifs'][0], canvasOptions);";
    $content .= "    isblogo.makeLogo('canvas_2', motifs['motifs'][1], canvasOptions);";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function model_overview_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $summary_json = file_get_contents($source_url . "/api/v1.0.0/summary");
    $summary = json_decode($summary_json, true);

    if ($content == null) {
        $content = '';
    }
    $content .= "<h2>Model Overview</h2>";
    $content .= "<table id=\"summary\" class=\"row-border\">";
    $content .= "  <thead><tr><th>#</th><th>Description</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td><a href=\"index.php/genes/\">" . $summary["num_genes"] . "</a></td><td>Genes</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/conditions/\">" . $summary["num_conditions"] . "</a></td><td>Conditions</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/corems/\">" . $summary["num_corems"] . "</a></td><td>Corems</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/biclusters/\">" . $summary["num_biclusters"] . "</a></td><td>Biclusters</td></tr>";
    $content .= "    <tr><td>" . $summary["num_gres"] . "</td><td>GREs</td></tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#summary').DataTable({";
    $content .= "      'paging': false,";
    $content .= "      'info': false";
    $content .= "      'searching': false";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function corems_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $corems_json = file_get_contents($source_url . "/api/v1.0.0/corems");
    $corems = json_decode($corems_json, true)["corems"];

    if ($content == null) {
        $content = '';
    }

    $content .= "<table id=\"corems\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Corem ID</th><th># Genes</th><th># Conditions</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($corems as $c) {
        $content .= "    <tr><td><a href=\"index.php/corem/?corem=" . $c["id"] . "\">" . $c["id"] . "</a></td><td>". $c["num_genes"] . "</td><td>" . $c["num_conds"] . "</td></tr>";
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
    $content = '';
    $content .= "<table id=\"conditions\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Name</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($conditions as $c) {
        $content .= "    <tr><td><a href=\"index.php/condition/?condition=" . $c["id"] . "\">". $c["name"] . "</a></td></tr>";
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

function conditions_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $conds_json = file_get_contents($source_url . "/api/v1.0.0/conditions");
    $conds = json_decode($conds_json, true)["conditions"];

    return conditions_table_html($conds);
}

function corem_conditions_table_shortcode($attr=[], $content=null)
{
    $corem_id = get_query_var('corem');
    $source_url = get_option('source_url', '');
    $conds_json = file_get_contents($source_url . "/api/v1.0.0/corem_conditions/" . $corem_id);
    $conds = json_decode($conds_json, true)["conditions"];

    return conditions_table_html($conds);
}

function genes_table_html($genes)
{
    $content = '';
    $content .= "<table id=\"genes\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Name</th><th>Common Name</th><th>Accession</th><th>Description</th><th>Start</th><th>Stop</th><th>Strand</th><th>Chromosome</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($genes as $g) {
        $content .= "    <tr><td><a href=\"index.php/gene/?gene=" . $g["gene_name"] .  "\">". $g["gene_name"] . "</a></td><td>" . $g["common_name"] . "</td><td><a href=\"https://www.ncbi.nlm.nih.gov/protein/" . $g["accession"] . "\">" . $g["accession"] ."</a></td><td>" . $g["description"] . "</td><td>" . $g["start"] . "</td><td>" . $g["stop"] . "</td><td>"  . $g["strand"] . "</td><td><a href=\"https://www.ncbi.nlm.nih.gov/nuccore/" . $g["chromosome"] . "\">" . $g["chromosome"] . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#genes').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function genes_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/genes");
    $genes = json_decode($genes_json, true)["genes"];

    return genes_table_html($genes);
}

function gene_info_shortcode($attr=[], $content=null)
{
    $gene_name = get_query_var('gene');
    error_log("gene name: " . $gene_name);
    if (!$gene_name) return "(no gene name provided)";
    $source_url = get_option('source_url', '');
    $gene_json = file_get_contents($source_url . "/api/v1.0.0/gene_info/" . $gene_name);
    $gene = json_decode($gene_json, true)["gene"];
    $content = "<div>";
    $content .= "Name: " . $gene["gene_name"] . "<br>";
    $content .= "Common Name: " . $gene["common_name"] . "<br>";
    $content .= "Accession: " . $gene["accession"] . "<br>";
    $content .= "Description: " . $gene["description"] . "<br>";
    $content .= "Chromosome: " . $gene["chromosome"] . "<br>";
    $content .= "Strand: " . $gene["strand"] . "<br>";
    $content .= "Position: " . $gene["start"] . "-" .  $gene["stop"] . "<br>";
    $content .= "</div>";
    return $content;
}

function corem_genes_table_shortcode($attr=[], $content=null)
{
    $corem_id = get_query_var('corem');
    $source_url = get_option('source_url', '');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/corem_genes/" . $corem_id);
    $genes = json_decode($genes_json, true)["genes"];

    return genes_table_html($genes);
}


function condition_name_shortcode($attr=[], $content=null)
{
    $condition_id = get_query_var('condition');
    if (!$condition_id) return "(no condition provided)";
    $source_url = get_option('source_url', '');
    $cond_json = file_get_contents($source_url . "/api/v1.0.0/condition_info/" . $condition_id);
    $cond = json_decode($cond_json, true)["condition"];
    error_log('$condition_id: ' . $condition_id);
    return $cond["name"];
}

function condition_blocks_shortcode($attr=[], $content=null)
{
    $condition_id = get_query_var('condition');
    if (!$condition_id) return "(no condition provided)";
    $source_url = get_option('source_url', '');
    error_log('$condition_id: ' . $condition_id);
    $cond_json = file_get_contents($source_url . "/api/v1.0.0/condition_info/" . $condition_id);
    $cond_blocks = json_decode($cond_json, true)["blocks"];
    $cond_block_names = array_keys($cond_blocks);

    if ($content == null) {
        $content = '';
    }

    $content .= "<div>";
    $block_num = 0;
    foreach ($cond_block_names as $b) {
        $content .= "  <span>" . $b . " <a title=\"click to view conditions\" style=\"text-decoration:none;\" class=\"expand_block_members\" href=\"javascript:void(0)\" id=\"block_" . $block_num . "\">+ </a></span>";
        $content .= "  <div id=\"block_conds_" . $block_num . "\" style=\"font-size: xx-small; display: none\">";
        foreach ($cond_blocks[$b] as $sub_cond) {
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
    $content = '';
    $content .= "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Bicluster ID</th><th># Genes</th><th># Conditions</th><th>Residual</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($clusters as $c) {
        $content .= "    <tr><td><a href=\"index.php/bicluster/?bicluster=" . $c["id"] . "\">" . $c["id"] . "</a></td><td>". $c["num_genes"] . "</td><td>" . $c["num_conditions"] . "</td><td>" . $c["residual"] . "</td></tr>";
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

function biclusters_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $clusters_json = file_get_contents($source_url . "/api/v1.0.0/biclusters");
    $clusters = json_decode($clusters_json, true)["biclusters"];

    return biclusters_table_html($clusters);
}

function gene_biclusters_table_shortcode($attr=[], $content=null)
{
    $gene = get_query_var('gene');
    $source_url = get_option('source_url', '');
    $clusters_json = file_get_contents($source_url . "/api/v1.0.0/gene_biclusters/" . $gene);
    $clusters = json_decode($clusters_json, true)["biclusters"];

    return biclusters_table_html($clusters);
}

function condition_biclusters_table_shortcode($attr=[], $content=null)
{
    $condition_id = get_query_var('condition');
    $source_url = get_option('source_url', '');
    $clusters_json = file_get_contents($source_url . "/api/v1.0.0/condition_biclusters/" . $condition_id);
    $clusters = json_decode($clusters_json, true)["biclusters"];

    return biclusters_table_html($clusters);
}

function bicluster_info_shortcode($attr=[], $content=null)
{
    $bicluster_id = get_query_var('bicluster');
    if (!$bicluster_id) return "(no bicluster id provided)";
    $source_url = get_option('source_url', '');
    $bicluster_json = file_get_contents($source_url . "/api/v1.0.0/bicluster_info/" . $bicluster_id);
    $bicluster = json_decode($bicluster_json, true)["bicluster"];
    $content = "<div>";
    $content .= "ID: " . $bicluster["id"] . "<br>";
    $content .= "# Conditions: " . $bicluster["num_conditions"] . "<br>";
    $content .= "# Genes: " . $bicluster["num_genes"] . "<br>";
    $content .= "Residual: " . $bicluster["residual"] . "<br>";
    $content .= "</div>";
    return $content;
}

function biclusters_add_shortcodes()
{
    add_shortcode('bicluster_genes', 'bicluster_genes_shortcode');
    add_shortcode('bicluster_conditions', 'bicluster_conditions_shortcode');

    add_shortcode('corem_genes_table', 'corem_genes_table_shortcode');
    add_shortcode('corem_conditions_table', 'corem_conditions_table_shortcode');

    add_shortcode('bicluster_motifs', 'bicluster_motifs_shortcode');
    add_shortcode('model_overview', 'model_overview_shortcode');
    add_shortcode('condition_name', 'condition_name_shortcode');
    add_shortcode('condition_blocks', 'condition_blocks_shortcode');
    add_shortcode('gene_info', 'gene_info_shortcode');
    add_shortcode('bicluster_info', 'bicluster_info_shortcode');
    add_shortcode('gene_biclusters_table', 'gene_biclusters_table_shortcode');
    add_shortcode('condition_biclusters_table', 'condition_biclusters_table_shortcode');

    // EGRIN2 specific
    add_shortcode('corems_table', 'corems_table_shortcode');
    add_shortcode('conditions_table', 'conditions_table_shortcode');
    add_shortcode('genes_table', 'genes_table_shortcode');
    add_shortcode('biclusters_table', 'biclusters_table_shortcode');
}

?>