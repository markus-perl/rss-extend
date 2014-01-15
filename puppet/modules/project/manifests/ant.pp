
class project::ant {

	file { "/etc/profile.d/java.sh":
	    owner => root,
	    group => root,
	    mode => 555,
	    content => '#!/bin/sh
	    export JAVA_HOME=/usr/lib/jvm/java-7-oracle'
	}

	package { 'ant':
		require => Class['project::apt'],
		ensure => installed,
	}

}
