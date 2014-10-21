class phpmyadmin ($mySQLPassword = '') {

  $tmpDir = "/tmp"
  $zip = "phpMyAdmin.zip"
  $version = "4.2.9"
  $folder = "phpMyAdmin-${version}-all-languages"
  $downloadUrl = "http://downloads.sourceforge.net/project/phpmyadmin/phpMyAdmin/${version}/phpMyAdmin-${version}-all-languages.zip?r=&ts=1322488265&use_mirror=netcologne"

  package { 'unzip':
    ensure  => installed,
    require => Class['apt'],
  }

  exec { "project::phpmyadmin download":
    require => [Class['nginx'], Class['php']],
    command => "/usr/bin/wget --no-verbose -q -O ${zip} '${downloadUrl}'",
    cwd     => $tmpDir,
    unless  => "/usr/bin/test -f ${zip}",
    timeout => 3600,
  }

  exec { "project::phpmyadmin unzip":
    command   => "/usr/bin/unzip ${tmpDir}/${zip}",
    cwd       => $tmpDir,
    require   => [Exec["project::phpmyadmin download"], Class["nginx"]],
    unless    => "/usr/bin/test -d /var/www/phpmyadmin",
    logoutput => true
  }

  exec { "project:phpmyadmin mv":
    command => "/bin/mv ${folder} /var/www/phpmyadmin",
    cwd     => $tmpDir,
    require => Exec["project::phpmyadmin unzip"],
    unless  => "/usr/bin/test -d /var/www/phpmyadmin"
  }

  file { "/var/www/phpmyadmin/config.inc.php":
    require => Exec['project:phpmyadmin mv'],
    owner   => www-data,
    group   => www-data,
    mode    => 644,
    source  => "puppet:///modules/phpmyadmin/config.inc.php",
  }

  nginx::site_enabled { 'phpmyadmin':
    source  => "puppet:///modules/phpmyadmin/nginx/phpmyadmin",
  }

  if $mySQLPassword == '' {
    $cmd = "/usr/bin/mysql -u root"
  } else {
    $cmd = "/usr/bin/mysql -u root -p${mySQLPassword}"
  }

  exec { "${$cmd} < /var/www/phpmyadmin/examples/create_tables.sql":
    require     => [
      Exec["project::phpmyadmin unzip"],
      Class["::mysql::server"]
    ],
    subscribe   => Exec["project:phpmyadmin mv"],
    refreshonly => true
  }
}
