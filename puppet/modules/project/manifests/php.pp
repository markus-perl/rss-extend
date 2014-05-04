
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

    package { "php5-fpm":
    		require => Class['project::apt'],
    }

   file { "/etc/php5/fpm/pool.d/www.conf":
        require => Package["php5-fpm"],
        owner => root,
        group => root,
        mode => 644,
        source => "puppet:///modules/project/php/www.conf"
    }

    service { "php5-fpm":
        subscribe => [
            Package["php5-fpm"],
            Package["php5-cgi"],
            Package["php5-cli"],
            Package["php5-memcache"],
            Package["php5-curl"],
            Package["php5-dev"],
            Package["php5-xdebug"],
            Package["php-pear"],
            File["/etc/php5/fpm/pool.d/www.conf"],
        ],
        ensure => "running",
    }

}
