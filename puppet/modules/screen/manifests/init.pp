class screen {
  package { "screen":
    ensure  => installed,
    require => Class['apt']
  }

}

