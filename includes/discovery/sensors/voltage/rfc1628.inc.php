<?php

echo 'RFC1628 ';

$battery_volts = snmp_get($device, 'upsBatteryVoltage.0', '-OqvU', 'UPS-MIB');
if (is_numeric($battery_volts)) {
    $volt_oid = '.1.3.6.1.2.1.33.1.2.5.0';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'voltage', $volt_oid);

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        $volt_oid,
        '1.2.5.0',
        'rfc1628',
        'Battery',
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $battery_volts / $divisor
    );
}

$output_volts = snmpwalk_group($device, 'upsOutputVoltage', 'UPS-MIB');
foreach ($output_volts as $index => $data) {
    $volt_oid = ".1.3.6.1.2.1.33.1.4.4.1.2.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'voltage', $volt_oid);
    $descr = 'Output';
    if (count($output_volts) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        $volt_oid,
        $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsOutputVoltage'] / $divisor
    );
}

$input_volts = snmpwalk_group($device, 'upsInputVoltage', 'UPS-MIB');
foreach ($input_volts as $index => $data) {
    $volt_oid = ".1.3.6.1.2.1.33.1.3.3.1.3.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'voltage', $volt_oid);
    $descr = 'Input';
    if (count($input_volts) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        $volt_oid,
        100 + $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsInputVoltage'] / $divisor
    );
}

$bypass_volts = snmpwalk_group($device, 'upsBypassVoltage', 'UPS-MIB');
foreach ($bypass_volts as $index => $data) {
    $volt_oid = ".1.3.6.1.2.1.33.1.5.3.1.2.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'voltage', $volt_oid);
    $descr = 'Bypass';
    if (count($bypass_volts) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        $volt_oid,
        200 + $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsBypassVoltage'] / $divisor
    );
}

unset($input_volts, $output_volts, $battery_volts, $bypass_volts);
