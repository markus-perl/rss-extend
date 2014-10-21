
define nginx::site_enabled (
  $ensure   = 'file',
  $content  = undef,
  $source   = undef
) {

  if $content and $source {
    fail('You may not supply both content and source parameters to httpd::conf::file')
  } elsif $content == undef and $source == undef {
    fail('You must supply either the content or source parameter to httpd::conf::file')
  }
  file { "/etc/nginx/sites-enabled/${name}":
    ensure  => $ensure,
    owner   => 'root',
    group   => 'root',
    mode    => '0640',
    content => $content,
    source  => $source,
    require => Package['nginx-extras'],
    notify  => Service['nginx'],
  }
}

class nginx {

  package { "nginx":
    ensure => purged,
  }

  package { "nginx-extras":
    ensure  => installed,
    require => Class['apt']
  }

  file { "/var/www/":
    ensure  => directory,
    owner   => www-data,
    group   => www-data,
    mode    => 755,
    require => Package["nginx-extras"]
  }

  file { "/etc/nginx/fastcgi_params":
    owner   => root,
    group   => root,
    mode    => 644,
    source  => "puppet:///modules/nginx/fastcgi_params",
    require => Package["nginx-extras"]
  }

  file_line { "server_names_hash_bucket_size":
    path    => "/etc/nginx/nginx.conf",
    line    => "server_names_hash_bucket_size 64;",
    match   => "server_names_hash_bucket_size",
    require => Package["nginx-extras"]
  }

  file { "/var/www/.ssh":
    ensure  => directory,
    owner   => www-data,
    group   => www-data,
    mode    => 755,
    require => File["/var/www"]
  }

  file { "/var/www/.ssh/authorized_keys":
    owner   => www-data,
    group   => www-data,
    mode    => 600,
    source  => "puppet:///modules/nginx/authorized_keys",
    require => File[ "/var/www/.ssh"]
  }

  service { "nginx":
    subscribe => [
      File["/etc/nginx/fastcgi_params"],
      File_Line["server_names_hash_bucket_size"]
    ],
    ensure    => "running",
    require   => Package["nginx-extras"]
  }

}
