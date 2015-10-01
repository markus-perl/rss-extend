class youtube_dl {

  $downloadUrl = "https://yt-dl.org/latest/youtube-dl"
  $target = "/usr/bin/youtube-dl"

  exec { "youtube_dl":
    require => [Class["nginx"], Class["php"]],
    command => "/usr/bin/wget --no-verbose -q -O ${target} '${ downloadUrl }'",
    unless  => "/usr/bin/test -f ${target}",
    timeout => 120,
  }

  file { "$target":
    require  => Exec["youtube_dl"],
    owner    => root,
    group    => root,
    mode     => 555,
  }

}