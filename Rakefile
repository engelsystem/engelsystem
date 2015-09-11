require 'rake'
require 'rubygems'

require 'puppet-lint/tasks/puppet-lint'
require 'rspec/core/rake_task'

PuppetLint.configuration.ignore_paths = [
  'modules/**/*',
  'vendor/**/*'
]

PuppetLint.configuration.log_format = '%{path}:%{linenumber}:%{check}:%{KIND}:%{message}'

PuppetLint.configuration.send('disable_documentation')
PuppetLint.configuration.send('disable_80chars')

manifest_patterns = ['site/**/*.pp', 'manifests/*.pp']

task :validate do
  pipe = IO.popen('xargs puppet parser validate --parser future', 'w')
  manifest_patterns.each do |pat|
    Dir[pat].each do |path|
      pipe.puts path
    end
  end
  pipe.close_write
end

task :default => [:validate, :lint]
