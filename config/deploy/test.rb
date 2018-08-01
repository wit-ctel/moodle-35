set :stage, :test
set :deploy_to, "/var/www/moodletest.wit.ie"
set :branch, ENV["BRANCH_NAME"] || "develop"

#server 'example.com', user: 'deploy', roles: %w{web app}, my_property: :my_value
server 'test-moodle-web1.heanet.ie', user: 'moodle', roles: %w{ web app admin }
server 'test-moodle-web2.heanet.ie', user: 'moodle', roles: %w{ web app }
server 'test-moodle-web3.heanet.ie', user: 'moodle', roles: %w{ web app }
server 'test-moodle-web4.heanet.ie', user: 'moodle', roles: %w{ web app }


