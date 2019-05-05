<?php
return array(
	'adapter' => '\\Dvelum\\User\\Auth\\Ldap',
	'host' => 'ldaps://ldapserver.local',
	'port' => '636',
	'protocolVersion' => 3,
	'baseDn' => 'dc=company,dc=local',
	'loginAttribute' => 'krbPrincipalName',
	'loginSearchFilter' => 'krbPrincipalName=%l@LOCAL',
	'firstBindDn' => '',
	'firstBindPassword' => '',
	'saveCredentials' => true
);