<?php

echo getLogoTag($device, 'device-icon-header pull-left') .'
<div class="pull-left" style="margin-top: 5px;">';

$host_id = get_vm_parent_id($device);
if ($host_id > 0) {
    echo '
<a href="'.generate_url(array('page'=>'device','device'=>$host_id)).'"><i class="fa fa-server fa-fw fa-lg"></i></a>';
}

echo '
<span style="font-size: 20px;">'.generate_device_link($device).'</span><br />
'.generate_link($device['location'], array('page' => 'devices', 'location' => $device['location'])) .'
</div>';
//<div class="col-xs-6 col-sm-8 col-md-8">';


if ($device['snmp_disable']) {
    $graphs = $config['os']['ping']['over'];
} elseif (isset($config['os'][$device['os']]['over'])) {
    $graphs = $config['os'][$device['os']]['over'];
} elseif (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over'])) {
    $graphs = $config['os'][$device['os_group']]['over'];
} else {
    $graphs = $config['os']['default']['over'];
}

$graph_array                = array();
$graph_array['height']      = '100';
$graph_array['width']       = '310';
$graph_array['to']          = $config['time']['now'];
$graph_array['device']      = $device['device_id'];
$graph_array['type']        = 'device_bits';
$graph_array['from']        = $config['time']['day'];
$graph_array['legend']      = 'no';
$graph_array['popup_title'] = $descr;

$graph_array['height'] = '45';
$graph_array['width']  = '150';
$graph_array['bg']     = 'FFFFFF00';

if (device_permitted($device['device_id']) || $config['allow_unauth_graphs']) {
    echo '<div class="pull-right">';
    foreach ($graphs as $entry) {
        if ($entry['graph']) {
            $graph_array['type'] = $entry['graph'];

            echo "<div style='float: right; text-align: center; padding: 1px 5px; margin: 0 1px; ' class='rounded-5px'>";
            print_graph_popup($graph_array);
            echo "<div style='font-weight: bold; font-size: 7pt; margin: -3px;'>" . $entry['text'] . '</div>';
            echo '</div>';
        }
    }
    echo '</div>';
}
echo '<br style="clear: both;" />';

unset($graph_array);
