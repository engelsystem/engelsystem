# -*- mode: ruby -*-
# vi: set ft=ruby :

require 'yaml'

Vagrant.require_version ">= 1.5"

if Vagrant.has_plugin?('vagrant-vbguest')
  class GuestAdditionsFixer < VagrantVbguest::Installers::Ubuntu
    def install(opts=nil, &block)
      super
      communicate.sudo('([ -e /opt/VBoxGuestAdditions-4.3.10 ] && sudo ln -s /opt/VBoxGuestAdditions-4.3.10/lib/VBoxGuestAdditions /usr/lib/VBoxGuestAdditions) || true')
    end
  end
end

cnf = {}

configdir = Dir.glob('*/vagrant-cfg', File::FNM_DOTMATCH)[0]

if not configdir
  abort 'Run vagrant/bootstrap.sh before running vagrant! (vagrant-cfg does not exit)'
end

basedir    = File.absolute_path(File.dirname(configdir))
vagrantdir = File.absolute_path(File.dirname(configdir) == '..' ? '.' : 'vagrant')

configs = [['common.yaml'], ['dev', 'common.yaml'], ['local', 'common.yaml']]
configs.each do |config|
  configfn = File.join(configdir, *config)
  if File.exist?(configfn)
    cnf = cnf.merge(YAML::load(File.open(configfn)))
  end
end

Vagrant.configure("2") do |config|
  config.vm.box = cnf['box_name']
  config.vm.hostname = cnf['vhost']

  # Use vagrant-hostmanager if installed
  if Vagrant.has_plugin?('vagrant-hostmanager')
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = (not File.exist?('/etc/NIXOS'))
    config.hostmanager.include_offline = true
    if cnf['vhost_aliases'].nil?
      cnf['vhost_aliases'] = ["hhvm.#{cnf['vhost']}"]
    end
    config.hostmanager.aliases = cnf['vhost_aliases']
  end

  if Vagrant.has_plugin?('vagrant-vbguest')
    config.vbguest.installer = GuestAdditionsFixer
  end

  # Use vagrant-cachier if installed
  if Vagrant.has_plugin?('vagrant-cachier')
    config.cache.scope = :box
    config.cache.auto_detect = true
  end

  # If vagrant-proxyconf is installed and proxy is configured set this proxy.
  if Vagrant.has_plugin?('vagrant-proxyconf') && cnf.has_key?('proxy') && cnf.has_key?('no-proxy')
    if !cnf['proxy'].nil? && !cnf['proxy'].empty?
      config.proxy.http = cnf['proxy']
      config.proxy.https = cnf['proxy']
      if !cnf['no-proxy'].nil? && !cnf['no-proxy'].empty?
        config.proxy.no_proxy = cnf['no-proxy']
      end
    end
  end

  config.vm.provider :virtualbox do |vb, override|
    cnf['box_cpus'] = 2 if cnf['box_cpus'].nil?
    cnf['box_memory'] = 1024 if cnf['box_memory'].nil?

    vb.customize ['modifyvm', :id, '--cpus', cnf['box_cpus']]
    vb.customize ['modifyvm', :id, '--memory', cnf['box_memory']]
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]

    override.vm.network :private_network, :ip => cnf['ip']
  end

  # no nfs on lxc
  config.vm.provider :lxc do |vb, override|
    override.vm.synced_folder "#{basedir}/", cnf['path'], :nfs => false
    override.vm.synced_folder basedir, '/vagrant', :nfs => false
  end

  config.vm.synced_folder "#{basedir}/", cnf['path'], :nfs => cnf['nfs']
  config.vm.synced_folder basedir, '/vagrant', :nfs => cnf['nfs']


  # Install r10k using the shell provisioner and download the Puppet modules
  config.vm.provision :puppet do |puppet|
    puppet.manifests_path = File.join(vagrantdir, 'manifests')
    puppet.manifest_file  = 'bootstrap.pp'
    puppet.options        = ['--verbose']
  end

  config.vm.provision :hostmanager if Vagrant.has_plugin?('vagrant-hostmanager')

  config.vm.provision :puppet do |puppet|
    puppet.manifests_path    = File.join(vagrantdir, 'manifests')
    puppet.manifest_file     = 'site.pp'
    puppet.module_path       = ['modules', 'site'].map { |dir| File.join(vagrantdir, dir) }
    puppet.options           = ["--graphdir=/vagrant/vagrant/graphs --graph --environment dev"] if not ENV["VAGRANT_PUPPET_DEBUG"]
    puppet.options           = ["--debug --graphdir=/vagrant/vagrant/graphs --graph --environment dev"] if ENV["VAGRANT_PUPPET_DEBUG"]
    puppet.hiera_config_path = File.join(vagrantdir, 'hiera.yaml')
  end
end
