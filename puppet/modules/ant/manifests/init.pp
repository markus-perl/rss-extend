class ant {
  package { "ant":
    ensure  => installed,
    require => Class['apt']
  }

}

