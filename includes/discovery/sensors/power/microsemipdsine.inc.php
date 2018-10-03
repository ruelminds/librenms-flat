<?php
/**
 * microsemipdsine.inc.php
 *
 * LibreNMS power sensor discovery module for Microsemi PoE Switches
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// Power consumption per port
$oids = snmpwalk_cache_oid_num($device, '1.3.6.1.4.1.7428.1.2.1.1.1.3.1', array());

foreach ($oids as $oid => $data) {
    $current_id = substr($oid, strrpos($oid, '.') + 1);
    $current_oid = ".$oid";

    $descr = 'Power Consumption Port ' . $current_id;
    $divisor = 1;
    $type = 'microsemipdsine';
    $current = current($data) / $divisor;
    $index = '1.1.3.1.' . $current_id;

    discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
