define locale::enable (
) {

  file_line { "locale ${name} utf8":
    path    => "/etc/locale.gen",
    line    => "${name}.UTF-8 UTF-8",
    match   => "$name.UTF-8 UTF-8",
    notify  =>  Exec["locale-gen"],
  }

  file_line { "locale ${name} iso":
    path     => "/etc/locale.gen",
    line     => "${name} ISO-8859-1",
    match    => "${name} ISO-8859-1",
    notify  =>  Exec["locale-gen"],
  }

  exec { "locale-gen":
    command     => "/usr/sbin/locale-gen",
    refreshonly => true,
  }

}

