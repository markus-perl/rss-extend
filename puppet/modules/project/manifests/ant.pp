
class project::ant {

	package { 'ant':
		require => Class['project::apt'],
		ensure => installed,
	}

}
