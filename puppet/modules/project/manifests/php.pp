
class project::php {
	
	package { 'php5-cgi':
		require => [Class['project::apt'], Class['project::nginx']],
		ensure => installed,
	}
	
	package { 'php5-cli':
		require => Class['project::apt'],
		ensure => installed,
	}
	
	package { 'php5-memcache':
		require => Package['php5-cgi'],
		ensure => installed,
	}
	
	package { 'php-apc':
		require => Package['php5-cgi'],
		ensure => installed,
	}
	
	package { 'php5-curl':
		require => Package['php5-cgi'],
		ensure => installed,
	}
	
	package { 'php5-dev':
		require => Package['php5-cgi'],
		ensure => installed,
	}
	
	package { "php5-xdebug":
		require => Package["php5-dev"]
	}
	
	package { "php-pear":
		require => Package["php5-dev"]
	}

	package { "spawn-fcgi":
		require => [Package["php5-memcache"], Package["php-apc"], Package["php5-curl"], Package["php5-dev"], Package["php5-xdebug"], Package["php-pear"]],
		ensure => installed,
	}
	
	file { "/usr/bin/php5-spawn":
		require => Package["spawn-fcgi"],
	    owner => root,
	    group => root,
	    mode => 555,
	    source => "/tmp/vagrant-puppet/modules-0/project/templates/php/php5-spawn.sh"
	}
	
		
	exec { "/usr/bin/php5-spawn":
		require => [File["/usr/bin/php5-spawn"]]
	}
	
}
