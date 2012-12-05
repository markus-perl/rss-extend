
class project {
	class { 'project::apt': }
	class { 'project::puppet': }
	class { 'project::php': }
	class { 'project::nginx': }
    class { 'project::bash': }
    class { 'project::composer': }
}
