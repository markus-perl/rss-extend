
class project::xhprof {

  file { "/usr/lib/php5/20121212/xhprof.so":
    require => Class['project::php'],
    owner => root,
    group => root,
    mode => 644,
    source => "puppet:///modules/project/xhprof/xhprof.so"
  }

  file { "/home/vagrant/xhprof.tar.gz":
    require => Package["php5-cgi"],
    owner => root,
    group => root,
    mode => 644,
    source => "puppet:///modules/project/xhprof/xhprof.tar.gz"
  }

  file { '/var/www':
    ensure => directory,
  }

  exec { "project::php xhprof extract":
    command => "/bin/tar -xvf /home/vagrant/xhprof.tar.gz",
    cwd => "/var/www",
    require => [
    File["/var/www"],
    File["/usr/lib/php5/20121212/xhprof.so"],
    File["/home/vagrant/xhprof.tar.gz"]
    ],
    unless => "/usr/bin/test -d /var/www/xhprof"
  }

  package { "graphviz":
    require => Exec["project::php xhprof extract"],
    ensure => latest,
  }

  file { "/usr/bin/xhprof":
    require => Exec["project::php xhprof extract"],
    owner => root,
    group => root,
    mode => 555,
    source => "puppet:///modules/project/xhprof/xhprof.php"
  }

  file { "/etc/nginx/sites-enabled/xhprof":
    require => File["/usr/bin/xhprof"],
    owner => root,
    group => root,
    mode => 644,
    source => "puppet:///modules/project/xhprof/xhprof",
  }

}
