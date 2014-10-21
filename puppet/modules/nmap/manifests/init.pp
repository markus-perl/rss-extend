class nmap {
  package { "nmap":
    ensure  => installed,
    require => Class['apt']
  }

}

