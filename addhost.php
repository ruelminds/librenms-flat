#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage cli
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Exceptions\HostUnreachableException;

$init_modules = array();
require __DIR__ . '/includes/init.php';

$options = getopt('Pbg:p:f::');

if (isset($options['g']) && $options['g'] >= 0) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
    $poller_group = $options['g'];
} elseif ($config['distributed_poller'] === true) {
    $poller_group = $config['distributed_poller_group'];
} else {
    $poller_group = 0;
}

if (isset($options['f']) && $options['f'] == 0) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
    $force_add = true;
} else {
    $force_add = false;
}

$port_assoc_mode = $config['default_port_association_mode'];
$valid_assoc_modes = get_port_assoc_modes();
if (isset($options['p'])) {
    $port_assoc_mode = $options['p'];
    if (! in_array($port_assoc_mode, $valid_assoc_modes)) {
        echo "Invalid port association mode '" . $port_assoc_mode . "'\n";
        echo 'Valid modes: ' . join(', ', $valid_assoc_modes) . "\n";
        exit(1);
    }

    $cmd = array_shift($argv);
    array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
}

if (isset($options['P'])) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
}

if (isset($options['b'])) {
    $cmd = array_shift($argv);
    array_shift($argv);
    array_unshift($argv, $cmd);
}

if (!empty($argv[1])) {
    $host      = strtolower($argv[1]);
    $community = $argv[2];
    $snmpver   = strtolower($argv[3]);

    $port      = 161;
    $transport = 'udp';

    $additional = array();
    if (isset($options['b'])) {
        $additional = array(
            'ping_fallback' => 1,
        );
    }
    if (isset($options['P'])) {
        $community = '';
        $snmpver   = 'v2c';
        $additional = array(
            'snmp_disable' => 1,
            'os'           => $argv[2] ? mres($argv[2]) : "ping",
            'hardware'     => $argv[3] ? mres($argv[3]) : '',
        );
    } elseif ($snmpver === 'v3') {
        $seclevel = $community;

        // These values are the same as in defaults.inc.php
        $v3 = array(
            'authlevel'  => 'noAuthNoPriv',
            'authname'   => 'root',
            'authpass'   => '',
            'authalgo'   => 'MD5',
            'cryptopass' => '',
            'cryptoalgo' => 'AES',
        );

        // v3
        if ($seclevel === 'nanp' or $seclevel === 'any' or $seclevel === 'noAuthNoPriv') {
            $v3['authlevel'] = 'noAuthNoPriv';
            $v3args          = array_slice($argv, 4);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $port = $arg;
                } elseif (preg_match('/^('.implode('|', $config['snmp']['transports']).')$/', $arg)) {
                    $transport = $arg;
                } else {
                    // should add a sanity check of chars allowed in user
                    $user = $arg;
                }
            }

            if ($seclevel === 'nanp') {
                array_unshift($config['snmp']['v3'], $v3);
            }
        } elseif ($seclevel === 'anp' or $seclevel === 'authNoPriv') {
            $v3['authlevel'] = 'authNoPriv';
            $v3args          = array_slice($argv, 4);
            $v3['authname']  = array_shift($v3args);
            $v3['authpass']  = array_shift($v3args);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $port = $arg;
                } elseif (preg_match('/^('.implode('|', $config['snmp']['transports']).')$/i', $arg)) {
                    $transport = $arg;
                } elseif (preg_match('/^(sha|md5)$/i', $arg)) {
                    $v3['authalgo'] = $arg;
                } else {
                    echo 'Invalid argument: '.$arg."\n";
                    exit(1);
                }
            }

            array_unshift($config['snmp']['v3'], $v3);
        } elseif ($seclevel === 'ap' or $seclevel === 'authPriv') {
            $v3['authlevel']  = 'authPriv';
            $v3args           = array_slice($argv, 4);
            $v3['authname']   = array_shift($v3args);
            $v3['authpass']   = array_shift($v3args);
            $v3['cryptopass'] = array_shift($v3args);

            while ($arg = array_shift($v3args)) {
                // parse all remaining args
                if (is_numeric($arg)) {
                    $port = $arg;
                } elseif (preg_match('/^('.implode('|', $config['snmp']['transports']).')$/i', $arg)) {
                    $transport = $arg;
                } elseif (preg_match('/^(sha|md5)$/i', $arg)) {
                    $v3['authalgo'] = $arg;
                } elseif (preg_match('/^(aes|des)$/i', $arg)) {
                    $v3['cryptoalgo'] = $arg;
                } else {
                    echo 'Invalid argument: '.$arg."\n";
                    exit(1);
                }
            }//end while

            array_unshift($config['snmp']['v3'], $v3);
        }
    } else {
        // v2c or v1
        $v2args = array_slice($argv, 2);

        while ($arg = array_shift($v2args)) {
            // parse all remaining args
            if (is_numeric($arg)) {
                $port = $arg;
            } elseif (preg_match('/('.implode('|', $config['snmp']['transports']).')/i', $arg)) {
                $transport = $arg;
            } elseif (preg_match('/^(v1|v2c)$/i', $arg)) {
                $snmpver = $arg;
            }
        }

        if ($community) {
            array_unshift($config['snmp']['community'], $community);
        }
    }//end if

    try {
        $device_id = addHost($host, $snmpver, $port, $transport, $poller_group, $force_add, $port_assoc_mode, $additional);
        $device = device_by_id_cache($device_id);
        echo "Added device {$device['hostname']} ($device_id)\n";
        exit(0);
    } catch (HostUnreachableException $e) {
        print_error($e->getMessage());
        foreach ($e->getReasons() as $reason) {
            echo "  $reason\n";
        }
        exit(2);
    } catch (Exception $e) {
        print_error($e->getMessage());
        exit(3);
    }
} else {
    c_echo(
        "\n".$config['project_name_version'].' Add Host Tool

    Usage (SNMPv1/2c)    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname%n> [community] [v1|v2c] [port] ['.implode('|', $config['snmp']['transports']).']
    Usage (SNMPv3)       :
        Config Defaults  : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname%n> any v3 [user] [port] ['.implode('|', $config['snmp']['transports']).']
        No Auth, No Priv : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname%n> nanp v3 [user] [port] ['.implode('|', $config['snmp']['transports']).']
        Auth, No Priv    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname%n> anp v3 <user> <password> [md5|sha] [port] ['.implode('|', $config['snmp']['transports']).']
        Auth,    Priv    : ./addhost.php [-g <poller group>] [-f] [-b] [-p <port assoc mode>] <%Whostname%n> ap v3 <user> <password> <enckey> [md5|sha] [aes|dsa] [port] ['.implode('|', $config['snmp']['transports']).']
    Usage (ICMP only)    : ./addhost.php [-g <poller group>] [-f] -P <%Whostname%n> [os] [hardware]

    -g <poller group> allows you to add a device to be pinned to a specific poller when using distributed polling. X can be any number associated with a poller group
    -f forces the device to be added by skipping the icmp and snmp check against the host.
    -p <port assoc mode> allow you to set a port association mode for this device. By default ports are associated by \'ifIndex\'.
        For Linux/Unix based devices \'ifName\' or \'ifDescr\' might be useful for a stable iface mapping.
        The default for this installation is \'' . $config['default_port_association_mode'] . '\'
        Valid port assoc modes are: ' . join(', ', $valid_assoc_modes) . '
    -b Add the host with SNMP if it replies to it, otherwise only ICMP.
    -P Add the host with only ICMP, no SNMP or OS discovery.

    %rRemember to run discovery for the host afterwards.%n
'
    );
    exit(1);
}
