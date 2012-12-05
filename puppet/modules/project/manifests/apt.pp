
class project::apt {

	file { "/etc/apt/sources.list.d/php.list":
	    owner => root,
	    group => root,
	    mode => 644,
	    content => "deb http://ppa.launchpad.net/ondrej/php5/ubuntu lucid main"
	}

	exec { "ppa key":
		command => "/usr/bin/apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C",
	}

	exec { "/usr/bin/apt-get update":
		require =>[Exec["ppa key"], File["/etc/apt/sources.list.d/php.list"]],
	}
	
	
	
	
}
