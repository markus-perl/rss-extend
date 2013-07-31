Vagrant.configure("2") do |config|

    # Every Vagrant virtual environment requires a box to build off of.
    config.vm.box = "mex_v4"
    config.vm.box_url = "https://dl.dropboxusercontent.com/u/32252351/mex_v4.box"

    config.vm.provider :virtualbox do |vb|
        vb.customize ["modifyvm", :id, "--memory", "512"]
        vb.customize ["modifyvm", :id, "--nestedpaging", "off"]
        vb.customize ["modifyvm", :id, "--cpus", "2"]
        # vb.gui = true
    end

    config.vm.network :private_network, ip: "33.33.33.10"
    config.vm.synced_folder ".", "/vagrant", :nfs => true

	#Forward a port from the guest to the host, which allows for outside computers to access the VM, whereas host only networking does not.
    config.vm.network :forwarded_port, guest: 80, host: 8080        #nginx

    # Puppet provision
    config.vm.provision :puppet, :module_path => "puppet/modules" do |puppet|
        puppet.manifests_path = "puppet/manifests"
        puppet.manifest_file  = "vm.box.pp"
    end

end
