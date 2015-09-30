class bash {

  file { "/root/.profile":
    owner   => root,
    group   => root,
    mode    => 555,
    source  => "puppet:///modules/bash/profile",
  }


  if (file_exists("/home/vagrant") == 1)  {
    file { "/home/vagrant/.profile":
      owner   => vagrant,
      group   => vagrant,
      mode    => 555,
      source  => "puppet:///modules/bash/profile",
    }
  }

  if ! defined(File["/etc/motd"]) {
    file { "/etc/motd":
      content => "Welcome back master. What can I do for you?";
    }
  }

  file { "/var/mail/vagrant":
    ensure => absent
  }
}

