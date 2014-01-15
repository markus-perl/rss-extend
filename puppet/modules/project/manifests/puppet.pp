
class project::puppet {

	file { "/usr/bin/puppet-apply":
      owner => root,
	  group => root,
	  mode => 555,
	  source => "puppet:///modules/project/puppet-apply.sh"
	}

}
