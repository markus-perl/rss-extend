class zf2 {

  nginx::site_enabled { 'default':
    source  => "puppet:///modules/zf2/nginx/default",
  }

}
