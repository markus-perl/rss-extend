node default {
  include apt
  include bash
  include php7
  include nginx
  include composer
  include zf2
  include youtube_dl
  include ffmpeg
  include ant

  locale::enable { 'de_DE': }

}