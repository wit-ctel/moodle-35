# Load DSL and set up stages
require "capistrano/setup"

# Include default deployment tasks
require "capistrano/deploy"

require 'capistrano/git'

# Load custom tasks from `lib/capistrano/tasks` if you have any defined
Dir.glob("config/lib/capistrano/tasks/*.cap").each { |r| import r }
