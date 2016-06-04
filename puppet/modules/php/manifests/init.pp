define php::cookieLifetime(){

  file { "/etc/php5/fpm/conf.d/session.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "session.gc_maxlifetime = ${name}
                  session.cookie_lifetime = ${name}",
    require  => Package["php5-fpm"],
    notify   => Service["php5-fpm"],
  }
}

define php::errorLog(){

  file_line { "error_log":
    path    => "/etc/php5/fpm/php.ini",
    line    => "error_log = ${name}",
    match => "^error_log",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }
}

define php::displayErrors(){

  file_line { "display_errors":
    path    => "/etc/php5/fpm/php.ini",
    line    => "display_errors = ${name}",
    match => "^display_errors",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }
}

define php::errorReporting(){

  file_line { "error_reporting":
    path    => "/etc/php5/fpm/php.ini",
    line    => "error_reporting = ${name}",
    match => "^error_reporting",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }
}

define php::opcache($memory, $revalidateSec){

  file { "/etc/php5/fpm/conf.d/06-opcache.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "opcache.enable=1
          opcache.memory_consumption=${memory}
          opcache.max_accelerated_files=50000
          opcache.revalidate_freq=${revalidateSec}
          opcache.fast_shutdown=1",
    require  => Package["php5-fpm"],
    notify   => Service["php5-fpm"],
  }
}

define php::fpmconfig($servers, $children){

  file_line { "php pm.start_servers":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "pm.start_servers = ${servers}",
    match   => "^pm.start_servers",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }


  file_line { "php pm.max_spare_servers":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "pm.max_spare_servers = ${servers}",
    match   => "^pm.max_spare_servers",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }

  file_line { "php pm.max_children":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "pm.max_spare_servers = ${children}",
    match   => "^pm.max_spare_servers",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }

}

define php::user () {

  file_line { "php user":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "user = ${name}",
    match   => "^user =",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }

  file_line { "php group":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "group = ${name}",
    match   => "^group =",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }

  file_line { "php listen.owner":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "listen.owner = ${name}",
    match   => "^listen.owner =",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }

  file_line { "php listen.group":
    path    => "/etc/php5/fpm/pool.d/www.conf",
    line    => "listen.group = ${name}",
    match   => "^listen.group =",
    require => Package["php5-fpm"],
    notify  => Service['php5-fpm'],
  }

  file { "/var/run/php5-fpm.sock":
    require  => Package["php5-fpm"],
    owner    => $name,
    group    => $name,
  }

}

class php {

  package { "php5-cli":
    ensure  => installed,
    require => Class["apt"],
  }

  package { "php5-fpm":
    ensure  => installed,
    require => Class["apt"],
  }

  package { "php5-mcrypt":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-apcu":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-gmp":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-intl":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-gd":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-mysql":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-curl":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-dev":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-ldap":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-imagick":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  package { "php5-sqlite":
    ensure  => installed,
    require => Class["apt"],
    notify  => Service["php5-fpm"],
  }

  file { "/etc/php5/fpm/conf.d/upload.ini":
    require  => Package["php5-fpm"],
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "upload_max_filesize = 51M
    post_max_size = 52M",
    notify   => Service["php5-fpm"],
  }

  file { "/etc/php5/cli/conf.d/upload.ini":
    require  => Package["php5-cli"],
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "upload_max_filesize = 51M
    post_max_size = 52M",
  }

  file { "/etc/php5/fpm/conf.d/timezone.ini":
    require => Package["php5-fpm"],
    owner   => root,
    group   => root,
    mode    => 644,
    content => "date.timezone = Europe/Berlin",
    notify  => Service["php5-fpm"],
  }

  file { "/etc/php5/cli/conf.d/timezone.ini":
    require => Package["php5-cli"],
    owner   => root,
    group   => root,
    mode    => 644,
    content => "date.timezone = Europe/Berlin"
  }

  file { "/etc/php5/cli/conf.d/apc.ini":
    require => Package["php5-cli"],
    owner   => root,
    group   => root,
    mode    => 644,
    content => "apc.enable_cli = On"
  }

  file { "/etc/php5/fpm/conf.d/opentag.ini":
    owner    => root,
    group    => root,
    mode     => 644,
    content  => "short_open_tag=On",
    require  => Package["php5-fpm"],
    notify   => Service["php5-fpm"],
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
    ],
    notify   => Service["php5-fpm"],
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
    ],
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
      Package["php5-gmp"],
      Package["php5-mysql"],
      Package["php5-mcrypt"],
      Package["php5-gd"],
      Package["php5-dev"],
      Package["php5-curl"],
      Package["php5-imagick"],
      Package["php5-sqlite"],
      Package["php5-ldap"],
    ],
  }
}

