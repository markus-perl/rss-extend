
class project::nginx {

	file { "/etc/nginx/fastcgi.conf":
	    owner => root,
	    group => root,
	    mode => 644,
	    source => "puppet:///modules/project/nginx/fastcgi.conf"
	}

	file { "/etc/nginx/sites-enabled/default":
		require => File['/etc/nginx/fastcgi.conf'],
	    owner => root,
	    group => root,
	    mode => 644,
	    source => "puppet:///modules/project/nginx/sites-enabled/default",
	}

	exec { "/etc/init.d/nginx restart":
		require => File['/etc/nginx/sites-enabled/default'],
	}

}
