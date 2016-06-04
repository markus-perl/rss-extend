class zf2 {

  nginx::site_enabled { "default":
    source  => "puppet:///modules/zf2/nginx/default",
  }

  nginx::user { "vagrant":
  }

  php::user { "vagrant":
  }

}
