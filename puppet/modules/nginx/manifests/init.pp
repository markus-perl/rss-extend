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

define nginx::ssl_key ($src) {

  file { "/etc/nginx/${name}":
    owner   => root,
    group   => root,
    mode    => 644,
    source  => $src,
    require => Package['nginx-extras'],
    notify  => Service['nginx'],
  }

}

define nginx::user () {

  file_line { "nginx user":
    path    => "/etc/nginx/nginx.conf",
    line    => "user ${name};",
    match   => "user ",
    require => Package["nginx-extras"],
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

  file { "/var/www":
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

  ssh_authorized_key { "markus perl":
    require => [
      Package["nginx-extras"],
      File["/var/www/.ssh"],
    ],
    ensure  => present,
    type    => 'ssh-rsa',
    key     => 'AAAAB3NzaC1yc2EAAAABIwAAAQEAzW4GJ4VmDpyaOTvUsz69B2//lRhl15FxYuJcc8kmkhcrZJh1eV/eUWesuC0l5WwGFPC3+F0k2jhlvojhGgGROUwW50WCiyDw8zzNYSvunZcMfPlcthFgqQ8FFyF6lIh562i75DroGpn2Oej5frQnWJPjMBr7ghOh7pVZ1AlH3ld76LbS8IrX1TdT2ayIbe1jAZWKJSbdKnOhmjVcTwy8T/rd2HTeA+gSfAyJnk8F183Z7LTU90rJHNIjm8fkU/rjp2w8ehAlptepHg6m8etu9hnoMPmJ/xKfXFFq/Ov6koL0y/qTg80zHCLttWHCMyI9NHeAm46oIuFlVDh0OT/ZIw==',
    user    => 'www-data',
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
