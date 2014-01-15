#!/bin/bash

cd /vagrant/puppet/manifests/ && sudo puppet  apply vm.box.pp --modulepath=../modules/