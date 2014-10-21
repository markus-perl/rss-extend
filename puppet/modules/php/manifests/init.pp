class php {

  package { "php5-cli":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-fpm":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-mcrypt":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-apcu":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-intl":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-gd":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-mysql":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-curl":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-dev":
    ensure  => installed,
    require => Class['apt'],
  }

  package { "php5-imagick":
    ensure  => installed,
    require => Class['apt'],
  }

  file { "/etc/php5/fpm/conf.d/upload.ini":
    require  => Package["php5-fpm"],
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "upload_max_filesize = 50M
    post_max_size = 50M"
  }

  file { "/etc/php5/cli/conf.d/upload.ini":
    require  => Package["php5-cli"],
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "upload_max_filesize = 50M
    post_max_size = 50M"
  }

  file { "/etc/php5/fpm/conf.d/session.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "session.gc_maxlifetime = 86400
                  session.cookie_lifetime = 0",
    require  => Package["php5-fpm"]
  }

  file { "/etc/php5/cli/conf.d/session.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "session.gc_maxlifetime = 86400
                  session.cookie_lifetime = 0",
    require  => Package["php5-cli"]
  }

  file { "/etc/php5/fpm/conf.d/timezone.ini":
    require => Package["php5-fpm"],
    owner   => root,
    group   => root,
    mode    => 644,
    content => "date.timezone = Europe/Berlin"
  }

  file { "/etc/php5/cli/conf.d/timezone.ini":
    require => Package["php5-cli"],
    owner   => root,
    group   => root,
    mode    => 644,
    content => "date.timezone = Europe/Berlin"
  }

  file { "/etc/php5/fpm/conf.d/opentag.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "short_open_tag=On",
    require  => Package["php5-fpm"]
  }

  file { "/etc/php5/cli/conf.d/opentag.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "short_open_tag = On",
    require  => Package["php5-cli"]
  }


  file { "/etc/php5/fpm/conf.d/error.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "error_log = /var/log/php/error.log
    log_errors_max_len = 2048",
    require  => [
      Package["php5-fpm"],
      File["/var/log/php"],
    ]
  }

  file { "/etc/php5/cli/conf.d/error.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "error_log = /var/log/php/error.log
    log_errors_max_len = 2048",
    require  => [
      Package["php5-cli"],
      File["/var/log/php"],
    ]
  }

  file { "/var/log/php":
    ensure  => "directory",
    owner   => "www-data",
    group   => "www-data",
    mode    => 750,
    require => [
      Package["php5-cli"],
      Package["php5-fpm"],
    ]
  }

  service { "php5-fpm":
    ensure    => "running",
    require   => [
      Package["php5-cli"],
      Package["php5-fpm"],
      Package["php5-apcu"],
      Package["php5-intl"],
      Package["php5-gd"],
      Package["php5-mysql"],
      Package["php5-mcrypt"],
      Package["php5-gd"],
      Package["php5-dev"],
      Package["php5-curl"],
      Package["php5-imagick"],
    ],
    subscribe => [
      File["/etc/php5/cli/conf.d/upload.ini"],
      File["/etc/php5/fpm/conf.d/upload.ini"],
      File["/etc/php5/fpm/conf.d/opentag.ini"],
      File["/etc/php5/cli/conf.d/opentag.ini"],
      File["/etc/php5/fpm/conf.d/session.ini"],
      File["/etc/php5/cli/conf.d/session.ini"],
      File["/etc/php5/fpm/conf.d/timezone.ini"],
      File["/etc/php5/cli/conf.d/timezone.ini"],
      File["/etc/php5/fpm/conf.d/error.ini"],
      File["/etc/php5/cli/conf.d/error.ini"],
    ],
  }
}

