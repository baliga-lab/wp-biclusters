<?php

/**
 * AJAX backend.
 */

function genes_dt_callback() {
    header("Content-type: application/json");
    $draw = intval($_GET['draw']);  // integer
    $start = $_GET['start'];  // integer
    $length = $_GET['length'];  // integer

    $source_url = get_option('source_url', '');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/genes?start=" . $start . "&length=" . $length);
    $genes = json_decode($genes_json)->genes;

    // turn gene_name, accession and chromosome into links before sending them to DataTables
    foreach ($genes as $g) {
        $chrom = $g->chromosome;
        $acc = $g->accession;
        $name = $g->gene_name;

        $g->gene_name = "<a href=\"index.php/gene/?gene=" . $name . "\">" . $name . "</a>";
        $g->chromosome = "<a href=\"https://www.ncbi.nlm.nih.gov/nuccore/" . $chrom . "\">" . $chrom . "</a>";
        // TODO: Tuberculist: http://tuberculist.epfl.ch/quicksearch.php?gene+name=Rv0005
        // TODO: PATRIC
        $g->links = "<a href=\"https://www.ncbi.nlm.nih.gov/protein/" . $acc . "\">" . NCBI . "</a><br>" .
            "<a href=\"http://tuberculist.epfl.ch/quicksearch.php?gene+name=" . $name . "\">" . Tuberculist . "</a>";
    }
    $data = json_encode($genes);
    $summary_json = file_get_contents($source_url . "/api/v1.0.0/summary");
    $summary = json_decode($summary_json);

    error_log("start: " . $start . " length: " . $length);
    $records_total = $summary->num_genes;

    $doc = <<<EOT
{
  "draw": $draw,
  "recordsTotal": $records_total,
  "recordsFiltered": $records_total,
  "data": $data
}
EOT;
    echo $doc;
    wp_die();
}

function biclusters_dt_callback() {
    header("Content-type: application/json");
    $draw = intval($_GET['draw']);  // integer
    $start = $_GET['start'];  // integer
    $length = $_GET['length'];  // integer

    $source_url = get_option('source_url', '');
    $biclusters_json = file_get_contents($source_url . "/api/v1.0.0/biclusters?start=" . $start . "&length=" . $length);
    $biclusters = json_decode($biclusters_json)->biclusters;

    // turn gene_name, accession and chromosome into links before sending them to DataTables
    foreach ($biclusters as $b) {
        $id = $b->id;
        $b->id = "<a href=\"index.php/bicluster/?bicluster=" . $id . "\">" . $id . "</a>";
    }
    $data = json_encode($biclusters);
    $summary_json = file_get_contents($source_url . "/api/v1.0.0/summary");
    $summary = json_decode($summary_json);

    error_log("start: " . $start . " length: " . $length);
    $records_total = $summary->num_biclusters;

    $doc = <<<EOT
{
  "draw": $draw,
  "recordsTotal": $records_total,
  "recordsFiltered": $records_total,
  "data": $data
}
EOT;
    echo $doc;
    wp_die();
}

function corem_coexps_dt_callback() {
    header("Content-type: application/json");
    $corem = $_GET['corem'];  // integer

    $source_url = get_option('source_url', '');
    $exps_json = file_get_contents($source_url . "/api/v1.0.0/corem_expressions/" . $corem);
    $exps = json_decode($exps_json);
    $conditions = json_encode($exps->conditions);
    $expdata = array();
    foreach ($exps->expressions as $gene => $values) {
        $expdata []= (object) array('name' => $gene, 'data' => $values);
    }
    $data = json_encode($expdata);

    $doc = <<<EOT
{
  "conditions": $conditions,
  "expressions": $data
}
EOT;
    echo $doc;
    wp_die();
}

function biclusters_datatables_source_init()
{
    // a hook Javascript to anchor our AJAX call
    wp_enqueue_script('ajax_dt', plugins_url('js/ajax_dt.js', __FILE__), array('jquery'));
    wp_localize_script('ajax_dt', 'ajax_dt', array('ajax_url' => admin_url('admin-ajax.php')), '1.0', true);

    // We need callbacks for both non-privileged and privileged users
    add_action('wp_ajax_nopriv_genes_dt', 'genes_dt_callback');
    add_action('wp_ajax_genes_dt', 'genes_dt_callback');
    add_action('wp_ajax_nopriv_biclusters_dt', 'biclusters_dt_callback');
    add_action('wp_ajax_biclusters_dt', 'biclusters_dt_callback');
    add_action('wp_ajax_nopriv_corem_coexps_dt', 'corem_coexps_dt_callback');
    add_action('wp_ajax_corem_coexps_dt', 'corem_coexps_dt_callback');
}

?>