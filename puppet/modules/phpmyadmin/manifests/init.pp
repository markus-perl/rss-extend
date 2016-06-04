class phpmyadmin ($mySQLPassword = "", $htaccess = false, $user = "www-data") {

  $tmpDir = "/tmp"
  $zip = "phpMyAdmin.zip"
  $version = "4.4.14.1"
  $folder = "phpMyAdmin-${version}-all-languages"
  $downloadUrl = "https://files.phpmyadmin.net/phpMyAdmin/${version}/phpMyAdmin-${version}-all-languages.zip"

  package { "unzip":
    ensure  => installed,
    require => Class["apt"],
  }

  package { "apache2-utils":
    ensure  => installed,
    require => Class["apt"],
  }

  exec { "project::phpmyadmin download":
    require => [Class["nginx"], Class["php"]],
    command => "/usr/bin/wget --no-verbose -q -O ${zip} '${ downloadUrl }'",
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

  file { "/var/www/phpmyadmin":
    ensure  => directory,
    owner   => $user,
    group   => $user,
    mode    => 755,
    recurse => true,
    require => Exec["project:phpmyadmin mv"],
  }

  file { "/var/www/phpmyadmin/config.inc.php":
    require  => Exec["project:phpmyadmin mv"],
    owner    => www-data,
    group    => www-data,
    mode     => 644,
    content  => template("phpmyadmin/config.inc.php.erb"),
  }

  nginx::site_enabled { "phpmyadmin":
    content  => template("phpmyadmin/nginx/phpmyadmin.erb"),
  }

  if $mySQLPassword == "" {
    $cmd = "/usr/bin/mysql -u root"
  } else {
    $cmd = "/usr/bin/mysql -u root -p${mySQLPassword}"
  }

  if $htaccess == true {
    exec { "/usr/bin/htpasswd -c -b /etc/nginx/phpmyadmin.htpasswd root ${mySQLPassword}":
      require     => [
        File["/var/www/phpmyadmin/config.inc.php"],
        Package["apache2-utils"]
      ],
    }
  }

  exec { "${$cmd} < /var/www/phpmyadmin/sql/create_tables.sql":
    require     => [
      Exec["project::phpmyadmin unzip"],
      Class["::mysql::server"]
    ],
    subscribe   => Exec["project:phpmyadmin mv"],
    refreshonly => true
  }
}
