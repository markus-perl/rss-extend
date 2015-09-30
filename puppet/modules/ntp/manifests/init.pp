class ntp {
  package { "ntp":
    ensure  => latest,
    require => Class['apt']
  }

  file { '/etc/timezone':
    ensure  => present,
    content => "Europe/Berlin\n",
  }

  exec { 'reconfigure-tzdata':
    user      => root,
    group     => root,
    command   => '/usr/sbin/dpkg-reconfigure --frontend noninteractive tzdata',
    subscribe => File['/etc/timezone'],
    refreshonly => true,
  }

}

