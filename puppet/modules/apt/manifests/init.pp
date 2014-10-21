class apt {
  $version = 'wheezy'

  file { "/etc/apt/sources.list.d/dotdeb.list":
    owner   => root,
    group   => root,
    mode    => 644,
    content => "deb http://packages.dotdeb.org $version all
    deb http://packages.dotdeb.org $version-php55 all"
  }

  file { "/etc/apt/sources.list.d/backports.list":
    owner   => root,
    group   => root,
    mode    => 644,
    content => "deb http://ftp.debian.org/debian/ $version-backports main non-free contrib"
  }

  file { "/etc/apt/sources.list.d/percona.list":
    owner   => root,
    group   => root,
    mode    => 644,
    content => "deb http://repo.percona.com/apt $version main"
  }

  file { "/etc/apt/dotdeb.gpg":
    owner   => root,
    group   => root,
    mode    => 644,
    source  => "puppet:///modules/apt/dotdeb.gpg",
  }

  file { "/etc/apt/percona.gpg":
    owner   => root,
    group   => root,
    mode    => 644,
    source  => "puppet:///modules/apt/percona.gpg",
  }

  exec { "dotdeb key":
    command     => "/usr/bin/apt-key add /etc/apt/dotdeb.gpg",
    require     => File["/etc/apt/dotdeb.gpg"],
    subscribe   => File["/etc/apt/sources.list.d/dotdeb.list"],
    refreshonly => true,
  }

  exec { "percona key":
    command     => "/usr/bin/apt-key add /etc/apt/percona.gpg",
    require     => File["/etc/apt/percona.gpg"],
    subscribe   => File["/etc/apt/sources.list.d/percona.list"],
    refreshonly => true,
  }

  file { "/usr/bin/apt-required":
    owner  => root,
    group  => root,
    mode   => 555,
    source => "puppet:///modules/apt/apt-required.php"
  }

  exec { "/usr/bin/apt-get update":
    require => [
      File["/usr/bin/apt-required"],
      Exec["dotdeb key"],
      Exec["percona key"],
      File["/etc/apt/sources.list.d/dotdeb.list"],
      File["/etc/apt/sources.list.d/percona.list"],
      File["/etc/apt/sources.list.d/backports.list"],
    ],
    unless  => "/usr/bin/apt-required",
  }

  Exec["/usr/bin/apt-get update"] -> Package <| |>

}

