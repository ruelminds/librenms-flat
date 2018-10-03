<?php

use LibreNMS\RRD\RrdDefinition;

if (!starts_with($device['os'], array('Snom', 'asa'))) {
    echo ' UDP';

    // These are at the start of large trees that we don't want to walk the entirety of, so we snmpget_multi them
    $oids = array(
        'udpInDatagrams',
        'udpOutDatagrams',
        'udpInErrors',
        'udpNoPorts',
    );

    $rrd_def = new RrdDefinition();
    $snmpstring = '';
    foreach ($oids as $oid) {
        $rrd_def->addDataset($oid, 'COUNTER', null, 1000000); // Limit to 1MPPS?
        $snmpstring .= ' UDP-MIB::'.$oid.'.0';
    }

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'UDP-MIB');

    $fields = array();
    foreach ($oids as $oid) {
        if (is_numeric($data[0][$oid])) {
            $value = $data[0][$oid];
        } else {
            $value = 'U';
        }
        $fields[$oid] = $value;
    }

    if (isset($data[0]['udpInDatagrams']) && isset($data[0]['udpOutDatagrams'])) {
        $tags = compact('rrd_def');
        data_update($device, 'netstats-udp', $tags, $fields);

        $graphs['netstat_udp'] = true;
    }
}//end if

unset($oids, $data, $rrd_def, $fields, $tags, $snmpstring);
