class apt {

  define add_key() {
    exec { "apt key ${name}":
      subscribe => File["/etc/apt/keys"],
      command     => "/usr/bin/apt-key add /etc/apt/keys/${name}",
      notify      => Exec["/usr/bin/apt-get update"],
      refreshonly => true,
    }

  }

  if $operatingsystemrelease =~ /^7./ {
    notice('debian 7.x found!')
    $version = 'wheezy'
  }

  if $operatingsystemrelease =~ /^8./ {
    notice('debian 8.x found!')
    $version = 'jessie'
  }

  if $operatingsystemrelease =~ /^7./ {
    file { "/etc/apt/sources.list.d/dotdeb.list":
      owner   => root,
      group   => root,
      mode    => 644,
      content => "deb http://packages.dotdeb.org $version all
    deb http://packages.dotdeb.org $version-php55 all"
    }
  } else {
    file { "/etc/apt/sources.list.d/dotdeb.list":
      ensure => absent
    }
  }

  file { "/etc/apt/sources.list.d/backports.list":
    owner   => root,
    group   => root,
    mode    => 644,
    content => "deb http://ftp.debian.org/debian/ $version-backports main non-free contrib"
  }

  file { "/etc/apt/sources.list.d/neo4j.list":
    owner   => root,
    group   => root,
    mode    => 644,
    content => "deb http://debian.neo4j.org/repo stable/"
  }

  if $operatingsystemrelease =~ /^7./ {
    file { "/etc/apt/sources.list.d/percona.list":
      owner   => root,
      group   => root,
      mode    => 644,
      content => "deb http://repo.percona.com/apt $version main"
    }
  } else {
    file { "/etc/apt/sources.list.d/percona.list":
      ensure => absent
    }
  }

  file { "/etc/apt/keys":
    ensure  => directory,
    recurse => true,
    owner   => root,
    group   => root,
    source  => "puppet:///modules/apt/keys",
  }

  add_key{ "dotdeb.gpg": }
  add_key{ "gluster.gpg": }
  add_key{ "launchpad.gpg": }
  add_key{ "mongodb.gpg": }
  add_key{ "percona.gpg": }
  add_key{ "neo4j.gpg": }

  file { "/usr/bin/apt-required":
    owner  => root,
    group  => root,
    mode   => 555,
    source => "puppet:///modules/apt/apt-required.php"
  }

  exec { "/usr/bin/apt-get update":
    require => [
      File["/usr/bin/apt-required"],
      File["/etc/apt/sources.list.d/dotdeb.list"],
      File["/etc/apt/sources.list.d/percona.list"],
      File["/etc/apt/sources.list.d/backports.list"],
    ],
    unless  => "/usr/bin/apt-required",
  }

  Exec["/usr/bin/apt-get update"] -> Package <| |>

}

