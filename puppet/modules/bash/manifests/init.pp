class bash {

  file { "/root/.profile":
    owner   => root,
    group   => root,
    mode    => 555,
    source  => "puppet:///modules/bash/profile",
  }

  file { "/home/vagrant/.profile":
    owner   => vagrant,
    group   => vagrant,
    mode    => 555,
    source  => "puppet:///modules/bash/profile",
  }

  file { '/etc/motd':
    content => "Welcome back master. What can I do for you?";
  }
}

