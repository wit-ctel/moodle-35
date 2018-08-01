set :stage, :edge
set :deploy_to, "/var/www/edge.moodle.wit.ie/"
set :branch, ENV["BRANCH_NAME"] || "master"

server 'wit-mdl-web1-1718.hst.heanet.ie', user: 'moodle', roles: %w{ web app admin }
#server 'wit-moodle-web2.heanet.ie', user: 'moodle', roles: %w{ web }
#server 'wit-moodle-web3.heanet.ie', user: 'moodle', roles: %w{ web }
#server 'wit-moodle-web4.heanet.ie', user: 'moodle', roles: %w{ web }