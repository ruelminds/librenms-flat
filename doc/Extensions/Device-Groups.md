source: Extensions/Device-Groups.md

LibreNMS supports grouping your devices together in much the same way as you can configure alerts. This document will hopefully help you get started.

### Patterns

Patterns work in the same way as Entities within the alerting system, the format of the pattern is based on the MySQL structure your data is in. Such
as __tablename.columnname__. If you already know the entity you want, you can browse around inside MySQL using `show tables` and `desc <tablename>`.

As a working example and a common question, let's assume you want to group devices by hostname. If your hostname format is dcX.[devicetype].example.com. You would use the pattern
`devices.hostname`. Select the condition which in this case would be `Like` and then enter `dc1\..*\.example.com`. This would then match dc1.sw01.example.com, dc1.rtr01.example.com but not
 dc2.sw01.example.com.

#### Wildcards

As with alerts, the `Like` operation allows MySQL Regular expressions.

A list of common entities is maintained in our [Alerting docs](/Alerting/Entities/).

### Conditions

Please see our [Alerting docs](/Alerting/Rules/#syntax) for explanations.

### Connection

If you only want to group based on one pattern then select And. If however you want to build a group based on multiple patterns then you can build a SQL like
query using And / Or.

As an example, we want to base our group on the devices hostname AND it's type. Use the pattern as before, `devices.hostname`, select 
the condition which in this case would be `Like` and then enter `dc1.@.example.com` then click And. Now enter `devices.type` in the pattern, select `Equals` 
and enter `firewall`. This would then match dc1.fw01.example.com but not dc1.sw01.example.com as that is a network type.

![Device Groups](/img/device_groups.png)

You can now select this group from the Devices -> All Devices link in the navigation at the top. You can also use the group to map alert rules to by creating an alert mapping
`Overview -> Alerts -> Rule Mapping`.
