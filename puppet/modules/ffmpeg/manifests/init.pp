class ffmpeg {

  package { "libav-tools":
    ensure  => installed,
    require => Class["apt"],
  }

  package { "atomicparsley":
    ensure  => installed,
    require => Class["apt"],
  }

}